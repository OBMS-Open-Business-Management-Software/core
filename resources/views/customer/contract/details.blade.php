@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('customer.contracts') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                @if ($contract->type->type !== 'prepaid_auto' && $contract->type->type !== 'prepaid_manual')
                    @if ($contract->status == 'started')
                        <a href="{{ route('customer.contracts.cancel', $contract->id) }}" class="btn btn-warning mb-4 mr-1 float-right"><i class="bi bi-stop-circle"></i> {{ __('interface.actions.cancel') }}</a>
                    @endif
                @else
                    @if ($contract->status !== 'template')
                        <a href="{{ route('customer.contracts.extend', $contract->id) }}" class="btn btn-primary mb-4 mr-1 float-right"><i class="bi bi-arrow-right-circle"></i> {{ __('interface.actions.extend') }}</a>
                    @endif
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
                                {{ $contract->number ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $contract->type->name ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.status') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch ($contract->status)
                                    @case ('cancelled')
                                        <span class="badge badge-danger">{{ __('interface.status.cancelled') }}</span>
                                        @break
                                    @case ('expires')
                                        <span class="badge badge-warning">{{ __('interface.status.expires') }}</span>
                                        @break
                                    @case ('started')
                                        <span class="badge badge-success">{{ __('interface.status.active') }}</span>
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
                                {!! $contract->user->reverseCharge ? '<span class="badge badge-success">' . __('interface.misc.applicable') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.not_applicable') . '</span>' !!}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.misc.created_at') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $contract->created_at->format('d.m.Y, H:i') }}
                            </div>
                        </div>
                        @if ($contract->started)
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.start_date') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $contract->started_at->format('d.m.Y, H:i') }}
                                </div>
                            </div>
                        @endif
                        @if ($contract->cancelled)
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.cancel_date') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $contract->cancelled_at->format('d.m.Y, H:i') }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.cancelled_to') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $contract->cancelled_to->format('d.m.Y, H:i') }}
                                </div>
                            </div>
                        @endif
                        @if ($contract->cancellationRevoked)
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.cancellation_revokation_date') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $contract->cancellation_revoked_at->format('d.m.Y, H:i') }}
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
                        @if ($contract->positionLinks->isNotEmpty())
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
                                    @foreach ($contract->positionLinks as $link)
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
                                            <td style="width: 10%" class="bg-disabled">{{ $contract->user->reverseCharge ? '0' : $link->position->vat_percentage }} %</td>
                                            <td style="width: 10%" class="bg-disabled">{{ $contract->user->reverseCharge ? number_format($link->position->netSum, 2) : number_format($link->position->grossSum, 2) }} €</td>
                                        </tr>
                                        @if (! empty($usageTracker = $link->trackerInstance) && $contract->type->type == 'contract_post_pay')
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">{{ __('interface.misc.usage_tracker') }}</span><br>
                                                    {{ __($usageTracker->tracker->name) }}
                                                </td>
                                                <td style="width: 10%">{{ number_format($usageTracker->tracker->amount, 2) }} €</td>
                                                <td style="width: 10%">{{ __('interface.data.variable') }}</td>
                                                <td style="width: 10%">{{ __('interface.data.variable') }}</td>
                                                <td style="width: 10%" class="bg-disabled">{{ $contract->user->reverseCharge ? '0' : (! empty($percentage = $usageTracker->vat_percentage) ? $percentage : $link->position->vat_percentage) }} %</td>
                                                <td style="width: 10%" class="bg-disabled">{{ __('interface.data.variable') }}</td>
                                                @if ($contract->status == 'template')
                                                    <td style="width: 1%"></td>
                                                    <td style="width: 1%"></td>
                                                @endif
                                            </tr>
                                        @endif
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
                                                        <td style="width: 10%" class="bg-disabled">{{ $contract->user->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $contract->user->reverse_charge ? number_format($link->position->netSum * ($discount->amount / 100), 2) : number_format($link->position->grossSum * ($discount->amount / 100), 2) }} €</td>
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
                                                        <td style="width: 10%" class="bg-disabled">{{ $contract->user->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $contract->user->reverse_charge ? number_format($discount->amount * $link->position->quantity, 2) : number_format($discount->amount * $link->position->quantity * (1 + ($link->position->vat_percentage / 100)), 2) }} €</td>
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
                                        <td class="bg-primary text-white">{{ number_format($contract->netSum, 2) }} €</td>
                                    </tr>
                                    @foreach ($contract->vatPositions as $percentage => $amount)
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
                                        <td class="bg-primary text-white border-0">{{ number_format($contract->grossSum, 2) }} €</td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.contracts.no_positions') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-exclamation-circle"></i> {{ __('interface.misc.status_history') }}
                    </div>
                    <div class="card-body">
                        @if ($contract->history()->exists())
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
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#service_runtime').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#serviceRuntimeConfig').show();
                } else {
                    $('#serviceRuntimeConfig').hide();
                }
            });

            $('.service_runtime_dynamic').each(function () {
                $(this).on('change', function () {
                    if ($(this).is(':checked')) {
                        $('#serviceRuntimeConfig' + $(this).attr('data-id')).show();
                    } else {
                        $('#serviceRuntimeConfig' + $(this).attr('data-id')).hide();
                    }
                });
            });

            $('#history').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/contracts/{{ $contract->id }}/history',
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
