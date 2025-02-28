@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.data.user_details') }}
                    </div>
                    <div class="card-body">
                        <label class="font-weight-bold">{{ __('interface.data.name') }}:</label> {{ Auth::user()->name }}<br>
                        <label class="font-weight-bold mb-0">{{ __('interface.data.email') }}:</label> {{ Auth::user()->email }} <span class="badge badge-success">{{ __('interface.status.verified') }}</span><br>
                        <br>
                        <a class="btn btn-primary" data-toggle="modal" data-target="#edit"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</a>
                        <a class="btn btn-warning" data-toggle="modal" data-target="#password"><i class="bi bi-key-fill"></i> {{ __('interface.actions.change_password') }}</a>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-qr-code"></i> {{ __('interface.misc.2fa_long') }}
                    </div>
                    <div class="card-body">
                        @if(Auth::user()->two_factor_confirmed)
                            <form action="{{ route('two-factor.disable') }}" method="post">
                                @csrf
                                @method('delete')
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-eye"></i> {{ __('interface.data.recovery_codes') }}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @foreach(Auth::user()->recoveryCodes() as $code)
                                            <li class="dropdown-item">{{ $code }}</li>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> {{ __('interface.actions.disable_2fa') }}</button>
                            </form>
                        @elseif(Auth::user()->two_factor_secret)
                            <p>{{ __('interface.misc.2fa_validation_notice') }}</p>
                            {!! Auth::user()->twoFactorQrCodeSvg() !!}
                            <form action="{{ route('two-factor.confirm.admin') }}" method="post">
                                @csrf
                                <div class="input-group mt-3">
                                    <input name="code" class="form-control" required />
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> {{ __('interface.actions.validate_2fa') }}</button>
                                </div>
                            </form>
                        @else
                            <form action="{{ route('two-factor.enable') }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> {{ __('interface.actions.activate_2fa') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.profile.update') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? Auth::user()->name }}">

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                <div class="alert alert-primary mt-4 mb-1">
                                    <i class="bi bi-info-circle"></i> {{ __('interface.misc.email_change_notice') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.email') }}</label>

                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('name') is-invalid @enderror" name="email" value="{{ old('email') ?? Auth::user()->email }}">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email-confirm" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.confirm_email') }}</label>

                            <div class="col-md-8">
                                <input id="email-confirm" type="email" class="form-control" name="email_confirmation">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="password" tabindex="-1" aria-labelledby="passwordLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="passwordLabel"><i class="bi bi-key-fill"></i> {{ __('interface.actions.change_password') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.profile.password') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="password-current" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.current_password') }}</label>

                            <div class="col-md-8">
                                <input id="password-current" type="password" class="form-control @error('password_current') is-invalid @enderror" name="password_current">

                                @error('password_current')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mt-5">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.new_password') }}</label>

                            <div class="col-md-8">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">

                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.confirm_new_password') }}</label>

                            <div class="col-md-8">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning"><i class="bi bi-key-fill"></i> {{ __('interface.actions.change') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
