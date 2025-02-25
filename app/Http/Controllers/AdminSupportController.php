<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Download;
use App\Helpers\SupportRun as SupportRunHelper;
use App\Models\FileManager\File;
use App\Models\ImapInbox;
use App\Models\Support\Category\SupportCategory;
use App\Models\Support\Category\SupportCategoryAssignment;
use App\Models\Support\Run\SupportRun;
use App\Models\Support\Run\SupportRunHistory;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAssignment;
use App\Models\Support\SupportTicketFile;
use App\Models\Support\SupportTicketHistory;
use App\Models\Support\SupportTicketMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminSupportController extends Controller
{
    /**
     * Show list of support tickets.
     *
     * @return Renderable
     */
    public function support_index(): Renderable
    {
        return view('admin.support.home', [
            'categories' => SupportCategory::whereHas('assignments', function (Builder $builder) {
                return $builder->where('user_id', '=', Auth::id());
            })->get(),
            'run' => SupportRun::where('user_id', '=', Auth::id())
                ->whereNull('ended_at')
                ->first(),
        ]);
    }

    /**
     * Get list of support tickets.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function support_index_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = SupportTicket::where(function (Builder $builder) {
            return $builder
                ->whereHas('category', function (Builder $builder) {
                    return $builder->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    });
                })
                ->orWhere('category_id', '=', 0)
                ->orWhereNull('category_id');
        })->where('category_id', '=', $request->category);

        switch ($request->type) {
            case 'open':
                $query = $query->where('status', '=', 'open');

                break;
            case 'closed':
                $query = $query->where('status', '=', 'closed');

                break;
            case 'locked':
                $query = $query->where('status', '=', 'locked');

                break;
        }

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('subject', 'LIKE', '%' . $request->search['value'] . '%');
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'subject':
                        $orderBy = 'subject';

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
                ->transform(function (SupportTicket $ticket) {
                    $status = '';

                    switch ($ticket->status) {
                        case 'open':
                            $status = '<span class="badge badge-primary">' . __('interface.status.open') . '</span>';

                            break;
                        case 'closed':
                            $status = '<span class="badge badge-secondary">' . __('interface.status.closed') . '</span>';

                            break;
                        case 'locked':
                            $status = '<span class="badge badge-danger">' . __('interface.status.locked') . '</span>';

                            break;
                    }

                    if ($ticket->escalated) {
                        $status .= (! empty($status) ? ' ' : '') . '<span class="badge badge-warning">' . __('interface.status.escalated') . '</span>';
                    }

                    if ($ticket->hold) {
                        $status .= (! empty($status) ? ' ' : '') . '<span class="badge badge-secondary">' . __('interface.status.on_hold') . '</span>';
                    }

                    switch ($ticket->priority) {
                        case 'low':
                        default:
                            $priority = '<span class="badge badge-secondary">' . __('interface.priorities.low') . '</span>';

                            break;
                        case 'medium':
                            $priority = '<span class="badge badge-success">' . __('interface.priorities.medium') . '</span>';

                            break;
                        case 'high':
                            $priority = '<span class="badge badge-warning">' . __('interface.priorities.high') . '</span>';

                            break;
                        case 'emergency':
                            $priority = '<span class="badge badge-danger">' . __('interface.priorities.emergency') . '</span>';

                            break;
                    }

                    return (object) [
                        'id'       => $ticket->id,
                        'subject'  => $ticket->subject,
                        'category' => ! empty($ticket->category) ? $ticket->category->name : __('interface.status.uncategorized'),
                        'status'   => $status,
                        'priority' => $priority,
                        'view'     => '<a href="' . route('admin.support.details', $ticket->id) . '" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * View a support ticket.
     *
     * @param Request $request
     *
     * @return Application|Factory|RedirectResponse|View
     */
    public function support_details(Request $request)
    {
        $ticket = SupportTicket::find($request->id);

        if (
            ! empty($ticket) &&
            (
                empty($ticket->category) ||
                $ticket->category->assignments->where('user_id', '=', Auth::id())->isNotEmpty()
            )
        ) {
            return view('admin.support.details', [
                'ticket' => $ticket,
                'run'    => SupportRun::where('user_id', '=', Auth::id())
                    ->whereNull('ended_at')
                    ->first(),
                'categories' => SupportCategory::whereHas('assignments', function (Builder $builder) {
                    return $builder->where('user_id', '=', Auth::id());
                })->get(),
                'move_categories' => SupportCategory::where('id', '!=', $ticket->category_id)->get(),
            ]);
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new support ticket answer.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_answer(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'id'      => ['required', 'integer'],
            'message' => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $request->id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            /* @var SupportTicketMessage $message */
            $message = SupportTicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
                'note'      => ! empty($request->note) && $request->note == 'true',
            ]);

            $fileNotification = false;

            /* @var UploadedFile|null $file */
            if (! empty($file = $request->files->get('file'))) {
                $file = File::create([
                    'user_id'   => ! empty($request->private) ? Auth::id() : null,
                    'folder_id' => null,
                    'name'      => Carbon::now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                    'data'      => $file->getContent(),
                    'mime'      => $file->getClientMimeType(),
                    'size'      => $file->getSize(),
                ]);

                if ($file instanceof File) {
                    SupportTicketFile::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => Auth::id(),
                        'file_id'   => $file->id,
                        'internal'  => ! empty($request->note) && $request->note == 'true',
                    ]);

                    SupportTicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => Auth::id(),
                        'type'      => 'file',
                        'action'    => 'add',
                        'reference' => $file->id,
                    ]);

                    $fileNotification = true;
                }
            }

            $message->sendEmailCreationNotification();

            if (
                empty($request->note) &&
                $fileNotification
            ) {
                $ticket->sendEmailFileUploadNotification();
            }

            if (
                ! empty($request->note) &&
                $request->note == 'true'
            ) {
                return redirect()->back()->with('success', __('interface.messages.ticket_note_added'));
            }

            return redirect()->back()->with('success', __('interface.messages.ticket_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Close a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_close(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'status' => 'closed',
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'status',
                'action'    => 'close',
            ]);

            $ticket->sendEmailCloseNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_closed'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Re-open a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_reopen(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'status' => 'open',
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'status',
                'action'    => 'reopen',
            ]);

            $ticket->sendEmailReopenNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_reopened'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Lock a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_lock(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'status' => 'locked',
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'status',
                'action'    => 'lock',
            ]);

            $ticket->sendEmailLockNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_locked'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Unlock a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_unlock(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'status' => 'open',
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'status',
                'action'    => 'unlock',
            ]);

            $ticket->sendEmailUnlockNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_unlocked_opened'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Join a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_join(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereDoesntHave('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            SupportTicketAssignment::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'role'      => Auth::user()->role,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'assignment',
                'action'    => 'assign',
                'reference' => Auth::id(),
            ]);

            return redirect()->back()->with('success', __('interface.messages.ticket_joined'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Join a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_leave(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            SupportTicketAssignment::where('ticket_id', '=', $ticket->id)
                ->where('user_id', '=', Auth::id())
                ->delete();

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'assignment',
                'action'    => 'unassign',
                'reference' => Auth::id(),
            ]);

            return redirect()->back()->with('success', __('interface.messages.ticket_left'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Escalate a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_escalate(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'escalated' => true,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'escalate',
                'action'    => 'escalate',
            ]);

            $ticket->sendEmailEscalationNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_escalated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Deescalate a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_deescalate(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'escalated' => false,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'escalate',
                'action'    => 'deescalate',
            ]);

            $ticket->sendEmailDeescalationNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_deescalated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Move a support ticket.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_move(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'ticket_id'   => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $request->ticket_id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'category_id' => $request->category_id,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'category',
                'action'    => 'move',
                'reference' => $request->category_id,
            ]);

            if (
                ! empty($request->category_id) &&
                $request->category_id > 0 &&
                ! empty($category = SupportCategory::find($request->category_id)) &&
                $category->assignments->where('user_id', '=', Auth::id())->isEmpty()
            ) {
                SupportTicketAssignment::where('ticket_id', '=', $ticket->id)
                    ->where('user_id', '=', Auth::id())
                    ->delete();

                SupportTicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'type'      => 'assignment',
                    'action'    => 'unassign',
                    'reference' => Auth::id(),
                ]);

                return redirect()->route('admin.support')->with('success', __('interface.messages.ticket_moved_redirected'));
            }

            return redirect()->back()->with('success', __('interface.messages.ticket_moved'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Prioritize a support ticket.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_priority(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'ticket_id' => ['required', 'integer'],
            'priority'  => ['required', 'string'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $request->ticket_id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'priority' => $request->priority,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'priority',
                'action'    => 'set',
                'reference' => $request->priority,
            ]);

            $ticket->sendEmailPriorityNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_prioritized'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Hold a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_hold(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'hold' => true,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'hold',
                'action'    => 'hold',
            ]);

            $ticket->sendEmailHoldNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_hold'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Unhold a support ticket.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_unhold(int $id): RedirectResponse
    {
        Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            )
        ) {
            $ticket->update([
                'hold' => false,
            ]);

            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'hold',
                'action'    => 'unhold',
            ]);

            $ticket->sendEmailUnholdNotification();

            return redirect()->back()->with('success', __('interface.messages.ticket_unhold'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Start a support ticket run. This process will fail with an error message
     * if no applicable ticket is found.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_run_start(Request $request): RedirectResponse
    {
        if (
            !empty($request->category) &&
            $request->category > 0
        ) {
            Validator::make($request->toArray(), [
                'category' => ['required', 'integer', 'min:1'],
            ])->validate();
        }

        if (
            ! SupportRun::where('user_id', '=', Auth::id())
                ->whereNull('ended_at')
                ->exists()
        ) {
            /* @var SupportTicket|null $ticket */
            if (! empty($ticket = SupportRunHelper::nextTicket($request->category ?? null))) {
                /* @var SupportRun|null $run */
                $run = SupportRun::create([
                    'category_id' => $request->category ?? null,
                    'ticket_id'   => $ticket->id,
                    'user_id'     => Auth::id(),
                ]);

                SupportTicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'type'      => 'run',
                    'action'    => 'opened',
                    'reference' => $run->id,
                ]);

                SupportRunHistory::create([
                    'run_id'    => $run->id,
                    'user_id'   => Auth::id(),
                    'ticket_id' => $ticket->id,
                    'type'      => 'status',
                    'action'    => 'start',
                ]);

                return redirect()->route('admin.support.details', $ticket->id)->with('success', __('interface.messages.ticket_run_started'));
            } else {
                return redirect()->back()->with('warning', __('interface.messages.ticket_run_not_applicable'));
            }
        }

        return redirect()->back()->with('warning', __('interface.messages.ticket_run_active_error'));
    }

    /**
     * Jump to next ticket in ticket run. If no applicable ticket is
     * found the run is automatically ended.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function support_run_next(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $run = SupportRun::where('user_id', '=', Auth::id())
                    ->whereNull('ended_at')
                    ->first()
            )
        ) {
            /* @var SupportTicket|null $nextTicket */
            if (! empty($nextTicket = SupportRunHelper::nextTicket($run->category_id))) {
                SupportTicketHistory::create([
                    'ticket_id' => $nextTicket->id,
                    'user_id'   => Auth::id(),
                    'type'      => 'run',
                    'action'    => 'opened',
                    'reference' => $run->id,
                ]);

                SupportRunHistory::create([
                    'run_id'    => $run->id,
                    'user_id'   => Auth::id(),
                    'ticket_id' => $nextTicket->id,
                    'type'      => 'message',
                    'action'    => 'rotate',
                ]);

                $run->update([
                    'ticket_id' => $nextTicket->id,
                ]);

                return redirect()->back()->with('success', __('interface.messages.ticket_run_skipped'));
            } else {
                $run->update([
                    'ended_at' => Carbon::now(),
                ]);

                return redirect()->back()->with('warning', __('interface.messages.ticket_run_not_applicable'));
            }
        }

        return redirect()->back()->with('warning', __('interface.messages.ticket_run_not_active'));
    }

    /**
     * End any existing ticket run a user is active in.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function support_run_stop(Request $request): RedirectResponse
    {
        if (
            ! empty(
                $run = SupportRun::where('user_id', '=', Auth::id())
                    ->whereNull('ended_at')
                    ->first()
            )
        ) {
            $run->update([
                'ended_at' => Carbon::now(),
            ]);

            SupportRunHistory::create([
                'run_id'  => $run->id,
                'user_id' => Auth::id(),
                'type'    => 'status',
                'action'  => 'stop',
            ]);

            return redirect()->back()->with('success', __('interface.messages.ticket_run_stopped'));
        }

        return redirect()->back()->with('warning', __('interface.messages.ticket_run_not_active'));
    }

    /**
     * Show list of support tickets.
     *
     * @return Renderable
     */
    public function support_categories(): Renderable
    {
        return view('admin.support.categories');
    }

    /**
     * Get list of support categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function support_categories_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = SupportCategory::query();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->where('id', 'LIKE', '%' . $request->search['value'] . '%')
                    ->orWhere('subject', 'LIKE', '%' . $request->search['value'] . '%');
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
                ->transform(function (SupportCategory $category) {
                    $edit = '
<a class="btn btn-warning btn-sm w-100" data-toggle="modal" data-target="#edit' . $category->id . '" data-type="edit" data-category="' . $category->id . '" data-table="#category-users-' . $category->id . '"><i class="bi bi-pencil-square"></i></a>
<div class="modal fade" id="edit' . $category->id . '" tabindex="-1" aria-labelledby="edit' . $category->id . 'Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="edit' . $category->id . 'Label">' . __('interface.actions.edit') . ' (' . $category->name . ')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="' . route('admin.support.categories.update', $category->id) . '" method="post">
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

                    <div class="form-group row">
                        <label for="email_address" class="col-md-4 col-form-label text-md-right">' . __('interface.data.email_address') . '</label>

                        <div class="col-md-8">
                            <input id="email_address" type="text" class="form-control" name="email_address" value="' . $category->email_address . '">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email_name" class="col-md-4 col-form-label text-md-right">' . __('interface.data.email_name') . '</label>

                        <div class="col-md-8">
                            <input id="email_name" type="text" class="form-control" name="email_name" value="' . $category->email_name . '">
                        </div>
                    </div>

                    <div class="form-group row align-items-center">
                        <label for="imap_enable" class="col-md-4 col-form-label text-md-right">' . __('interface.data.enable_imap_import') . '</label>

                        <div class="col-md-8">
                            <input type="checkbox" class="form-control imap_enable" data-id="' . $category->id . '" name="imap[enable]" value="true"' . (isset($category->imap_inbox_id) && $category->imap_inbox_id > 0 ? ' checked' : '') . '>
                        </div>
                    </div>

                    <div id="imap_import_' . $category->id . '"' . (isset($category->imap_inbox_id) && $category->imap_inbox_id > 0 ? '' : 'style="display: none"') . '>
                        <div class="form-group row">
                            <label for="imap_host" class="col-md-4 col-form-label text-md-right">' . __('interface.data.host') . '</label>

                            <div class="col-md-8">
                                <input id="imap_host" type="text" class="form-control" name="imap[host]" value="' . ($category->imapInbox->host ?? '') . '">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_port" class="col-md-4 col-form-label text-md-right">' . __('interface.data.port') . '</label>

                            <div class="col-md-8">
                                <input id="imap_port" type="text" class="form-control" name="imap[port]" value="' . ($category->imapInbox->port ?? '') . '">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_protocol" class="col-md-4 col-form-label text-md-right">' . __('interface.data.protocol') . '</label>

                            <div class="col-md-8">
                                <select id="imap_protocol" type="text" class="form-control" name="imap[protocol]">
                                    <option value="none"' . (($category->imapInbox->protocol ?? '') == 'none' ? ' selected' : '') . '>' . __('interface.misc.none') . '</option>
                                    <option value="tls"' . (($category->imapInbox->protocol ?? '') == 'tls' ? ' selected' : '') . '>' . __('interface.misc.tls') . '</option>
                                    <option value="ssl"' . (($category->imapInbox->protocol ?? '') == 'ssl' ? ' selected' : '') . '>' . __('interface.misc.ssl') . '</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_username" class="col-md-4 col-form-label text-md-right">' . __('interface.data.username') . '</label>

                            <div class="col-md-8">
                                <input id="imap_username" type="text" class="form-control" name="imap[username]" value="' . ($category->imapInbox->username ?? '') . '">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_password" class="col-md-4 col-form-label text-md-right">' . __('interface.data.password') . '</label>

                            <div class="col-md-8">
                                <input id="imap_password" type="password" class="form-control" name="imap[password]">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_folder" class="col-md-4 col-form-label text-md-right">' . __('interface.data.folder') . '</label>

                            <div class="col-md-8">
                                <input id="imap_folder" type="text" class="form-control" name="imap[folder]" value="' . ($category->imapInbox->folder ?? 'INBOX') . '">
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="imap_validate_cert" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.validate_certificate') . '</label>

                            <div class="col-md-8">
                                <input id="imap_validate_cert" type="checkbox" class="form-control" name="imap[validate_cert]" value="true"' . (($category->imapInbox->validate_cert ?? false) ? ' checked' : '') . '>
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="delete_after_import" class="col-md-4 col-form-label text-md-right">' . __('interface.misc.delete_after_import') . '</label>

                            <div class="col-md-8">
                                <input id="delete_after_import" type="checkbox" class="form-control" name="imap[delete_after_import]" value="true"' . (($category->imapInbox->delete_after_import ?? false) ? ' checked' : '') . '>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning w-100"><i class="bi bi-pencil-square"></i> ' . __('interface.actions.edit') . '</button>
                </form>
                <div class="my-4">
                    <table id="category-users-' . $category->id . '" class="table">
                        <thead>
                            <tr>
                                <td>' . __('interface.data.user') . '</td>
                                <td>' . __('interface.actions.delete') . '</td>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <form action="' . route('admin.support.categories.user.add', $category->id) . '" method="post">
                    <input type="hidden" name="_token" value="' . csrf_token() . '" />
                    <input type="hidden" name="category_id" value="' . $category->id . '" />

                    <div class="form-group row">
                        <label for="user_id" class="col-md-4 col-form-label text-md-right">' . __('interface.data.user_id') . '</label>

                        <div class="col-md-8">
                            <input id="user_id" type="number" min="1" class="form-control" name="user_id">
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
                        'delete'      => '<a href="' . route('admin.support.categories.delete', $category->id) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Add a support category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_categories_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'name'          => ['required', 'string'],
            'description'   => ['required', 'string'],
            'email_address' => ['string', 'nullable'],
            'email_name'    => ['string', 'nullable'],
        ])->validate();

        $category = SupportCategory::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'email_address' => $request->email_address ?? null,
            'email_name'    => $request->email_name ?? null,
        ]);

        if (
            ! empty($category) &&
            ! empty($request->imap['enable'])
        ) {
            Validator::make($request->imap, [
                'host'                => ['required', 'string'],
                'port'                => ['required', 'integer'],
                'protocol'            => ['required', 'string'],
                'username'            => ['string', 'nullable'],
                'password'            => ['string', 'nullable'],
                'folder'              => ['required', 'string'],
                'validate_cert'       => ['string',  'nullable'],
                'delete_after_import' => ['string',  'nullable'],
            ])->validate();

            /* @var ImapInbox $inbox */
            if (
                ! empty(
                    $inbox = ImapInbox::create([
                        'host'                => $request->imap['host'],
                        'username'            => $request->imap['username'],
                        'password'            => $request->imap['password'],
                        'port'                => (int) $request->imap['port'],
                        'protocol'            => $request->imap['protocol'],
                        'validate_cert'       => (bool) $request->imap['validate_cert'] ?? false,
                        'folder'              => $request->imap['folder'],
                        'delete_after_import' => (bool) $request->imap['delete_after_import'] ?? false,
                    ])
                )
            ) {
                $category->update([
                    'imap_inbox_id' => $inbox->id,
                ]);
            }
        }

        return redirect()->back()->with('success', __('interface.messages.ticket_category_added'));
    }

    /**
     * Update a support category.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_categories_update(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id'   => ['required', 'integer'],
            'name'          => ['required', 'string'],
            'description'   => ['required', 'string'],
            'email_address' => ['string', 'nullable'],
            'email_name'    => ['string', 'nullable'],
        ])->validate();

        /* @var SupportCategory $category */
        if (! empty($category = SupportCategory::find($request->category_id))) {
            $category->update([
                'name'          => $request->name,
                'description'   => $request->description,
                'email_address' => $request->email_address ?? null,
                'email_name'    => $request->email_name ?? null,
            ]);

            if (! empty($request->imap['enable'])) {
                Validator::make($request->imap, [
                    'host'                => ['required', 'string'],
                    'port'                => ['required', 'integer'],
                    'protocol'            => ['required', 'string'],
                    'username'            => ['string', 'nullable'],
                    'password'            => ['string', 'nullable'],
                    'folder'              => ['required', 'string'],
                    'validate_cert'       => ['string', 'nullable'],
                    'delete_after_import' => ['string', 'nullable'],
                ])->validate();

                if (! empty($inbox = $category->imapInbox)) {
                    $data = [
                        'host'                => $request->imap['host'],
                        'username'            => $request->imap['username'],
                        'port'                => (int) $request->imap['port'],
                        'protocol'            => $request->imap['protocol'],
                        'validate_cert'       => (bool) $request->imap['validate_cert'] ?? false,
                        'folder'              => $request->imap['folder'],
                        'delete_after_import' => (bool) $request->imap['delete_after_import'] ?? false,
                    ];

                    if (! empty($request->imap['password'])) {
                        $data['password'] = $request->imap['password'];
                    }

                    $inbox->update($data);
                } else {
                    /* @var ImapInbox $inbox */
                    if (
                        ! empty(
                            $inbox = ImapInbox::create([
                                'host'                => $request->imap['host'],
                                'username'            => $request->imap['username'],
                                'password'            => $request->imap['password'],
                                'port'                => (int) $request->imap['port'],
                                'protocol'            => $request->imap['protocol'],
                                'validate_cert'       => (bool) $request->imap['validate_cert'] ?? false,
                                'folder'              => $request->imap['folder'],
                                'delete_after_import' => (bool) $request->imap['delete_after_import'] ?? false,
                            ])
                        )
                    ) {
                        $category->update([
                            'imap_inbox_id' => $inbox->id,
                        ]);
                    }
                }
            } else {
                $category->imapInbox()->delete();

                $category->update([
                    'imap_inbox_id' => null,
                ]);
            }

            return redirect()->back()->with('success', __('interface.messages.ticket_category_updated'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a support category.
     *
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_categories_delete(int $id): RedirectResponse
    {
        Validator::make([
            'category_id' => $id,
        ], [
            'category_id' => ['required', 'integer'],
        ])->validate();

        SupportCategory::where('id', '=', $id)->delete();

        return redirect()->back()->with('success', __('interface.messages.ticket_category_deleted'));
    }

    /**
     * Get list of support category users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function support_category_user_list(Request $request): JsonResponse
    {
        session_write_close();

        $query = SupportCategory::find($request->id)->assignments();

        $totalCount = (clone $query)->count();

        if (! empty($request->search['value'])) {
            $query = $query->where(function (Builder $query) use ($request) {
                return $query->whereHas('user', function (Builder $builder) use ($request) {
                    return $builder->where('name', 'LIKE', '%' . $request->search['value'] . '%');
                });
            });
        }

        if (! empty($request->order)) {
            foreach ($request->order as $order) {
                switch ($request->columns[$order['column']]) {
                    case 'name':
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

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data'            => $query
                ->get()
                ->transform(function (SupportCategoryAssignment $assignment) {
                    return (object) [
                        'name'   => $assignment->user->name,
                        'delete' => '<a href="' . route('admin.support.categories.user.delete', ['id' => $assignment->category_id, 'category_link_id' => $assignment->id]) . '" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>',
                    ];
                }),
        ]);
    }

    /**
     * Add a support category user link.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_category_user_add(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'category_id' => ['required', 'integer'],
            'user_id'     => ['required', 'integer'],
        ])->validate();

        /* @var User|null $user */
        if (! empty($user = User::find($request->user_id))) {
            SupportCategoryAssignment::create([
                'category_id' => $request->category_id,
                'user_id'     => $user->id,
                'role'        => $user->role,
            ]);

            return redirect()->back()->with('success', __('interface.messages.ticket_category_user_link_added'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Delete a support category user link.
     *
     * @param int $category_id
     * @param int $category_link_id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_category_user_delete(int $category_id, int $category_link_id): RedirectResponse
    {
        Validator::make([
            'category_link_id' => $category_link_id,
        ], [
            'category_link_id' => ['required', 'integer'],
        ])->validate();

        SupportCategoryAssignment::where('id', '=', $category_link_id)->delete();

        return redirect()->back()->with('success', __('interface.messages.ticket_category_user_link_deleted'));
    }

    /**
     * Download a file linked to a support ticket.
     *
     * @param int $id
     * @param int $filelink_id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_file_download(int $id, int $filelink_id): RedirectResponse
    {
        Validator::make([
            'id'          => $id,
            'filelink_id' => $filelink_id,
        ], [
            'id'          => ['required', 'integer'],
            'filelink_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            ) &&
            ! empty(
                $file = $ticket->fileLinks
                        ->where('id', '=', $filelink_id)
                        ->first()
                        ->file ?? null
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
     * Delete a file linked to a support ticket.
     *
     * @param int $id
     * @param int $filelink_id
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_file_delete(int $id, int $filelink_id): RedirectResponse
    {
        Validator::make([
            'id'          => $id,
            'filelink_id' => $filelink_id,
        ], [
            'id'          => ['required', 'integer'],
            'filelink_id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            ) &&
            ! empty(
                $fileLink = $ticket->fileLinks
                    ->where('id', '=', $filelink_id)
                    ->first()
            )
        ) {
            SupportTicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'type'      => 'file',
                'action'    => 'remove',
                'reference' => $fileLink->file_id,
            ]);

            $fileLink->file()->delete();
            $fileLink->delete();

            return redirect()->back()->with('success', __('interface.messages.ticket_attachment_removed'));
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }

    /**
     * Create a new support ticket answer.
     *
     * @param Request $request
     *
     * @throws ValidationException
     *
     * @return RedirectResponse
     */
    public function support_file_upload(Request $request): RedirectResponse
    {
        Validator::make($request->toArray(), [
            'id' => ['required', 'integer'],
        ])->validate();

        if (
            ! empty(
                $ticket = SupportTicket::where('id', '=', $request->id)
                    ->whereHas('assignments', function (Builder $builder) {
                        return $builder->where('user_id', '=', Auth::id());
                    })
                    ->first()
            ) &&
            ! empty($file = $request->files->get('file'))
        ) {
            $file = File::create([
                'user_id'   => ! empty($request->private) ? Auth::id() : null,
                'folder_id' => null,
                'name'      => Carbon::now()->format('YmdHis') . '_' . $file->getClientOriginalName(),
                'data'      => $file->getContent(),
                'mime'      => $file->getClientMimeType(),
                'size'      => $file->getSize(),
            ]);

            if ($file instanceof File) {
                SupportTicketFile::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'file_id'   => $file->id,
                ]);

                SupportTicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'type'      => 'file',
                    'action'    => 'add',
                    'reference' => $file->id,
                ]);

                $ticket->sendEmailFileUploadNotification();

                return redirect()->back()->with('success', __('interface.messages.ticket_attachment_uploaded'));
            }
        }

        return redirect()->back()->with('warning', __('interface.misc.something_wrong_notice'));
    }
}
