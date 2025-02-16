<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

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
        <main>
            <div class="row mx-0 vh-100 align-content-center">
                <div class="col-md-12 d-flex align-items-center">
                    <div class="wrapper w-100">
                        <div class="container mb-5">
                            <div class="row justify-content-center">
                                <div class="col-md-8 text-center">
                                    <div class="logo logo-lg logo-center logo-stacked">
                                        <img src="{{ config('company.logo') ?? asset('images/full.logo.svg') }}">
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

                        <div class="row justify-content-center text-center my-4">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
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
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    @yield('javascript')
</body>
</html>
