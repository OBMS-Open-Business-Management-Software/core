@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('public.shop') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_shop') }}</a>
                <a href="{{ route('customer.home') }}" class="btn btn-primary float-right"><i class="bi bi-person"></i> {{ __('interface.misc.customer_area') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-check-circle"></i> {{ __('interface.misc.order_succeeded') }}
                    </div>
                    <div class="card-body mb-0">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.number') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->number }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.product') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->form->name }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.approval') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($order->approved)
                                    <span class="badge badge-success"><i class="bi bi-check-circle"></i> {{ __('interface.status.approved') }}</span>
                                @elseif ($order->disapproved)
                                    <span class="badge badge-danger"><i class="bi bi-check-circle"></i> {{ __('interface.status.disapproved') }}</span>
                                @else
                                    <span class="badge badge-warning"><i class="bi bi-play-circle"></i> {{ __('interface.status.approval_waiting') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.contract') }}
                    </div>
                    <div class="card-body mb-0">
                        @if ($order->form->contractType->type == 'prepaid_auto')
                            <div class="alert alert-primary mb-3">
                                <i class="bi bi-info-circle"></i> {{ __('interface.misc.prepaid_auto_hint') }}
                            </div>
                        @endif
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($order->form->contractType->type == 'contract_pre_pay')
                                    {{ __('interface.billing.contract_pre') }}
                                @elseif ($order->form->contractType->type == 'contract_post_pay')
                                    {{ __('interface.billing.contract_post') }}
                                @elseif ($order->form->contractType->type == 'prepaid_auto')
                                    {{ __('interface.billing.prepaid_auto') }}
                                @elseif ($order->form->contractType->type == 'prepaid_manual')
                                    {{ __('interface.billing.prepaid_manual') }}
                                @else
                                    {{ __('interface.status.unknown') }}
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.payment_cycle') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->form->contractType->invoice_period }} {{ __('interface.units.days') }}
                            </div>
                        </div>
                        @if ($order->form->contractType->type == 'contract_pre_pay' || $order->form->contractType->type == 'contract_post_pay')
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.notice_period') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $order->form->contractType->cancellation_period }} {{ __('interface.units.days') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card mt-0">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-cash-stack"></i> {{ __('interface.data.payment') }}
                    </div>
                    <div class="card-body mb-0">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.net_amount') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ number_format($order->amount, 2) }} €
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.vat_amount') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ number_format($order->amount * ($order->vat_percentage / 100), 2) }} €
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.gross_amount') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ number_format($order->amount * ((100 + $order->vat_percentage) / 100), 2) }} €
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
