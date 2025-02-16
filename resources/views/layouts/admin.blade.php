<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.logo.svg') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-dark sticky-top bg-primary bg-image flex-md-nowrap p-0">
            <a class="navbar-brand col-md-3 col-lg-2 bg-white py-3" href="{{ route('admin.home') }}">
                <img src="{{ asset('images/full.logo.svg') }}" class="logo px-3">
            </a>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav px-3">
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->realName }} <span class="badge badge-light badge-pill ml-2 font-weight-normal">{{ Auth::user()->number }}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            {{ __('Account') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block shadow-sm sidebar collapse">
                    <div class="sidebar-sticky pt-4">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 text-primary">
                            <span>{{ __('Overview') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ Request::route()->getName() == 'admin.home' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.home') }}">
                                    <i class="bi bi-house-fill"></i> {{ __('Dashboard') }}
                                </a>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                            <span>{{ __('Communication') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item dropdown show {{ str_contains(Request::route()->getName(), 'admin.support') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-telephone-fill"></i> {{ __('Support') }}</a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.support') }}">{{ __('Tickets') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.support.categories') }}">{{ __('Categories') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                            <span>{{ __('Sales') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.customers') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.customers') }}">
                                    <i class="bi bi-person-fill"></i> {{ __('Customers') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown show {{ str_contains(Request::route()->getName(), 'admin.invoices.customers') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-file-earmark-text"></i> {{ __('Invoices') }}</a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.invoices.customers') }}">{{ __('List') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.invoices.types') }}">{{ __('Types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.invoices.discounts') }}">{{ __('Invoice Discounts') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.discounts') }}">{{ __('Position Discounts') }}</a>
                                    @endif
                                </div>
                            </li>
                            <li class="nav-item dropdown show {{ (str_contains(Request::route()->getName(), 'admin.contracts') ? 'active' : '') || (str_contains(Request::route()->getName(), 'admin.discounts') ? 'active' : '') }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-file-earmark-text-fill"></i> {{ __('Contracts') }}</a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.contracts') }}">{{ __('List') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.contracts.types') }}">{{ __('Types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.contracts.trackers') }}">{{ __('Usage Trackers') }}</a>
                                    @endif
                                </div>
                            </li>
                            <li class="nav-item dropdown show {{ (str_contains(Request::route()->getName(), 'admin.shop') ? 'active' : '') }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-box"></i> {{ __('Shop') }}</a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.shop.orders') }}">{{ __('Orders') }}</a>
                                    <a class="dropdown-item" href="{{ route('admin.shop.categories') }}">{{ __('Configuration') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.products') }}">{{ __('Product Types') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                            <span>{{ __('Procurement') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.suppliers') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.suppliers') }}">
                                    <i class="bi bi-person-fill"></i> {{ __('Suppliers') }}
                                </a>
                            </li>
                            <li class="nav-item dropdown show {{ str_contains(Request::route()->getName(), 'admin.invoices.suppliers') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-file-earmark-text"></i> {{ __('Invoices') }}</a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.invoices.suppliers') }}">{{ __('List') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.invoices.types') }}">{{ __('Types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.invoices.discounts') }}">{{ __('Invoice Discounts') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.discounts') }}">{{ __('Position Discounts') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                            <span>{{ __('Miscellaneous') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.filemanager') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.filemanager') }}">
                                    <i class="bi bi-folder"></i> {{ __('File Manager') }}
                                </a>
                            </li>
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.paymentgateways') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.paymentgateways') }}">
                                        <i class="bi bi-currency-euro"></i> {{ __('Payment Gateways') }}
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.pages') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.pages') }}">
                                    <i class="bi bi-list"></i> {{ __('Custom Pages') }}
                                </a>
                            </li>
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item dropdown show {{ (str_contains(Request::route()->getName(), 'admin.api.users') ? 'active' : '') }}">
                                    <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="true"><i class="bi bi-gear-wide-connected"></i> {{ __('API') }}</a>
                                    <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                        <a class="dropdown-item" href="{{ route('admin.api.users') }}">{{ __('Users') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.api.oauth-clients') }}">{{ __('OAUTH Clients') }}</a>
                                    </div>
                                </li>
                                @if (empty(request()->tenant))
                                    <li class="nav-item {{ str_contains(Request::route()->getName(), 'admin.tenants') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.tenants') }}">
                                            <i class="bi bi-people-fill"></i> {{ __('Tenants') }}
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </nav>

                <main role="main" class="col-md-10 ml-sm-auto col-lg-10 px-0"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
                    @if ($errors->any() || Session::has('success') || Session::has('warning') || Session::has('danger'))
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12" id="collapseGroup">
                                    <div class="alert-container mt-4">
                                        @if ($errors->any())
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill"></i> {{$error}}</div>
                                            @endforeach
                                        @endif
                                        @if (Session::has('success'))
                                            <div class="alert alert-success mb-0"><i class="bi bi-check-circle"></i> {!! Session::get('success') !!}</div>
                                        @endif
                                        @if (Session::has('warning'))
                                            <div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle"></i> {!! Session::get('warning') !!}</div>
                                        @endif
                                        @if (Session::has('danger'))
                                            <div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle"></i> {!! Session::get('danger') !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')

                    <div class="container-fluid">
                        <div class="row mb-4 text-center">
                            <div class="col-md-12">
                                @if (request()->get('navigateables')->isNotEmpty())
                                    @foreach (request()->get('navigateables') as $page)
                                        <a class="small" href="{{ route('public.page.' . $page->id) }}" target="_blank">
                                            {{ __($page->title) }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <script type="text/javascript">
        $('.custom-file input').change(function (e) {
            var files = [];

            for (var i = 0; i < $(this)[0].files.length; i++) {
                files.push($(this)[0].files[i].name);
            }

            $(this).next('.custom-file-label').html(files.join(', '));
        });
    </script>

    @yield('javascript')
</body>
</html>
