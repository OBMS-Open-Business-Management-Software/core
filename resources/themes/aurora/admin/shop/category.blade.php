@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">{{ __('interface.data.route') }}:</div>
                    </div>
                    <input class="form-control" value="{{ ! empty($category) ? $category->fullRoute : '/shop/' }}" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (! empty($category))
                    @if (! empty($category->category_id))
                        <a href="{{ route('admin.shop.categories.details', $category->category_id) }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.misc.previous_category') }}</a>
                    @else
                        <a href="{{ route('admin.shop.categories') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.misc.previous_category') }}</a>
                    @endif
                @endif
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#addCategory"><i class="bi bi-plus-circle"></i> {{ __('interface.category.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.categories') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.route') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.description') }}</td>
                                <td>{{ __('interface.misc.publicly_visible') }}</td>
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
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right my-4" data-toggle="modal" data-target="#addForm"><i class="bi bi-plus-circle"></i> {{ __('interface.form.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.forms') }}
                    </div>
                    <div class="card-body">
                        <table id="forms" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.route') }}</td>
                                <td>{{ __('interface.data.type') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.description') }}</td>
                                <td>{{ __('interface.misc.approval_required') }}</td>
                                <td>{{ __('interface.misc.publicly_visible') }}</td>
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

    <div class="modal fade" id="addCategory" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCategoryLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.category.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.shop.categories.add') }}" method="post">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ ! empty($category) ? $category->id : '' }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.route') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="routePrefix">{{ ! empty($category) ? $category->fullRoute : '/shop/' }}</span>
                                    </div>
                                    <input id="route" type="text" class="form-control @error('route') is-invalid @enderror" name="route" value="{{ old('route') }}">
                                </div>

                                @error('route')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
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
                        <div class="form-group row align-items-center">
                            <label for="public" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.category_publicly_visible') }}</label>

                            <div class="col-md-8">
                                <input id="public" type="checkbox" class="form-control @error('public') is-invalid @enderror" name="public" value="true">

                                @error('public')
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

    <div class="modal fade" id="addForm" tabindex="-1" aria-labelledby="addFormLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addFormLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.form.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.shop.forms.add') }}" method="post">
                    @csrf
                    <input type="hidden" name="category_id" value="{{ ! empty($category) ? $category->id : '' }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.route') }}</label>

                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="routePrefix">{{ ! empty($category) ? $category->fullRoute : '/shop/' }}</span>
                                    </div>
                                    <input id="route" type="text" class="form-control @error('route') is-invalid @enderror" name="route" value="{{ old('route') }}">
                                </div>

                                @error('route')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.product_type') }}</label>

                            <div class="col-md-8">
                                <select id="type" type="text" class="form-control @error('type') is-invalid @enderror" name="type">
                                    <option value="form"{{ old('type') == 'form' ? ' selected' : '' }}>{{ __('interface.data.form') }}</option>
                                    <option value="package"{{ old('type') == 'package' ? ' selected' : '' }}>{{ __('interface.data.package') }}</option>
                                </select>

                                @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
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
                            <label for="product_type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.product_type') }}</label>

                            <div class="col-md-8">
                                <input id="product_type" type="text" class="form-control @error('product_type') is-invalid @enderror" name="product_type" value="{{ old('product_type') }}">

                                @error('product_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="vat_type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.vat_type') }}</label>

                            <div class="col-md-8">
                                <select id="vat_type" type="text" class="form-control @error('vat_type') is-invalid @enderror" name="vat_type">
                                    <option value="basic"{{ old('vat_type') == 'basic' ? ' selected' : '' }}>{{ __('interface.misc.basic') }}</option>
                                    <option value="reduced"{{ old('vat_type') == 'reduced' ? ' selected' : '' }}>{{ __('interface.misc.reduced') }}</option>
                                </select>

                                @error('vat_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="contract_type_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.contract_type') }}</label>

                            <div class="col-md-8">
                                <select id="contract_type_id" type="text" class="form-control @error('contract_type_id') is-invalid @enderror" name="contract_type_id">
                                    @foreach ($contractTypes as $type)
                                        <option value="{{ $type->id }}"{{ $type->id == old('contract_type_id') ? ' selected' : '' }}>{{ __($type->name) }}</option>
                                    @endforeach
                                </select>

                                @error('contract_type_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="tracker_id" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.usage_tracker') }}</label>

                            <div class="col-md-8">
                                <select id="tracker_id" type="text" class="form-control @error('tracker_id') is-invalid @enderror" name="tracker_id">
                                    <option value="">{{ __('interface.misc.none') }}</option>
                                    @foreach ($trackers as $type)
                                        <option value="{{ $type->id }}"{{ $type->id == old('tracker_id') ? ' selected' : '' }}>{{ __($type->name) }}</option>
                                    @endforeach
                                </select>

                                @error('tracker_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="approval" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.require_manual_approval') }}</label>

                            <div class="col-md-8">
                                <input id="approval" type="checkbox" class="form-control @error('approval') is-invalid @enderror" name="approval" value="true">

                                @error('approval')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="public" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.form_publicly_visible') }}</label>

                            <div class="col-md-8">
                                <input id="public" type="checkbox" class="form-control @error('public') is-invalid @enderror" name="public" value="true">

                                @error('public')
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
                ajax: '/admin/shop/categories/list{{ ! empty($category) ? '/' . $category->id : '' }}',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'route' },
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'public', sWidth: '1%' },
                    { data: 'view', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });

            $('#forms').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/shop/forms/list{{ ! empty($category) ? '/' . $category->id : '' }}',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'route' },
                    { data: 'type' },
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'approval', sWidth: '1%' },
                    { data: 'public', sWidth: '1%' },
                    { data: 'view', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
