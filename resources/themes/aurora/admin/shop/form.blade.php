@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">{{ __('interface.data.route') }}:</div>
                    </div>
                    <input class="form-control" value="{{ $form->fullRoute }}" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (! empty($category = $form->category))
                    <a href="{{ route('admin.shop.categories.details', $category->id) }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_category') }}</a>
                @else
                    <a href="{{ route('admin.shop.categories') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_category') }}</a>
                @endif
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#addField"><i class="bi bi-plus-circle"></i> {{ __('interface.field.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.fields') }}
                    </div>
                    <div class="card-body">
                        <table id="fields" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.type') }}</td>
                                <td>{{ __('interface.data.key') }}</td>
                                <td>{{ __('interface.data.label') }}</td>
                                <td>{{ __('interface.data.default_value') }}</td>
                                <td>{{ __('interface.misc.required') }}</td>
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

    <div class="modal fade" id="addField" tabindex="-1" aria-labelledby="addFieldLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addFieldLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.field.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.shop.fields.add') }}" method="post">
                    @csrf
                    <input type="hidden" name="form_id" value="{{ $form->id }}">
                    <div class="modal-body">
                        @if ($form->type == 'form')
                            <div class="form-group row">
                                <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.type') }}</label>

                                <div class="col-md-8">
                                    <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type">
                                        <option value="input_text"{{ old('key') == 'input_text' ? ' selected' : '' }}>{{ __('interface.data.text') }}</option>
                                        <option value="input_number"{{ old('key') == 'input_number' ? ' selected' : '' }}>{{ __('interface.data.number') }}</option>
                                        <option value="input_range"{{ old('key') == 'input_range' ? ' selected' : '' }}>{{ __('interface.data.range') }}</option>
                                        <option value="input_radio"{{ old('key') == 'input_radio' ? ' selected' : '' }}>{{ __('interface.data.radio_text') }}</option>
                                        <option value="input_radio_image"{{ old('key') == 'input_radio_image' ? ' selected' : '' }}>{{ __('interface.data.radio_image') }}</option>
                                        <option value="input_checkbox"{{ old('key') == 'input_checkbox' ? ' selected' : '' }}>{{ __('interface.data.checkbox') }}</option>
                                        <option value="input_hidden"{{ old('key') == 'input_hidden' ? ' selected' : '' }}>{{ __('interface.data.hidden_text') }}</option>
                                        <option value="select"{{ old('key') == 'select' ? ' selected' : '' }}>{{ __('interface.data.select') }}</option>
                                        <option value="textarea"{{ old('key') == 'textarea' ? ' selected' : '' }}>{{ __('interface.data.textarea') }}</option>
                                    </select>

                                    @error('type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        @elseif ($form->type == 'package')
                            <input type="hidden" name="type" value="input_hidden">
                        @endif
                        <div class="form-group row">
                            <label for="label" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.label') }}</label>

                            <div class="col-md-8">
                                <input id="label" type="text" class="form-control @error('label') is-invalid @enderror" name="label" value="{{ old('label') }}">

                                @error('label')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="key" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.key') }}</label>

                            <div class="col-md-8">
                                <input id="key" type="text" class="form-control @error('key') is-invalid @enderror" name="key" value="{{ old('key') }}">

                                @error('key')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="value" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.default_value') }}</label>

                            <div class="col-md-8">
                                <input id="value" type="text" class="form-control @error('value') is-invalid @enderror" name="value" value="{{ old('value') }}">

                                @error('value')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="value_prefix" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.value_output_prefix') }}</label>

                            <div class="col-md-8">
                                <input id="value_prefix" type="text" class="form-control @error('value_prefix') is-invalid @enderror" name="value_prefix" value="{{ old('value_prefix') }}">

                                @error('value_prefix')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="value_suffix" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.value_output_suffix') }}</label>

                            <div class="col-md-8">
                                <input id="value_suffix" type="text" class="form-control @error('value_suffix') is-invalid @enderror" name="value_suffix" value="{{ old('value_suffix') }}">

                                @error('value_suffix')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="amount" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.fees') }}</label>

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
                        <div class="form-group row" id="min" style="display: none">
                            <label for="min" class="col-md-4 col-form-label text-md-right">{{ __('interface.data_processing.minimum') }}</label>

                            <div class="col-md-8">
                                <input id="min" type="number" class="form-control @error('min') is-invalid @enderror" name="min" min="{{ old('min') }}">

                                @error('min')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="max" style="display: none">
                            <label for="max" class="col-md-4 col-form-label text-md-right">{{ __('interface.data_processing.maximum') }}</label>

                            <div class="col-md-8">
                                <input id="max" type="number" class="form-control @error('max') is-invalid @enderror" name="max" max="{{ old('max') }}">

                                @error('max')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="step" style="display: none">
                            <label for="step" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.step_size') }}</label>

                            <div class="col-md-8">
                                <input id="step" type="number" class="form-control @error('step') is-invalid @enderror" name="step" step="{{ old('step') }}">

                                @error('step')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="required" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.required') }}</label>

                            <div class="col-md-8">
                                <input id="required" type="checkbox" class="form-control @error('required') is-invalid @enderror" name="required" value="true">

                                @error('required')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row" id="options" style="display: none">
                            <label for="options" class="col-md-12 col-form-label text-md-center">{{ __('interface.data.options') }}</label>

                            <div class="col-md-12 mt-3">
                                <table class="table w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.label') }}</td>
                                        <td>{{ __('interface.data.value') }}</td>
                                        <td>{{ __('interface.data.fees') }}</td>
                                        <td width="1%">{{ __('interface.data.default_value') }}</td>
                                        <td width="1%">{{ __('interface.misc.action') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody id="options_tbody">

                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" id="fieldLabel">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="fieldValue">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0.01" class="form-control" id="fieldFees">
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="basic-addon2">€</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="checkbox" class="form-control" id="fieldDefault" value="true">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary" id="fieldAdd"><i class="bi bi-plus-circle"></i></button>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
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
        function initOptionRemovalClickListener() {
            $('.fieldDelete').off();
            $('.fieldDelete').on('click', function () {
                $(this).parent().parent().remove();
            });
        }

        function initTableOptionRemovalClickListener(table) {
            table.find('.fieldDelete').off();
            table.find('.fieldDelete').on('click', function () {
                $(this).parent().parent().remove();
            });
        }

        $(window).on('load', function () {
            initOptionRemovalClickListener();

            $('#fields').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/shop/fields/list/{{ $form->id  }}',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'type' },
                    { data: 'key' },
                    { data: 'label' },
                    { data: 'value' },
                    { data: 'required', sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
                fnDrawCallback: function () {
                    $('.options_table').each(function () {
                        let table = $(this);

                        table.find('.fieldAdd').on('click', function () {
                            let label = table.find('.fieldLabel').first().val();
                            let value = table.find('.fieldValue').first().val();
                            let fees = table.find('.fieldFees').first().val();
                            let isDefault = table.find('.fieldDefault').first().is(':checked');
                            let timestamp = Date.now();

                            table.find('.fieldLabel').first().val('');
                            table.find('.fieldValue').first().val('');
                            table.find('.fieldFees').val('');
                            table.find('.fieldDefault').first().prop('checked', false);

                            table.find('.options_tbody').first().append('<tr><td><input type="text" class="form-control" name="options[' + timestamp + '][label]" value="' + label + '"></td><td><input type="text" class="form-control" name="options[' + timestamp + '][value]" value="' + value + '"></td><td><div class="input-group"><input type="number" step="0.01" min="0.01" class="form-control" name="options[' + timestamp + '][amount]" value="' + fees + '"><div class="input-group-append"><span class="input-group-text" id="basic-addon2">€</span></div></div></td><td><input type="checkbox" class="form-control" name="options[' + timestamp + '][default]" value="true"' + (isDefault ? ' checked' : '') + '></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>');
                        });

                        initTableOptionRemovalClickListener(table);
                    });
                }
            });

            $('#type').on('change', function () {
                if (
                    $(this).val() === 'input_text' ||
                    $(this).val() === 'input_radio' ||
                    $(this).val() === 'input_radio_image' ||
                    $(this).val() === 'input_checkbox' ||
                    $(this).val() === 'input_hidden' ||
                    $(this).val() === 'select' ||
                    $(this).val() === 'input_textarea'
                ) {
                    $('#min, #max, #step').hide();
                }

                if (
                    $(this).val() === 'input_number' ||
                    $(this).val() === 'input_range'
                ) {
                    $('#min, #max, #step').show();
                }

                if (
                    $(this).val() === 'input_text' ||
                    $(this).val() === 'input_number' ||
                    $(this).val() === 'input_range' ||
                    $(this).val() === 'input_checkbox' ||
                    $(this).val() === 'input_hidden' ||
                    $(this).val() === 'textarea'
                ) {
                    $('#options').hide();
                }

                if (
                    $(this).val() === 'input_radio' ||
                    $(this).val() === 'input_radio_image' ||
                    $(this).val() === 'select'
                ) {
                    $('#options').show();
                }
            });

            $('#fieldAdd').on('click', function () {
                let label = $('#fieldLabel').val();
                let value = $('#fieldValue').val();
                let fees = $('#fieldFees').val();
                let isDefault = $('#fieldDefault').is(':checked');
                let timestamp = Date.now();

                $('#fieldLabel').val('');
                $('#fieldValue').val('');
                $('#fieldFees').val('');
                $('#fieldDefault').prop('checked', false);

                $('#options_tbody').append('<tr><td><input type="text" class="form-control" name="options[' + timestamp + '][label]" value="' + label + '"></td><td><input type="text" class="form-control" name="options[' + timestamp + '][value]" value="' + value + '"></td><td><div class="input-group"><input type="number" step="0.01" min="0.01" class="form-control" name="options[' + timestamp + '][amount]" value="' + fees + '"><div class="input-group-append"><span class="input-group-text" id="basic-addon2">€</span></div></div></td><td><input type="checkbox" class="form-control" name="options[' + timestamp + '][default]" value="true"' + (isDefault ? ' checked' : '') + '></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>');

                initOptionRemovalClickListener();
            });
        });
    </script>
@endsection
