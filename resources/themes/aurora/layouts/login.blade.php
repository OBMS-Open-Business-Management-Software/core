<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
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
<body class="h-100">
    <div class="login" id="app">
        <main class="w-100">
            <div class="row mx-0">
                <div class="col-md-12 d-flex align-items-center">
                    <div class="wrapper w-100">
                        <div class="container mb-5">
                            <div class="row justify-content-center">
                                <div class="col-md-8 text-center">
                                    <div class="logo logo-lg logo-center logo-stacked">
                                        <img src="{{ config('company.logo') ?? asset('themes/aurora/images/full.logo.svg') }}">
                                        @if (config('app.slogan'))
                                            <div class="slogan-stacked small">{{ config('app.slogan', 'Open Business Management Software') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (Session::has('message') || Session::has('success') || Session::has('warning') || Session::has('danger'))
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="alert-container mb-4">
                                            @if (Session::has('status'))
                                                <div class="alert alert-success" role="alert"><i class="bi bi-check-circle"></i> {!! Session::get('status') !!}</div>
                                            @endif
                                            @if (Session::has('message'))
                                                <div class="alert alert-primary"><i class="bi bi-info-circle-fill"></i> {!! Session::get('message') !!}</div>
                                            @endif
                                            @if (Session::has('success'))
                                                <div class="alert alert-success"><i class="bi bi-check-circle"></i> {!! Session::get('success') !!}</div>
                                            @endif
                                            @if (Session::has('warning'))
                                                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> {!! Session::get('warning') !!}</div>
                                            @endif
                                            @if (Session::has('danger'))
                                                <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> {!! Session::get('danger') !!}</div>
                                            @endif
                                            @if (Session::has('resent'))
                                                <div class="alert alert-success" role="alert"><i class="bi bi-check-circle"></i> {{ __('interface.misc.verification_resent_notification') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @yield('content')

                        @if (request()->get('navigateables')->isNotEmpty())
                            <div class="row justify-content-center text-center mt-5">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-8 offset-md-2">
                                            @foreach (request()->get('navigateables') as $page)
                                                <a class="small" href="{{ route('cms.page.' . $page->id) }}" target="_blank">
                                                    {{ __($page->title) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('themes/aurora/js/app.js') }}" defer></script>

    @yield('javascript')
</body>
</html>
