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
    <link rel="icon" type="image/svg+xml" href="{{ config('company.favicon') ?? theme_asset('aurora', 'images/favicon.logo.svg') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="app" id="app">
        <nav class="navbar navbar-dark sticky-top bg-white p-0 shadow-sm">
            <a class="navbar-brand bg-white py-3" href="{{ route('customer.home') }}">
                <div class="logo">
                    <img src="{{ config('company.logo') ?? theme_asset('aurora', 'images/full.logo.svg') }}" class="px-3">
                    @if (config('app.slogan'))
                        <div class="slogan small">{{ config('app.slogan', 'Open Business Management Software') }}</div>
                    @endif
                </div>
            </a>
            <div class="d-flex gap-3">
                <button class="navbar-toggler shadow-sm rounded d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <ul class="navbar-nav px-3 d-flex flex-row">
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle px-3 rounded border border-gray" href="#" role="button" data-toggle="dropdown" aria-expanded="false" v-pre>
                            <div>
                                {{ Auth::user()->realName }} <span class="badge badge-primary ml-2 font-weight-normal">{{ Auth::user()->number }}</span>
                            </div>
                            <i class="bi bi-chevron-down dropdown-indicator"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                {{ __('interface.misc.account') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('customer.profile.transactions') }}">
                                {{ __('interface.misc.transactions') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('interface.actions.logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="languageDropdown" class="nav-link dropdown-toggle px-3 rounded border border-gray" href="#" role="button" data-toggle="dropdown" aria-expanded="false" v-pre>
                            <div>
                                <i class="bi bi-globe"></i>
                            </div>
                            <i class="bi bi-chevron-down dropdown-indicator"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="languageDropdown">
                            <a class="dropdown-item" href="{{ route('language.change', ['locale' => 'en']) }}">
                                {{ __('languages.english') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('language.change', ['locale' => 'de']) }}">
                                {{ __('languages.german') }}
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="wrapper">
            <nav id="sidebarMenu" class="d-block shadow-sm sidebar collapse px-3 py-4">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-2 text-primary">
                    <span>{{ __('interface.misc.overview') }}</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item {{ Request::route()?->getName() == 'customer.home' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('customer.home') }}" title="{{ __('interface.misc.dashboard') }}">
                            <i class="bi bi-house-fill"></i>
                            <span>{{ __('interface.misc.dashboard') }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'customer.support') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('customer.support') }}" title="{{ __('interface.misc.tickets') }}">
                            <i class="bi bi-ticket-fill"></i>
                            <span>{{ __('interface.misc.tickets') }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'customer.contracts') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('customer.contracts') }}" title="{{ __('interface.misc.contracts') }}">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <span>{{ __('interface.misc.contracts') }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'customer.invoices') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('customer.invoices') }}" title="{{ __('interface.misc.invoices') }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>{{ __('interface.misc.invoices') }}</span>
                        </a>
                    </li>
                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'customer.orders') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('customer.shop.orders') }}" title="{{ __('interface.misc.orders') }}">
                            <i class="bi bi-cart-fill"></i>
                            <span>{{ __('interface.misc.orders') }}</span>
                        </a>
                    </li>
                </ul>
                @if (!empty(request()->get('products')))
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                        <span>{{ __('interface.misc.products') }}</span>
                    </h6>
                    <ul class="nav flex-column">
                        @foreach (request()->get('products') as $product)
                            <li class="nav-item {{ Request::route()?->getName() == 'customer.services.' . $product->slug ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('customer.services.' . $product->slug) }}" title="{{ $product->name }}">
                                    <i class="{{ $product->icon ?: 'bi bi-box' }}"></i>
                                    <span>{{ $product->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-2 mt-3 text-primary">
                    <span>{{ __('interface.misc.shop') }}</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item {{ str_contains(Request::route()?->getName(), 'public.shop') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('public.shop') }}" title="{{ __('interface.actions.browse') }}">
                            <i class="bi bi-arrow-right"></i>
                            <span>{{ __('interface.actions.browse') }}</span>
                        </a>
                    </li>
                </ul>
                @if (request()->get('navigateables')->isNotEmpty())
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-2 mt-3 text-primary">
                        <span>{{ __('interface.misc.resources') }}</span>
                    </h6>
                    <ul class="nav flex-column">
                        @foreach (request()->get('navigateables') as $page)
                            <li class="nav-item {{ Request::route()?->getName() == 'cms.page.' . $page->id ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('cms.page.' . $page->id) }}" title="{{ __($page->title) }}">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>{{ __($page->title) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </nav>

            <main role="main" class="content">
                <div class="main">
                    <div class="chartjs-size-monitor">
                        <div class="chartjs-size-monitor-expand">
                            <div class=""></div>
                        </div>
                        <div class="chartjs-size-monitor-shrink">
                            <div class=""></div>
                        </div>
                    </div>
                    @if ((empty(Auth::user()->profile) && Auth::user()->accepted) || $errors->any() || Session::has('success') || Session::has('warning') || Session::has('danger'))
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12" id="collapseGroup">
                                    <div class="alert-container mt-4">
                                        @if (empty(Auth::user()->profile) && Auth::user()->accepted)
                                            <div class="alert alert-warning mb-0 shadow-sm">
                                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.missing_data_notice') }}
                                                @if (Request::route()?->getName() !== 'customer.profile')
                                                    <br>
                                                    <br>
                                                    <a href="{{ route('customer.profile') }}" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.complete_now') }}</a>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($errors->any())
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-warning mb-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i> {{$error}}</div>
                                            @endforeach
                                        @endif
                                        @if (Session::has('success'))
                                            <div class="alert alert-success mb-0 shadow-sm"><i class="bi bi-check-circle"></i> {!! Session::get('success') !!}</div>
                                        @endif
                                        @if (Session::has('warning'))
                                            <div class="alert alert-warning mb-0 shadow-sm"><i class="bi bi-exclamation-triangle"></i> {!! Session::get('warning') !!}</div>
                                        @endif
                                        @if (Session::has('danger'))
                                            <div class="alert alert-danger mb-0 shadow-sm"><i class="bi bi-exclamation-circle"></i> {!! Session::get('danger') !!}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')

                    @if (request()->get('navigateables')->isNotEmpty())
                        <div class="container-fluid">
                            <div class="row mb-4 text-center">
                                <div class="col-md-12">
                                    @foreach (request()->get('navigateables') as $page)
                                        <a class="small" href="{{ route('cms.page.' . $page->id) }}" target="_blank">
                                            {{ __($page->title) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @if(View::hasSection('actions'))
                    <div class="actions shadow-sm">
                        <div class="container-fluid my-4">
                            <div class="row">
                                <div class="col-md-12">
                                    @yield('actions')
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ theme_asset('aurora', 'js/app.js') }}"></script>

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
