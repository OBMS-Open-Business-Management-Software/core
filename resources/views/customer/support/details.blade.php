@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('Details') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Status') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch($ticket->status)
                                    @case('open')
                                        <span class="badge badge-primary badge-pill">{{ __('Open') }}</span>
                                        @break
                                    @case('closed')
                                        <span class="badge badge-secondary badge-pill">{{ __('Closed') }}</span>
                                        @break
                                    @case('locked')
                                        <span class="badge badge-danger badge-pill">{{ __('Locked') }}</span>
                                        @break
                                @endswitch
                                @if ($ticket->escalated)
                                    <span class="badge badge-warning badge-pill">{{ __('Escalated') }}</span>
                                @endif
                                @if ($ticket->hold)
                                    <span class="badge badge-secondary badge-pill">{{ __('On-Hold') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Priority') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch($ticket->priority)
                                    @case('low')
                                    @default
                                        <span class="badge badge-secondary badge-pill">{{ __('Low') }}</span>
                                        @break
                                    @case('medium')
                                        <span class="badge badge-success badge-pill">{{ __('Medium') }}</span>
                                        @break
                                    @case('high')
                                        <span class="badge badge-warning badge-pill">{{ __('High') }}</span>
                                        @break
                                    @case('emergency')
                                        <span class="badge badge-danger badge-pill">{{ __('Emergency') }}</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Subject') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->subject }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Category') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->category->name ?? __('Uncategorized') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Created at') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $ticket->created_at->format('d.m.Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-chat"></i> {{ __('History') }}
                    </div>
                    <div class="card-body py-3">
                        @foreach ($ticket->historyItems as $item)
                            @if ($item instanceof \App\Models\Support\SupportTicketMessage && ! $item->note)
                                <div class="card my-2" style="border-left: .5rem solid var(--primary) !important">
                                    <div class="card-body">
                                        {{ $item->message }}<br>
                                        <br>
                                        {{ $item->user->name }}
                                        <span class="small d-block">{{ __('Posted at:') }} {{ $item->created_at->format('d.m.Y, H:i') }}</span>
                                    </div>
                                </div>
                            @elseif ($item instanceof \App\Models\Support\SupportTicketHistory)
                                @if ($item->type !== 'file' || (! empty($link = $item->referenceAttribute->ticketLinks->first()) && ! $link->internal) || empty($item->referenceAttribute->ticketLinks))
                                    <div class="card my-2" style="border-left: .5rem solid rgba(0, 0, 0, 0.125) !important">
                                        <div class="card-body">
                                            <i class="bi bi-info-circle"></i>
                                            @switch($item->type)
                                                @case('status')
                                                    {{ __('Ticket status changed:') }}
                                                    @switch($item->action)
                                                        @case('open')
                                                            <span class="font-weight-bold">{{ __('Open') }}</span>
                                                            @break
                                                        @case('close')
                                                            <span class="font-weight-bold">{{ __('Closed') }}</span>
                                                            @break
                                                        @case('reopen')
                                                            <span class="font-weight-bold">{{ __('Re-opened') }}</span>
                                                            @break
                                                        @case('lock')
                                                            <span class="font-weight-bold">{{ __('Locked') }}</span>
                                                            @break
                                                        @case('unlock')
                                                            <span class="font-weight-bold">{{ __('Unlocked') }}</span>
                                                            @break
                                                    @endswitch
                                                    @break
                                                @case('assignment')
                                                    {{ __('Assignment changed:') }}
                                                    @switch($item->action)
                                                        @case('assign')
                                                            <span class="font-weight-bold">{{ __('Added user') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name . ' (#' . $item->referenceAttribute->id . ')' : '#' . ($item->reference ?? $item->user_id) }}"</span>
                                                            @break
                                                        @case('unassign')
                                                            <span class="font-weight-bold">{{ __('Removed user') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name . ' (#' . $item->referenceAttribute->id . ')' : '#' . ($item->reference ?? $item->user_id) }}"</span>
                                                            @break
                                                    @endswitch
                                                    @break
                                                @case('hold')
                                                    {{ __('Hold status changed:') }}
                                                    @switch($item->action)
                                                        @case('hold')
                                                            <span class="font-weight-bold">{{ __('On-hold') }}</span>
                                                            @break
                                                        @case('unhold')
                                                            <span class="font-weight-bold">{{ __('Not on-hold') }}</span>
                                                            @break
                                                    @endswitch
                                                    @break
                                                @case('escalate')
                                                    {{ __('Escalation status changed:') }}
                                                    @switch($item->action)
                                                        @case('escalate')
                                                            <span class="font-weight-bold">{{ __('Escalated') }}</span>
                                                            @break
                                                        @case('deescalate')
                                                            <span class="font-weight-bold">{{ __('Deescalated') }}</span>
                                                            @break
                                                    @endswitch
                                                    @break
                                                @case('run')
                                                    {{ __('Processed in ticket run:') }}
                                                    @switch($item->action)
                                                        @case('opened')
                                                            <span class="font-weight-bold">{{ __('Opened') }}</span>
                                                    @endswitch
                                                    @break
                                                @case('category')
                                                    {{ __('Category changed:') }}
                                                    @switch($item->action)
                                                        @case('move')
                                                            <span class="font-weight-bold">{{ __('Moved to') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('Uncategorized')) }}"</span>
                                                    @endswitch
                                                    @break
                                                @case('priority')
                                                    {{ __('Priority changed:') }}
                                                    @switch($item->action)
                                                        @case('set')
                                                            <span class="font-weight-bold">{{ __('Set to') }} "{{ __(ucfirst($item->reference ?? __('Unknown'))) }}"</span>
                                                    @endswitch
                                                    @break
                                                @case('file')
                                                    {{ __('Attachment:') }}
                                                    @switch($item->action)
                                                        @case('add')
                                                            <span class="font-weight-bold">{{ __('Uploaded') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('Unknown')) }}"</span>
                                                            @break
                                                        @case('remove')
                                                            <span class="font-weight-bold">{{ __('Removed') }} "{{ ! empty($item->referenceAttribute) ? $item->referenceAttribute->name : (! empty($item->reference) ? '#' . $item->reference : __('Unknown')) }}"</span>
                                                            @break
                                                    @endswitch
                                                    @break
                                            @endswitch
                                            <br>
                                            <br>
                                            {{ $item->user->name }}
                                            <span class="small d-block">{{ __('Performed at:') }} {{ $item->created_at->format('d.m.Y, H:i') }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('customer.support') }}" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to list') }}</a>
                @switch($ticket->status)
                    @case('open')
                        <a class="btn btn-primary w-100 mb-3" data-toggle="modal" data-target="#answer"><i class="bi bi-pencil-square"></i> {{ __('Answer') }}</a>
                        <a href="{{ route('customer.support.close', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-check-circle"></i> {{ __('interface.actions.close') }}</a>
                        @break
                    @case('closed')
                        <a href="{{ route('customer.support.reopen', $ticket->id) }}" class="btn btn-success w-100 mb-3"><i class="bi bi-play-circle"></i> {{ __('Re-open') }}</a>
                        @break
                    @case('locked')
                        <div class="alert alert-warning mb-3"><i class="bi bi-exclamation-triangle"></i> {{ __('The ticket has been locked by an employee or administrator. You are not permitted to perform any action.') }}</div>
                        @break
                @endswitch
                <div class="card mt-1">
                    <div class="card-header">
                        <i class="bi bi-files-alt"></i> {{ __('Attachments') }}
                    </div>
                    <div class="card-body">
                        @if (! empty($ticket->fileLinks->where('internal', '=', false)->isNotEmpty()))
                            <ul class="mb-0 list-unstyled">
                                @foreach ($ticket->fileLinks->where('internal', '=', false) as $fileLink)
                                    @if (! empty($file = $fileLink->file))
                                        <li>
                                            <div class="row align-items-center">
                                                <div class="col-md-9">
                                                    {{ $file->name }}
                                                    <span class="small d-block">{{ __('Size:') }} {{ $file->size }}B</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <a href="{{ route('customer.support.file.download', ['id' => $ticket->id, 'filelink_id' => $fileLink->id]) }}" class="btn btn-warning btn-sm d-block mb-1" download><i class="bi bi-download"></i></a>
                                                    @if ($fileLink->user_id == Auth::id())
                                                        <a href="{{ route('customer.support.file.delete', ['id' => $ticket->id, 'filelink_id' => $fileLink->id]) }}" class="btn btn-danger btn-sm d-block"><i class="bi bi-trash"></i></a>
                                                    @else
                                                        <button type="button" class="btn btn-danger btn-sm d-block w-100" disabled><i class="bi bi-trash"></i></button>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($ticket->fileLinks->where('internal', '=', false)->last() !== $fileLink)
                                                <hr>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('This ticket does not contain any files.') }}
                            </div>
                        @endif
                        <a class="btn btn-warning w-100 mt-3" data-toggle="modal" data-target="#upload"><i class="bi bi-upload"></i> {{ __('Upload') }}</a>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-people"></i> {{ __('Active Participants') }}
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 list-unstyled">
                            @foreach ($ticket->activeAssignments as $assignment)
                                @switch($ticket->assignments->where('user_id', '=', $assignment->user_id)->first()->role ?? null)
                                    @case('customer')
                                        @php ($role = __('Customer'))
                                        @break
                                    @case('employee')
                                        @php ($role = __('Employee'))
                                        @break
                                    @case('admin')
                                        @php ($role = __('Administrator'))
                                        @break
                                @endswitch
                                <li>
                                    {{ $assignment->user->name }} ({{ $role ?? __('N/A') }})
                                    <span class="small d-block">{{ __('Since:') }} {{ $assignment->created_at->format('d.m.Y, H:i') }}</span>
                                    @if ($ticket->activeAssignments->last() !== $assignment)
                                        <hr>
                                    @endif
                                </li>
                            @endforeach
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
                    <h5 class="modal-title" id="answerLabel"><i class="bi bi-pencil-square"></i> {{ __('Answer Ticket') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('customer.support.answer', $ticket->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('Message') }}</label>

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
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('File') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('Answer') }}</button>
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
                    <h5 class="modal-title" id="uploadLabel"><i class="bi bi-upload"></i> {{ __('Upload File') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('customer.support.file.upload', $ticket->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $ticket->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="file" class="col-md-4 col-form-label text-md-right">{{ __('File') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> {{ __('Upload') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
