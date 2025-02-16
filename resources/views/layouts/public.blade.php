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
            <a class="navbar-brand col-md-3 col-lg-2 text-center" href="{{ route('customer.home') }}">
                <img src="{{ asset('images/favicon.logo.svg') }}" class="logo">
            </a>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav px-3">
                @if (! empty($user = Auth::user()))
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ $user->realName }} <span class="badge badge-light badge-pill ml-2 font-weight-normal">{{ $user->number }}</span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                {{ __('Account') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('customer.profile.transactions') }}">
                                {{ __('Transactions') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i> {{ __('Login') }}
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block shadow-sm sidebar collapse">
                    <div class="sidebar-sticky pt-4">
                        @if (request()->get('navigateables')->isNotEmpty())
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                                <span>{{ __('Resources') }}</span>
                            </h6>
                            <ul class="nav flex-column">
                                @foreach (request()->get('navigateables') as $page)
                                    <li class="nav-item {{ Request::route()->getName() == 'public.page.' . $page->id ? 'active' : '' }}">
                                        <a class="nav-link" href="{{ route('public.page.' . $page->id) }}">
                                            <i class="bi bi-file-earmark-text"></i> {{ __($page->title) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-0 mb-1 mt-3 text-primary">
                            <span>{{ __('Shop') }}</span>
                        </h6>
                        <ul class="nav flex-column">
                            <li class="nav-item {{ str_contains(Request::route()->getName(), 'public.shop') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('public.shop') }}">
                                    <i class="bi bi-arrow-right"></i> {{ __('Browse') }}
                                </a>
                            </li>
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

                    <div class="container-fluid">
                        <div class="row mb-4 text-center">
                            <div class="col-md-12">
                                @if (request()->get('navigateables')->isNotEmpty())
                                    @foreach (request()->get('navigateables') as $page)
                                        <a class="small" href="{{ route('public.page.' . $page->id) }}">
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

    @yield('javascript')
</body>
</html>
