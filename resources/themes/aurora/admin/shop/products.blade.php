@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-box"></i> {{ __('interface.misc.product_types') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.product') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.status') }}</td>
                                <td>{{ __('interface.actions.edit') }}</td>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->technicalName() }}</td>
                                        <td>{{ $product->name() }}</td>
                                        <td>{!! $product->status() ? '<span class="badge badge-success">' . __('interface.status.enabled') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.disabled') . '</span>' !!}</td>
                                        <td><a class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit{{ $product->technicalName() }}"><i class="bi bi-pencil-square"></i></a></td>
                                    </tr>
                                    <div class="modal fade" id="edit{{ $product->technicalName() }}" tabindex="-1" aria-labelledby="edit{{ $product->technicalName() }}Label" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning">
                                                    <h5 class="modal-title" id="edit{{ $product->technicalName() }}Label">{{ __('interface.actions.edit') }} ({{ $product->name() }})</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                                <form action="{{ route('admin.products.save') }}" method="post">
                                                    <div class="modal-body">
                                                        @csrf
                                                        <input type="hidden" name="product_type" value="{{ $product->technicalName() }}" />
                                                        @foreach ($product->parameters() as $parameter => $name)
                                                            <div class="form-group row">
                                                                <label for="{{ $parameter }}" class="col-md-4 col-form-label text-md-right">{{ $name }}</label>

                                                                <div class="col-md-8">
                                                                    <input id="{{ $parameter }}" type="text" class="form-control" name="{{ $parameter }}" value="{{ $product->settings()->where('setting', '=', $parameter)->first()->value ?? '' }}">
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="modal-footer bg-white">
                                                        <button type="submit" class="btn btn-warning"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
