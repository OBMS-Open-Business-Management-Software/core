<?php

namespace App\Http\Controllers;

use App\Models\Content\Page;
use App\Models\Content\PageVersion;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminPageController extends Controller
{
    /**
     * Show list of pages.
     *
     * @return Renderable
     */
    public function page_index(): Renderable
    {
        return view('admin.page.home');
    }

    /**
     * Get list of pages.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function page_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Page::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('route', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('title', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('must_accept', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('navigation_item', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'route':
                        $orderBy = 'route';
                        break;
                    case 'title':
                        $orderBy = 'title';
                        break;
                    case 'must_accept':
                        $orderBy = 'must_accept';
                        break;
                    case 'navigation_item':
                        $orderBy = 'navigation_item';
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

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (Page $page) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPage' . $page->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPage' . $page->id . '" tabindex="-1" aria-labelledby="editPage' . $page->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPage' . $page->id . 'Label">' . __('interface.actions.edit') . ' (' . __($page->title) . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.pages.update', $page->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="page_id" value="' . $page->id . '" />
                    <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">' . __('interface.data.route') . '</label>

                            <div class="col-md-8">
                                <input id="route" type="text" class="form-control" name="route" value="' . $page->route . '">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="title" class="col-md-4 col-form-label text-md-right">' . __('interface.data.title') . '</label>

                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control" name="title" value="' . $page->title . '">
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="must_accept" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.must_be_accepted') . '</label>

                            <div class="col-md-8">
                                <input id="must_accept" type="checkbox" class="form-control" name="must_accept" value="true"' . ($page->must_accept ? ' checked' : '') . '>
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="navigation_item" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.display_in_navigation') . '</label>

                            <div class="col-md-8">
                                <input id="navigation_item" type="checkbox" class="form-control" name="navigation_item" value="true"' . ($page->navigation_item ? ' checked' : '') . '>
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
                        'id' => $page->id,
                        'title' => __($page->title),
                        'route' => $page->route,
                        'must_accept' => $page->must_accept ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'navigation_item' => $page->navigation_item ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'view' => '<a href="' . route('admin.pages.details', $page->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit' => $edit,
                        'delete' => $page->acceptance->isEmpty() ? '<a href="' . route('admin.invoices.customers.delete', $page->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                    ];
                })
        ]);
    }

    /**
     * Create a new page.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'route' => ['required', 'string'],
            'title' => ['required', 'string'],
            'page_content' => ['required', 'string'],
            'must_accept' => ['nullable', 'string'],
            'navigation_item' => ['nullable', 'string'],
        ])->validate();

        if (
            ! empty(
                $page = Page::create([
                    'route' => $request->route,
                    'title' => $request->title,
                    'must_accept' => ! empty($request->must_accept),
                    'navigation_item' => ! empty($request->navigation_item),
                ])
            )
        ) {
            if (
                ! empty(
                    $version = PageVersion::create([
                        'page_id' => $page->id,
                        'user_id' => Auth::id(),
                        'content' => $request->page_content,
                    ])
                )
            ) {
                if (! empty($request->must_accept)) {
                    // TODO: Send an email to customers that they have to accept the new terms
                }

                return redirect()->back()->with('success', __('interface.messages.page_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing page.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'page_id' => ['required', 'integer'],
            'route' => ['required', 'string'],
            'title' => ['required', 'string'],
            'must_accept' => ['nullable', 'string'],
            'navigation_item' => ['nullable', 'string'],
        ])->validate();

        if (! empty($page = Page::find($request->page_id))) {
            $page->update([
                'route' => $request->route,
                'title' => $request->title,
                'must_accept' => ! empty($request->must_accept),
                'navigation_item' => ! empty($request->navigation_item),
            ]);

            return redirect()->back()->with('success', __('interface.messages.page_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing page.
     *
     * @param int $id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_delete(int $id): RedirectResponse
    {
        Validator::make([
            'page_id' => $id,
        ], [
            'page_id' => ['required', 'integer'],
        ])->validate();

        /* @var Page $page */
        if (
            ! empty($page = Page::find($id)) &&
            $page->acceptance->isEmpty()
        ) {
            $page->versions()->delete();
            $page->delete();

            return redirect()->route('admin.invoices.customers')->with('success', __('interface.messages.page_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of page versions.
     *
     * @param int $id
     *
     * @return Renderable
     */
    public function page_details(int $id): Renderable
    {
        return view('admin.page.details', [
            'page' => Page::find($id),
        ]);
    }

    /**
     * Get list of page versions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function page_version_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = PageVersion::where('page_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('content', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'content':
                        $orderBy = 'content';
                        break;
                    case 'created_at':
                        $orderBy = 'created_at';
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

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (PageVersion $version) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPageVersion' . $version->id . '" xmlns="http://www.w3.org/1999/html"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editPageVersion' . $version->id . '" tabindex="-1" aria-labelledby="editPageVersion' . $version->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editPageVersion' . $version->id . 'Label">' . __('interface.actions.edit') . ' (#' . __($version->id) . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="' . route('admin.pages.versions.update', ['id' => $version->page_id, 'version_id' => $version->id]) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="page_version_id" value="' . $version->id . '" />
                    <div class="form-group row">
                        <label for="page_content" class="col-md-4 col-form-label text-md-right">' . __('interface.data.content') . '</label>

                        <div class="col-md-8">
                            <textarea id="page_content" type="text" class="form-control" name="page_content"' . ($version->acceptance->isNotEmpty() ? ' readonly' : '') . '>' . $version->content . '</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning"' . ($version->acceptance->isNotEmpty() ? ' disabled' : '') . '><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

                    return (object) [
                        'id' => $version->id,
                        'created_at' => $version->created_at->format('d.m.Y, H:i'),
                        'content' => substr($version->content, 0, 100) . (strlen($version->content) > 100 ? '...' : ''),
                        'acceptance' => $version->acceptance()->count(),
                        'view' => '<a href="' . route('cms.page.' . $version->page_id . '.version', $version->id) . '" class="btn btn-primary btn-sm" target="_blank"><i class="bi bi-eye"></i></a>',
                        'edit' => $edit,
                        'delete' => $version->acceptance->isEmpty() ? '<a href="' . route('admin.pages.versions.delete', ['id' => $version->page_id, 'version_id' => $version->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                    ];
                })
        ]);
    }

    /**
     * Create a new page version.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_version_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'page_id' => ['required', 'integer'],
            'page_content' => ['required', 'string'],
        ])->validate();

        if (! empty($page = Page::find($request->page_id))) {
            if (
                ! empty(
                    $version = PageVersion::create([
                        'page_id' => $page->id,
                        'user_id' => Auth::id(),
                        'content' => $request->page_content,
                    ])
                )
            ) {
                if ($page->must_accept) {
                    // TODO: Send an email to customers that they have to accept the new terms
                }

                return redirect()->back()->with('success', __('interface.messages.page_version_added'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an existing page version.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_version_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'page_version_id' => ['required', 'integer'],
            'page_content' => ['required', 'string'],
        ])->validate();

        if (! empty($version = PageVersion::find($request->page_version_id))) {
            $version->update([
                'user_id' => Auth::id(),
                'content' => $request->page_content,
            ]);

            return redirect()->back()->with('success', __('interface.messages.page_version_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing page version.
     *
     * @param int $id
     * @param int $version_id
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function page_version_delete(int $id, int $version_id): RedirectResponse
    {
        Validator::make([
            'page_id' => $id,
            'page_version_id' => $id,
        ], [
            'page_id' => ['required', 'integer'],
            'page_version_id' => ['required', 'integer'],
        ])->validate();

        /* @var PageVersion $version */
        if (! empty($version = PageVersion::find($version_id))) {
            $version->delete();

            return redirect()->back()->with('success', __('interface.messages.page_version_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
