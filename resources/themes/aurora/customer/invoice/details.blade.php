@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('customer.invoices') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                @if (! empty($invoice->original) && $invoice->status == 'refund')
                    <a href="{{ route('customer.invoices.details', $invoice->original->id) }}" class="btn btn-primary mb-4"><i class="bi bi-file-earmark-text"></i> {{ __('interface.actions.open_original') }}</a>
                @endif
                @if (! empty($invoice->refunded) && ($invoice->status == 'refunded' || $invoice->status == 'revoked'))
                    <a href="{{ route('customer.invoices.details', $invoice->refunded->id) }}" class="btn btn-primary mb-4"><i class="bi bi-file-earmark-text"></i> {{ __('interface.actions.open_refund') }}</a>
                @endif
                @if ($invoice->status !== 'template')
                    <a href="{{ route('customer.invoices.download', $invoice->id) }}" class="btn btn-primary mb-4 mr-1 float-right" download><i class="bi bi-download"></i> {{ __('interface.actions.download') }}</a>
                @endif
                @if ($invoice->status == 'unpaid')
                    <a class="btn btn-primary float-right mr-1" data-toggle="modal" data-target="#pay"><i class="bi bi-cash-stack"></i> {{ __('interface.actions.pay') }}</a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.misc.details') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.number') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->number ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->type->name ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.status') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch ($invoice->status)
                                    @case ('unpaid')
                                        @if ($invoice->overdue)
                                            <span class="badge badge-danger">{{ __('interface.status.overdue') }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ __('interface.status.unpaid') }}</span>
                                        @endif
                                        @break
                                    @case ('paid')
                                        <span class="badge badge-success">{{ __('interface.status.paid') }}</span>
                                        @break
                                    @case ('refunded')
                                        <span class="badge badge-secondary">{{ __('interface.status.refunded') }}</span>
                                        @break
                                    @case ('refund')
                                        <span class="badge badge-info text-white">{{ __('interface.actions.refund') }}</span>
                                        @break
                                    @case ('revoked')
                                        <span class="badge badge-secondary">{{ __('interface.status.revoked') }}</span>
                                        @break
                                    @case ('template')
                                    @default
                                        <span class="badge badge-primary">{{ __('interface.status.draft') }}</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.reverse_charge') }}</label>

                            <div class="col-md-9 col-form-label">
                                {!! $invoice->reverse_charge ? '<span class="badge badge-success">' . __('interface.misc.applicable') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.not_applicable') . '</span>' !!}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.misc.created_at') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->created_at->format('d.m.Y, H:i') }}
                            </div>
                        </div>
                        @if (isset($invoice->archived_at))
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.invoice_date') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $invoice->archived_at->format('d.m.Y, H:i') }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $invoice->status == 'refund' ? __('interface.documents.refunded_until') : __('interface.documents.payable_until') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $invoice->archived_at->addDays($invoice->type->period)->format('d.m.Y') }}, 23:59
                                </div>
                            </div>
                        @endif
                        @if (! empty($discount = $invoice->type->discount))
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.discount') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $discount->percentage_amount }} % {{ __('interface.misc.when_paid_within') }} {{ $discount->period }} {{ __('interface.units.days') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.data.positions') }}
                    </div>
                    <div class="card-body">
                        @if ($invoice->positionLinks->isNotEmpty())
                            <table class="table mt-4 w-100">
                                <thead>
                                <tr>
                                    <td>{{ __('interface.documents.position') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.net_unit') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.units') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.net_position') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.vat') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.gross_position') }}</td>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->positionLinks as $link)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $link->position->name }}</span><br>
                                                {{ $link->position->description }}
                                                <small class="d-block">
                                                    @if (isset($link->started_at))
                                                        {{ __('interface.time.from') }}: {{ $link->started_at->format('d.m.Y H:i') }}
                                                    @endif
                                                    @if (isset($link->started_at, $link->ended_at))
                                                        |
                                                    @endif
                                                    @if (isset($link->ended_at))
                                                        {{ __('interface.time.to') }}: {{ $link->ended_at->format('d.m.Y H:i') }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td style="width: 10%">{{ number_format($link->position->amount, 2) }} €</td>
                                            <td style="width: 10%">{{ $link->position->quantity }}</td>
                                            <td style="width: 10%">{{ number_format($link->position->netSum, 2) }} €</td>
                                            <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                            <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? number_format($link->position->netSum, 2) : number_format($link->position->grossSum, 2) }} €</td>
                                        </tr>
                                        @if (! empty($discount = $link->position->discount))
                                            @switch ($discount->type)
                                                @case ('percentage')
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                                            {{ number_format($discount->amount, 2) }} %
                                                        </td>
                                                        <td style="width: 10%">- {{ number_format($link->position->amount * ($discount->amount / 100), 2) }} €</td>
                                                        <td style="width: 10%">{{ $link->position->quantity }}</td>
                                                        <td style="width: 10%">- {{ number_format($link->position->netSum * ($discount->amount / 100), 2) }} €</td>
                                                        <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $invoice->reverse_charge ? number_format($link->position->netSum * ($discount->amount / 100), 2) : number_format($link->position->grossSum * ($discount->amount / 100), 2) }} €</td>
                                                    </tr>
                                                    @break
                                                @case ('fixed')
                                                @default
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                                            {{ number_format($discount->amount, 2) }} €
                                                        </td>
                                                        <td style="width: 10%">- {{ number_format($discount->amount, 2) }} €</td>
                                                        <td style="width: 10%">{{ $link->position->quantity }}</td>
                                                        <td style="width: 10%">- {{ number_format($discount->amount * $link->position->quantity, 2) }} €</td>
                                                        <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $invoice->reverse_charge ? number_format($discount->amount * $link->position->quantity, 2) : number_format($discount->amount * $link->position->quantity * (1 + ($link->position->vat_percentage / 100)), 2) }} €</td>
                                                    </tr>
                                                    @break
                                            @endswitch
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td colspan="2" class="bg-primary text-white">
                                            {{ __('interface.documents.net_sum') }}
                                        </td>
                                        <td class="bg-primary text-white">{{ number_format($invoice->netSum, 2) }} €</td>
                                    </tr>
                                    @foreach ($invoice->vatPositions as $percentage => $amount)
                                        <tr>
                                            <td class="border-0"></td>
                                            <td colspan="2" class="bg-primary text-white border-0">
                                                {{ $percentage }} % {{ __('interface.documents.vat') }}
                                            </td>
                                            <td class="bg-primary text-white border-0">{{ number_format($amount, 2) }} €</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td class="border-0"></td>
                                        <td colspan="2" class="bg-primary text-white border-0">
                                            {{ __('interface.documents.gross_sum') }}
                                        </td>
                                        <td class="bg-primary text-white border-0">{{ number_format($invoice->grossSum, 2) }} €</td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.invoices.no_positions') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ $invoice->type->dunning && $invoice->status !== 'refund' ? 'col-md-6' : 'col-md-12' }}">
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-exclamation-circle"></i> {{ __('interface.misc.status_history') }}
                    </div>
                    <div class="card-body">
                        @if ($invoice->history()->exists())
                            <table class="table" id="history">
                                <thead>
                                <tr>
                                    <td>#</td>
                                    <td>{{ __('interface.data.date') }}</td>
                                    <td>{{ __('interface.data.name') }}</td>
                                    <td>{{ __('interface.data.status') }}</td>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.no_status_history') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if ($invoice->type->dunning && $invoice->status !== 'refund')
                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-header">
                            <i class="bi bi-exclamation-triangle"></i> {{ __('interface.data.dunning') }}
                        </div>
                        <div class="card-body">
                            @if ($invoice->type->dunnings->isNotEmpty())
                                <table class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.overdue_time') }}</td>
                                        <td style="width: 10%">{{ __('interface.data.date') }}</td>
                                        <td style="width: 10%">{{ __('interface.status.sent') }}</td>
                                        <td style="width: 1%">{{ __('interface.actions.download') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($invoice->type->dunnings->sortBy('after') as $dunning)
                                        <tr>
                                            <td> {{ $dunning->after }} {{ __('interface.units.days') }}</td>
                                            <td style="width: 20%">{{ ! empty($invoice->archived_at) ? $invoice->archived_at->addDays($invoice->type->period)->addDays($dunning->after)->format('d.m.Y') : $invoice->created_at->addDays($invoice->type->period)->addDays($dunning->after)->format('d.m.Y') }}, 00:00</td>
                                            <td style="width: 10%">{!! $invoice->reminders->contains('dunning_id', $dunning->id) ? '<span class="badge badge-success">' . __('interface.status.generated') . '</span>' : '<span class="badge badge-secondary">' . __('interface.status.ungenerated') . '</span>' !!}</td>
                                            <td style="width: 10%">{!! $invoice->reminders->contains('dunning_id', $dunning->id) && $invoice->status !== 'template' ? '<a href="' . route('admin.invoices.customers.reminders.download', ['id' => $invoice->id, 'reminder_id' => $invoice->reminders->where('dunning_id', '=', $dunning->id)->first()->id]) . '" class="btn btn-primary btn-sm"><i class="bi bi-download"></i></a>' : '<button type="button" class="btn btn-primary btn-sm" disabled><i class="bi bi-download"></i></button>' !!}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.no_dunning_reminder_notice') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="pay" tabindex="-1" aria-labelledby="payLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="payLabel"><i class="bi bi-cash-stack"></i> {{ __('interface.actions.pay') }} ({{ $invoice->number }})</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('customer.profile.transactions.invoice', $invoice->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="modal-body">
                        <div class="form-group row mb-4">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input id="amount" type="number" step="0.01" min="0.01" class="form-control" value="{{ number_format($invoice->grossSumDiscounted, 2) }}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="typeSuffix">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ($paymentMethods as $paymentMethod)
                            <div class="payment-method-container mt-3">
                                <input type="radio" name="payment_method" id="payment_method_{{ $paymentMethod->technicalName() }}" value="{{ $paymentMethod->technicalName() }}">
                                @if (! empty($icon = $paymentMethod->icon()))
                                    <label for="payment_method_{{ $paymentMethod->technicalName() }}">
                                        <img src="{{ $icon }}" class="icon"></label>
                                    </label>
                                @else
                                    <label for="payment_method_{{ $paymentMethod->technicalName() }}">{{ $paymentMethod->name() }}</label>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cash-stack"></i> {{ __('interface.actions.pay') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#history').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/customer/invoices/{{ $invoice->id }}/history',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'date' },
                    { data: 'name' },
                    { data: 'status', sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
