@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-decoration-none">
                    <i class="bi bi-key"></i> {{ __('interface.misc.password_reset') }}
                </div>
                <div class="card-body mb-0">
                    {{ __('interface.misc.confirm_password_hint') }}

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.password') }}</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    {{ __('interface.data.confirm_password') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link w-100 mt-1" href="{{ route('password.request') }}">
                                        {{ __('interface.misc.forgot_password_cta') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
