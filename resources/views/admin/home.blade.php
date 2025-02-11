@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-primary mb-4">{{ __('interface.misc.sales') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('admin.contracts') }}" class="text-reset text-decoration-none">
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
            <div class="col-md-4">
                <a href="{{ route('admin.invoices.customers') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoicesCustomers }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.unpaid_invoices') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.support') }}" class="text-reset text-decoration-none">
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
        </div>
        <div class="row mt-4">
            <div class="col-md">
                <a href="{{ route('admin.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersApproval }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-question-circle-fill"></i> {{ __('interface.misc.unapproved_orders') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.shop.orders') }}" class="text-reset text-decoration-none">
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
            <div class="col-md">
                <a href="{{ route('admin.shop.orders') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $ordersFailed }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-x-circle-fill"></i> {{ __('interface.misc.failed_orders') }}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md">
                <a href="{{ route('admin.shop.orders') }}" class="text-reset text-decoration-none">
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
            <div class="col-md">
                <a href="{{ route('admin.shop.orders') }}" class="text-reset text-decoration-none">
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
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-primary my-4">{{ __('interface.misc.procurement') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('admin.invoices.suppliers') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoicesSuppliers }}
                        </div>
                        <div class="card-footer text-decoration-none">
                            <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.unpaid_invoices') }}
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
