<?php

namespace App\Http\Controllers;

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Content\Page;
use App\Models\Content\PageAcceptance;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\Support\SupportTicket;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerDashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('customer.home', [
            'tickets' => SupportTicket::whereHas('assignments', function (Builder $builder) {
                return $builder->where('user_id', '=', Auth::id());
            })
                ->where('status', '=', 'open')
                ->count(),
            'invoices' => Invoice::where('user_id', '=', Auth::id())
                ->where('status', '=', 'unpaid')
                ->whereDoesntHave('type', function (Builder $builder) {
                    return $builder->where('type', '=', 'prepaid');
                })
                ->count(),
            'contracts' => Contract::where('user_id', '=', Auth::id())
                ->where('started_at', '>=', Carbon::now())
                ->whereNotNull('last_invoice_at')
                ->where(function (Builder $builder) {
                    return $builder->whereNull('cancelled_to')
                        ->orWhere('cancelled_to', '<', Carbon::now());
                })
                ->count(),
            'ordersOpen' => ShopOrderQueue::where('user_id', '=', Auth::id())
                ->where('disapproved', '=', false)
                ->where('setup', '=', false)
                ->count(),
            'ordersSetup' => ShopOrderQueue::where('user_id', '=', Auth::id())
                ->where('setup', '=', true)
                ->where('locked', '=', false)
                ->where('deleted', '=', false)
                ->count(),
            'ordersLocked' => ShopOrderQueue::where('user_id', '=', Auth::id())
                ->where('setup', '=', true)
                ->where('locked', '=', true)
                ->where('deleted', '=', false)
                ->count(),
        ]);
    }

    /**
     * Show the application acceptance wall.
     *
     * @return Renderable
     */
    public function accept(): Renderable
    {
        return view('customer.accept', [
            'acceptable' => Auth::user()->acceptable,
        ]);
    }

    public function acceptSubmit(Request $request): RedirectResponse
    {
        if (! empty($acceptable = Auth::user()->acceptable)) {
            $validationRules = [];

            $acceptable->each(function (Page $page) use (&$validationRules) {
                $validationRules['accept_' . $page->id] = ['required', 'string'];
            });

            if (Validator::make($request->toArray(), $validationRules)->fails()) {
                return redirect()->back()->with('warning', __('Please accept all of the current page versions before continuing.'));
            }

            $signedAt = Carbon::now();

            $acceptable->each(function (Page $page) use ($request, $signedAt) {
                PageAcceptance::where('page_id', '=', $page->id)
                    ->where('user_id', '=', Auth::id())
                    ->where('page_version_id', '!=', $page->latest->id)
                    ->delete();

                PageAcceptance::updateOrCreate([
                    'page_id' => $page->id,
                    'page_version_id' => $page->latest->id,
                    'user_id' => Auth::id(),
                    'user_agent' => $request->server('HTTP_USER_AGENT'),
                    'ip' => $request->ip(),
                    'signature' => md5($page->id . $page->latest->id . Auth::id(). $request->server('HTTP_USER_AGENT') . $request->ip() . $signedAt),
                    'signed_at' => $signedAt,
                ]);
            });

            return redirect()->route('customer.home')->with('success', __('The current page versions have been accepted successfully. Your acceptance has been logged.'));
        }

        return redirect()->back()->with('warning', __('Ooops, something went wrong. Please try again later.'));
    }
}
