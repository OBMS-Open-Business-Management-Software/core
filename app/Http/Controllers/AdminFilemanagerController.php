<?php

namespace App\Http\Controllers;

use App\Helpers\Download;
use App\Models\FileManager\File;
use App\Models\FileManager\Folder;
use App\Webdav\Authentication;
use App\Webdav\Directory as WebdavDirectory;
use App\Webdav\Locks;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Exception;
use Sabre\DAV\Locks\Plugin as LocksPlugin;
use Sabre\DAV\Server;

class AdminFilemanagerController extends Controller
{
    /**
     * Show list of files and folders.
     *
     * @param int|null $folder_id
     *
     * @return Renderable
     */
    public function filemanager_index(?int $folder_id = null): Renderable
    {
        if (
            ! empty($folder_id) &&
            ! empty($folder = Folder::find($folder_id))
        ) {
            if (! empty($folder->parent)) {
                $parent = $folder->parent->id;
            } else {
                $parent = 0;
            }
        }

        return view('admin.filemanager.home', [
            'parent' => $parent ?? null,
            'folder' => $folder ?? null,
        ]);
    }

    /**
     * Get list of profile email addresses.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function filemanager_list(Request $request): JsonResponse
    {
        session_write_close();

        $query_folders = Folder::where(function (Builder $builder) {
            return $builder->where('user_id', '=', Auth::id())
                ->orWhereNull('user_id');
        });

        if ($request->id > 0) {
            $query_folders = $query_folders->where('parent_id', '=', $request->id);
        } else {
            $query_folders = $query_folders->where(function (Builder $builder) use ($request) {
                return $builder->where('parent_id', '=', $request->id)
                    ->orWhereNull('parent_id');
            });
        }

        $query_folders = $query_folders->selectRaw('id, user_id, name, \'folder\' AS type, \'\' AS size, \'\' AS mime');

        $query_files = File::where(function (Builder $builder) {
            return $builder->where('user_id', '=', Auth::id())
                ->orWhereNull('user_id');
        });

        if ($request->id > 0) {
            $query_files = $query_files->where('folder_id', '=', $request->id);
        } else {
            $query_files = $query_files->where(function (Builder $builder) use ($request) {
                return $builder->where('folder_id', '=', $request->id)
                    ->orWhereNull('folder_id');
            });
        }

        $query_files = $query_files->selectRaw('id, user_id, name, \'file\' AS type, size, mime');

        $totalCount = (clone $query_folders)->union(clone $query_files)->count();

        if (! empty($request->search['value'])) {
            $query_folders = $query_folders->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%');
            });

            $query_files = $query_files->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        $query = $query_folders->union($query_files);

        $query = $query->orderByDesc('type');

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';
                        break;
                    case 'id':
                    default:
                        $orderBy = 'id';
                        break;
                }

                $query = $query->orderBy($orderBy, $order['dir']);
            }
        }

        $filteredCount = (clone $query)->count();

        $query = $query->offset($request->start)
            ->limit($request->length);

        /* @var Folder|File $fileOrFolder */
        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function ($fileOrFolder) use ($totalCount) {
                    if ($fileOrFolder->type == 'folder') {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editFolder' . $fileOrFolder->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editFolder' . $fileOrFolder->id . '" tabindex="-1" aria-labelledby="editFolder' . $fileOrFolder->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editFolder' . $fileOrFolder->id . 'Label">' . __('interface.actions.edit') . ' (' . $fileOrFolder->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.filemanager.folder.update', $fileOrFolder->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="folder_id" value="' . $fileOrFolder->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $fileOrFolder->name . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="private" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.private') . '</label>

                        <div class="col-md-8">
                            <input id="private" type="checkbox" class="form-control" name="private" value="true" ' . ($fileOrFolder->user_id > 0 ? 'checked' : '') . '>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'icon' => '<i class="bi bi-folder"></i>',
                            'name' => $fileOrFolder->name,
                            'private' => $fileOrFolder->user_id > 0 ? '<i class="bi bi-lock"></i>' : '',
                            'size' => $fileOrFolder->folderSize . 'B',
                            'edit' => $edit,
                            'action' => '<a href="' . route('admin.filemanager.folder', $fileOrFolder->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                            'delete' => '<a href="' . route('admin.filemanager.folder.delete', $fileOrFolder->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                        ];
                    } elseif ($fileOrFolder->type == 'file') {
                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editFile' . $fileOrFolder->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editFile' . $fileOrFolder->id . '" tabindex="-1" aria-labelledby="editFile' . $fileOrFolder->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editFile' . $fileOrFolder->id . 'Label">' . __('interface.actions.edit') . ' (' . $fileOrFolder->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.filemanager.file.update', $fileOrFolder->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="file_id" value="' . $fileOrFolder->id . '" />
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $fileOrFolder->name . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="private" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.private') . '</label>

                        <div class="col-md-8">
                            <input id="private" type="checkbox" class="form-control" name="private" value="true" ' . ($fileOrFolder->user_id > 0 ? 'checked' : '') . '>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                        return (object) [
                            'icon' => '<i class="bi bi-file-earmark"></i> ' . (! empty($fileOrFolder->mime) ? '<span class="badge badge-secondary ml-2 font-weight-normal">' . $fileOrFolder->mime . '</span>' : ''),
                            'name' => $fileOrFolder->name,
                            'private' => $fileOrFolder->user_id > 0 ? '<i class="bi bi-lock"></i>' : '',
                            'size' => $fileOrFolder->size . 'B',
                            'edit' => $edit,
                            'action' => '<a href="' . route('admin.filemanager.file.download', $fileOrFolder->id) . '" class="btn btn-warning btn-sm" download><i class="bi bi-download"></i></a>',
                            'delete' => '<a href="' . route('admin.filemanager.file.delete', $fileOrFolder->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                        ];
                    }

                    return null;
                })
                ->reject(function ($fileOrFolder) {
                    return ! isset($fileOrFolder);
                })
        ]);
    }

    /**
     * Create a new folder.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_folder_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'folder_id' => ['integer', 'nullable'],
            'name' => ['required', 'string', 'max:255'],
            'private' => ['string', 'nullable'],
        ])->validate();

        if (
            ! Folder::where('name', '=', $request->name)
                ->where('parent_id', '=', $request->folder_id)
                ->exists() &&
            ! File::where('name', '=', $request->name)
                ->where('folder_id', '=', $request->folder_id)
                ->exists()
        ) {
            Folder::create([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'parent_id' => $request->folder_id,
                'name' => $request->name,
            ]);

            return redirect()->back()->with('success', 'The folder has been created successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing folder.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_folder_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'folder_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'private' => ['string', 'nullable'],
        ])->validate();

        if (
            ! empty($folder = Folder::find($request->folder_id)) &&
            (
                empty($folder->user_id) ||
                $folder->user_id == 0 ||
                $folder->user_id == Auth::id()
            ) &&
            ! Folder::where('name', '=', $folder->name)
                ->where('id', '!=', $folder->id)
                ->exists()
        ) {
            $folder->update([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'name' => $request->name,
            ]);

            return redirect()->back()->with('success', 'The folder has been updated successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing folder.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_folder_delete(int $id): RedirectResponse
    {
        Validator::make([
            'folder_id' => $id,
        ], [
            'folder_id' => ['integer', 'required'],
        ])->validate();

        if (
            ! empty($folder = Folder::find($id)) &&
            (
                empty($folder->user_id) ||
                $folder->user_id == 0 ||
                $folder->user_id == Auth::id()
            )
        ) {
            $folder->recursiveDelete();

            return redirect()->back()->with('success', 'The file has been deleted successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Upload and create a new file.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_file_create(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'folder_id' => ['integer', 'nullable'],
            'private' => ['string', 'nullable'],
        ])->validate();

        /* @var UploadedFile|null $file */
        if (
            ! empty($file = $request->files->get('file')) &&
            ! Folder::where('name', '=', $request->name)
                ->where('parent_id', '=', $request->folder_id)
                ->exists() &&
            ! File::where('name', '=', $request->name)
                ->where('folder_id', '=', $request->folder_id)
                ->exists()
        ) {
            File::create([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'folder_id' => $request->folder_id,
                'name' => $file->getClientOriginalName(),
                'data' => $file->getContent(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            return redirect()->back()->with('success', 'The file has been uploaded successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing file.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_file_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'file_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'private' => ['string', 'nullable'],
        ])->validate();

        if (
            ! empty($file = File::find($request->file_id)) &&
            (
                empty($file->user_id) ||
                $file->user_id == 0 ||
                $file->user_id == Auth::id()
            ) &&
            ! File::where('folder_id', '=', $file->folder_id)
                ->where('name', '=', $file->name)
                ->where('id', '!=', $file->id)
                ->exists()
        ) {
            $file->update([
                'user_id' => ! empty($request->private) ? Auth::id() : null,
                'name' => $request->name,
            ]);

            return redirect()->back()->with('success', 'The file has been updated successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing file.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_file_delete(int $id): RedirectResponse
    {
        Validator::make([
            'file_id' => $id,
        ], [
            'file_id' => ['integer', 'required'],
        ])->validate();

        if (
            ! empty($file = File::find($id)) &&
            (
                empty($file->user_id) ||
                $file->user_id == 0 ||
                $file->user_id == Auth::id()
            )
        ) {
            $file->delete();

            return redirect()->back()->with('success', 'The file has been deleted successfully.');
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Download an existing file.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function filemanager_file_download(int $id): RedirectResponse
    {
        Validator::make([
            'file_id' => $id,
        ], [
            'file_id' => ['integer', 'required'],
        ])->validate();

        if (
            ! empty($file = File::find($id)) &&
            (
                empty($file->user_id) ||
                $file->user_id == 0 ||
                $file->user_id == Auth::id()
            )
        ) {
            $file = $file->makeVisible('data');

            Download::prepare($file->name)
                ->data($file->data)
                ->output();
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Download an existing file.
     *
     * @param string $path
     */
    public function filemanager_file_webdav(string $path = ''): void
    {
        $publicDir = new WebdavDirectory();

        $authBackend = new Authentication();
        $authBackend->setRealm('WebDAV');
        $authPlugin = new AuthPlugin($authBackend);

        $locksBackend = new Locks();
        $locksPlugin = new LocksPlugin($locksBackend);

        try {
            $server = new Server($publicDir);
            $server->setBaseUri('/api/webdav');
            $server->addPlugin($authPlugin);
            $server->addPlugin($locksPlugin);
            $server->start();
        } catch (Exception $e) {
        }
    }
}
