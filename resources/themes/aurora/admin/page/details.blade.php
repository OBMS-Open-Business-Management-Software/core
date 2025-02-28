@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.pages') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> {{ __('interface.misc.details') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.title') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ __($page->title) }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.route') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $page->route }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.must_accept') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($page->must_accept)
                                    <span class="badge badge-success">{{ __('interface.misc.yes') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('interface.misc.no') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.navigation_item') }}</label>

                            <div class="col-md-9 col-form-label">
                                @if ($page->navigation_item)
                                    <span class="badge badge-success">{{ __('interface.misc.yes') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('interface.misc.no') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.current_version') }}</label>

                            <div class="col-md-9 col-form-label">
                                #{{ $page->latest->id }}
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-md-3 col-form-label text-md-right font-weight-bold">{{ __('interface.data.acceptance') }}</label>

                            <div class="col-md-9 col-form-label">
                                {{ $page->latest->acceptance()->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.page_version.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.page_versions') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.misc.created_at') }}</td>
                                <td>{{ __('interface.data.preview') }}</td>
                                <td>{{ __('interface.data.acceptance') }}</td>
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
                <form action="{{ route('admin.pages.versions.add', $page->id) }}" method="post">
                    @csrf
                    <input type="hidden" name="page_id" value="{{ $page->id }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="page_content" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.content') }}</label>

                            <div class="col-md-8">
                                <textarea id="page_content" type="text" class="form-control @error('page_content') is-invalid @enderror" name="page_content">{{ $page->latest->content }}</textarea>

                                @error('page_content')
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
                ajax: '/admin/pages/list/{{ $page->id }}',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'created_at' },
                    { data: 'content' },
                    { data: 'acceptance', bSortable: false },
                    { data: 'view', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
