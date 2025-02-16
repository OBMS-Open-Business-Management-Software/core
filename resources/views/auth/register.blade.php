@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header text-decoration-none">
                    <i class="bi bi-key"></i> {{ __('Register') }}
                </div>
                <div class="card-body mb-0">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                @if (Route::has('login'))
                                    <a class="btn btn-outline-primary w-100 mb-4" href="{{ route('login') }}">
                                        {{ __('Already have an account? Log in!') }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-8">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        @php
                        $acceptable = \App\Models\Content\Page::acceptable()->get();
                        @endphp

                        @if ($acceptable->isNotEmpty())
                            @foreach ($acceptable as $accept)
                                <div class="row">
                                    <div class="col-md-8 offset-md-4">
                                        <div class="form-group bg-white border rounded py-2 px-3">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <input id="accept_{{ $accept->id }}" type="checkbox" class="form-control" name="accept_{{ $accept->id }}" value="true">
                                                </div>

                                                <label for="accept_{{ $accept->id }}" class="col-md-10 col-form-label">{!! __('I confirm that I have read, understood the :link (Date: :date) and I agree to be bound to it.', ['link' => '<a href="' . (Route::has($accept->route) ? route($accept->route) : $accept->route) . '" target="_blank">' . __($accept->title) . '</a>', 'date' => $accept->latest->created_at->format('d.m.Y, H:i')]) !!}</label>
                                            </div>
                                            @error('accept_' . $accept->id)
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
