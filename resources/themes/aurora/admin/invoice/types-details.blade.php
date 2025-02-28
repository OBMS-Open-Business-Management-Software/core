@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.invoices.types') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                <a class="btn btn-warning mb-4 float-right" data-toggle="modal" data-target="#editPaymentType"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.data.payment_type') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.name') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $type->name }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.description') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $type->description }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.payment_period') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $type->period }} {{ __('interface.units.days') }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch ($type->type)
                                    @case ('prepaid')
                                        {{ __('interface.misc.prepaid_receipt') }}
                                        @break
                                    @case ('auto_revoke')
                                        {{ __('interface.misc.auto_revoked_invoice') }}
                                        @break
                                    @case ('normal')
                                    @default
                                        {{ __('interface.misc.invoice') }}
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.dunning') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($type->dunning)
                                    <span class="badge badge-success">{{ __('interface.status.enabled') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('interface.status.disabled') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($type->dunning)
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary float-right my-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.reminder.create') }}</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.payment_reminders') }}
                        </div>
                        <div class="card-body">
                            <table id="dunnings" class="table mt-4 w-100">
                                <thead>
                                <tr>
                                    <td>{{ __('interface.data.id') }}</td>
                                    <td>{{ __('interface.data.after') }}</td>
                                    <td>{{ __('interface.data.payment_period') }}</td>
                                    <td>{{ __('interface.data.fees') }}</td>
                                    <td>{{ __('interface.data.interest_charges') }}</td>
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
        @endif
    </div>

    <div class="modal fade" id="editPaymentType" tabindex="-1" aria-labelledby="editPaymentTypeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editPaymentTypeLabel">{{ __('interface.actions.edit') }} ({{ $type->name }})</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.types.update', $type->id) }}" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="type_id" value="{{ $type->id }}" />
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $type->name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                            <div class="col-md-8">
                                <input id="description" type="text" class="form-control" name="description" value="{{ $type->description }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.type') }}</label>

                            <div class="col-md-8">
                                <select id="type" type="text" class="form-control type" name="type">
                                    <option value="normal"{{ $type->type == 'normal' ? ' selected' : '' }}>{{ __('interface.misc.basic') }}</option>
                                    <option value="auto_revoke"{{ $type->type == 'auto_revoke' ? ' selected' : '' }}>{{ __('interface.misc.autorevoke_overdue') }}</option>
                                    <option value="prepaid"{{ $type->type == 'prepaid' ? ' selected' : '' }}>{{ __('interface.misc.prepaid_receipt') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="period" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.payment_period') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="period" type="number" step="0.01" min="0.01" class="form-control trigger-dunning" name="period" value="{{ $type->period }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">{{ __('interface.units.days') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="hiddenDunning"{!!  $type->type !== 'normal' ? ' style="display: none"' : '' !!}>
                            <div class="form-group row align-items-center">
                                <label for="dunning" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.enable_dunning') }}</label>

                                <div class="col-md-8">
                                    <input id="dunning" type="checkbox" class="form-control" name="dunning" value="true"{{ $type->dunning ? ' checked' : '' }}>
                                </div>
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

    <div class="modal fade" id="add" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.payment_type.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.dunning.add', $type->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="type_id" value="{{ $type->id }}" />
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="after" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.after') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="after" type="number" step="0.01" min="0.01" class="form-control @error('after') is-invalid @enderror" name="after" value="{{ old('after') ?? 1 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">{{ __('interface.units.days') }}</span>
                                    </div>
                                </div>

                                @error('after')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="period" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.payment_period') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="period" type="number" step="0.01" min="0.01" class="form-control @error('period') is-invalid @enderror" name="period" value="{{ old('period') ?? 14 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">{{ __('interface.units.days') }}</span>
                                    </div>
                                </div>

                                @error('period')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fixed_amount" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.fees') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="fixed_amount" type="number" step="0.01" min="0.01" class="form-control @error('fixed_amount') is-invalid @enderror" name="fixed_amount" value="{{ old('fixed_amount') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">€</span>
                                    </div>
                                </div>

                                @error('fixed_amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="percentage_amount" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.interest_charges') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="percentage_amount" type="number" step="0.01" min="0.01" class="form-control @error('percentage_amount') is-invalid @enderror" name="percentage_amount" value="{{ old('percentage_amount') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">%</span>
                                    </div>
                                </div>

                                @error('percentage_amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="cancel_contract_regular" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.cancel_regularly') }}</label>

                            <div class="col-md-8">
                                <input id="cancel_contract_regular" type="checkbox" class="form-control @error('cancel_contract_regular') is-invalid @enderror" name="cancel_contract_regular" value="true">

                                @error('cancel_contract_regular')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="cancel_contract_instant" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.cancel_instantly') }}</label>

                            <div class="col-md-8">
                                <input id="cancel_contract_instant" type="checkbox" class="form-control @error('cancel_contract_instant') is-invalid @enderror" name="cancel_contract_instant" value="true">

                                @error('cancel_contract_instant')
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
            $('#type').on('change', function () {
                if ($(this).val() === 'normal') {
                    $('#hiddenDunning').show();
                } else {
                    $('#hiddenDunning').hide();
                    $('#dunning').prop('checked', false);
                }
            });

            @if ($type->dunning)
                $('#dunnings').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/admin/invoices/types/{{ $type->id }}/dunning/list',
                    columns: [
                        { data: 'id', sWidth: '1%' },
                        { data: 'after' },
                        { data: 'period' },
                        { data: 'fees' },
                        { data: 'interest' },
                        { data: 'edit', bSortable: false, sWidth: '1%' },
                        { data: 'delete', bSortable: false, sWidth: '1%' }
                    ],
                    order: [[0, 'desc']],
                });
            @endif
        });
    </script>
@endsection
