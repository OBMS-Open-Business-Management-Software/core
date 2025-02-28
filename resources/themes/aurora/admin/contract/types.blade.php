@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.contract_type.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.data.contract_types') }}
                    </div>
                    <div class="card-body">
                        <table id="types" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.description') }}</td>
                                <td>{{ __('interface.data.invoice_type') }}</td>
                                <td>{{ __('interface.data.invoice_period') }}</td>
                                <td>{{ __('interface.data.cancellation_period') }}</td>
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

    <div class="modal fade" id="add" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.contract_type.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.contracts.types.add') }}" method="post">
                    @csrf
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
                            <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                            <div class="col-md-8">
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}">

                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.type') }}</label>

                            <div class="col-md-8">
                                <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type">
                                    <option value="contract_pre_pay"{{ old('type') == 'contract_pre_pay' ? ' selected' : '' }}>{{ __('interface.billing.contract_pre') }}</option>
                                    <option value="contract_post_pay"{{ old('type') == 'contract_post_pay' ? ' selected' : '' }}>{{ __('interface.billing.contract_post') }}</option>
                                    <option value="prepaid_auto"{{ old('type') == 'prepaid_auto' ? ' selected' : '' }}>{{ __('interface.billing.prepaid_auto') }}</option>
                                    <option value="prepaid_manual"{{ old('type') == 'prepaid_manual' ? ' selected' : '' }}>{{ __('interface.billing.prepaid_manual') }}</option>
                                </select>

                                @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="invoice_type_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.invoice_type') }}</label>

                            <div class="col-md-8">
                                <select id="invoice_type_id" type="text" class="form-control @error('invoice_type_id') is-invalid @enderror" name="invoice_type_id">
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}"{{ old('invoice_type_id') == $type->id ? ' selected' : '' }}>{{ __($type->name) }}</option>
                                    @endforeach
                                </select>

                                @error('invoice_type_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="invoice_period" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.invoice_period') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="invoice_period" type="number" step="0.01" min="0.01" class="form-control @error('invoice_period') is-invalid @enderror" name="invoice_period" value="{{ old('invoice_period') ?? 30 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">{{ __('interface.units.days') }}</span>
                                    </div>
                                </div>

                                @error('invoice_period')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cancellation_period" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.cancellation_period') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="cancellation_period" type="number" step="0.01" min="0.01" class="form-control @error('cancellation_period') is-invalid @enderror" name="cancellation_period" value="{{ old('cancellation_period') ?? 14 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="basic-addon2">{{ __('interface.units.days') }}</span>
                                    </div>
                                </div>

                                @error('cancellation_period')
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
            $('#types').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/contracts/types/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'invoice_type', sWidth: '20%' },
                    { data: 'invoice_period', sWidth: '10%' },
                    { data: 'cancellation_period', sWidth: '10%' },
                    { data: 'type', sWidth: '20%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
