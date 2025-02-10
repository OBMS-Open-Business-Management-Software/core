@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ route('customer.contracts') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $contracts }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-file-earmark-text-fill"></i> {{ __('Active Contracts') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('customer.invoices') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoices }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-file-earmark-text"></i> {{ __('Unpaid Invoices') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('customer.support') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $tickets }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-ticket-fill"></i> {{ __('Open Tickets') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('customer.profile.transactions') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ number_format(Auth::user()->prepaidAccountBalance, 2) }} €
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-bank2"></i> {{ __('Account Balance') }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersOpen }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-cart-fill"></i> {{ __('Open Orders') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersSetup }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-check-circle-fill"></i> {{ __('Active Products') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('customer.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersLocked }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-lock-fill"></i> {{ __('Locked Products') }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
