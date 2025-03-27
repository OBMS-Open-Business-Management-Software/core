<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CRM\Category\LeadCategory;
use App\Models\CRM\Category\LeadCategoryAssignment;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadContact;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminCRMController extends Controller
{
    public function crm_index(): Renderable
    {
        return view('admin.crm.home');
    }

    public function crm_index_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = Lead::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('phone', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('address', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('city', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('state', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('zip', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('country', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('notes', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';

                        break;
                    case 'email':
                        $orderBy = 'email';

                        break;
                    case 'phone':
                        $orderBy = 'phone';

                        break;
                    case 'country':
                        $orderBy = 'country';

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
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (LeadCategory $category) {
                    $edit = '';

                    return (object) [
                        'id'          => $category->id,
                        'name'        => $category->name,
                        'email'       => $category->email,
                        'phone'       => $category->phone,
                        'country'     => $category->country,
                        'notes'       => $category->notes,
                        'edit'        => $edit,
                        'delete'      => '<a href="' . route('admin.crm.categories.delete', $category->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    public function crm_details(): Renderable
    {
        return view('admin.crm.details');
    }

    public function crm_details_list(string $id, Request $request): JsonResponse
    {
        session_write_close();

        $query = LeadContact::where('lead_id', '=', $id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('phone', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('notes', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';

                        break;
                    case 'email':
                        $orderBy = 'email';

                        break;
                    case 'phone':
                        $orderBy = 'phone';

                        break;
                    case 'role':
                        $orderBy = 'role';

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

        // TODO: Render customer list

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (LeadContact $contact) {
                    $edit = '';

                    return (object) [
                        'id'          => $contact->id,
                        'name'        => $contact->name,
                        'email'       => $contact->email,
                        'phone'       => $contact->phone,
                        'role'        => $contact->role,
                        'notes'       => $contact->notes,
                        'edit'        => $edit,
                        'delete'      => '<a href="' . route('admin.crm.contacts.delete', $contact->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    public function crm_contacts_add(string $lead_id, Request $request)
    {
        Validator::make([
            'lead_id'     => $lead_id,
            ...$request->toArray(),
        ], [
            'lead_id'     => ['required', 'integer'],
            'name'        => ['required', 'string'],
            'email'       => ['required', 'string'],
            'phone'       => ['required', 'string'],
            'roles'       => ['required', 'string'],
            'notes'       => ['required', 'string'],
        ])->validate();

        $isFirst = LeadContact::where('lead_id', '=', $lead_id)->first() === null;

        if (
            LeadContact::create([
                'lead_id'     => $request->lead_id,
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'roles'       => $request->roles,
                'notes'       => $request->notes,
            ])
        ) {
            return redirect()->back()->with('success', __('interface.messages.lead_contact_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    public function crm_contacts_update(string $lead_id, string $contact_id, Request $request)
    {
        Validator::make([
            'lead_id'     => $lead_id,
            'contact_id'  => $contact_id,
            ...$request->toArray(),
        ], [
            'lead_id'     => ['required', 'integer'],
            'contact_id'  => ['required', 'integer'],
            'name'        => ['required', 'string'],
            'email'       => ['required', 'string'],
            'phone'       => ['required', 'string'],
            'roles'       => ['required', 'string'],
            'notes'       => ['required', 'string'],
        ])->validate();

        /* @var LeadContact $contact */
        if (! empty($contact = LeadContact::find($contact_id))) {
            $contact->update([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'roles'       => $request->roles,
                'notes'       => $request->notes,
            ]);

            return redirect()->back()->with('success', __('interface.messages.lead_contact_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    public function crm_contacts_delete(string $lead_id, string $contact_id)
    {
        Validator::make([
            'lead_id' => $lead_id,
            'contact_id' => $contact_id,
        ], [
            'lead_id' => ['required', 'integer'],
            'contact_id' => ['required', 'integer'],
        ])->validate();

        LeadContact::where('id', '=', $id)->delete();

        return redirect()->back()->with('success', __('interface.messages.lead_contact_deleted'));
    }

    /**
     * Show list of crm categories.
     *
     * @return Renderable
     */
    public function crm_categories(): Renderable
    {
        return view('admin.crm.categories');
    }

    /**
     * Get list of crm categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function crm_categories_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = LeadCategory::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'name';

                        break;
                    case 'description':
                        $orderBy = 'description';

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
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (LeadCategory $category) {
                    $edit = '
<a class="btn btn-warning btn-sm w-100" data-toggle="modal" data-target="#edit' . $category->id . '" data-type="edit" data-category="' . $category->id . '" data-table="#category-leads-' . $category->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="edit' . $category->id . '" tabindex="-1" aria-labelledby="edit' . $category->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="edit' . $category->id . 'Label">' . __('interface.actions.edit') . ' (' . $category->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <form action="' . route('admin.crm.categories.update', $category->id) . '" method="post">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="category_id" value="' . $category->id . '" />

                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $category->name . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $category->description . '">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                </form>
                <div class="my-4">
                    <table id="category-leads-' . $category->id . '" class="table">
                        <thead>
                            <tr>
                                <td>' . __('interface.data.lead') . '</td>
                                <td>' . __('interface.actions.delete') . '</td>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <form action="' . route('admin.crm.categories.lead.add', $category->id) . '" method="post">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="category_id" value="' . $category->id . '" />

                    <div class="form-group row">
                        <label for="lead_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.lead_id') . '</label>

                        <div class="col-md-8">
                            <input id="lead_id" type="number" min="1" class="form-control" name="lead_id">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-link"></i> ' . __('interface.data.link') . '</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
            </div>
        </div>
    </div>
</div>
';

                    return (object) [
                        'id'          => $category->id,
                        'name'        => $category->name,
                        'description' => $category->description,
                        'edit'        => $edit,
                        'delete'      => '<a href="' . route('admin.crm.categories.delete', $category->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Add a crm category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function crm_categories_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
        ])->validate();

        if (
            LeadCategory::create([
                'name'        => $request->name,
                'description' => $request->description,
            ])
        ) {
            return redirect()->back()->with('success', __('interface.messages.lead_category_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update a crm category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function crm_categories_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id' => ['required', 'integer'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
        ])->validate();

        /* @var LeadCategory $category */
        if (! empty($category = LeadCategory::find($request->category_id))) {
            $category->update([
                'name'        => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', __('interface.messages.lead_category_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a crm category.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function crm_categories_delete(int $id): RedirectResponse
    {
        Validator::make([
            'category_id' => $id,
        ], [
            'category_id' => ['required', 'integer'],
        ])->validate();

        LeadCategory::where('id', '=', $id)->delete();

        return redirect()->back()->with('success', __('interface.messages.lead_category_deleted'));
    }

    /**
     * Get list of crm category leads.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function crm_category_lead_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = LeadCategory::find($request->id)->assignments();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->whereHas('assignments', function (Builder $builder) use ($request) {
                    return $builder->whereHas('lead', function (Builder $builder) use ($request) {
                        return $builder->where('name', 'LIKE', '%' . $request->search['value'] . '%')
                            ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%');
                    });
                });
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
                        $orderBy = 'lead_id';

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
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (LeadCategoryAssignment $assignment) {
                    return (object) [
                        'name'   => $assignment->lead->name,
                        'delete' => '<a href="' . route('admin.crm.categories.lead.delete', ['id' => $assignment->category_id, 'category_link_id' => $assignment->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Add a crm category lead link.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function crm_category_lead_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id' => ['required', 'integer'],
            'lead_id'     => ['required', 'integer'],
        ])->validate();

        /* @var Lead|null $lead */
        if (! empty($lead = Lead::find($request->lead_id))) {
            LeadCategoryAssignment::create([
                'category_id' => $request->category_id,
                'lead_id'     => $lead->id,
            ]);

            return redirect()->back()->with('success', __('interface.messages.lead_category_lead_link_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a crm category lead link.
     *
     * @param int $category_id
     * @param int $category_link_id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function crm_category_lead_delete(int $category_id, int $category_link_id): RedirectResponse
    {
        Validator::make([
            'category_link_id' => $category_link_id,
        ], [
            'category_link_id' => ['required', 'integer'],
        ])->validate();

        LeadCategoryAssignment::where('id', '=', $category_link_id)->delete();

        return redirect()->back()->with('success', __('interface.messages.lead_category_lead_link_deleted'));
    }
}
