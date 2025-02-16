<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Support\SupportTicket;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('admin.home', [
            'tickets' => SupportTicket::where('status', '=', 'open')
                ->where(function (Builder $builder) {
                    return $builder->where('category_id', '=', 0)
                        ->orWhereNull('category_id')
                        ->orWhereHas('category', function (Builder $builder) {
                            return $builder->whereHas('assignments', function (Builder $builder) {
                                return $builder->where('user_id', '=', Auth::id());
                            });
                        });
                })
                ->count(),
            'invoicesCustomers' => Invoice::where('status', '=', 'unpaid')
                ->whereDoesntHave('type', function (Builder $builder) {
                    return $builder->where('type', '=', 'prepaid');
                })
                ->whereHas('user', function (Builder $builder) {
                    return $builder->where('role', '=', 'customer');
                })
                ->count(),
            'invoicesSuppliers' => Invoice::where('status', '=', 'unpaid')
                ->whereDoesntHave('type', function (Builder $builder) {
                    return $builder->where('type', '=', 'prepaid');
                })
                ->whereHas('user', function (Builder $builder) {
                    return $builder->where('role', '=', 'supplier');
                })
                ->count(),
            'contracts' => Contract::where('started_at', '>=', Carbon::now())
                ->whereNotNull('last_invoice_at')
                ->where(function (Builder $builder) {
                    return $builder->whereNull('cancelled_to')
                        ->orWhere('cancelled_to', '<', Carbon::now());
                })
                ->count(),
            'ordersApproval' => ShopOrderQueue::where('invalid', '=', false)
                ->where('approved', '=', false)
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->where('deleted', '=', false)
                ->where('fails', '<', 3)
                ->count(),
            'ordersOpen' => ShopOrderQueue::where('invalid', '=', false)
                ->where('approved', '=', true)
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->where('deleted', '=', false)
                ->where('fails', '<', 3)
                ->count(),
            'ordersFailed' => ShopOrderQueue::where('invalid', '=', false)
                ->where('approved', '=', true)
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->where('deleted', '=', false)
                ->where(function (Builder $builder) {
                    return $builder->where('fails', '>=', 3)
                        ->orWhere('invalid', '=', true);
                })
                ->count(),
            'ordersSetup' => ShopOrderQueue::where('setup', '=', true)
                ->where('locked', '=', false)
                ->where('deleted', '=', false)
                ->count(),
            'ordersLocked' => ShopOrderQueue::where('setup', '=', true)
                ->where('locked', '=', true)
                ->where('deleted', '=', false)
                ->count(),
        ]);
    }
}
