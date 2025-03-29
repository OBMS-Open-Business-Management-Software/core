@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-primary mb-4 h4">{{ __('interface.misc.performance') }}</h2>
            </div>
        </div>
        <div class="row mb-4" style="height: 400px">
            <div class="col-md-12">
                <div class="card h-100">
                    <div class="card-body">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-primary mb-4 h4">{{ __('interface.misc.sales') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('admin.invoices.customers') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoicesCustomers['count'] }}
                        </div>
                        <div class="card-footer text-decoration-none d-flex justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.unpaid_invoices') }}
                            </div>
                            <span class="badge badge-secondary d-flex align-items-center justify-content-center">{{ number_format($invoicesCustomers['amount'], 2) }}€</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
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
            <div class="col-lg-4 col-md-6">
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
                <h2 class="text-primary my-4 h4">{{ __('interface.misc.procurement') }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('admin.invoices.suppliers') }}" class="text-reset text-decoration-none">
                    <div class="card">
                        <div class="card-body h1 mb-0">
                            {{ $invoicesSuppliers['count'] }}
                        </div>
                        <div class="card-footer text-decoration-none d-flex justify-content-between">
                            <div>
                                <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.unpaid_invoices') }}
                            </div>
                            <span class="badge badge-secondary d-flex align-items-center justify-content-center">{{ number_format($invoicesSuppliers['amount'], 2) }}€</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            const ctx = document.getElementById('performanceChart');
            const DATA_COUNT = 7;
            const NUMBER_CFG = {count: DATA_COUNT, min: {!! $performance->min !!}, max: {!! $performance->max !!}};
            const labels = {!! $performance->labels !!};
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: '{{ __('data.revenue') }}',
                        data: {!! $performance->datasets->in !!},
                        backgroundColor: '{{ config('theme.primary', '#040E29') }}',
                    },
                    {
                        label: '{{ __('data.expenses') }}',
                        data: {!! $performance->datasets->out !!},
                        backgroundColor: '{{ config('theme.secondary', '#040E29') }}',
                    },
                ]
            };
            
            new Chart.Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'category',
                            stacked: true,
                        },
                        y: {
                            type: 'linear',
                            stacked: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2) + '€';
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.raw.toFixed(2) + '€';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
