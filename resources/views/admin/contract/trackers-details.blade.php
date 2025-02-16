@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.contracts.trackers') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to list') }}</a>
                <a class="btn btn-warning mb-4 float-right" data-toggle="modal" data-target="#editTracker"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('Usage Tracker') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.name') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $tracker->name }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.description') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $tracker->description }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('VAT Type') }}</label>

                            <div class="col-md-9 col-form-label">
                                @switch ($tracker->vat_type)
                                    @case ('reduced')
                                        {{ __('Reduced') }}
                                        @break
                                    @case ('basic')
                                    @default
                                        {{ __('Basic') }}
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right my-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('Create Item') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('Items') }}
                    </div>
                    <div class="card-body">
                        <table id="items" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.type') }}</td>
                                <td>{{ __('Process Type') }}</td>
                                <td>{{ __('Number Rounding') }}</td>
                                <td>{{ __('Step') }}</td>
                                <td>{{ __('Amount Per Step') }}</td>
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

    <div class="modal fade" id="editTracker" tabindex="-1" aria-labelledby="editTrackerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editTrackerLabel">{{ __('interface.actions.edit') }} ({{ $tracker->name }})</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.contracts.trackers.update', $tracker->id) }}" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="tracker_id" value="{{ $tracker->id }}" />
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control" name="name" value="{{ $tracker->name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                            <div class="col-md-8">
                                <input id="description" type="text" class="form-control" name="description" value="{{ $tracker->description }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="vat_type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.type') }}</label>

                            <div class="col-md-8">
                                <select id="vat_type" type="text" class="form-control type" name="vat_type">
                                    <option value="basic"{{ $tracker->vat_type == 'basic' ? ' selected' : '' }}>{{ __('Basic') }}</option>
                                    <option value="reduced"{{ $tracker->vat_type == 'reduced' ? ' selected' : '' }}>{{ __('Reduced') }}</option>
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

    <div class="modal fade" id="add" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Item') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.contracts.trackers.items.add', $tracker->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="tracker_id" value="{{ $tracker->id }}" />
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.type') }}</label>

                            <div class="col-md-8">
                                <select id="type" type="text" class="form-control type" name="type">
                                    <option value="string"{{ old('type') == 'string' ? ' selected' : '' }}>{{ __('String') }}</option>
                                    <option value="integer"{{ old('type') == 'integer' ? ' selected' : '' }}>{{ __('Integer') }}</option>
                                    <option value="double"{{ old('type') == 'double' ? ' selected' : '' }}>{{ __('Double') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="process" class="col-md-4 col-form-label text-md-right">{{ __('Process Type') }}</label>

                            <div class="col-md-8">
                                <select id="process" type="text" class="form-control" name="process">
                                    <option value="min"{{ old('process') == 'min' ? ' selected' : '' }}>{{ __('Minimum') }}</option>
                                    <option value="median"{{ old('process') == 'median' ? ' selected' : '' }}>{{ __('Median') }}</option>
                                    <option value="average"{{ old('process') == 'average' ? ' selected' : '' }}>{{ __('Average') }}</option>
                                    <option value="max"{{ old('process') == 'max' ? ' selected' : '' }}>{{ __('Maximum') }}</option>
                                    <option value="equals"{{ old('process') == 'equals' ? ' selected' : '' }}>{{ __('Equals') }}</option>
                                </select>
                            </div>
                        </div>
                        <div id="hiddenNumber"{!! old('type') == 'string' || empty(old('type')) ? ' style="display: none"' : '' !!}>
                            <div class="form-group row">
                                <label for="round" class="col-md-4 col-form-label text-md-right">{{ __('Round Number') }}</label>

                                <div class="col-md-8">
                                    <select id="round" type="text" class="form-control" name="round">
                                        <option value="up"{{ old('round') == 'up' ? ' selected' : '' }}>{{ __('Up') }}</option>
                                        <option value="down"{{ old('round') == 'down' ? ' selected' : '' }}>{{ __('Down') }}</option>
                                        <option value="regular"{{ old('round') == 'regular' ? ' selected' : '' }}>{{ __('Regular') }}</option>
                                        <option value="none"{{ old('round') == 'none' ? ' selected' : '' }}>{{ __('None') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="step" class="col-md-4 col-form-label text-md-right">{{ __('Step') }}</label>

                            <div class="col-md-8">
                                <input id="step" type="text" class="form-control" name="step" value="{{ old('step') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="amount" class="col-md-4 col-form-label text-md-right">{{ __('Amount Per Step') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <input id="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="typeSuffix">€</span>
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
            $('#type').on('change', function () {
                if ($(this).val() === 'integer' || $(this).val() === 'double') {
                    $('#hiddenNumber').show();

                    $('#process option[value="min"]').prop('disabled', false);
                    $('#process option[value="median"]').prop('disabled', false);
                    $('#process option[value="average"]').prop('disabled', false);
                    $('#process option[value="max"]').prop('disabled', false);
                } else {
                    $('#hiddenNumber').hide();
                    $('#dunning').prop('checked', false);
                    $('#process').val('equals');

                    $('#process option[value="min"]').prop('disabled', true);
                    $('#process option[value="median"]').prop('disabled', true);
                    $('#process option[value="average"]').prop('disabled', true);
                    $('#process option[value="max"]').prop('disabled', true);
                }
            });

            $('#items').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/contracts/trackers/{{ $tracker->id }}/items/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'type' },
                    { data: 'process' },
                    { data: 'round' },
                    { data: 'step' },
                    { data: 'amount' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
