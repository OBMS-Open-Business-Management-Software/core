@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.profile.transactions') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ number_format(Auth::user()->prepaidAccountBalance, 2) }} €
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-bank2"></i> {{ __('interface.data.account_balance') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.contracts') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $contracts }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-file-earmark-text-fill"></i> {{ __('interface.misc.active_contracts') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.invoices') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoices['count'] }}
                        </div>
                        <div class="card-footer text-decoration-none d-flex justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.unpaid_invoices') }}
                            </div>
                            <span class="badge badge-warning d-flex align-items-center justify-content-center">{{ number_format($invoices['amount'], 2) }}€</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.support') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $tickets }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-ticket-fill"></i> {{ __('interface.misc.open_tickets') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersOpen }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-cart-fill"></i> {{ __('interface.misc.open_orders') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersSetup }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-check-circle-fill"></i> {{ __('interface.misc.active_products') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersLocked }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-lock-fill"></i> {{ __('interface.misc.locked_products') }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
