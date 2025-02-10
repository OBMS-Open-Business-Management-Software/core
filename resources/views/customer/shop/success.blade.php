@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('public.shop') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to shop') }}</a>
                <a href="{{ route('customer.home') }}" class="btn btn-primary float-right"><i class="bi bi-person"></i> {{ __('Customer area') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-check-circle"></i> {{ __('Order succeeded') }}
                    </div>
                    <div class="card-body mb-0">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Number') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->number }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Product') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->form->name }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Approval') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($order->approved)
                                    <span class="badge badge-success"><i class="bi bi-check-circle"></i> {{ __('Approved') }}</span>
                                @elseif ($order->disapproved)
                                    <span class="badge badge-danger"><i class="bi bi-check-circle"></i> {{ __('Disapproved') }}</span>
                                @else
                                    <span class="badge badge-warning"><i class="bi bi-play-circle"></i> {{ __('Waiting for approval') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-file-earmark-text"></i> {{ __('Contract') }}
                    </div>
                    <div class="card-body mb-0">
                        @if ($order->form->contractType->type == 'prepaid_auto')
                            <div class="alert alert-primary mb-3">
                                <i class="bi bi-info-circle"></i> {{ __('A prepaid contract automatically renews for as long as you have sufficient funds on your account. It automatically ends as soon as your account has insufficient funds.') }}
                            </div>
                        @endif
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($order->form->contractType->type == 'contract_pre_pay')
                                    {{ __('Contract (pre-paid)') }}
                                @elseif ($order->form->contractType->type == 'contract_post_pay')
                                    {{ __('Contract (post-paid)') }}
                                @elseif ($order->form->contractType->type == 'prepaid_auto')
                                    {{ __('Prepaid (auto-renew)') }}
                                @elseif ($order->form->contractType->type == 'prepaid_manual')
                                    {{ __('Prepaid (manual renew)') }}
                                @else
                                    {{ __('Unknown') }}
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Payment Cycle') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $order->form->contractType->invoice_period }} {{ __('interface.units.days') }}
                            </div>
                        </div>
                        @if ($order->form->contractType->type == 'contract_pre_pay' || $order->form->contractType->type == 'contract_post_pay')
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Notice Period') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $order->form->contractType->cancellation_period }} {{ __('interface.units.days') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card mt-0">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-cash-stack"></i> {{ __('Payment') }}
                    </div>
                    <div class="card-body mb-0">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Net Amount') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ number_format($order->amount, 2) }} €
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('VAT Amount') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ number_format($order->amount * ($order->vat_percentage / 100), 2) }} €
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('Gross Amount') }}</label>

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
