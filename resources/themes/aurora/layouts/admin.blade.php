<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'OBMS') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ config('company.favicon') ?? asset('themes/aurora/images/favicon.logo.svg') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-dark sticky-top bg-primary bg-image p-0">
            <a class="navbar-brand col-md-3 col-lg-2 bg-white py-3" href="{{ route('admin.home') }}">
                <div class="logo">
                    <img src="{{ config('company.logo') ?? asset('themes/aurora/images/full.logo.svg') }}" class="px-3">
                    @if (config('app.slogan'))
                        <div class="slogan small">{{ config('app.slogan', 'Open Business Management Software') }}</div>
                    @endif
                </div>
            </a>
            <button class="navbar-toggler shadow-sm rounded d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
            <ul class="navbar-nav px-3">
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle px-3 rounded shadow-sm" href="#" role="button" data-toggle="dropdown" aria-expanded="false" v-pre>
                        <div>
                            {{ Auth::user()->realName }} <span class="badge badge-light ml-2 font-weight-normal">{{ Auth::user()->number }}</span>
                        </div>
                        <i class="bi bi-chevron-down dropdown-indicator"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            {{ __('interface.misc.account') }}
                        </a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('interface.actions.logout') }}
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
                    <div class="sidebar-sticky py-4">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-2 text-primary">
                            <span>{{ __('interface.misc.overview') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ Request::route()?->getName() == 'admin.home' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.home') }}" title="{{ __('interface.misc.dashboard') }}">
                                    <i class="bi bi-house-fill"></i>
                                    <span>{{ __('interface.misc.dashboard') }}</span>
                                </a>
                            </li>
                        </ul>
                        @if (!empty(request()->get('products')))
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                            <span>{{ __('interface.misc.products') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            @foreach (request()->get('products') as $product)
                                <li class="nav-item {{ Request::route()?->getName() == 'admin.services.' . $product->slug ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.services.' . $product->slug) }}" title="{{ $product->name }}">
                                        <i class="{{ $product->icon ?: 'bi bi-box' }}"></i>
                                        <span>{{ $product->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @endif
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                            <span>{{ __('interface.misc.communication') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item dropdown show {{ str_contains(Request::route()?->getName(), 'admin.support') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.support') }}">
                                    <div>
                                        <i class="bi bi-telephone"></i>
                                        <span>{{ __('interface.misc.support') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                                </a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.support') }}" title="{{ __('interface.misc.tickets') }}">{{ __('interface.misc.tickets') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.support.categories') }}" title="{{ __('interface.misc.categories') }}">{{ __('interface.misc.categories') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                            <span>{{ __('interface.misc.sales') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.customers') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.customers') }}" title="{{ __('interface.misc.customers') }}">
                                    <i class="bi bi-person"></i>
                                    <span>{{ __('interface.misc.customers') }}</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown show {{ str_contains(Request::route()?->getName(), 'admin.invoices.customers') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.invoices') }}">
                                    <div>
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span>{{ __('interface.misc.invoices') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                                </a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.invoices.customers') }}" title="{{ __('interface.misc.list') }}">{{ __('interface.misc.list') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.invoices.types') }}" title="{{ __('interface.misc.types') }}">{{ __('interface.misc.types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.invoices.discounts') }}" title="{{ __('interface.misc.invoice_discounts') }}">{{ __('interface.misc.invoice_discounts') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.discounts') }}" title="{{ __('interface.misc.position_discounts') }}">{{ __('interface.misc.position_discounts') }}</a>
                                    @endif
                                </div>
                            </li>
                            <li class="nav-item dropdown show {{ (str_contains(Request::route()?->getName(), 'admin.contracts') ? 'active' : '') || (str_contains(Request::route()?->getName(), 'admin.discounts') ? 'active' : '') }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.contracts') }}">
                                    <div>
                                        <i class="bi bi-file-earmark-text-fill"></i>
                                        <span>{{ __('interface.misc.contracts') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                                </a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.contracts') }}" title="{{ __('interface.misc.list') }}">{{ __('interface.misc.list') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.contracts.types') }}" title="{{ __('interface.misc.types') }}">{{ __('interface.misc.types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.contracts.trackers') }}" title="{{ __('interface.misc.usage_trackers') }}">{{ __('interface.misc.usage_trackers') }}</a>
                                    @endif
                                </div>
                            </li>
                            <li class="nav-item dropdown show {{ (str_contains(Request::route()?->getName(), 'admin.shop') ? 'active' : '') }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.shop') }}">
                                    <div>
                                        <i class="bi bi-box"></i>
                                        <span>{{ __('interface.misc.shop') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                                </a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.shop.orders') }}" title="{{ __('interface.misc.orders') }}">{{ __('interface.misc.orders') }}</a>
                                    <a class="dropdown-item" href="{{ route('admin.shop.categories') }}" title="{{ __('interface.misc.configuration') }}">{{ __('interface.misc.configuration') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.products') }}" title="{{ __('interface.misc.product_types') }}">{{ __('interface.misc.product_types') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                            <span>{{ __('interface.misc.procurement') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.suppliers') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.suppliers') }}" title="{{ __('interface.misc.suppliers') }}">
                                    <i class="bi bi-person"></i>
                                    <span>{{ __('interface.misc.suppliers') }}</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown show {{ str_contains(Request::route()?->getName(), 'admin.invoices.suppliers') ? 'active' : '' }}">
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.invoices') }}">
                                    <div>
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span>{{ __('interface.misc.invoices') }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                                </a>
                                <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                    <a class="dropdown-item" href="{{ route('admin.invoices.suppliers') }}" title="{{ __('interface.misc.list') }}">{{ __('interface.misc.list') }}</a>
                                    @if (Auth::user()->role == 'admin')
                                        <a class="dropdown-item" href="{{ route('admin.invoices.types') }}" title="{{ __('interface.misc.types') }}">{{ __('interface.misc.types') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.invoices.discounts') }}" title="{{ __('interface.misc.invoice_discounts') }}">{{ __('interface.misc.invoice_discounts') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.discounts') }}" title="{{ __('interface.misc.position_discounts') }}">{{ __('interface.misc.position_discounts') }}</a>
                                    @endif
                                </div>
                            </li>
                        </ul>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                            <span>{{ __('interface.misc.miscellaneous') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.settings') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.settings') }}" title="{{ __('interface.misc.parameters') }}">
                                        <i class="bi bi-gear-wide-connected"></i>
                                        <span>{{ __('interface.misc.parameters') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.filemanager') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.filemanager') }}" title="{{ __('interface.misc.file_manager') }}">
                                    <i class="bi bi-folder"></i>
                                    <span>{{ __('interface.misc.file_manager') }}</span>
                                </a>
                            </li>
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.paymentgateways') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.paymentgateways') }}" title="{{ __('interface.misc.payment_gateways') }}">
                                        <i class="bi bi-currency-euro"></i>
                                        <span>{{ __('interface.misc.payment_gateways') }}</span>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.pages') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('admin.pages') }}" title="{{ __('interface.misc.custom_pages') }}">
                                    <i class="bi bi-list"></i>
                                    <span>{{ __('interface.misc.custom_pages') }}</span>
                                </a>
                            </li>
                            @if (Auth::user()->role == 'admin')
                                <li class="nav-item dropdown show {{ (str_contains(Request::route()?->getName(), 'admin.api.users') ? 'active' : '') }}">
                                    <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.api') }}">
                                        <div>
                                            <i class="bi bi-code-slash"></i>
                                            <span>{{ __('interface.misc.api') }}</span>
                                        </div>
                                        <i class="bi bi-chevron-down dropdown-indicator"></i>
                                    </a>
                                    <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                        <a class="dropdown-item" href="{{ route('admin.api.users') }}" title="{{ __('interface.misc.users') }}">{{ __('interface.misc.users') }}</a>
                                        <a class="dropdown-item" href="{{ route('admin.api.oauth-clients') }}" title="{{ __('interface.misc.oauth_clients') }}">{{ __('interface.misc.oauth_clients') }}</a>
                                    </div>
                                </li>
                                @if (empty(request()->get('tenant')))
                                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'admin.tenants') ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('admin.tenants') }}" title="{{ __('interface.misc.tenants') }}">
                                            <i class="bi bi-people"></i>
                                            <span>{{ __('interface.misc.tenants') }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item dropdown show {{ (str_contains(Request::route()?->getName(), 'admin.api.users') ? 'active' : '') }}">
                                        <a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown" aria-expanded="false" title="{{ __('interface.misc.monitoring') }}">
                                            <div>
                                                <i class="bi bi-activity"></i>
                                                <span>{{ __('interface.misc.monitoring') }}</span>
                                            </div>
                                            <i class="bi bi-chevron-down dropdown-indicator"></i>
                                        </a>
                                        <div class="dropdown-menu w-100" aria-labelledby="dropdown04">
                                            <a class="dropdown-item" href="{{ url('/horizon') }}" target="_blank" title="{{ __('interface.misc.horizon') }}">{{ __('interface.misc.horizon') }}</a>
                                            <a class="dropdown-item" href="{{ url('/pulse') }}" target="_blank" title="{{ __('interface.misc.pulse') }}">{{ __('interface.misc.pulse') }}</a>
                                        </div>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </nav>

                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-0"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
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
                                        <a class="small" href="{{ route('cms.page.' . $page->id) }}" target="_blank">
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
    <script src="{{ asset('themes/aurora/js/app.js') }}"></script>

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
