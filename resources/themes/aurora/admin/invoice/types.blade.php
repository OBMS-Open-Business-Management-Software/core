@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.payment_type.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.data.payment_types') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.description') }}</td>
                                <td>{{ __('interface.data.payment_period') }}</td>
                                <td>{{ __('interface.data.type') }}</td>
                                <td>{{ __('interface.data.dunning') }}</td>
                                <td>{{ __('interface.actions.view') }}</td>
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
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.payment_type.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.types.add') }}" method="post">
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
                                    <option value="normal"{{ old('type') == 'normal' ? ' selected' : '' }}>{{ __('interface.misc.basic') }}</option>
                                    <option value="auto_revoke"{{ old('type') == 'auto_revoke' ? ' selected' : '' }}>{{ __('interface.misc.autorevoke_overdue') }}</option>
                                    <option value="prepaid"{{ old('type') == 'prepaid' ? ' selected' : '' }}>{{ __('interface.misc.prepaid_receipt') }}</option>
                                </select>

                                @error('type')
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
                        <div id="hiddenDunning">
                            <div class="form-group row align-items-center">
                                <label for="dunning" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.enable_dunning') }}</label>

                                <div class="col-md-8">
                                    <input id="dunning" type="checkbox" class="form-control @error('dunning') is-invalid @enderror" name="dunning" value="true">

                                    @error('dunning')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="discount_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.discount') }}</label>

                            <div class="col-md-8">
                                <select id="discount_id" type="text" class="form-control @error('discount_id') is-invalid @enderror" name="discount_id">
                                    <option value=""{{ empty(old('discount_id')) ? ' selected' : '' }}>{{ __('interface.misc.none') }}</option>
                                    @foreach ($discounts as $discount)
                                        <option value="{{ $discount->id }}"{{ old('discount_id') == $discount->id ? ' selected' : '' }}>{{ __($discount->name) }}</option>
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
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#categories').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/types/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'period', sWidth: '10%' },
                    { data: 'type', sWidth: '10%' },
                    { data: 'dunning', sWidth: '10%' },
                    { data: 'view', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
                fnDrawCallback: function () {
                    $('#categories').find('.type').each(function () {
                        $(this).on('change', function () {
                            if ($(this).val() === 'normal') {
                                $('#hiddenDunning' + $(this).attr('data-id')).show();
                            } else {
                                $('#hiddenDunning' + $(this).attr('data-id')).hide();
                                $('#dunning' + $(this).attr('data-id')).prop('checked', false);
                            }
                        });
                    });
                }
            });

            $('#type').on('change', function () {
                if ($(this).val() === 'normal') {
                    $('#hiddenDunning').show();
                } else {
                    $('#hiddenDunning').hide();
                    $('#dunning').prop('checked', false);
                }
            })
        });
    </script>
@endsection
