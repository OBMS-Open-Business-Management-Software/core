<?php

namespace App\Http\Controllers;

use App\Products\Product;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    protected $handler;

    public function __construct(Product $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Show list of services.
     *
     * @return Renderable
     */
    public function service_index(): Renderable
    {
        return view()->file($this->handler->folderName() . '/Views/admin/home.blade.php', [
            'handler' => $this->handler,
        ]);
    }

    /**
     * Get list of services.
     *
     * @param Request $request
     */
    public function service_list(Request $request): void
    {
        session_write_close();

        $model = $this->handler->model();
        $query = $model::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('user_id', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'user_id':
                        $orderBy = 'user_id';
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
                ->transform(function ($result) {
                    return (object) [
                        'id' => $result->id,
                        'user_id' => $result->user_id,
                        'status' => $result->status(),
                        'view' => '<a href="' . $this->handler->technicalName() . '/' . $result->id . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                })
        ]);
    }

    /**
     * View a service.
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function service_details(int $id, Request $request)
    {
        $model = $this->handler->model();
        $service = $model::find($id);

        if (!$service) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        return view()->file($this->handler->folderName() . '/Views/admin/details.blade.php', [
            'service' => $service,
        ]);
    }

    /**
     * View service statistics.
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function service_statistics(int $id, Request $request)
    {
        $model = $this->handler->model();
        $service = $model::find($id);

        if (!$service) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        return view()->file($this->handler->folderName() . '/Views/admin/statistics.blade.php', [
            'service' => $service,
        ]);
    }

    /**
     * Start a service.
     *
     * @param int $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function service_start(int $id, Request $request)
    {
        $model = $this->handler->model();
        $service = $model::find($id);

        if (!$service) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        $service->start();

        return redirect()->back()->with('success', __('interface.messages.service_started'));
    }

    /**
     * Stop a service.
     *
     * @param int $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function service_stop(int $id, Request $request)
    {
        $model = $this->handler->model();
        $service = $model::find($id);

        if (!$service) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        $service->start();

        return redirect()->back()->with('success', __('interface.messages.service_stopped'));
    }

    /**
     * Restart a service.
     *
     * @param int $id
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function service_restart(int $id, Request $request)
    {
        $model = $this->handler->model();
        $service = $model::find($id);

        if (!$service) {
            return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
        }

        $service->start();

        return redirect()->back()->with('success', __('interface.messages.service_restarted'));
    }
}
