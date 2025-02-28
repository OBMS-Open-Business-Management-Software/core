@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.page.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.pages') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.title') }}</td>
                                <td>{{ __('interface.data.route') }}</td>
                                <td>{{ __('interface.data.must_accept') }}</td>
                                <td>{{ __('interface.data.navigation_item') }}</td>
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
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.page.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.pages.add') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="route" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.route') }}</label>

                            <div class="col-md-8">
                                <input id="route" type="text" class="form-control @error('route') is-invalid @enderror" name="route" value="{{ old('route') }}">

                                @error('route')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.title') }}</label>

                            <div class="col-md-8">
                                <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}">

                                @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="page_content" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.content') }}</label>

                            <div class="col-md-8">
                                <textarea id="page_content" type="text" class="form-control @error('page_content') is-invalid @enderror" name="page_content">{{ old('page_content') }}</textarea>

                                @error('page_content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="must_accept" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.must_be_accepted') }}</label>

                            <div class="col-md-8">
                                <input id="must_accept" type="checkbox" class="form-control @error('must_accept') is-invalid @enderror" name="must_accept" value="true">

                                @error('must_accept')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="navigation_item" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.display_in_navigation') }}</label>

                            <div class="col-md-8">
                                <input id="navigation_item" type="checkbox" class="form-control @error('navigation_item') is-invalid @enderror" name="navigation_item" value="true">

                                @error('navigation_item')
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
                ajax: '/admin/pages/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'title' },
                    { data: 'route' },
                    { data: 'must_accept', sWidth: '10%' },
                    { data: 'navigation_item', sWidth: '10%' },
                    { data: 'view', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
