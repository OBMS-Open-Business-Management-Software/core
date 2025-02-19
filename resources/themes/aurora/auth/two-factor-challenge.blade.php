@extends('layouts.login')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header text-decoration-none">
                        <i class="bi bi-qr-code"></i> {{ __('interface.data.2fa') }}
                    </div>
                    <div class="card-body mb-0">
                        <form method="POST" action="">
                            @csrf

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.2fa_auth_code') }}</label>

                                <div class="col-md-8">
                                    <input class="form-control" id="email" type="text" name="code" required autofocus/>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        {{ __('interface.actions.send') }}
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
