<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Products;
use App\Models\Accounting\Contract\ContractType;
use App\Models\Accounting\Prepaid\PrepaidHistory;
use App\Models\ProductSetting;
use App\Models\Shop\Configurator\ShopConfiguratorCategory;
use App\Models\Shop\Configurator\ShopConfiguratorField;
use App\Models\Shop\Configurator\ShopConfiguratorFieldOption;
use App\Models\Shop\Configurator\ShopConfiguratorForm;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Shop\OrderQueue\ShopOrderQueueField;
use App\Models\Shop\OrderQueue\ShopOrderQueueHistory;
use App\Models\UsageTracker\Tracker;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminShopController extends Controller
{
    public function shop_categories_index(?int $id = null): Renderable
    {
        return view('admin.shop.category', [
            'category'      => ShopConfiguratorCategory::find($id),
            'contractTypes' => ContractType::all(),
            'trackers'      => Tracker::all(),
        ]);
    }

    /**
     * Get list of shop categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function shop_categories_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ShopConfiguratorCategory::query();

        if (! empty($request->id)) {
            $query = $query->where('category_id', '=', $request->id);
        } else {
            $query = $query->whereNull('category_id');
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('route', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('public', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'route':
                        $orderBy = 'route';

                        break;
                    case 'name':
                        $orderBy = 'name';

                        break;
                    case 'description':
                        $orderBy = 'description';

                        break;
                    case 'public':
                        $orderBy = 'public';

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
                ->transform(function (ShopConfiguratorCategory $category) {
                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCategory' . $category->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editCategory' . $category->id . '" tabindex="-1" aria-labelledby="editCategory' . $category->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editCategory' . $category->id . 'Label">' . __('interface.actions.edit') . ' (' . $category->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form action="' . route('admin.shop.categories.update', $category->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="category_id" value="' . $category->id . '" />
                    <div class="form-group row">
                        <label for="route" class="col-md-4 col-form-label text-md-right">' . __('interface.data.route') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="routePrefix">' . $category->fullRoute . '</span>
                                </div>
                                <input id="route" type="text" class="form-control" name="route" value="' . $category->route . '">
                            </div>
                        </div>
                    </div>
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
                    <div class="form-group row align-items-center">
                        <label for="public" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.category_publicly_visible') . '</label>

                        <div class="col-md-8">
                            <input id="public" type="checkbox" class="form-control" name="public" value="true"' . ($category->public ? ' checked' : '') . '>
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
                        'id'          => $category->id,
                        'route'       => $category->fullRoute,
                        'name'        => $category->name,
                        'description' => $category->description,
                        'public'      => $category->public ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'view'        => '<a href="' . route('admin.shop.categories.details', $category->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit'        => $edit,
                        'delete'      => '<a href="' . route('admin.shop.categories.delete', $category->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Create a new shop category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_categories_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id' => ['nullable', 'integer'],
            'route'       => ['required', 'string'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'public'      => ['nullable', 'string'],
        ])->validate();

        $category = ShopConfiguratorCategory::create([
            'category_id' => ! empty($request->category_id) ? $request->category_id : null,
            'route'       => $request->route,
            'name'        => $request->name,
            'description' => $request->description,
            'public'      => ! empty($request->public),
        ]);

        return redirect()->route('admin.shop.categories.details', $category->id)->with('success', __('interface.messages.shop_category_added'));
    }

    /**
     * Update an existing shop category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_categories_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id' => ['required', 'integer'],
            'route'       => ['required', 'string'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'public'      => ['nullable', 'string'],
        ])->validate();

        /* @var ShopConfiguratorCategory $category */
        if (! empty($category = ShopConfiguratorCategory::find($request->category_id))) {
            $category->update([
                'route'       => $request->route,
                'name'        => $request->name,
                'description' => $request->description,
                'public'      => ! empty($request->public),
            ]);

            return redirect()->back()->with('success', __('interface.messages.shop_category_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing shop category.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_categories_delete(int $id): RedirectResponse
    {
        Validator::make([
            'category_id' => $id,
        ], [
            'category_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopConfiguratorCategory $category */
        if (! empty($category = ShopConfiguratorCategory::find($id))) {
            $category->forms()->delete();
            $category->delete();

            // TODO: Extend, so forms in and subcategories themselves are being deleted as well

            return redirect()->back()->with('success', __('interface.messages.shop_category_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of shop forms.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function shop_forms_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ShopConfiguratorForm::query();

        if (! empty($request->id)) {
            $query = $query->where('category_id', '=', $request->id);
        } else {
            $query = $query->whereNull('category_id');
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('route', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('approval', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('public', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'route':
                        $orderBy = 'route';

                        break;
                    case 'name':
                        $orderBy = 'name';

                        break;
                    case 'description':
                        $orderBy = 'description';

                        break;
                    case 'approval':
                        $orderBy = 'approval';

                        break;
                    case 'public':
                        $orderBy = 'public';

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

        $availableContractTypes = ContractType::all();
        $availableTrackerTypes  = Tracker::all();

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (ShopConfiguratorForm $form) use ($availableContractTypes, $availableTrackerTypes) {
                    $contractTypes = '';

                    $availableContractTypes->each(function (ContractType $contractType) use ($form, &$contractTypes) {
                        $contractTypes .= '<option value="' . $contractType->id . '"' . ($form->contract_type_id == $contractType->id ? ' selected' : '') . '>' . __($contractType->name) . '</option>';
                    });

                    $trackerTypes = '';

                    $availableTrackerTypes->each(function (Tracker $tracker) use ($form, &$trackerTypes) {
                        $trackerTypes .= '<option value="' . $tracker->id . '"' . ($form->tracker_id == $tracker->id ? ' selected' : '') . '>' . __($tracker->name) . '</option>';
                    });

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editForm' . $form->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editForm' . $form->id . '" tabindex="-1" aria-labelledby="editForm' . $form->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editForm' . $form->id . 'Label">' . __('interface.actions.edit') . ' (' . $form->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form action="' . route('admin.shop.forms.update', $form->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="form_id" value="' . $form->id . '" />
                    <div class="form-group row">
                        <label for="route" class="col-md-4 col-form-label text-md-right">' . __('interface.data.route') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="routePrefix">' . (! empty($form) ? $form->fullRoute : '/shop/') . '</span>
                                </div>
                                <input id="route" type="text" class="form-control" name="route" value="' . $form->route . '">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.name') . '</label>

                        <div class="col-md-8">
                            <input id="name" type="text" class="form-control" name="name" value="' . $form->name . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label text-md-right">' . __('interface.data.description') . '</label>

                        <div class="col-md-8">
                            <input id="description" type="text" class="form-control" name="description" value="' . $form->description . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="product_type" class="col-md-4 col-form-label text-md-right">' . __('interface.data.product_type') . '</label>

                        <div class="col-md-8">
                            <input id="product_type" type="text" class="form-control" name="product_type" value="' . $form->product_type . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="vat_type" class="col-md-4 col-form-label text-md-right">' . __('interface.data.vat_type') . '</label>

                        <div class="col-md-8">
                            <select id="vat_type" type="text" class="form-control" name="vat_type">
                                <option value="basic"' . ($form->vat_type == 'basic' ? ' selected' : '') . '>' . __('interface.misc.basic') . '</option>
                                <option value="reduced"' . ($form->vat_type == 'reduced' ? ' selected' : '') . '>' . __('interface.misc.reduced') . '</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="contract_type_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.contract_type') . '</label>

                        <div class="col-md-8">
                            <select id="contract_type_id" type="text" class="form-control" name="contract_type_id">
                                ' . $contractTypes . '
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                            <label for="tracker_id" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.usage_tracker') . '</label>

                            <div class="col-md-8">
                                <select id="tracker_id" type="text" class="form-control" name="tracker_id">
                                    <option value="">' . __('interface.misc.none') . '</option>
                                    ' . $trackerTypes . '
                                </select>
                            </div>
                        </div>
                    <div class="form-group row align-items-center">
                        <label for="approval" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.require_manual_approval') . '</label>

                        <div class="col-md-8">
                            <input id="approval" type="checkbox" class="form-control" name="approval" value="true"' . ($form->approval ? ' checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="public" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.form_publicly_visible') . '</label>

                        <div class="col-md-8">
                            <input id="public" type="checkbox" class="form-control" name="public" value="true"' . ($form->public ? ' checked' : '') . '>
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

                    switch ($form->type) {
                        case 'form':
                            $type = __('interface.data.form');

                            break;
                        case 'package':
                            $type = __('interface.data.package');

                            break;
                        default:
                            $type = __('interface.misc.not_available');

                            break;
                    }

                    return (object) [
                        'id'          => $form->id,
                        'route'       => $form->fullRoute,
                        'type'        => $type,
                        'name'        => $form->name,
                        'description' => $form->description,
                        'approval'    => $form->approval ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'public'      => $form->public ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'view'        => '<a href="' . route('admin.shop.forms', $form->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                        'edit'        => $edit,
                        'delete'      => '<a href="' . route('admin.shop.forms.delete', $form->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Create a new shop form.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_forms_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id'      => ['nullable', 'integer'],
            'tracker_id'       => ['nullable', 'integer'],
            'route'            => ['required', 'string'],
            'type'             => ['required', 'string'],
            'name'             => ['required', 'string'],
            'description'      => ['required', 'string'],
            'approval'         => ['nullable', 'string'],
            'public'           => ['nullable', 'string'],
            'product_type'     => ['required', 'string'],
            'vat_type'         => ['required', 'string'],
            'contract_type_id' => ['required', 'integer'],
        ])->validate();

        $form = ShopConfiguratorForm::create([
            'category_id'      => ! empty($request->category_id) ? $request->category_id : null,
            'tracker_id'       => ! empty($request->tracker_id) ? $request->tracker_id : null,
            'route'            => $request->route,
            'type'             => $request->type,
            'name'             => $request->name,
            'description'      => $request->description,
            'approval'         => ! empty($request->approval),
            'public'           => ! empty($request->public),
            'contract_type_id' => $request->contract_type_id,
            'product_type'     => $request->product_type,
            'vat_type'         => $request->vat_type,
        ]);

        if (! empty($form->category_id)) {
            return redirect()->route('admin.shop.forms', $form->id)->with('success', __('interface.messages.shop_form_added'));
        }

        return redirect()->route('admin.shop.forms', $form->id)->with('success', __('interface.messages.shop_form_added'));
    }

    /**
     * Update an existing shop form.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_forms_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'form_id'          => ['required', 'integer'],
            'tracker_id'       => ['nullable', 'integer'],
            'route'            => ['required', 'string'],
            'name'             => ['required', 'string'],
            'description'      => ['required', 'string'],
            'approval'         => ['nullable', 'string'],
            'public'           => ['nullable', 'string'],
            'product_type'     => ['required', 'string'],
            'vat_type'         => ['required', 'string'],
            'contract_type_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopConfiguratorForm $form */
        if (! empty($form = ShopConfiguratorForm::find($request->form_id))) {
            $form->update([
                'tracker_id'       => ! empty($request->tracker_id) ? $request->tracker_id : null,
                'route'            => $request->route,
                'name'             => $request->name,
                'description'      => $request->description,
                'approval'         => ! empty($request->approval),
                'public'           => ! empty($request->public),
                'contract_type_id' => $request->contract_type_id,
                'product_type'     => $request->product_type,
                'vat_type'         => $request->vat_type,
            ]);

            return redirect()->back()->with('success', __('interface.messages.shop_form_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing shop form.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_forms_delete(int $id): RedirectResponse
    {
        Validator::make([
            'form_id' => $id,
        ], [
            'form_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopConfiguratorForm $form */
        if (! empty($form = ShopConfiguratorForm::find($id))) {
            $form->fields()->each(function (ShopConfiguratorField $field) {
                $field->options()->delete();
            });
            $form->fields()->delete();
            $form->delete();

            return redirect()->back()->with('success', __('interface.messages.shop_form_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    public function shop_forms_index(int $id): Renderable
    {
        return view('admin.shop.form', [
            'form' => ShopConfiguratorForm::find($id),
        ]);
    }

    /**
     * Create a new shop form field.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_fields_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'form_id'      => ['required', 'integer'],
            'type'         => ['required', 'string'],
            'label'        => ['required', 'string'],
            'key'          => ['required', 'string'],
            'value'        => ['nullable', 'string'],
            'value_prefix' => ['nullable', 'string'],
            'value_suffix' => ['nullable', 'string'],
            'amount'       => ['required', 'numeric'],
            'required'     => ['nullable', 'string'],
        ])->validate();

        switch ($request->type) {
            case 'input_number':
            case 'input_range':
                Validator::make($request->toArray(), [
                    'min'  => ['required', 'numeric'],
                    'max'  => ['required', 'numeric'],
                    'step' => ['required', 'numeric'],
                ])->validate();

                ShopConfiguratorField::create([
                    'form_id'      => $request->form_id,
                    'type'         => $request->type,
                    'label'        => $request->label,
                    'key'          => $request->key,
                    'value'        => $request->value,
                    'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                    'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                    'amount'       => $request->amount,
                    'min'          => $request->min,
                    'max'          => $request->max,
                    'step'         => $request->step,
                    'required'     => ! empty($request->required),
                ]);

                break;
            case 'input_radio':
            case 'input_radio_image':
            case 'select':
                Validator::make($request->toArray(), [
                    'options' => ['required'],
                ])->validate();

                /* @var ShopConfiguratorField|null $field */
                $field = ShopConfiguratorField::create([
                    'form_id'      => $request->form_id,
                    'type'         => $request->type,
                    'label'        => $request->label,
                    'key'          => $request->key,
                    'value'        => $request->value,
                    'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                    'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                    'amount'       => $request->amount,
                    'required'     => ! empty($request->required),
                ]);

                if (
                    ! empty($field) &&
                    ! empty($options = $request->options)
                ) {
                    collect($options)->each(function (array $option) use ($field) {
                        ShopConfiguratorFieldOption::create([
                            'field_id' => $field->id,
                            'label'    => $option['label'],
                            'value'    => $option['value'],
                            'amount'   => $option['amount'],
                            'default'  => ! empty($option['default']),
                        ]);
                    });
                }

                break;
            default:
                ShopConfiguratorField::create([
                    'form_id'      => $request->form_id,
                    'type'         => $request->type,
                    'label'        => $request->label,
                    'key'          => $request->key,
                    'value'        => $request->value,
                    'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                    'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                    'amount'       => $request->amount,
                    'required'     => ! empty($request->required),
                ]);

                break;
        }

        return redirect()->back()->with('success', __('interface.messages.shop_form_field_added'));
    }

    /**
     * Update an existing shop form field.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_fields_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'field_id'     => ['required', 'integer'],
            'label'        => ['required', 'string'],
            'key'          => ['required', 'string'],
            'value'        => ['nullable', 'string'],
            'value_prefix' => ['nullable', 'string'],
            'value_suffix' => ['nullable', 'string'],
            'amount'       => ['required', 'numeric'],
            'required'     => ['nullable', 'string'],
        ])->validate();

        /* @var ShopConfiguratorField $field */
        if (! empty($field = ShopConfiguratorField::find($request->field_id))) {
            switch ($field->type) {
                case 'input_number':
                case 'input_range':
                    Validator::make($request->toArray(), [
                        'min'  => ['required', 'numeric'],
                        'max'  => ['required', 'numeric'],
                        'step' => ['required', 'numeric'],
                    ])->validate();

                    $field->update([
                        'label'        => $request->label,
                        'key'          => $request->key,
                        'value'        => $request->value,
                        'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                        'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                        'amount'       => $request->amount,
                        'min'          => $request->min,
                        'max'          => $request->max,
                        'step'         => $request->step,
                        'required'     => ! empty($request->required),
                    ]);

                    break;
                case 'input_radio':
                case 'input_radio_image':
                case 'select':
                    Validator::make($request->toArray(), [
                        'options' => ['required'],
                    ])->validate();

                    $field->update([
                        'label'        => $request->label,
                        'key'          => $request->key,
                        'value'        => $request->value,
                        'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                        'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                        'amount'       => $request->amount,
                        'required'     => ! empty($request->required),
                    ]);

                    if (
                        ! empty($field) &&
                        ! empty($options = $request->options)
                    ) {
                        $field->options()->delete();

                        collect($options)->each(function (array $option) use ($field) {
                            ShopConfiguratorFieldOption::create([
                                'field_id' => $field->id,
                                'label'    => $option['label'],
                                'value'    => $option['value'],
                                'amount'   => $option['amount'],
                                'default'  => ! empty($option['default']),
                            ]);
                        });
                    }

                    break;
                default:
                    $field->update([
                        'label'        => $request->label,
                        'key'          => $request->key,
                        'value'        => $request->value,
                        'value_prefix' => ! empty($request->value_prefix) ? $request->value_prefix : null,
                        'value_suffix' => ! empty($request->value_suffix) ? $request->value_suffix : null,
                        'amount'       => $request->amount,
                        'required'     => ! empty($request->required),
                    ]);

                    break;
            }

            return redirect()->back()->with('success', __('interface.messages.shop_form_field_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an existing shop form field.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_fields_delete(int $id): RedirectResponse
    {
        Validator::make([
            'field_id' => $id,
        ], [
            'field_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopConfiguratorField $field */
        if (! empty($field = ShopConfiguratorField::find($id))) {
            $field->options()->delete();
            $field->delete();

            return redirect()->back()->with('success', __('interface.messages.shop_form_field_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Get list of shop fields.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function shop_fields_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ShopConfiguratorField::where('form_id', '=', $request->id);

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('key', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('label', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('value', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('required', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'type':
                        $orderBy = 'type';

                        break;
                    case 'key':
                        $orderBy = 'key';

                        break;
                    case 'label':
                        $orderBy = 'label';

                        break;
                    case 'value':
                        $orderBy = 'value';

                        break;
                    case 'required':
                        $orderBy = 'required';

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
                ->transform(function (ShopConfiguratorField $field) {
                    switch ($field->type) {
                        case 'input_text':
                            $type = __('interface.data.text');

                            break;
                        case 'input_number':
                            $type = __('interface.data.number');

                            break;
                        case 'input_range':
                            $type = __('interface.data.range');

                            break;
                        case 'input_radio':
                            $type = __('interface.data.radio_text');

                            break;
                        case 'input_radio_image':
                            $type = __('interface.data.radio_image');

                            break;
                        case 'input_checkbox':
                            $type = __('interface.data.checkbox');

                            break;
                        case 'input_hidden':
                            $type = __('interface.data.hidden_text');

                            break;
                        case 'select':
                            $type = __('interface.data.select');

                            break;
                        case 'textarea':
                            $type = __('interface.data.textarea');

                            break;
                        default:
                            $type = __('interface.status.unknown');

                            break;
                    }

                    $options = '';

                    $field->options->each(function (ShopConfiguratorFieldOption $option) use (&$options) {
                        $options .= '<tr><td><input type="text" class="form-control" name="options[old' . $option->id . '][label]" value="' . $option->label . '"></td><td><input type="text" class="form-control" name="options[old' . $option->id . '][value]" value="' . $option->value . '"></td><td><div class="input-group"><input type="number" step="0.01" min="0.01" class="form-control" name="options[old' . $option->id . '][amount]" value="' . $option->amount . '"><div class="input-group-append"><span class="input-group-text" id="basic-addon2">€</span></div></div></td><td><input type="checkbox" class="form-control" name="options[old' . $option->id . '][default]" value="true"' . ($option->default ? ' checked' : '') . '></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>';
                    });

                    $showOptions = $field->type == 'input_radio' || $field->type == 'input_radio_image' || $field->type == 'select';
                    $showNumbers = $field->type == 'input_number' || $field->type == 'input_range';

                    $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editField' . $field->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editField' . $field->id . '" tabindex="-1" aria-labelledby="editField' . $field->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editField' . $field->id . 'Label">' . __('interface.actions.edit') . ' (' . $field->label . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form action="' . route('admin.shop.fields.update', $field->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="field_id" value="' . $field->id . '" />

                    <div class="form-group row">
                        <label for="label' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.label') . '</label>

                        <div class="col-md-8">
                            <input id="label' . $field->id . '" type="text" class="form-control" name="label" value="' . $field->amount . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="key' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.key') . '</label>

                        <div class="col-md-8">
                            <input id="key' . $field->id . '" type="text" class="form-control" name="key" value="' . $field->key . '">
                        </div>
                    </div>
                    <div class="form-group row" id="value">
                        <label for="value' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.default_value') . '</label>

                        <div class="col-md-8">
                            <input id="value' . $field->id . '" type="text" class="form-control" name="value" value="' . $field->value . '">
                        </div>
                    </div>
                    <div class="form-group row" id="value">
                        <label for="value_prefix' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.value_output_prefix') . '</label>

                        <div class="col-md-8">
                            <input id="value_prefix' . $field->id . '" type="text" class="form-control" name="value_prefix" value="' . $field->value_prefix . '">
                        </div>
                    </div>
                    <div class="form-group row" id="value">
                        <label for="value_suffix' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.value_output_suffix') . '</label>

                        <div class="col-md-8">
                            <input id="value_suffix' . $field->id . '" type="text" class="form-control" name="value_suffix" value="' . $field->value_suffix . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amount' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.fees') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="amount' . $field->id . '" type="number" step="0.01" min="0.01" class="form-control" name="amount" value="' . $field->amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row" id="min' . $field->id . '"' . (! $showNumbers ? ' style="display: none"' : '') . '>
                        <label for="min' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data_processing.minimum') . '</label>

                        <div class="col-md-8">
                            <input id="min' . $field->id . '" type="number" class="form-control" name="min" min="' . $field->min . '">
                        </div>
                    </div>
                    <div class="form-group row" id="max' . $field->id . '"' . (! $showNumbers ? ' style="display: none"' : '') . '>
                        <label for="max' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data_processing.maximum') . '</label>

                        <div class="col-md-8">
                            <input id="max' . $field->id . '" type="number" class="form-control" name="max" max="' . $field->max . '">
                        </div>
                    </div>
                    <div class="form-group row" id="step' . $field->id . '"' . (! $showNumbers ? ' style="display: none"' : '') . '>
                        <label for="step' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.step_size') . '</label>

                        <div class="col-md-8">
                            <input id="step' . $field->id . '" type="number" class="form-control" name="step" step="' . $field->step . '">
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="required' . $field->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.required') . '</label>

                        <div class="col-md-8">
                            <input id="required' . $field->id . '" type="checkbox" class="form-control" name="required" value="true"' . ($field->required ? ' checked' : '') . '>
                        </div>
                    </div>
                    <div class="form-group row" id="options' . $field->id . '"' . (! $showOptions ? ' style="display: none"' : '') . '>
                        <label for="options' . $field->id . '" class="col-md-12 col-form-label text-md-center">' . __('interface.data.options') . '</label>

                        <div class="col-md-12 mt-3">
                            <table class="table w-100 options_table">
                                <thead>
                                <tr>
                                    <td>' . __('interface.data.label') . '</td>
                                    <td>' . __('interface.data.value') . '</td>
                                    <td>' . __('interface.data.fees') . '</td>
                                    <td width="1%">' . __('interface.data.default_value') . '</td>
                                    <td width="1%">' . __('interface.misc.action') . '</td>
                                </tr>
                                </thead>
                                <tbody class="options_tbody">
                                    ' . $options . '
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control fieldLabel">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control fieldValue">
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0.01" class="form-control fieldFees">
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="basic-addon2">€</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="form-control fieldDefault" value="true">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary fieldAdd"><i class="bi bi-plus-circle"></i></button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
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
                        'id'       => $field->id,
                        'type'     => $type,
                        'key'      => $field->key,
                        'label'    => $field->label,
                        'value'    => $field->value,
                        'required' => $field->required ? '<span class="badge badge-success">' . __('interface.misc.yes') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.no') . '</span>',
                        'edit'     => $edit,
                        'delete'   => '<a href="' . route('admin.shop.fields.delete', $field->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    public function shop_orders_index(): Renderable
    {
        return view('admin.shop.orders');
    }

    /**
     * Get list of shop fields.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function shop_orders_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = ShopOrderQueue::query();

        if (! empty($request->user_id)) {
            $query = $query->where('user_id', '=', $request->user_id);
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('form_id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhereHas('form', function (Builder $builder) use ($request) {
                        return $builder->where('name', 'LIKE', '%' . $request->search['value'] . '%');
                    })
                    ->orWhere('user_id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('product_type', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('approved', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('disapproved', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'status':
                        $orderBy       = 'approved';
                        $orderBySecond = 'disapproved';
                        $orderByThird  = 'setup';
                        $orderByFourth = 'fails';

                        break;
                    case 'amount':
                        $orderBy = 'amount';

                        break;
                    case 'product_type':
                        $orderBy = 'product_type';

                        break;
                    case 'user':
                        $orderBy = 'user_id';

                        break;
                    case 'form':
                        $orderBy = 'form_id';

                        break;
                    case 'id':
                    default:
                        $orderBy = 'id';

                        break;
                }

                $query = $query->orderBy($orderBy, $order['dir']);

                if (isset($orderBySecond)) {
                    $query = $query->orderBy($orderBySecond, $order['dir']);
                }

                if (isset($orderByThird)) {
                    $query = $query->orderBy($orderByThird, $order['dir']);
                }

                if (isset($orderByFourth)) {
                    $query = $query->orderBy($orderByFourth, $order['dir']);
                }
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
                ->transform(function (ShopOrderQueue $queue) {
                    if (
                        (
                            ! $queue->disapproved &&
                            ! $queue->approved && ! $queue->setup
                        ) || (
                            ! $queue->setup &&
                            $queue->fails >= 3
                        )
                    ) {
                        $options = '';

                        $queue->fields->each(function (ShopOrderQueueField $field) use (&$options) {
                            $options .= '<tr><td><input type="text" class="form-control" name="options[old' . $field->id . '][key]" value="' . $field->key . '"></td><td><input type="text" class="form-control" name="options[old' . $field->id . '][value]" value="' . $field->value . '"></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>';
                        });

                        $edit = '
<a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editQueueItem' . $queue->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="editQueueItem' . $queue->id . '" tabindex="-1" aria-labelledby="editQueueItem' . $queue->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editQueueItem' . $queue->id . 'Label">' . __('interface.actions.edit') . ' (' . $queue->number . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <form action="' . route('admin.shop.orders.update', $queue->id) . '" method="post">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="order_id" value="' . $queue->id . '" />

                    <div class="form-group row" id="value">
                        <label for="product_type' . $queue->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.product_type') . '</label>

                        <div class="col-md-8">
                            <input id="product_type' . $queue->id . '" type="text" class="form-control" name="product_type" value="' . $queue->product_type . '">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="amount' . $queue->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.fees_net') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="amount' . $queue->id . '" type="number" step="0.01" min="0.01" class="form-control" name="amount" value="' . $queue->amount . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="vat_percentage' . $queue->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.vat_percentage') . '</label>

                        <div class="col-md-8">
                            <div class="input-group">
                                <input id="vat_percentage' . $queue->id . '" type="number" step="0.01" min="0.01" class="form-control" name="vat_percentage" value="' . $queue->vat_percentage . '">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="basic-addon2">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row align-items-center">
                        <label for="reverse_charge' . $queue->id . '" class="col-md-4 col-form-label text-md-right">' . __('interface.data.reverse_charge') . '</label>

                        <div class="col-md-8">
                            <input id="reverse_charge' . $queue->id . '" type="checkbox" class="form-control" name="reverse_charge" value="true"' . ($queue->reverse_charge ? ' checked' : '') . '>
                        </div>
                    </div>

                    <table class="table w-100 options_table">
                        <thead>
                            <tr>
                                <td>' . __('interface.data.key') . '</td>
                                <td>' . __('interface.data.value') . '</td>
                                <td>' . __('interface.misc.action') . '</td>
                            </tr>
                        </thead>
                        <tbody class="options_tbody">
                            ' . $options . '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <input type="text" class="form-control fieldKey">
                                </td>
                                <td>
                                    <input type="text" class="form-control fieldValue">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary fieldAdd"><i class="bi bi-plus-circle"></i></button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
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
                    }

                    if ($queue->history->isNotEmpty()) {
                        $historyRows = '';

                        $queue->history->sortByDesc('id')->each(function (ShopOrderQueueHistory $history) use (&$historyRows) {
                            $class = 'table-' . $history->type;

                            switch ($history->type) {
                                case 'success':
                                    $icon = 'bi bi-check-circle';

                                    break;
                                case 'warning':
                                    $icon = 'bi bi-exclamation-triangle';

                                    break;
                                case 'danger':
                                    $icon = 'bi bi-exclamation-circle';

                                    break;
                                case 'info':
                                    $icon = 'bi bi-info-circle';

                                    break;
                                default:
                                    $icon = null;

                                    break;
                            }

                            $historyRows .= '
<tr class="' . $class . '">
    <td>' . (! empty($icon) ? '<i class="' . $icon . '"></i>' : '') . '</td>
    <td>' . $history->created_at->format('d.m.Y, H:i') . '</td>
    <td>' . __($history->message) . '</td>
</tr>
';
                        });

                        $historyItems = '
<table class="table w-100 options_table">
    <thead>
        <tr>
            <td width="1%"></td>
            <td>' . __('interface.data.date') . '</td>
            <td>' . __('interface.data.message') . '</td>
        </tr>
    </thead>
    <tbody class="options_tbody">
        ' . $historyRows . '
    </tbody>
</table>
';
                    } else {
                        $historyItems = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.messages.no_history_unapproved') . '</div>';
                    }

                    $history = '
<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#historyQueueItem' . $queue->id . '"><i class="bi bi-list"></i></a>
<div class="modal fade" id="historyQueueItem' . $queue->id . '" tabindex="-1" aria-labelledby="historyQueueItem' . $queue->id . 'Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="historyQueueItem' . $queue->id . 'Label">' . __('interface.data.history') . ' (' . $queue->number . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                ' . $historyItems . '
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">' . __('interface.actions.close') . '</button>
            </div>
        </div>
    </div>
</div>
';

                    $status = '';

                    if ($queue->approved) {
                        $status .= '<span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.approved') . '</span>';

                        if ($queue->verified) {
                            $status .= '<br><span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.verified') . '</span>';

                            if ($queue->setup) {
                                $status .= '<br><span class="badge badge-success"><i class="bi bi-check-circle"></i> ' . __('interface.status.completed') . '</span>';
                            } else {
                                if ($queue->fails < 3) {
                                    $status .= '<br><span class="badge badge-success"><i class="bi bi-play-circle"></i> ' . __('interface.status.running') . '</span>';
                                } else {
                                    $status .= '<br><span class="badge badge-danger"><i class="bi bi-stop-circle"></i> ' . __('interface.status.failed') . '</span>';
                                }
                            }

                            if ($queue->fails > 0) {
                                $status .= '<span class="badge badge-warning ml-1"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.data.fails_num', [
                                    'num' => $queue->fails,
                                ]) . '</span>';
                            }
                        } else {
                            if ($queue->invalid) {
                                $status .= '<br><span class="badge badge-warning"><i class="bi bi-exclamation-triangle"></i> ' . __('interface.status.invalid') . '</span>';
                            } else {
                                $status .= '<br><span class="badge badge-success"><i class="bi bi-play-circle"></i> ' . __('interface.status.verifying') . '</span>';
                            }
                        }
                    } elseif ($queue->disapproved) {
                        $status .= '<span class="badge badge-danger"><i class="bi bi-x-circle"></i> ' . __('interface.status.disapproved') . '</span>';
                    } else {
                        $status .= '<span class="badge badge-warning"><i class="bi bi-play-circle"></i> ' . __('interface.data.approval') . '</span>';
                    }

                    return (object) [
                        'id'           => $queue->number,
                        'user'         => ! empty($user = $queue->user) ? '<a href="' . route('admin.customers.profile', $user->id) . '" class="text-decoration-none" target="_blank">' . $user->realName . ' <i class="bi bi-box-arrow-up-right"></i></a>' : __('interface.misc.not_available'),
                        'form'         => __($queue->form->name),
                        'product_type' => ! empty($handler = $queue->handler) ? $handler->name() : '&lt;' . $queue->product_type . '&gt;',
                        'amount'       => number_format($queue->amount, 2) . ' €<span class="d-block small">' . number_format($queue->amount * (100 + $queue->vat_percentage) / 100, 2) . ' €</span>',
                        'status'       => $status,
                        'history'      => $history,
                        'approve'      => ! $queue->approved && ! $queue->disapproved ? '<a href="' . route('admin.shop.orders.approve', $queue->id) . '" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i></a>' : '<button type="button" class="btn btn-success btn-sm" disabled><i class="bi bi-check-circle"></i></button>',
                        'disapprove'   => ! $queue->disapproved && ! $queue->approved ? '<a href="' . route('admin.shop.orders.disapprove', $queue->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-x-circle"></i></button>',
                        'edit'         => (! $queue->disapproved && ! $queue->approved && ! $queue->setup) || (! $queue->setup && $queue->fails >= 3) ? $edit : '<button type="button" class="btn btn-warning btn-sm" disabled><i class="bi bi-pencil-square"></i></button>',
                        'delete'       => (! $queue->disapproved && ! $queue->approved && ! $queue->setup) || (! $queue->setup && $queue->fails >= 3) ? '<a href="' . route('admin.shop.orders.delete', $queue->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>' : '<button type="button" class="btn btn-danger btn-sm" disabled><i class="bi bi-trash"></i></button>',
                    ];
                }),
        ]);
    }

    /**
     * Approve an order.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_orders_approve(int $id): RedirectResponse
    {
        Validator::make([
            'order_id' => $id,
        ], [
            'order_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopOrderQueue $order */
        if (
            ! empty($order = ShopOrderQueue::find($id)) &&
            ! $order->approved &&
            ! $order->disapproved
        ) {
            $order->update([
                'approved' => true,
            ]);

            ShopOrderQueueHistory::create([
                'order_id' => $order->id,
                'type'     => 'success',
                'message'  => 'Order approval succeeded.',
            ]);

            $order->sendEmailSuccessfulApprovalNotification();

            return redirect()->back()->with('success', __('interface.messages.order_approved'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Disapprove an order.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_orders_disapprove(int $id): RedirectResponse
    {
        Validator::make([
            'order_id' => $id,
        ], [
            'order_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopOrderQueue $order */
        if (
            ! empty($order = ShopOrderQueue::find($id)) &&
            ! $order->approved &&
            ! $order->disapproved
        ) {
            if (
                $order->form->contractType->invoiceType->type == 'prepaid' &&
                ($grossAmount = $order->amount * (100 + $order->vat_percentage) / 100) > 0
            ) {
                PrepaidHistory::create([
                    'user_id'            => Auth::id(),
                    'amount'             => $grossAmount,
                    'transaction_method' => 'account',
                ]);
            }

            $order->update([
                'disapproved' => true,
            ]);

            ShopOrderQueueHistory::create([
                'order_id' => $order->id,
                'type'     => 'success',
                'message'  => 'Order approval declined. Setup won\'t be executed.',
            ]);

            $order->sendEmailUnsuccessfulApprovalNotification();

            return redirect()->back()->with('success', __('interface.messages.order_disapproved'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete an order.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_orders_delete(int $id): RedirectResponse
    {
        Validator::make([
            'order_id' => $id,
        ], [
            'order_id' => ['required', 'integer'],
        ])->validate();

        /* @var ShopOrderQueue $order */
        if (
            ! empty($order = ShopOrderQueue::find($id)) &&
            ! $order->approved &&
            ! $order->disapproved
        ) {
            if (
                $order->form->contractType->invoiceType->type == 'prepaid' &&
                ($grossAmount = $order->amount * (100 + $order->vat_percentage) / 100) > 0
            ) {
                PrepaidHistory::create([
                    'user_id'            => Auth::id(),
                    'amount'             => $grossAmount,
                    'transaction_method' => 'account',
                ]);
            }

            $order->delete();

            return redirect()->back()->with('success', __('interface.messages.order_deleted'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Update an order.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function shop_orders_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'order_id'       => ['required', 'integer'],
            'product_type'   => ['required', 'string'],
            'amount'         => ['required', 'numeric'],
            'vat_percentage' => ['required', 'numeric'],
            'reverse_charge' => ['nullable', 'string'],
        ])->validate();

        /* @var ShopOrderQueue $order */
        if (! empty($order = ShopOrderQueue::find($request->order_id))) {
            $orderResetAfterFailure = false;
            $orderApproval          = false;

            if ($order->fails >= 3) {
                $orderResetAfterFailure = true;
                $orderApproval          = ! empty($form = $order->form) && ! $form->approval;
            }

            $order->update([
                'product_type'   => $request->product_type,
                'amount'         => $request->amount,
                'vat_percentage' => $request->vat_percentage,
                'reverse_charge' => ! empty($request->reverse_charge),
                'invalid'        => false,
                'verified'       => false,
                'approved'       => $orderApproval,
                'disapproved'    => false,
                'fails'          => 0,
            ]);

            $order->fields()->delete();

            if (! empty($options = $request->options)) {
                collect($options)->each(function (array $option) use ($order) {
                    ShopOrderQueueField::create([
                        'order_id' => $order->id,
                        'key'      => $option['key'],
                        'value'    => $option['value'],
                    ]);
                });
            }

            if ($orderResetAfterFailure) {
                ShopOrderQueueHistory::create([
                    'order_id' => $order->id,
                    'type'     => 'info',
                    'message'  => 'Failed order status reset after setup modification.',
                ]);

                if ($orderApproval) {
                    $order->sendEmailUpdateSuccessfulApprovalNotification();
                } else {
                    $order->sendEmailUpdatePendingApprovalNotification();
                }
            }

            return redirect()->back()->with('success', __('interface.messages.order_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Show list of payment gateways.
     *
     * @return Renderable
     */
    public function products_index(): Renderable
    {
        return view('admin.shop.products', [
            'products' => Products::list(),
        ]);
    }

    /**
     * Save product settings.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function products_save(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $product = Products::list()
                    ->filter(function ($object, $key) use ($request) {
                        return $key == $request->product_type;
                    })
                    ->first()
            )
        ) {
            $validation = [];
            $fields     = collect();

            $product->parameters()->each(function ($name, $key) use (&$validation, &$fields) {
                $validation[$key] = ['required'];
                $fields->push($key);
            });

            Validator::make($request->toArray(), $validation)->validate();

            ProductSetting::where('product_type', '=', $request->product_type)
                ->delete();

            collect($request->toArray())->each(function ($value, $key) use ($fields, $request) {
                if ($fields->contains($key)) {
                    ProductSetting::create([
                        'product_type' => $request->product_type,
                        'setting'      => $key,
                        'value'        => $value,
                    ]);
                }
            });

            return redirect()->back()->with('success', __('interface.messages.product_type_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
