@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-pencil-square"></i> {{ __('interface.support.create') }}</a>
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="pills-open-tab" data-toggle="pill" href="#pills-open" role="tab" aria-controls="pills-open" aria-selected="true">{{ __('interface.status.open') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pills-closed-tab" data-toggle="pill" href="#pills-closed" role="tab" aria-controls="pills-closed" aria-selected="false">{{ __('interface.status.closed') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pills-unknown-tab" data-toggle="pill" href="#pills-unknown" role="tab" aria-controls="pills-unknown" aria-selected="false">{{ __('interface.status.locked') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="pills-all-tab" data-toggle="pill" href="#pills-all" role="tab" aria-controls="pills-all" aria-selected="false">{{ __('interface.misc.all') }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-open" role="tabpanel" aria-labelledby="pills-open-tab">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-question-circle"></i> {{ __('interface.status.open') }}
                            </div>
                            <div class="card-body">
                                <table id="open-tickets" class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.id') }}</td>
                                        <td>{{ __('interface.data.subject') }}</td>
                                        <td>{{ __('interface.data.category') }}</td>
                                        <td>{{ __('interface.data.status') }}</td>
                                        <td>{{ __('interface.data.priority') }}</td>
                                        <td>{{ __('interface.actions.view') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-closed" role="tabpanel" aria-labelledby="pills-closed-tab">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-check-circle"></i> {{ __('interface.status.closed') }}
                            </div>
                            <div class="card-body">
                                <table id="closed-tickets" class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.id') }}</td>
                                        <td>{{ __('interface.data.subject') }}</td>
                                        <td>{{ __('interface.data.category') }}</td>
                                        <td>{{ __('interface.data.status') }}</td>
                                        <td>{{ __('interface.data.priority') }}</td>
                                        <td>{{ __('interface.actions.view') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-unknown" role="tabpanel" aria-labelledby="pills-unknown-tab">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-exclamation-circle"></i> {{ __('interface.status.locked') }}
                            </div>
                            <div class="card-body">
                                <table id="locked-tickets" class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.id') }}</td>
                                        <td>{{ __('interface.data.subject') }}</td>
                                        <td>{{ __('interface.data.category') }}</td>
                                        <td>{{ __('interface.data.status') }}</td>
                                        <td>{{ __('interface.data.priority') }}</td>
                                        <td>{{ __('interface.actions.view') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> {{ __('interface.misc.all') }}
                            </div>
                            <div class="card-body">
                                <table id="all-tickets" class="table mt-4 w-100">
                                    <thead>
                                    <tr>
                                        <td>{{ __('interface.data.id') }}</td>
                                        <td>{{ __('interface.data.subject') }}</td>
                                        <td>{{ __('interface.data.category') }}</td>
                                        <td>{{ __('interface.data.status') }}</td>
                                        <td>{{ __('interface.data.priority') }}</td>
                                        <td>{{ __('interface.actions.view') }}</td>
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
        </div>
    </div>

    <div class="modal fade" id="add" tabindex="-1" aria-labelledby="addLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.support.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('customer.support.create') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.subject') }}</label>

                            <div class="col-md-8">
                                <input id="subject" type="text" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}">

                                @error('subject')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.category') }}</label>

                            <div class="col-md-8">
                                <select id="category_id" class="form-control @error('category_id') is-invalid @enderror" name="category_id">
                                    <option value="0"{{ old('category_id') == '0' ? ' selected' : '' }}>{{ __('interface.status.uncategorized') }}</option>
                                    @foreach ($categories as $catgory)
                                        <option value="{{ $catgory->id }}"{{ old('category_id') == $catgory->id ? ' selected' : '' }}>{{ $catgory->name }}</option>
                                    @endforeach
                                </select>

                                @error('category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.priority') }}</label>

                            <div class="col-md-8">
                                <select id="priority" class="form-control @error('priority') is-invalid @enderror" name="priority">
                                    <option value="low"{{ old('priority') == 'low' ? ' selected' : '' }}>{{ __('interface.priorities.low') }}</option>
                                    <option value="medium"{{ old('priority') == 'medium' ? ' selected' : '' }}>{{ __('interface.priorities.medium') }}</option>
                                    <option value="high"{{ old('priority') == 'high' ? ' selected' : '' }}>{{ __('interface.priorities.high') }}</option>
                                    <option value="emergency"{{ old('priority') == 'emergency' ? ' selected' : '' }}>{{ __('interface.priorities.emergency') }}</option>
                                </select>

                                @error('priority')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="subject" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.message') }}</label>

                            <div class="col-md-8">
                                <textarea id="message" class="form-control @error('message') is-invalid @enderror" name="message" style="height: 15rem">{{ old('message') }}</textarea>

                                @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.file') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('interface.actions.choose_file') }}</label>
                                </div>

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.create') }}</button>
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
            $('#open-tickets').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/customer/support/list/open',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'subject' },
                    { data: 'category', sWidth: '20%' },
                    { data: 'status', sWidth: '20%' },
                    { data: 'priority', sWidth: '20%' },
                    { data: 'view', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });

            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                let target = $(this).attr('href');

                if (! $.fn.DataTable.isDataTable(target + ' #closed-tickets')) {
                    $(target + ' #closed-tickets').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/customer/support/list/closed',
                        columns: [
                            { data: 'id', sWidth: '1%' },
                            { data: 'subject' },
                            { data: 'category', sWidth: '20%' },
                            { data: 'status', sWidth: '20%' },
                            { data: 'priority', sWidth: '20%' },
                            { data: 'view', bSortable: false, sWidth: '1%' }
                        ],
                        order: [[0, 'desc']]
                    });
                }

                if (! $.fn.DataTable.isDataTable(target + ' #locked-tickets')) {
                    $(target + ' #locked-tickets').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/customer/support/list/locked',
                        columns: [
                            { data: 'id', sWidth: '1%' },
                            { data: 'subject' },
                            { data: 'category', sWidth: '20%' },
                            { data: 'status', sWidth: '20%' },
                            { data: 'priority', sWidth: '20%' },
                            { data: 'view', bSortable: false, sWidth: '1%' }
                        ],
                        order: [[0, 'desc']]
                    });
                }

                if (! $.fn.DataTable.isDataTable(target + ' #all-tickets')) {
                    $(target + ' #all-tickets').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/customer/support/list/all',
                        columns: [
                            { data: 'id', sWidth: '1%' },
                            { data: 'subject' },
                            { data: 'category', sWidth: '20%' },
                            { data: 'status', sWidth: '20%' },
                            { data: 'priority', sWidth: '20%' },
                            { data: 'view', bSortable: false, sWidth: '1%' }
                        ],
                        order: [[0, 'desc']]
                    });
                }
            });
        });
    </script>
@endsection
