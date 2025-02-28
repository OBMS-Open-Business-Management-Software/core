@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.misc.details') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.status') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch($ticket->status)
                                    @case('open')
                                        <span class="badge badge-primary">{{ __('interface.status.open') }}</span>
                                        @break
                                    @case('closed')
                                        <span class="badge badge-secondary">{{ __('interface.status.closed') }}</span>
                                        @break
                                    @case('locked')
                                        <span class="badge badge-danger">{{ __('interface.status.locked') }}</span>
                                        @break
                                @endswitch
                                @if ($ticket->escalated)
                                    <span class="badge badge-warning">{{ __('interface.status.escalated') }}</span>
                                @endif
                                @if ($ticket->hold)
                                    <span class="badge badge-secondary">{{ __('interface.status.on_hold') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.source') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($ticket->external)
                                    <span class="badge badge-warning">{{ __('interface.data.email') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('interface.misc.ticket_system') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.priority') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch($ticket->priority)
                                    @case('low')
                                    @default
                                        <span class="badge badge-secondary">{{ __('interface.priorities.low') }}</span>
                                        @break
                                    @case('medium')
                                        <span class="badge badge-success">{{ __('interface.priorities.medium') }}</span>
                                        @break
                                    @case('high')
                                        <span class="badge badge-warning">{{ __('interface.priorities.high') }}</span>
                                        @break
                                    @case('emergency')
                                        <span class="badge badge-danger">{{ __('interface.priorities.emergency') }}</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.subject') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->subject }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.category') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->category->name ?? __('interface.status.uncategorized') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.misc.created_at') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->created_at->format('d.m.Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-chat"></i> {{ __('interface.data.history') }}
                    </div>
                    <div class="card-body py-3">
                        @foreach ($ticket->historyItems as $item)
                            @if ($item instanceof \App\Models\Support\SupportTicketMessage && ! $item->note)
                                <div class="card my-2" style="border-left: .5rem solid var(--primary) !important">
                                    <div class="card-body">
                                        {{ $item->message }}<br>
                                        <br>
                                        {{ $item->user->name }}
                                        <span class="small d-block">{{ __('interface.misc.posted_at') }}: {{ $item->created_at->format('d.m.Y, H:i') }}</span>
                                    </div>
                                </div>
                            @elseif ($item instanceof \App\Models\Support\SupportTicketMessage && $item->note)
                                <div class="card my-2" style="border-left: .5rem solid var(--secondary) !important">
                                    <div class="card-body">
                                        <i class="bi bi-sticky"></i> {{ $item->message }}<br>
                                        <br>
                                        {{ $item->user->name }}
                                        <span class="small d-block">{{ __('interface.misc.posted_at') }}: {{ $item->created_at->format('d.m.Y, H:i') }}</span>
                                    </div>
                                </div>
                            @elseif ($item instanceof \App\Models\Support\SupportTicketHistory)
                                <div class="card my-2" style="border-left: .5rem solid rgba(0, 0, 0, 0.125) !important">
                                    <div class="card-body">
                                        <i class="bi bi-info-circle"></i>
                                        @switch($item->type)
                                            @case('status')
                                                {{ __('interface.misc.ticket_status_changed') }}:
                                                @switch($item->action)
                                                    @case('open')
                                                        <span class="font-weight-bold">{{ __('interface.status.open') }}</span>
                                                        @break
                                                    @case('close')
                                                        <span class="font-weight-bold">{{ __('interface.status.closed') }}</span>
                                                        @break
                                                    @case('reopen')
                                                        <span class="font-weight-bold">{{ __('interface.status.reopened') }}</span>
                                                        @break
                                                    @case('lock')
                                                        <span class="font-weight-bold">{{ __('interface.status.locked') }}</span>
                                                        @break
                                                    @case('unlock')
                                                        <span class="font-weight-bold">{{ __('interface.status.unlocked') }}</span>
                                                        @break
                                                @endswitch
                                                @break
                                            @case('assignment')
                                                {{ __('interface.misc.assignment_changed') }}:
                                                @switch($item->action)
                                                    @case('assign')
                                                        <span class="font-weight-bold">{{ __('interface.misc.added_user') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name . ' (#' . $item->referenceAttribute->id . ')' : '#' . ($item->reference ?? $item->user_id) }}"</span>
                                                        @break
                                                    @case('unassign')
                                                        <span class="font-weight-bold">{{ __('interface.misc.removed_user') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name . ' (#' . $item->referenceAttribute->id . ')' : '#' . ($item->reference ?? $item->user_id) }}"</span>
                                                        @break
                                                @endswitch
                                                @break
                                            @case('hold')
                                                {{ __('interface.misc.hold_status_changed') }}:
                                                @switch($item->action)
                                                    @case('hold')
                                                        <span class="font-weight-bold">{{ __('interface.status.on_hold') }}</span>
                                                        @break
                                                    @case('unhold')
                                                        <span class="font-weight-bold">{{ __('interface.status.not_on_hold') }}</span>
                                                        @break
                                                @endswitch
                                                @break
                                            @case('escalate')
                                                {{ __('interface.misc.escalation_status_changed') }}:
                                                @switch($item->action)
                                                    @case('escalate')
                                                        <span class="font-weight-bold">{{ __('interface.status.escalated') }}</span>
                                                        @break
                                                    @case('deescalate')
                                                        <span class="font-weight-bold">{{ __('interface.status.deescalated') }}</span>
                                                        @break
                                                @endswitch
                                                @break
                                            @case('run')
                                                {{ __('interface.misc.processed_in_ticket_run') }}:
                                                @switch($item->action)
                                                    @case('opened')
                                                        <span class="font-weight-bold">{{ __('interface.status.opened') }}</span>
                                                @endswitch
                                                @break
                                            @case('category')
                                                {{ __('interface.misc.category_changed') }}:
                                                @switch($item->action)
                                                    @case('move')
                                                        <span class="font-weight-bold">{{ __('interface.misc.moved_to') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('interface.status.uncategorized')) }}"</span>
                                                @endswitch
                                                @break
                                            @case('priority')
                                                {{ __('interface.misc.priority_changed') }}:
                                                @switch($item->action)
                                                    @case('set')
                                                        <span class="font-weight-bold">{{ __('interface.misc.set_to') }} "{{ __(ucfirst($item->reference ?? __('interface.status.unknown'))) }}"</span>
                                                @endswitch
                                                @break
                                            @case('file')
                                                {{ __('interface.data.attachment') }}:
                                                @switch($item->action)
                                                    @case('add')
                                                        <span class="font-weight-bold">{{ __('interface.status.uploaded') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('interface.status.unknown')) }}"</span>
                                                        @break
                                                    @case('remove')
                                                        <span class="font-weight-bold">{{ __('interface.status.removed') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('interface.status.unknown')) }}"</span>
                                                        @break
                                                @endswitch
                                                @break
                                        @endswitch
                                        <br>
                                        <br>
                                        {{ $item->user->name }}
                                        <span class="small d-block">{{ __('interface.misc.performed_at') }}: {{ $item->created_at->format('d.m.Y, H:i') }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.support') }}" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                @if ($ticket->assignments->where('user_id', '=', Auth::id())->isNotEmpty())
                    @switch($ticket->status)
                        @case('open')
                            <a class="btn btn-primary w-100 mb-3" data-toggle="modal" data-target="#answer"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.answer') }}</a>
                            @if ($ticket->hold)
                                <a href="{{ route('admin.support.unhold', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-play-circle"></i> {{ __('interface.actions.unhold') }}</a>
                            @else
                                <a href="{{ route('admin.support.hold', $ticket->id) }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-pause-circle"></i> {{ __('interface.actions.hold') }}</a>
                            @endif
                            @if ($ticket->escalated)
                                <a href="{{ route('admin.support.deescalate', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-arrow-down-circle"></i> {{ __('interface.actions.deescalate') }}</a>
                            @else
                                <a href="{{ route('admin.support.escalate', $ticket->id) }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-arrow-up-circle"></i> {{ __('interface.actions.escalate') }}</a>
                            @endif
                            <a class="btn btn-warning w-100 mb-3" data-toggle="modal" data-target="#move"><i class="bi bi-truck"></i> {{ __('interface.actions.move') }}</a>
                            <a class="btn btn-warning w-100 mb-3" data-toggle="modal" data-target="#priority"><i class="bi bi-star"></i> {{ __('interface.actions.set_priority') }}</a>
                            <a href="{{ route('admin.support.close', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-check-circle"></i> {{ __('interface.actions.close') }}</a>
                            <a href="{{ route('admin.support.lock', $ticket->id) }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-lock"></i> {{ __('interface.actions.lock') }}</a>
                            @break
                        @case('closed')
                            <a href="{{ route('admin.support.reopen', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-play-circle"></i> {{ __('interface.actions.reopen') }}</a>
                            <a href="{{ route('admin.support.lock', $ticket->id) }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-lock"></i> {{ __('interface.actions.lock') }}</a>
                            @break
                        @case('locked')
                            <a href="{{ route('admin.support.unlock', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-unlock"></i> {{ __('interface.actions.unlock') }}</a>
                            @break
                    @endswitch

                    <a href="{{ route('admin.support.leave', $ticket->id) }}" class="btn btn-danger w-100 mb-3"><i class="bi bi-door-open"></i> {{ __('interface.actions.leave') }}</a>
                @else
                    <a href="{{ route('admin.support.join', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-door-open"></i> {{ __('interface.actions.join') }}</a>
                @endif
                <div class="card mt-1">
                    <div class="card-header">
                        <i class="bi bi-arrow-right-circle"></i> {{ __('interface.actions.run') }}
                    </div>
                    <div class="card-body">
                        @if (empty($run))
                            <a href="#" class="btn btn-success w-100" data-toggle="modal" data-target="#runStart"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start') }}</a>
                        @else
                            <label class="font-weight-bold mb-0">{{ __('interface.data.category') }}:</label> {{ ! empty($run->category_id) ? (! empty($run->category) ? $run->category->name : __('interface.status.uncategorized')) : __('interface.misc.all') }}<br>
                            <label class="font-weight-bold mb-3">{{ __('interface.misc.started_at') }}:</label> {{ $run->created_at->format('d.m.Y, H:i') }}
                            <a href="{{ route('admin.support.run.next') }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-skip-forward-fill"></i> {{ __('interface.actions.next') }}</a>
                            <a href="{{ route('admin.support.run.stop') }}" class="btn btn-danger w-100"><i class="bi bi-stop-fill"></i> {{ __('interface.actions.stop') }}</a>
                        @endif
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-files-alt"></i> {{ __('interface.data.attachments') }}
                    </div>
                    <div class="card-body">
                        @if (! empty($ticket->fileLinks->isNotEmpty()))
                            <ul class="mb-0 list-unstyled">
                                @foreach ($ticket->fileLinks as $fileLink)
                                    @if (! empty($file = $fileLink->file))
                                        <li>
                                            <div class="row align-items-center">
                                                <div class="col-md-9">
                                                    {{ $file->name }}
                                                    @if ($fileLink->internal)
                                                        <i class="bi bi-lock-fill"></i>
                                                    @endif
                                                    <span class="small d-block">{{ __('interface.data.size') }}: {{ $file->size }}B</span>
                                                </div>
                                                <div class="col-md-3">
                                                    @if ($ticket->assignments->where('user_id', '=', Auth::id())->isNotEmpty())
                                                        <a href="{{ route('admin.support.file.download', ['id' => $ticket->id, 'filelink_id' => $fileLink->id]) }}" class="btn btn-warning btn-sm d-block mb-1" download><i class="bi bi-download"></i></a>
                                                        <a href="{{ route('admin.support.file.delete', ['id' => $ticket->id, 'filelink_id' => $fileLink->id]) }}" class="btn btn-danger btn-sm d-block"><i class="bi bi-trash"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($ticket->fileLinks->last() !== $fileLink)
                                                <hr>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.support.no_files') }}
                            </div>
                        @endif
                        @if ($ticket->assignments->where('user_id', '=', Auth::id())->isNotEmpty())
                            <a class="btn btn-warning w-100 mt-3" data-toggle="modal" data-target="#upload"><i class="bi bi-upload"></i> {{ __('interface.actions.upload') }}</a>
                        @endif
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-people"></i> {{ __('interface.data.active_participants') }}
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 list-unstyled">
                            @if ($ticket->activeAssignments->isNotEmpty())
                                @foreach ($ticket->activeAssignments as $assignment)
                                    @switch($ticket->assignments->where('user_id', '=', $assignment->user_id)->first()->role ?? null)
                                        @case('customer')
                                            @php ($role = __('interface.user_type.customer'))
                                            @break
                                        @case('employee')
                                            @php ($role = __('interface.user_type.employee'))
                                            @break
                                        @case('admin')
                                            @php ($role = __('interface.user_type.administrator'))
                                            @break
                                    @endswitch
                                    <li>
                                        {{ $assignment->user->name }} ({{ $role ?? __('interface.misc.not_available') }})
                                        <span class="small d-block">{{ __('interface.misc.since') }}: {{ $assignment->created_at->format('d.m.Y, H:i') }}</span>
                                        @if ($ticket->activeAssignments->last() !== $assignment)
                                            <hr>
                                        @endif
                                    </li>
                                @endforeach
                            @else
                                <div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.ticket_without_participants_notice') }}</div>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="answer" tabindex="-1" aria-labelledby="answerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="answerLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.support.answer') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.support.answer', $ticket->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.message') }}</label>

                            <div class="col-md-8">
                                <textarea id="message" class="form-control @error('message') is-invalid @enderror" name="message" style="height: 15rem">{{ old('message') }}</textarea>

                                @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.file') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('interface.actions.choose_file') }}</label>
                                </div>

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.internal_note') }}</label>

                            <div class="col-md-8">
                                <input id="note" type="checkbox" class="form-control @error('note') is-invalid @enderror" name="note" value="true">

                                @error('note')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.answer') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="move" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-truck"></i> {{ __('interface.actions.move_ticket') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.support.move', $ticket->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="category_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.category') }}</label>

                            <div class="col-md-8">
                                <select id="category_id" class="form-control @error('category_id') is-invalid @enderror" name="category_id">
                                    <option value="0">{{ __('interface.status.unassigned') }}</option>
                                    @foreach ($move_categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>

                                @error('category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-truck"></i> {{ __('interface.actions.move') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="priority" tabindex="-1" aria-labelledby="priorityLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="priorityLabel"><i class="bi bi-star"></i> {{ __('interface.actions.set_priority') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.support.priority', $ticket->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="category_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.priority') }}</label>

                            <div class="col-md-8">
                                <select id="priority" class="form-control @error('priority') is-invalid @enderror" name="priority">
                                    <option value="low"{{ $ticket->priority == 'low' ? ' selected' : '' }}>{{ __('interface.priorities.low') }}</option>
                                    <option value="medium"{{ $ticket->priority == 'medium' ? ' selected' : '' }}>{{ __('interface.priorities.medium') }}</option>
                                    <option value="high"{{ $ticket->priority == 'high' ? ' selected' : '' }}>{{ __('interface.priorities.high') }}</option>
                                    <option value="emergency"{{ $ticket->priority == 'emergency' ? ' selected' : '' }}>{{ __('interface.priorities.emergency') }}</option>
                                </select>

                                @error('priority')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-star"></i> {{ __('interface.actions.set_priority') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload" tabindex="-1" aria-labelledby="uploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadLabel"><i class="bi bi-upload"></i> {{ __('interface.actions.upload_file') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.support.file.upload', $ticket->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="file" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.file') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('interface.actions.choose_file') }}</label>
                                </div>

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> {{ __('interface.actions.upload') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (empty($run))
        <div class="modal fade" id="runStart" tabindex="-1" aria-labelledby="runStartLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="runStartLabel"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start_ticket_run') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.support.run.start') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="category" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.category') }}</label>

                                <div class="col-md-8">
                                    <select id="category" class="form-control @error('category') is-invalid @enderror" name="category">
                                        <option value="">{{ __('interface.misc.all') }}</option>
                                        <option value="0">{{ __('interface.status.uncategorized') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"{{ $category->id == old('category') ? ' selected' : '' }}></option>
                                        @endforeach
                                    </select>

                                    @error('category')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
