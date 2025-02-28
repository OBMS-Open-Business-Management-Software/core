@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.suppliers') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                <a class="btn btn-primary float-right ml-1" data-toggle="modal" data-target="#edit"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> {{ __('interface.data.user_details') }}
                            </div>
                            <div class="card-body">
                                <label class="font-weight-bold mb-0">{{ __('interface.data.name') }}:</label> {{ $user->name }}<br>
                                <label class="font-weight-bold mb-0">{{ __('interface.data.email') }}:</label> {{ $user->email }} {!! $user->hasVerifiedEmail() ? '<span class="badge badge-success">' . __('interface.status.verified') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.unverified') . '</span>' !!}<br>
                                <label class="font-weight-bold mb-0">{{ __('interface.data.account_balance') }}:</label> {{ number_format($user->prepaidAccountBalance, 2) }} €
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-person"></i> {{ __('interface.data.profile_details') }}
                    </div>
                    <div class="card-body">
                        @if (empty($profile = $user->profile))
                            <form action="{{ route('admin.suppliers.profile.complete', $user->id) }}" method="post">
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
                            <label class="font-weight-bold mb-0">{{ __('interface.data.reverse_charge') }}:</label> {!! $user->reverse_charge ? '<span class="badge badge-success">' . __('interface.misc.applicable') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.not_applicable') . '</span>' !!}<br>
                            <label class="font-weight-bold mb-0">{{ __('interface.data.verification_status') }}:</label> {!! $profile->verified ? '<span class="badge badge-success">' . __('interface.status.verified') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.unverified') . '</span>' !!}<br>
                            <br>
                            <a class="btn btn-primary" data-toggle="modal" data-target="#editProfile"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit_profile_details') }}</a>
                        @endif
                    </div>
                </div>
                @if (! empty($user->profile))
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
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-text"></i> {{ __('interface.misc.invoices') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addInvoice"><i class="bi bi-plus-circle"></i> {{ __('interface.invoices.create') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <table id="invoices" class="table mt-4 w-100">
                                            <thead>
                                            <tr>
                                                <td>{{ __('interface.misc.invoice_no') }}</td>
                                                <td>{{ __('interface.data.type') }}</td>
                                                <td>{{ __('interface.data.status') }}</td>
                                                <td>{{ __('interface.data.date') }}</td>
                                                <td>{{ __('interface.misc.due_by') }}</td>
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
                                <i class="bi bi-list"></i> {{ __('interface.misc.wire_transfer_transactions') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary float-right" data-toggle="modal" data-target="#addTransaction"><i class="bi bi-plus-circle"></i> {{ __('interface.transaction.create') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <table id="transactions" class="table mt-4 w-100">
                                            <thead>
                                            <tr>
                                                <td>{{ __('interface.data.date') }}</td>
                                                <td>{{ __('interface.misc.invoice_no') }}</td>
                                                <td>{{ __('interface.data.amount') }}</td>
                                                <td>{{ __('interface.data.transaction_method') }}</td>
                                                <td>{{ __('interface.data.transaction_no') }}</td>
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
                    <h5 class="modal-title" id="editLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.suppliers.profile.update', $user->id) }}" method="post">
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
                                    <i class="bi bi-info-circle"></i> {{ __('interface.misc.email_change_notice') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.email') }}</label>

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

    @if (! empty($profile = $user->profile))
        <div class="modal fade" id="editProfile" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editProfileLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit_profile_details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.suppliers.profile.update.details', $user->id) }}" method="post">
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
                    <form action="{{ route('admin.suppliers.profile.email.create', $user->id) }}" method="post">
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
                    <form action="{{ route('admin.suppliers.profile.phone.create', $user->id) }}" method="post">
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
                    <form action="{{ route('admin.suppliers.profile.address.create', $user->id) }}" method="post">
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
                    <form action="{{ route('admin.suppliers.profile.bank.create', $user->id) }}" method="post">
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

    <div class="modal fade" id="addInvoice" tabindex="-1" aria-labelledby="addInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addInvoiceLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.invoices.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.suppliers.add') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input id="user_id" type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="type_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.payment_type') }}</label>

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
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.file') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('interface.actions.choose_file') }}</label>
                                </div>

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="reverse_charge" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.reverse_charge') }}</label>

                            <div class="col-md-8">
                                <input id="reverse_charge" type="checkbox" class="form-control @error('reverse_charge') is-invalid @enderror" name="reverse_charge" value="true">

                                @error('reverse_charge')
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
                    <h5 class="modal-title" id="addTransactionLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.transaction.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.suppliers.transactions.create', $user->id) }}" method="post">
                    @csrf
                    <input id="user_id" type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="contract_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.contract_id') }}</label>

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
                            <label for="invoice_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.invoice_id') }}</label>

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
                    ajax: '/admin/suppliers/{{ $user->id }}/list/emailaddresses?user_id={{ $user->id }}',
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
                            ajax: '/admin/suppliers/{{ $user->id }}/list/phonenumbers?user_id={{ $user->id }}',
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
                            ajax: '/admin/suppliers/{{ $user->id }}/list/addresses?user_id={{ $user->id }}',
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
                            ajax: '/admin/suppliers/{{ $user->id }}/list/bankaccounts?user_id={{ $user->id }}',
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

            $('#invoices').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/suppliers/list/{{ $user->id }}',
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
                ajax: '/admin/suppliers/{{ $user->id }}/list/transactions',
                columns: [
                    { data: 'date' },
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
        });
    </script>
@endsection
