@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.customers') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to list') }}</a>
                <a class="btn btn-primary float-right ml-1" data-toggle="modal" data-target="#edit"><i class="bi bi-pencil-square"></i> {{ __('Edit user details') }}</a>
                @if ($user->locked)
                    <a href="{{ route('admin.customers.lock', $user->id) }}" class="btn btn-success float-right ml-1"><i class="bi bi-unlock"></i> {{ __('Unlock') }}</a>
                @else
                    <a href="{{ route('admin.customers.lock', $user->id) }}" class="btn btn-warning float-right ml-1"><i class="bi bi-lock"></i> {{ __('Lock') }}</a>
                @endif
                <a class="btn btn-warning float-right ml-1" data-toggle="modal" data-target="#password"><i class="bi bi-key-fill"></i> {{ __('Change password') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> {{ __('User Details') }}
                            </div>
                            <div class="card-body">
                                <label class="font-weight-bold mb-0">{{ __('Name:') }}</label> {{ $user->name }}<br>
                                <label class="font-weight-bold mb-0">{{ __('Email:') }}</label> {{ $user->email }} {!! $user->hasVerifiedEmail() ? '<span class="badge badge-success badge-pill">' . __('Verified') . '</span>' : '<span class="badge badge-warning badge-pill">' . __('Unverified') . '</span>' !!}<br>
                                <label class="font-weight-bold mb-0">{{ __('Account status:') }}</label> {!! $user->locked ? '<span class="badge badge-warning badge-pill">' . __('Locked') . '</span>' : '<span class="badge badge-success badge-pill">' . __('Unlocked') . '</span>' !!}<br>
                                <label class="font-weight-bold mb-0">{{ __('Account Balance:') }}</label> {{ number_format($user->prepaidAccountBalance, 2) }} €<br>
                                <label class="font-weight-bold mb-0">{{ __('Internal Credit Score:') }}</label>
                                @if ($user->creditScore == 0)
                                    {{ __('Neutral') }} <span class="badge badge-secondary badge-pill">{{ $user->creditScore }} {{ __('Points') }}</span>
                                @elseif ($user->creditScore > 0)
                                    {{ __('Good') }} <span class="badge badge-secondary badge-pill">{{ $user->creditScore }} {{ __('Points') }}</span>
                                @elseif ($user->creditScore < 0)
                                    {{ __('Bad') }} <span class="badge badge-danger badge-pill">{{ $user->creditScore }} {{ __('Points') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-list"></i> {{ __('Accepted Pages') }}
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <td>{{ __('Page') }}</td>
                                            <td>{{ __('Signature') }}</td>
                                            <td>{{ __('IP Address') }}</td>
                                            <td width="50%">{{ __('User Agent') }}</td>
                                            <td width="1%">{{ __('interface.actions.view') }}</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->acceptance as $acceptance)
                                            <tr>
                                                <td>
                                                    {{ __($acceptance->pageVersion->page->title) }}
                                                    <span class="small d-block">{{ $acceptance->pageVersion->created_at->format('d.m.Y, H:i') }}</span>
                                                </td>
                                                <td>
                                                    {{ ! empty($acceptance->signed_at) ? $acceptance->signed_at->format('d.m.Y, H:i') : __('Never') }}<br>
                                                    {!! $acceptance->signed ? '<span class="badge badge-success badge-pill">' . __('Valid') . '</span>' : '<span class="badge badge-warning badge-pill">' . __('Invalid') . '</span>' !!}<br>
                                                    <span class="small">{{ $acceptance->signature }}</span>
                                                </td>
                                                <td>{{ $acceptance->ip }}</td>
                                                <td>{{ $acceptance->user_agent }}</td>
                                                <td width="1%"><a href="{{ route('public.page.' . $acceptance->pageVersion->page->id . '.version', $acceptance->pageVersion->id) }}" class="btn btn-primary btn-sm" target="_blank"><i class="bi bi-eye"></i></a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @if($user->two_factor_confirmed)
                    <div class="card mt-4">
                        <div class="card-header">
                            <i class="bi bi-qr-code"></i> {{ __('2-Factor Authentication') }}
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.customers.profile.2fa.disable', $user->id) }}" method="post"> <!-- TODO: Write route where admin can remove user 2FA -->
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> {{ __('Disable 2FA') }}</button>
                            </form>
                        </div>
                    </div>
                @endif
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-person"></i> {{ __('Profile Details') }}
                    </div>
                    <div class="card-body">
                        @if (empty($profile = $user->profile))
                            <form action="{{ route('admin.customers.profile.complete', $user->id) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="firstname" class="col-md-4 col-form-label text-md-right">{{ __('Firstname') }}*</label>

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
                                            <label for="lastname" class="col-md-4 col-form-label text-md-right">{{ __('Lastname') }}*</label>

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
                                            <label for="company" class="col-md-2 col-form-label text-md-right">{{ __('Company') }}</label>

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
                                            <label for="tax_id" class="col-md-4 col-form-label text-md-right">{{ __('Tax Number') }}</label>

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
                                            <label for="vat_id" class="col-md-4 col-form-label text-md-right">{{ __('VAT Identification Number') }}</label>

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
                                            <label for="street" class="col-md-3 col-form-label text-md-right">{{ __('Street') }}*</label>

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
                                            <label for="housenumber" class="col-md-4 col-form-label text-md-right">{{ __('Housenumber') }}*</label>

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
                                            <label for="addition" class="col-md-2 col-form-label text-md-right">{{ __('Additional Info') }}</label>

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
                                            <label for="postalcode" class="col-md-6 col-form-label text-md-right">{{ __('Postalcode') }}*</label>

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
                                            <label for="city" class="col-md-2 col-form-label text-md-right">{{ __('City') }}*</label>

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
                                            <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('State') }}*</label>

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
                                            <label for="country" class="col-md-4 col-form-label text-md-right">{{ __('Country') }}*</label>

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
                                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}*</label>

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
                                            <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Phone') }}*</label>

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
                                <button class="btn btn-primary float-right" type="submit"><i class="bi bi-check-circle"></i> {{ __('Complete now') }}</button>
                                <span class="float-right col-form-label mr-4 font-italic">* {{ __('Required fields') }}</span>
                            </form>
                        @else
                            @if (! empty($profile->company))
                                <label class="font-weight-bold mb-0">{{ __('Company:') }}</label> {{ $profile->company }}<br>
                                <label class="font-weight-bold mb-0">{{ __('Account Type:') }}</label> {{ __('Company') }}<br>
                            @else
                                <label class="font-weight-bold mb-0">{{ __('Account Type:') }}</label> {{ __('Personal') }}<br>
                            @endif
                            <label class="font-weight-bold mb-0">{{ __('Contact Person:') }}</label> {{ $profile->firstname }} {{ $profile->lastname }}<br>
                            @if (! empty($profile->tax_id))
                                <label class="font-weight-bold mb-0">{{ __('Tax Number:') }}</label> {{ $profile->tax_id }}<br>
                            @endif
                            @if (! empty($profile->vat_id))
                                <label class="font-weight-bold mb-0">{{ __('VAT Identification Number:') }}</label> {{ $profile->vat_id }}<br>
                            @endif
                            <label class="font-weight-bold mb-0">{{ __('Reverse Charge:') }}</label> {!! $user->reverseCharge ? '<span class="badge badge-success badge-pill">' . __('Applicable') . '</span>' : '<span class="badge badge-warning badge-pill">' . __('Not applicable') . '</span>' !!}<br>
                            <label class="font-weight-bold mb-0">{{ __('Verification Status:') }}</label> {!! $profile->verified ? '<span class="badge badge-success badge-pill">' . __('Verified') . '</span>' : '<span class="badge badge-warning badge-pill">' . __('Unverified') . '</span>' !!}<br>
                            <br>
                            <a class="btn btn-primary" data-toggle="modal" data-target="#editProfile"><i class="bi bi-pencil-square"></i> {{ __('Edit profile details') }}</a>
                        @endif
                    </div>
                </div>
                @if (! empty($user->profile))
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="pills-email-tab" data-toggle="pill" href="#pills-email" role="tab" aria-controls="pills-email" aria-selected="true">{{ __('Email Addresses') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-phone-tab" data-toggle="pill" href="#pills-phone" role="tab" aria-controls="pills-phone" aria-selected="false">{{ __('Phone Numbers') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-address-tab" data-toggle="pill" href="#pills-address" role="tab" aria-controls="pills-address" aria-selected="false">{{ __('Postal Addresses') }}</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-bank-tab" data-toggle="pill" href="#pills-bank" role="tab" aria-controls="pills-bank" aria-selected="false">{{ __('Bank Accounts') }}</a>
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
                                            <i class="bi bi-envelope"></i> {{ __('Email Addresses') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addEmail"><i class="bi bi-plus-circle"></i> {{ __('Create Email Address') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="emailaddresses" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('Email Address') }}</td>
                                                            <td>{{ __('interface.data.type') }}</td>
                                                            <td>{{ __('Status') }}</td>
                                                            <td>{{ __('Resend Confirmation') }}</td>
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
                                            <i class="bi bi-telephone"></i> {{ __('Phone Numbers') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addPhone"><i class="bi bi-plus-circle"></i> {{ __('Create Phone Number') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="phonenumbers" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('Phone Number') }}</td>
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
                                            <i class="bi bi-house"></i> {{ __('Postal Addresses') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addAddress"><i class="bi bi-plus-circle"></i> {{ __('Create Address') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="addresses" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('Address') }}</td>
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
                                            <i class="bi bi-receipt"></i> {{ __('Bank Accounts') }}
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addAccount"><i class="bi bi-plus-circle"></i> {{ __('Create Bank Account') }}</a>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <table id="bankaccounts" class="table mt-4 w-100">
                                                        <thead>
                                                        <tr>
                                                            <td>{{ __('IBAN') }}</td>
                                                            <td>{{ __('BIC') }}</td>
                                                            <td>{{ __('Bank') }}</td>
                                                            <td>{{ __('Owner') }}</td>
                                                            <td>{{ __('Sign SEPA Mandate') }}</td>
                                                            <td>{{ __('Make Primary') }}</td>
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
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-cart-fill"></i> {{ __('Orders') }}
                            </div>
                            <div class="card-body">
                                <table id="orders" class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.id') }}</td>
                                        <td>{{ __('Form') }}</td>
                                        <td>{{ __('Product Type') }}</td>
                                        <td>{{ __('interface.data.amount') }}</td>
                                        <td>{{ __('Steps') }}</td>
                                        <td>{{ __('History') }}</td>
                                        <td>{{ __('Approve') }}</td>
                                        <td>{{ __('Disapprove') }}</td>
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
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-text"></i> {{ __('Invoices') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addInvoice"><i class="bi bi-plus-circle"></i> {{ __('Create Invoice') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <table id="invoices" class="table mt-4 w-100">
                                            <thead>
                                            <tr>
                                                <td>{{ __('Invoice No.') }}</td>
                                                <td>{{ __('interface.data.type') }}</td>
                                                <td>{{ __('Status') }}</td>
                                                <td>{{ __('Date') }}</td>
                                                <td>{{ __('Due By') }}</td>
                                                <td>{{ __('interface.actions.view') }}</td>
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-text-fill"></i> {{ __('Contracts') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addContract"><i class="bi bi-plus-circle"></i> {{ __('Create Contract') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <table id="contracts" class="table mt-4 w-100">
                                            <thead>
                                            <tr>
                                                <td>{{ __('Contract No.') }}</td>
                                                <td>{{ __('interface.data.type') }}</td>
                                                <td>{{ __('Status') }}</td>
                                                <td>{{ __('interface.actions.view') }}</td>
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
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-list"></i> {{ __('Account Transactions') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addTransaction"><i class="bi bi-plus-circle"></i> {{ __('Create Transaction') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <table id="transactions" class="table mt-4 w-100">
                                            <thead>
                                            <tr>
                                                <td>{{ __('Date') }}</td>
                                                <td>{{ __('Contract No.') }}</td>
                                                <td>{{ __('Invoice No.') }}</td>
                                                <td>{{ __('interface.data.amount') }}</td>
                                                <td>{{ __('Transaction Method') }}</td>
                                                <td>{{ __('Transaction No.') }}</td>
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
                    <div class="col-md-6">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editLabel"><i class="bi bi-pencil-square"></i> {{ __('Edit User Details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.customers.profile.update', $user->id) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? $user->name }}">

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
                                    <i class="bi bi-info-circle"></i> The email address only needs to be confirmed if it has changed.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}</label>

                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('name') is-invalid @enderror" name="email" value="{{ old('email') ?? $user->email }}">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Email') }}</label>

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
                    <h5 class="modal-title" id="passwordLabel"><i class="bi bi-key-fill"></i> {{ __('Change Password') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.customers.profile.password', $user->id) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('New Password') }}</label>

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
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                            <div class="col-md-8">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning"><i class="bi bi-key-fill"></i> {{ __('Change') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (! empty($profile = $user->profile))
        <div class="modal fade" id="editProfile" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProfileLabel"><i class="bi bi-pencil-square"></i> {{ __('Edit Profile Details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.customers.profile.update.details', $user->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label for="firstname" class="col-md-4 col-form-label text-md-right">{{ __('Firstname') }}*</label>

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
                                        <label for="lastname" class="col-md-4 col-form-label text-md-right">{{ __('Lastname') }}*</label>

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
                                        <label for="company" class="col-md-2 col-form-label text-md-right">{{ __('Company') }}</label>

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
                                        <label for="tax_id" class="col-md-2 col-form-label text-md-right">{{ __('Tax Number') }}</label>

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
                                        <label for="vat_id" class="col-md-2 col-form-label text-md-right">{{ __('VAT Identification Number') }}</label>

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
                        <h5 class="modal-title text-white" id="addEmailLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Email Address') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.customers.profile.email.create', $user->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Email') }}*</label>

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
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('All') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('Billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('Contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('None') }}</label>

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
                        <h5 class="modal-title text-white" id="addPhoneLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Phone Number') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.customers.profile.phone.create', $user->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="phone" class="col-md-4 col-form-label text-md-right">{{ __('Phone') }}*</label>

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
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('All') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('Billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('Contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('None') }}</label>

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
                        <h5 class="modal-title text-white" id="addAddressLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Address') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.customers.profile.address.create', $user->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <div class="form-group row">
                                        <label for="street" class="col-md-3 col-form-label text-md-right">{{ __('Street') }}*</label>

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
                                        <label for="housenumber" class="col-md-4 col-form-label text-md-right">{{ __('Housenumber') }}*</label>

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
                                        <label for="addition" class="col-md-2 col-form-label text-md-right">{{ __('Additional Info') }}</label>

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
                                        <label for="postalcode" class="col-md-6 col-form-label text-md-right">{{ __('Postalcode') }}*</label>

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
                                        <label for="city" class="col-md-2 col-form-label text-md-right">{{ __('City') }}*</label>

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
                                        <label for="state" class="col-md-4 col-form-label text-md-right">{{ __('State') }}*</label>

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
                                        <label for="country" class="col-md-4 col-form-label text-md-right">{{ __('Country') }}*</label>

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
                                <label for="all" class="col-md-6 col-form-label text-md-right">{{ __('All') }}</label>

                                <div class="col-md-2">
                                    <input id="all" type="radio" class="form-control" name="type" value="all">
                                </div>

                                <label for="billing" class="col-md-2 col-form-label text-md-right">{{ __('Billing') }}</label>

                                <div class="col-md-2">
                                    <input id="billing" type="radio" class="form-control" name="type" value="billing">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="contact" class="col-md-6 col-form-label text-md-right">{{ __('Contact') }}</label>

                                <div class="col-md-2">
                                    <input id="contact" type="radio" class="form-control" name="type" value="contact">
                                </div>

                                <label for="none" class="col-md-2 col-form-label text-md-right">{{ __('None') }}</label>

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
                        <h5 class="modal-title text-white" id="addAccountLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Bank Account') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.customers.profile.bank.create', $user->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('IBAN') }}*</label>

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
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('BIC') }}*</label>

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
                                <label for="iban" class="col-md-4 col-form-label text-md-right">{{ __('Bank') }}*</label>

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
                                <label for="owner" class="col-md-4 col-form-label text-md-right">{{ __('Owner') }}*</label>

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
                                <label for="primary" class="col-md-4 col-form-label text-md-right">{{ __('Primary bank account') }}</label>

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

    <div class="modal fade" id="addContract" tabindex="-1" aria-labelledby="addContractLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addContractLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Contract') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.contracts.add') }}" method="post">
                    @csrf
                    <input id="user_id" type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="type_id" class="col-md-4 col-form-label text-md-right">{{ __('Contract Type') }}</label>

                            <div class="col-md-8">
                                <select id="type_id" class="form-control @error('type_id') is-invalid @enderror" name="type_id">
                                    @foreach ($contractTypes as $type)
                                        <option value="{{ $type->id }}"{{ $type->id == old('type_id') ? ' selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                @error('type_id')
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

    <div class="modal fade" id="addInvoice" tabindex="-1" aria-labelledby="addInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addInvoiceLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Invoice') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.customers.add') }}" method="post">
                    @csrf
                    <input id="user_id" type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="type_id" class="col-md-4 col-form-label text-md-right">{{ __('Payment Type') }}</label>

                            <div class="col-md-8">
                                <select id="type_id" class="form-control @error('type_id') is-invalid @enderror" name="type_id">
                                    @foreach ($invoiceTypes as $type)
                                        <option value="{{ $type->id }}"{{ $type->id == old('type_id') ? ' selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                @error('type_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="contract_id" class="col-md-4 col-form-label text-md-right">{{ __('Contract ID') }}</label>

                            <div class="col-md-8">
                                <input id="contract_id" type="number" class="form-control @error('contract_id') is-invalid @enderror" name="contract_id" value="{{ old('contract_id') }}">

                                @error('contract_id')
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

    <div class="modal fade" id="addTransaction" tabindex="-1" aria-labelledby="addTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addTransactionLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Transaction') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.customers.transactions.create', $user->id) }}" method="post">
                    @csrf
                    <input id="user_id" type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="contract_id" class="col-md-4 col-form-label text-md-right">{{ __('Contract ID') }}</label>

                            <div class="col-md-8">
                                <input id="contract_id" type="number" class="form-control @error('contract_id') is-invalid @enderror" name="contract_id" value="{{ old('contract_id') }}">

                                @error('contract_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="invoice_id" class="col-md-4 col-form-label text-md-right">{{ __('Invoice ID') }}</label>

                            <div class="col-md-8">
                                <input id="invoice_id" type="number" class="form-control @error('invoice_id') is-invalid @enderror" name="invoice_id" value="{{ old('invoice_id') }}">

                                @error('invoice_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="amount" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.amount') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="amount" type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">€</span>
                                    </div>
                                </div>

                                @error('amount')
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
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            @if (! empty($user->profile))
                $('#emailaddresses').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/admin/customers/{{ $user->id }}/list/emailaddresses?user_id={{ $user->id }}',
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
                            ajax: '/admin/customers/{{ $user->id }}/list/phonenumbers?user_id={{ $user->id }}',
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
                            ajax: '/admin/customers/{{ $user->id }}/list/addresses?user_id={{ $user->id }}',
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
                            ajax: '/admin/customers/{{ $user->id }}/list/bankaccounts?user_id={{ $user->id }}',
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
            @endif

            $('#contracts').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/contracts/list/{{ $user->id }}',
                columns: [
                    { data: 'id' },
                    { data: 'type', sWidth: '20%' },
                    { data: 'status', sWidth: '10%' },
                    { data: 'view', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });

            $('#invoices').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/customers/list/{{ $user->id }}',
                columns: [
                    { data: 'id' },
                    { data: 'type', sWidth: '20%' },
                    { data: 'status', sWidth: '10%' },
                    { data: 'date', sWidth: '20%' },
                    { data: 'due', sWidth: '20%' },
                    { data: 'view', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });

            $('#transactions').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/customers/{{ $user->id }}/list/transactions',
                columns: [
                    { data: 'date' },
                    { data: 'contract_id' },
                    { data: 'invoice_id' },
                    { data: 'amount', sWidth: '10%' },
                    { data: 'transaction_method', sWidth: '20%' },
                    { data: 'transaction_id', sWidth: '20%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' },
                ],
                order: [[0, 'desc']]
            });

            function initTableOptionRemovalClickListener(table) {
                table.find('.fieldDelete').off();
                table.find('.fieldDelete').on('click', function () {
                    $(this).parent().parent().remove();
                });
            }

            $('#orders').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/shop/orders/list/{{ $user->id }}',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'form' },
                    { data: 'product_type' },
                    { data: 'amount' },
                    { data: 'status' },
                    { data: 'history', bSortable: false, sWidth: '1%' },
                    { data: 'approve', bSortable: false, sWidth: '1%' },
                    { data: 'disapprove', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
                fnDrawCallback: function () {
                    $('.options_table').each(function () {
                        let table = $(this);

                        table.find('.fieldAdd').on('click', function () {
                            let key = table.find('.fieldKey').first().val();
                            let value = table.find('.fieldValue').first().val();
                            let timestamp = Date.now();

                            table.find('.fieldKey').first().val('');
                            table.find('.fieldValue').first().val('');
                            table.find('.fieldFees').val('');
                            table.find('.fieldDefault').first().prop('checked', false);

                            table.find('.options_tbody').first().append('<tr><td><input type="text" class="form-control" name="options[' + timestamp + '][key]" value="' + key + '"></td><td><input type="text" class="form-control" name="options[' + timestamp + '][value]" value="' + value + '"></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>');
                        });

                        initTableOptionRemovalClickListener(table);
                    });
                }
            });
        });
    </script>
@endsection
