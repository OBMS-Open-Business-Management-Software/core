@extends('layouts.customer')

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
                        <label class="font-weight-bold mb-0">{{ __('interface.data.email') }}:</label> {{ Auth::user()->email }} {!! Auth::user()->hasVerifiedEmail() ? '<span class="badge badge-success">' . __('interface.status.verified') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.unverified') . '</span>' !!}<br>
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
                            <form action="{{ route('two-factor.confirm') }}" method="post">
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
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-person"></i> {{ __('interface.data.profile_details') }}
                    </div>
                    <div class="card-body">
                        @if (empty($profile = Auth::user()->profile))
                            <form action="{{ route('customer.profile.complete') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="firstname" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.firstname') }}*</label>

                                            <div class="col-md-8">
                                                <input id="firstname" type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" value="{{ old('firstname') }}">

                                                @error('firstname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="lastname" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.lastname') }}*</label>

                                            <div class="col-md-8">
                                                <input id="lastname" type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ old('lastname') }}">

                                                @error('lastname')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label for="company" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.company') }}</label>

                                            <div class="col-md-10">
                                                <input id="company" type="text" class="form-control @error('company') is-invalid @enderror" name="company" value="{{ old('company') }}">

                                                @error('company')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="tax_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.tax_number') }}</label>

                                            <div class="col-md-8">
                                                <input id="tax_id" type="text" class="form-control @error('tax_id') is-invalid @enderror" name="tax_id" value="{{ old('tax_id') }}">

                                                @error('tax_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="vat_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.vat_id') }}</label>

                                            <div class="col-md-8">
                                                <input id="vat_id" type="text" class="form-control @error('vat_id') is-invalid @enderror" name="vat_id" value="{{ old('vat_id') }}">

                                                @error('vat_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-8">
                                        <div class="form-group row">
                                            <label for="street" class="col-md-3 col-form-label text-md-right">{{ __('interface.data.street') }}*</label>

                                            <div class="col-md-9">
                                                <input id="street" type="text" class="form-control @error('street') is-invalid @enderror" name="street" value="{{ old('street') }}">

                                                @error('street')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label for="housenumber" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.housenumber') }}*</label>

                                            <div class="col-md-8">
                                                <input id="housenumber" type="text" class="form-control @error('housenumber') is-invalid @enderror" name="housenumber" value="{{ old('housenumber') }}">

                                                @error('housenumber')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row align-items-center">
                                            <label for="addition" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.additional_info') }}</label>

                                            <div class="col-md-10">
                                                <input id="addition" type="text" class="form-control @error('addition') is-invalid @enderror" name="addition" value="{{ old('addition') }}">

                                                @error('addition')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label for="postalcode" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.postalcode') }}*</label>

                                            <div class="col-md-6">
                                                <input id="postalcode" type="text" class="form-control @error('postalcode') is-invalid @enderror" name="postalcode" value="{{ old('postalcode') }}">

                                                @error('postalcode')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group row">
                                            <label for="city" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.city') }}*</label>

                                            <div class="col-md-10">
                                                <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}">

                                                @error('city')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.state') }}*</label>

                                            <div class="col-md-8">
                                                <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}">

                                                @error('state')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="country" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.country') }}*</label>

                                            <div class="col-md-8">
                                                <select id="country" class="form-control @error('country') is-invalid @enderror" name="country">
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}">{{ __($country->name) }}</option>
                                                    @endforeach
                                                </select>

                                                @error('country')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.email') }}*</label>

                                            <div class="col-md-8">
                                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">

                                                @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.phone') }}*</label>

                                            <div class="col-md-8">
                                                <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">

                                                @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary float-right" type="submit"><i class="bi bi-check-circle"></i> {{ __('interface.actions.complete_now') }}</button>
                                <span class="float-right col-form-label mr-4 font-italic">* {{ __('interface.misc.required_fields') }}</span>
                            </form>
                        @else
                            @if (! empty($profile->company))
                                <label class="font-weight-bold mb-0">{{ __('interface.data.company') }}:</label> {{ $profile->company }}<br>
                                <label class="font-weight-bold mb-0">{{ __('interface.data.account_type') }}:</label> {{ __('interface.data.company') }}<br>
                            @else
                                <label class="font-weight-bold mb-0">{{ __('interface.data.account_type') }}:</label> {{ __('interface.data.personal') }}<br>
                            @endif
                            <label class="font-weight-bold mb-0">{{ __('interface.data.contact_person') }}:</label> {{ $profile->firstname }} {{ $profile->lastname }}<br>
                            @if (! empty($profile->tax_id))
                                <label class="font-weight-bold mb-0">{{ __('interface.data.tax_number') }}:</label> {{ $profile->tax_id }}<br>
                            @endif
                            @if (! empty($profile->vat_id))
                                <label class="font-weight-bold mb-0">{{ __('interface.data.vat_id') }}:</label> {{ $profile->vat_id }}<br>
                            @endif
                            <label class="font-weight-bold mb-0">{{ __('interface.data.reverse_charge') }}:</label> {!! Auth::user()->reverseCharge ? '<span class="badge badge-success">' . __('interface.misc.applicable') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.not_applicable') . '</span>' !!}<br>
                            <label class="font-weight-bold mb-0">{{ __('interface.data.verification_status') }}:</label> {!! $profile->verified ? '<span class="badge badge-success">' . __('interface.status.verified') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.unverified') . '</span>' !!}<br>
                            <br>
                            <a class="btn btn-primary" data-toggle="modal" data-target="#editProfile"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit_profile_details') }}</a>
                        @endif
                    </div>
                </div>
                @if (! empty(Auth::user()->profile))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="pills-email-tab" data-toggle="pill" href="#pills-email" role="tab" aria-controls="pills-email" aria-selected="true">{{ __('interface.data.email_addresses') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-phone-tab" data-toggle="pill" href="#pills-phone" role="tab" aria-controls="pills-phone" aria-selected="false">{{ __('interface.data.phone_numbers') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-address-tab" data-toggle="pill" href="#pills-address" role="tab" aria-controls="pills-address" aria-selected="false">{{ __('interface.data.postal_addresses') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-bank-tab" data-toggle="pill" href="#pills-bank" role="tab" aria-controls="pills-bank" aria-selected="false">{{ __('interface.data.bank_accounts') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-email" role="tabpanel" aria-labelledby="pills-email-tab">
                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <i class="bi bi-envelope"></i> {{ __('interface.data.email_addresses') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addEmail"><i class="bi bi-plus-circle"></i> {{ __('interface.email.create') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="emailaddresses" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('interface.data.email_address') }}</td>
                                                            <td>{{ __('interface.data.type') }}</td>
                                                            <td>{{ __('interface.data.status') }}</td>
                                                            <td>{{ __('interface.actions.resend_confirmation') }}</td>
                                                            <td>{{ __('interface.actions.edit') }}</td>
                                                            <td>{{ __('interface.actions.delete') }}</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-phone" role="tabpanel" aria-labelledby="pills-phone-tab">
                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <i class="bi bi-telephone"></i> {{ __('interface.data.phone_numbers') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addPhone"><i class="bi bi-plus-circle"></i> {{ __('interface.phone.create') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="phonenumbers" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('interface.data.phone_number') }}</td>
                                                            <td>{{ __('interface.data.type') }}</td>
                                                            <td>{{ __('interface.actions.edit') }}</td>
                                                            <td>{{ __('interface.actions.delete') }}</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-address" role="tabpanel" aria-labelledby="pills-address-tab">
                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <i class="bi bi-house"></i> {{ __('interface.data.postal_addresses') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addAddress"><i class="bi bi-plus-circle"></i> {{ __('interface.address.create') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="addresses" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('interface.data.address') }}</td>
                                                            <td>{{ __('interface.data.type') }}</td>
                                                            <td>{{ __('interface.actions.edit') }}</td>
                                                            <td>{{ __('interface.actions.delete') }}</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-bank" role="tabpanel" aria-labelledby="pills-bank-tab">
                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <i class="bi bi-receipt"></i> {{ __('interface.data.bank_accounts') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addAccount"><i class="bi bi-plus-circle"></i> {{ __('interface.bank_account.create') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="bankaccounts" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('interface.bank_account.iban') }}</td>
                                                            <td>{{ __('interface.bank_account.bic') }}</td>
                                                            <td>{{ __('interface.bank_account.bank') }}</td>
                                                            <td>{{ __('interface.bank_account.owner') }}</td>
                                                            <td>{{ __('interface.actions.sign_sepa') }}</td>
                                                            <td>{{ __('interface.actions.make_primary') }}</td>
                                                            <td>{{ __('interface.actions.delete') }}</td>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
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
                <form action="{{ route('customer.profile.update') }}" method="post">
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
                <form action="{{ route('customer.profile.password') }}" method="post">
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

    @if (! empty($profile = Auth::user()->profile))
        <div class="modal fade" id="editProfile" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProfileLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit_profile_details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('customer.profile.update.details') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="firstname" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.firstname') }}*</label>

                                        <div class="col-md-8">
                                            <input id="firstname" type="text" class="form-control @error('firstname') is-invalid @enderror" name="firstname" value="{{ $profile->firstname }}">

                                            @error('firstname')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="lastname" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.lastname') }}*</label>

                                        <div class="col-md-8">
                                            <input id="lastname" type="text" class="form-control @error('lastname') is-invalid @enderror" name="lastname" value="{{ $profile->lastname }}">

                                            @error('lastname')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label for="company" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.company') }}</label>

                                        <div class="col-md-10">
                                            <input id="company" type="text" class="form-control @error('company') is-invalid @enderror" name="company" value="{{ $profile->company }}">

                                            @error('company')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label for="tax_id" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.tax_number') }}</label>

                                        <div class="col-md-10">
                                            <input id="tax_id" type="text" class="form-control @error('tax_id') is-invalid @enderror" name="tax_id" value="{{ $profile->tax_id }}">

                                            @error('tax_id')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label for="vat_id" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.vat_id') }}</label>

                                        <div class="col-md-10">
                                            <input id="vat_id" type="text" class="form-control @error('vat_id') is-invalid @enderror" name="vat_id" value="{{ $profile->vat_id }}">

                                            @error('vat_id')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
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

        <div class="modal fade" id="addEmail" tabindex="-1" aria-labelledby="addEmailLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addEmailLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.email.create') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('customer.profile.email.create') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.email') }}*</label>

                                <div class="col-md-8">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mt-4">
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('interface.misc.all') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('interface.misc.none') }}</label>

                                <div class="col-md-2">
                                    <input id="none" type="radio" class="form-control" name="type" value="none" checked>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('interface.actions.create') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addPhone" tabindex="-1" aria-labelledby="addPhoneLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addPhoneLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.phone.create') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('customer.profile.phone.create') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.phone') }}*</label>

                                <div class="col-md-8">
                                    <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">

                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mt-4">
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('interface.misc.all') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('interface.misc.none') }}</label>

                                <div class="col-md-2">
                                    <input id="none" type="radio" class="form-control" name="type" value="none" checked>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('interface.actions.create') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addAddress" tabindex="-1" aria-labelledby="addAddressLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addAddressLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.address.create') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('customer.profile.address.create') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <label for="street" class="col-md-3 col-form-label text-md-right">{{ __('interface.data.street') }}*</label>

                                        <div class="col-md-9">
                                            <input id="street" type="text" class="form-control @error('street') is-invalid @enderror" name="street" value="{{ old('street') }}">

                                            @error('street')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label for="housenumber" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.housenumber') }}*</label>

                                        <div class="col-md-8">
                                            <input id="housenumber" type="text" class="form-control @error('housenumber') is-invalid @enderror" name="housenumber" value="{{ old('housenumber') }}">

                                            @error('housenumber')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row align-items-center">
                                        <label for="addition" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.additional_info') }}</label>

                                        <div class="col-md-10">
                                            <input id="addition" type="text" class="form-control @error('addition') is-invalid @enderror" name="addition" value="{{ old('addition') }}">

                                            @error('addition')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label for="postalcode" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.postalcode') }}*</label>

                                        <div class="col-md-6">
                                            <input id="postalcode" type="text" class="form-control @error('postalcode') is-invalid @enderror" name="postalcode" value="{{ old('postalcode') }}">

                                            @error('postalcode')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <label for="city" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.city') }}*</label>

                                        <div class="col-md-10">
                                            <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}">

                                            @error('city')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.state') }}*</label>

                                        <div class="col-md-8">
                                            <input id="state" type="text" class="form-control @error('state') is-invalid @enderror" name="state" value="{{ old('state') }}">

                                            @error('state')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="country" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.country') }}*</label>

                                        <div class="col-md-8">
                                            <select id="country" class="form-control @error('country') is-invalid @enderror" name="country">
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}">{{ __($country->name) }}</option>
                                                @endforeach
                                            </select>

                                            @error('country')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mt-4">
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('interface.misc.all') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('interface.misc.none') }}</label>

                                <div class="col-md-2">
                                    <input id="none" type="radio" class="form-control" name="type" value="none" checked>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('interface.actions.create') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addAccount" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addAccountLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.bank_account.create') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('customer.profile.bank.create') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('interface.bank_account.iban') }}*</label>

                                <div class="col-md-8">
                                    <input id="iban" type="text" class="form-control @error('iban') is-invalid @enderror" name="iban" value="{{ old('iban') }}">

                                    @error('iban')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('interface.bank_account.bic') }}*</label>

                                <div class="col-md-8">
                                    <input id="bic" type="text" class="form-control @error('bic') is-invalid @enderror" name="bic" value="{{ old('bic') }}">

                                    @error('bic')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('interface.bank_account.bank') }}*</label>

                                <div class="col-md-8">
                                    <input id="bank" type="text" class="form-control @error('bank') is-invalid @enderror" name="bank" value="{{ old('bank') }}">

                                    @error('bank')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="owner" class="col-md-4 col-form-label text-md-right">{{ __('interface.bank_account.owner') }}*</label>

                                <div class="col-md-8">
                                    <input id="owner" type="text" class="form-control @error('owner') is-invalid @enderror" name="owner" value="{{ old('owner') }}">

                                    @error('owner')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label for="primary" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.primary_bank_account') }}</label>

                                <div class="col-md-8">
                                    <input id="primary" type="checkbox" class="form-control @error('primary') is-invalid @enderror" name="primary" value="true">

                                    @error('primary')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('interface.actions.create') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('javascript')
    @if (! empty(Auth::user()->profile))
        <script type="text/javascript">
            $(window).on('load', function () {
                $('#emailaddresses').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/customer/profile/list/emailaddresses',
                    columns: [
                        { data: 'email' },
                        { data: 'type', sWidth: '20%' },
                        { data: 'status', sWidth: '20%' },
                        { data: 'resend', bSortable: false, sWidth: '1%' },
                        { data: 'edit', bSortable: false, sWidth: '1%' },
                        { data: 'delete', bSortable: false, sWidth: '1%' }
                    ],
                    order: [[0, 'desc']]
                });

                $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                    let target = $(this).attr('href');

                    if (! $.fn.DataTable.isDataTable(target + ' #phonenumbers')) {
                        $(target + ' #phonenumbers').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/customer/profile/list/phonenumbers',
                            columns: [
                                { data: 'phone' },
                                { data: 'type', sWidth: '20%' },
                                { data: 'edit', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }

                    if (! $.fn.DataTable.isDataTable(target + ' #addresses')) {
                        $(target + ' #addresses').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/customer/profile/list/addresses',
                            columns: [
                                { data: 'address' },
                                { data: 'type', sWidth: '20%' },
                                { data: 'edit', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }

                    if (! $.fn.DataTable.isDataTable(target + ' #bankaccounts')) {
                        $(target + ' #bankaccounts').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/customer/profile/list/bankaccounts',
                            columns: [
                                { data: 'iban' },
                                { data: 'bic' },
                                { data: 'bank' },
                                { data: 'owner' },
                                { data: 'sepa', bSortable: false, sWidth: '1%' },
                                { data: 'primary', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }
                });
            });
        </script>
    @endif
@endsection
