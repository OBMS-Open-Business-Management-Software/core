@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.invoices.customers') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                <a href="{{ route('admin.customers.profile', $invoice->user_id) }}" class="btn btn-primary mb-4" target="_blank"><i class="bi bi-person"></i> {{ __('interface.user_type.customer') }}</a>
                @if (! empty($invoice->original) && $invoice->status == 'refund')
                    <a href="{{ route('admin.invoices.customers.details', $invoice->original->id) }}" class="btn btn-primary mb-4"><i class="bi bi-file-earmark-text"></i> {{ __('interface.actions.open_original') }}</a>
                @endif
                @if (! empty($invoice->refunded) && ($invoice->status == 'refunded' || $invoice->status == 'revoked'))
                    <a href="{{ route('admin.invoices.customers.details', $invoice->refunded->id) }}" class="btn btn-primary mb-4"><i class="bi bi-file-earmark-text"></i> {{ __('interface.actions.open_refund') }}</a>
                @endif
                @if ($invoice->status == 'template')
                    <a class="btn btn-warning mb-4 float-right" data-toggle="modal" data-target="#editInvoice"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</a>
                    @if ($invoice->positionLinks->isNotEmpty())
                        <a href="{{ route('admin.invoices.customers.publish', $invoice->id) }}" class="btn btn-primary mb-4 mr-1 float-right"><i class="bi bi-check-circle"></i> {{ __('interface.actions.publish') }}</a>
                    @endif
                @else
                    @if ($invoice->status == 'paid')
                        <a href="{{ route('admin.invoices.customers.unpaid', $invoice->id) }}" class="btn btn-warning mb-4 mr-1 float-right"><i class="bi bi-x-circle"></i> {{ __('interface.status.unpaid') }}</a>
                    @endif
                    @if ($invoice->status == 'unpaid')
                        <a href="{{ route('admin.invoices.customers.paid', $invoice->id) }}" class="btn btn-success mb-4 mr-1 float-right"><i class="bi bi-check-circle"></i> {{ __('interface.status.paid') }}</a>
                        <a href="{{ route('admin.invoices.customers.revoke', $invoice->id) }}" class="btn btn-warning mb-4 mr-1 float-right"><i class="bi bi-dash-circle"></i> {{ __('interface.actions.revoke') }}</a>
                    @endif
                    @if ($invoice->status == 'paid')
                        <a href="{{ route('admin.invoices.customers.refund', $invoice->id) }}" class="btn btn-warning mb-4 mr-1 float-right"><i class="bi bi-dash-circle"></i> {{ __('interface.actions.refund') }}</a>
                    @endif
                    <a href="{{ route('admin.invoices.customers.download', $invoice->id) }}" class="btn btn-primary mb-4 mr-1 float-right" download><i class="bi bi-download"></i> {{ __('interface.actions.download') }}</a>
                    <a href="{{ route('admin.invoices.customers.resend', $invoice->id) }}" class="btn btn-primary mb-4 mr-1 float-right"><i class="bi bi-envelope"></i> {{ __('interface.actions.resend') }}</a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.misc.details') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.number') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->number ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->type->name ?? __('interface.misc.not_available') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.status') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch ($invoice->status)
                                    @case ('unpaid')
                                        @if ($invoice->overdue)
                                            <span class="badge badge-danger">{{ __('interface.status.overdue') }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ __('interface.status.unpaid') }}</span>
                                        @endif
                                        @break
                                    @case ('paid')
                                        <span class="badge badge-success">{{ __('interface.status.paid') }}</span>
                                        @break
                                    @case ('refunded')
                                        <span class="badge badge-secondary">{{ __('interface.status.refunded') }}</span>
                                        @break
                                    @case ('refund')
                                        <span class="badge badge-info text-white">{{ __('interface.actions.refund') }}</span>
                                        @break
                                    @case ('revoked')
                                        <span class="badge badge-secondary">{{ __('interface.status.revoked') }}</span>
                                        @break
                                    @case ('template')
                                    @default
                                        <span class="badge badge-primary">{{ __('interface.status.draft') }}</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.reverse_charge') }}</label>

                            <div class="col-md-9 col-form-label">
                                {!! $invoice->reverse_charge ? '<span class="badge badge-success">' . __('interface.misc.applicable') . '</span>' : '<span class="badge badge-warning">' . __('interface.misc.not_applicable') . '</span>' !!}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.misc.created_at') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->created_at->format('d.m.Y, H:i') }}
                            </div>
                        </div>
                        @if (isset($invoice->archived_at))
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.invoice_date') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $invoice->archived_at->format('d.m.Y, H:i') }}
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ $invoice->status == 'refund' ? __('interface.documents.refunded_until') : __('interface.documents.payable_until') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $invoice->archived_at->addDays($invoice->type->period)->format('d.m.Y') }}, 23:59
                                </div>
                            </div>
                        @endif
                        @if (! empty($discount = $invoice->type->discount))
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.discount') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $discount->percentage_amount }} % {{ __('interface.misc.when_paid_within') }} {{ $discount->period }} {{ __('interface.units.days') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person"></i> {{ __('interface.user_type.customer') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.user') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $invoice->user->realName ?? __('interface.misc.not_available') }}
                                @if (empty($profile = $invoice->user->profile))
                                    <span class="badge badge-warning">{{ __('interface.status.incomplete') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.account_status') }}</label>

                            <div class="col-md-9 col-form-label">
                                {!! $invoice->user->locked ? '<span class="badge badge-warning">' . __('interface.status.locked') . '</span>' : '<span class="badge badge-success">' . __('interface.status.unlocked') . '</span>' !!}
                            </div>
                        </div>

                        @if (! empty($invoice->user->profile))
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.account_type') }}</label>

                                <div class="col-md-9 col-form-label">
                                    @if (! empty($profile->company))
                                        {{ __('interface.data.company') }}
                                    @else
                                        {{ __('interface.data.personal') }}
                                    @endif
                                </div>
                            </div>
                            @if (! empty($profile->company))
                                <div class="row">
                                    <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.company') }}</label>

                                    <div class="col-md-9 col-form-label">
                                        {{ $profile->company }}
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.contact_person') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ $profile->firstname }} {{ $profile->lastname }}
                                </div>
                            </div>
                            @if (! empty($address = $profile->billingPostalAddress))
                                <div class="row">
                                    <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.postal_address') }}</label>

                                    <div class="col-md-9 col-form-label">
                                        {{ $address->street }} {{ $address->housenumber }}<br>
                                        @if (! empty($address->addition))
                                            {{ $address->addition }}<br>
                                        @endif
                                        {{ $address->postalcode }} {{ $address->city }}<br>
                                        {{ $address->state }}, {{ $address->country->name }}<br>
                                    </div>
                                </div>
                            @endif
                            @if (! empty($profile->billingEmailAddress))
                                <div class="row">
                                    <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.email_address') }}</label>

                                    <div class="col-md-9 col-form-label">
                                        {{ $profile->billingEmailAddress->email }} <a href="mailto:{{ $profile->billingEmailAddress->email }}"><i class="bi bi-envelope"></i></a>
                                    </div>
                                </div>
                            @endif
                            @if (! empty($profile->billingPhoneNumber))
                                <div class="row">
                                    <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.phone_number') }}</label>

                                    <div class="col-md-9 col-form-label">
                                        {{ $profile->billingPhoneNumber->phone }} <a href="tel:{{ $profile->billingPhoneNumber->phone }}"><i class="bi bi-telephone"></i></a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="row">
                                <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.account_type') }}</label>

                                <div class="col-md-9 col-form-label">
                                    {{ __('interface.misc.prepaid') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if ($invoice->status == 'template')
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary float-right mt-4" data-toggle="modal" data-target="#addPosition"><i class="bi bi-plus-circle"></i> {{ __('interface.documents.create_position') }}</a>
                </div>
            </div>
        @endif
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.data.positions') }}
                    </div>
                    <div class="card-body">
                        @if ($invoice->positionLinks->isNotEmpty())
                            <table class="table mt-4 w-100">
                                <thead>
                                <tr>
                                    <td>{{ __('interface.documents.position') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.net_unit') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.units') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.net_position') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.vat') }}</td>
                                    <td style="width: 10%">{{ __('interface.documents.gross_position') }}</td>
                                    @if ($invoice->status == 'template')
                                        <td style="width: 1%">{{ __('interface.actions.edit') }}</td>
                                        <td style="width: 1%">{{ __('interface.actions.delete') }}</td>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->positionLinks as $link)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $link->position->name }}</span><br>
                                                {{ $link->position->description }}
                                                <small class="d-block">
                                                    @if (isset($link->started_at))
                                                        {{ __('interface.time.from') }}: {{ $link->started_at->format('d.m.Y H:i') }}
                                                    @endif
                                                    @if (isset($link->started_at, $link->ended_at))
                                                        |
                                                    @endif
                                                    @if (isset($link->ended_at))
                                                        {{ __('interface.time.to') }}: {{ $link->ended_at->format('d.m.Y H:i') }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td style="width: 10%">{{ number_format($link->position->amount, 2) }} €</td>
                                            <td style="width: 10%">{{ $link->position->quantity }}</td>
                                            <td style="width: 10%">{{ number_format($link->position->netSum, 2) }} €</td>
                                            <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                            <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? number_format($link->position->netSum, 2) : number_format($link->position->grossSum, 2) }} €</td>
                                            @if ($invoice->status == 'template')
                                                <td style="width: 1%">
                                                    <a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPosition{{ $link->id }}"><i class="bi bi-pencil-square"></i></a>
                                                </td>
                                                <td style="width: 1%">
                                                    <a href="{{ route('admin.invoices.customers.positions.delete', ['id' => $invoice->id, 'position_id' => $link->id]) }}" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></a>
                                                </td>
                                            @endif
                                        </tr>
                                        @if (! empty($discount = $link->position->discount))
                                            @switch ($discount->type)
                                                @case ('percentage')
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                                            {{ number_format($discount->amount, 2) }} %
                                                        </td>
                                                        <td style="width: 10%">- {{ number_format($link->position->amount * ($discount->amount / 100), 2) }} €</td>
                                                        <td style="width: 10%">{{ $link->position->quantity }}</td>
                                                        <td style="width: 10%">- {{ number_format($link->position->netSum * ($discount->amount / 100), 2) }} €</td>
                                                        <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $invoice->reverse_charge ? number_format($link->position->netSum * ($discount->amount / 100), 2) : number_format($link->position->grossSum * ($discount->amount / 100), 2) }} €</td>
                                                        @if ($invoice->status == 'template')
                                                            <td style="width: 1%"></td>
                                                            <td style="width: 1%"></td>
                                                        @endif
                                                    </tr>
                                                    @break
                                                @case ('fixed')
                                                @default
                                                    <tr>
                                                        <td>
                                                            <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                                            {{ number_format($discount->amount, 2) }} €
                                                        </td>
                                                        <td style="width: 10%">- {{ number_format($discount->amount, 2) }} €</td>
                                                        <td style="width: 10%">{{ $link->position->quantity }}</td>
                                                        <td style="width: 10%">- {{ number_format($discount->amount * $link->position->quantity, 2) }} €</td>
                                                        <td style="width: 10%" class="bg-disabled">{{ $invoice->reverse_charge ? '0' : $link->position->vat_percentage }} %</td>
                                                        <td style="width: 10%" class="bg-disabled">- {{ $invoice->reverse_charge ? number_format($discount->amount * $link->position->quantity, 2) : number_format($discount->amount * $link->position->quantity * (1 + ($link->position->vat_percentage / 100)), 2) }} €</td>
                                                        @if ($invoice->status == 'template')
                                                            <td style="width: 1%"></td>
                                                            <td style="width: 1%"></td>
                                                        @endif
                                                    </tr>
                                                    @break
                                            @endswitch
                                        @endif
                                        <div class="modal fade" id="editPosition{{ $link->id }}" tabindex="-1" aria-labelledby="editFile{{ $link->id }}Label" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning">
                                                        <h5 class="modal-title" id="editFile{{ $link->id }}Label">{{ __('interface.actions.edit') }} ({{ $link->position->name }})</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('admin.invoices.customers.positions.update', ['id' => $invoice->id, 'position_id' => $link->id]) }}" method="post">
                                                        <div class="modal-body">
                                                            @csrf
                                                            <input type="hidden" name="position_id" value="{{ $link->id }}" />
                                                            <div class="form-group row">
                                                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                                                                <div class="col-md-8">
                                                                    <input id="name" type="text" class="form-control " name="name" value="{{ $link->position->name }}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                                                                <div class="col-md-8">
                                                                    <textarea id="description" type="text" class="form-control " name="description">{{ $link->position->description }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="amount" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.amount') }}</label>

                                                                <div class="col-md-8">
                                                                    <div class="input-group">
                                                                        <input id="amount" type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ $link->position->amount }}">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" id="basic-addon2">€</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="vat_percentage" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.vat_percentage') }}</label>

                                                                <div class="col-md-8">
                                                                    <div class="input-group">
                                                                        <input id="vat_percentage" type="number" step="0.01" min="0.01" class="form-control" name="vat_percentage" value="{{ $link->position->vat_percentage }}">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text" id="basic-addon2">%</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <label for="quantity" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.quantity') }}</label>

                                                                <div class="col-md-8">
                                                                    <input id="quantity" type="number" step="0.01" min="0.01" class="form-control" name="quantity" value="{{ $link->position->quantity }}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group row align-items-center">
                                                                <label for="service_runtime_dynamic" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.position_has_runtime') }}</label>

                                                                <div class="col-md-8">
                                                                    <input id="service_runtime_dynamic{{ $link->id }}" data-id="{{ $link->id }}" type="checkbox" class="form-control service_runtime_dynamic" name="service_runtime" value="true"{{ isset($link->started_at, $link->ended_at) ? ' checked' : '' }}>
                                                                </div>
                                                            </div>
                                                            <div id="serviceRuntimeConfig{{ $link->id }}"{!!  isset($link->started_at, $link->ended_at) ? '' : ' style="display: none"' !!}>
                                                                <div class="form-group row align-items-center">
                                                                    <label for="started_at" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.started_at') }}</label>

                                                                    <div class="col-md-8">
                                                                        <input id="started_at" type="datetime-local" class="form-control" name="started_at" value="{{ isset($link->ended_at) ? $link->started_at->format('Y-m-d\TH:i:s') : '' }}">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row align-items-center">
                                                                    <label for="ended_at" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.ended_at') }}</label>

                                                                    <div class="col-md-8">
                                                                        <input id="ended_at" type="datetime-local" class="form-control" name="ended_at" value="{{ isset($link->ended_at) ? $link->ended_at->format('Y-m-d\TH:i:s') : '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row align-items-center">
                                                                <label for="discount_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.discount') }}</label>

                                                                <div class="col-md-8">
                                                                    <select id="discount_id" class="form-control @error('discount_id') is-invalid @enderror" name="discount_id">
                                                                        <option value="">{{ __('interface.misc.none') }}</option>
                                                                        @foreach ($discounts as $discount)
                                                                            <option value="{{ $discount->id }}"{{ $link->position->discount_id == $discount->id ? ' selected' : '' }}>{{ $discount->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td colspan="2" class="bg-primary text-white">
                                            {{ __('interface.documents.net_sum') }}
                                        </td>
                                        <td class="bg-primary text-white">{{ number_format($invoice->netSum, 2) }} €</td>
                                    </tr>
                                    @foreach ($invoice->vatPositions as $percentage => $amount)
                                        <tr>
                                            <td class="border-0"></td>
                                            <td colspan="2" class="bg-primary text-white border-0">
                                                {{ $percentage }} % {{ __('interface.documents.vat') }}
                                            </td>
                                            <td class="bg-primary text-white border-0">{{ number_format($amount, 2) }} €</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td class="border-0"></td>
                                        <td colspan="2" class="bg-primary text-white border-0">
                                            {{ __('interface.documents.gross_sum') }}
                                        </td>
                                        <td class="bg-primary text-white border-0">{{ number_format($invoice->grossSum, 2) }} €</td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.invoices.no_positions') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ $invoice->type->dunning && $invoice->status !== 'refund' ? 'col-md-6' : 'col-md-12' }}">
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-exclamation-circle"></i> {{ __('interface.misc.status_history') }}
                    </div>
                    <div class="card-body">
                        @if ($invoice->history()->exists())
                            <table class="table" id="history">
                                <thead>
                                <tr>
                                    <td>#</td>
                                    <td>{{ __('interface.data.date') }}</td>
                                    <td>{{ __('interface.data.name') }}</td>
                                    <td>{{ __('interface.data.status') }}</td>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.no_status_history') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if ($invoice->type->dunning && $invoice->status !== 'refund')
                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-header">
                            <i class="bi bi-exclamation-triangle"></i> {{ __('interface.data.dunning') }}
                        </div>
                        <div class="card-body">
                            @if ($invoice->type->dunnings->isNotEmpty())
                                <table class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.overdue_time') }}</td>
                                        <td style="width: 10%">{{ __('interface.data.date') }}</td>
                                        <td style="width: 10%">{{ __('interface.status.sent') }}</td>
                                        <td style="width: 1%">{{ __('interface.actions.download') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($invoice->type->dunnings->sortBy('after') as $dunning)
                                        <tr>
                                            <td> {{ $dunning->after }} {{ __('interface.units.days') }}</td>
                                            <td style="width: 20%">{{ ! empty($invoice->archived_at) ? $invoice->archived_at->addDays($invoice->type->period)->addDays($dunning->after)->format('d.m.Y') : $invoice->created_at->addDays($invoice->type->period)->addDays($dunning->after)->format('d.m.Y') }}, 00:00</td>
                                            <td style="width: 10%">{!! $invoice->reminders->contains('dunning_id', $dunning->id) ? '<span class="badge badge-success">' . __('interface.status.generated') . '</span>' : '<span class="badge badge-secondary">' . __('interface.status.ungenerated') . '</span>' !!}</td>
                                            <td style="width: 10%">{!! $invoice->reminders->contains('dunning_id', $dunning->id) && $invoice->status !== 'template' ? '<a href="' . route('admin.invoices.customers.reminders.download', ['id' => $invoice->id, 'reminder_id' => $invoice->reminders->where('dunning_id', '=', $dunning->id)->first()->id]) . '" class="btn btn-primary btn-sm"><i class="bi bi-download"></i></a>' : '<button type="button" class="btn btn-primary btn-sm" disabled><i class="bi bi-download"></i></button>' !!}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.no_dunning_reminder_notice') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($invoice->status == 'template')
        <div class="modal fade" id="editInvoice" tabindex="-1" aria-labelledby="editInvoiceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="editInvoiceLabel">{{ __('interface.actions.edit') }} ({{ $invoice->number }})</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.invoices.customers.update', $invoice->id) }}" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}" />
                            <div class="form-group row">
                                <label for="user_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.user_id') }}</label>

                                <div class="col-md-8">
                                    <input id="user_id" type="number" class="form-control " name="user_id" value="{{ $invoice->user_id }}">

                                    @error('user_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="type_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.payment_type') }}</label>

                                <div class="col-md-8">
                                    <select id="type_id" class="form-control " name="type_id">
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
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
                                <label for="contract_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.contract_id') }}</label>

                                <div class="col-md-8">
                                    <input id="contract_id" type="number" class="form-control " name="contract_id" value="{{ $invoice->contract_id }}">

                                    @error('contract_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addPosition" tabindex="-1" aria-labelledby="addPositionLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addPositionLabel">{{ __('interface.position.create') }} ({{ $invoice->number }})</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.invoices.customers.positions.add', $invoice->id) }}" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}" />

                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control " name="name" value="{{ old('name') }}">

                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                                <div class="col-md-8">
                                    <textarea id="description" type="text" class="form-control " name="description">{{ old('description') }}</textarea>

                                    @error('description')
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
                                        <input id="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}">
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
                            <div class="form-group row">
                                <label for="vat_percentage" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.vat_percentage') }}</label>

                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input id="vat_percentage" type="number" step="0.01" min="0.01" class="form-control @error('vat_percentage') is-invalid @enderror" name="vat_percentage" value="{{ old('vat_percentage') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="basic-addon2">%</span>
                                        </div>
                                    </div>

                                    @error('vat_percentage')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="quantity" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.quantity') }}</label>

                                <div class="col-md-8">
                                    <input id="quantity" type="number" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity') ?? 1 }}">

                                    @error('quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label for="service_runtime" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.position_has_runtime') }}</label>

                                <div class="col-md-8">
                                    <input id="service_runtime" type="checkbox" class="form-control @error('service_runtime') is-invalid @enderror" name="service_runtime" value="true">

                                    @error('service_runtime')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div id="serviceRuntimeConfig" style="display: none">
                                <div class="form-group row align-items-center">
                                    <label for="started_at" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.started_at') }}</label>

                                    <div class="col-md-8">
                                        <input id="started_at" type="datetime-local" class="form-control @error('started_at') is-invalid @enderror" name="started_at">

                                        @error('started_at')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label for="ended_at" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.ended_at') }}</label>

                                    <div class="col-md-8">
                                        <input id="ended_at" type="datetime-local" class="form-control @error('ended_at') is-invalid @enderror" name="ended_at">

                                        @error('ended_at')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label for="discount_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.discount') }}</label>

                                <div class="col-md-8">
                                    <select id="discount_id" class="form-control @error('discount_id') is-invalid @enderror" name="discount_id">
                                        <option value="">{{ __('interface.misc.none') }}</option>
                                        @foreach ($discounts as $discount)
                                            <option value="{{ $discount->id }}">{{ $discount->name }}</option>
                                        @endforeach
                                    </select>

                                    @error('discount_id')
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
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#service_runtime').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#serviceRuntimeConfig').show();
                } else {
                    $('#serviceRuntimeConfig').hide();
                }
            });

            $('.service_runtime_dynamic').each(function () {
                $(this).on('change', function () {
                    if ($(this).is(':checked')) {
                        $('#serviceRuntimeConfig' + $(this).attr('data-id')).show();
                    } else {
                        $('#serviceRuntimeConfig' + $(this).attr('data-id')).hide();
                    }
                });
            });

            $('#history').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/customers/{{ $invoice->id }}/history',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'date' },
                    { data: 'name' },
                    { data: 'status', sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
