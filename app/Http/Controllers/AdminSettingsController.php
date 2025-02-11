<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminSettingsController extends Controller
{
    /**
     * Show list of settings.
     *
     * @return Renderable
     */
    public function settings_index(): Renderable
    {
        return view('admin.settings.home');
    }

    /**
     * Get list of settings.
     *
     * @param Request $request
     */
    public function settings_list(Request $request): void
    {
        session_write_close();

        $query = Setting::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('setting', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'setting':
                        $orderBy = 'setting';
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

        header('Content-type: application/json');
        echo json_encode([
            'draw' => (int) $request->draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => $query
                ->get()
                ->transform(function (Setting $setting) {
                    $edit = '
<a class="btn btn-warning btn-sm w-100" data-toggle="modal" data-target="#edit' . $setting->id . '" data-type="edit" data-category="' . $setting->id . '" data-table="#settings-' . $setting->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="edit' . $setting->id . '" tabindex="-1" aria-labelledby="edit' . $setting->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="edit' . $setting->id . 'Label">' . __('interface.actions.edit') . ' (' . $setting->setting . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="' . route('admin.settings.update', $setting->id) . '" method="post">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="setting_id" value="' . $setting->id . '" />

                    <div class="form-group row">
                        <label for="value" class="col-md-4 col-form-label text-md-right">' . __('interface.data.value') . '</label>

                        <div class="col-md-8">
                            <input id="value" type="text" class="form-control" name="value" value="' . $setting->value . '">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
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
                        'id' => $setting->id,
                        'setting' => $setting->setting,
                        'value' => $setting->value,
                        'edit' => $edit,
                    ];
                })
        ]);
    }

    /**
     * Update a setting.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function settings_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'setting_id' => ['required', 'integer'],
            'value' => ['string', 'nullable'],
        ])->validate();

        /* @var Setting $setting */
        if (! empty($setting = Setting::find($request->setting_id))) {
            $setting->update([
                'value' => $request->value ? $request->value : null,
            ]);

            if (
                collect([
                    'theme.primary',
                    'theme.secondary',
                    'theme.secondary-dark',
                    'theme.white',
                    'theme.gray',
                ])->contains($setting->setting)
            ) {
                $cacheKey = 'stylesheet-' . str_replace(['/', ':'], '_', str_replace(['http://', 'https://'], '', config('app.url')));

                Cache::forget($cacheKey);
            }

            return redirect()->back()->with('success', __('interface.messages.setting_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
