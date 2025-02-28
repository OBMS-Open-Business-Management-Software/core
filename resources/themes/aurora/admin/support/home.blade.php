@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="pills-category-uncategorized-tab" data-toggle="pill" data-type="category" href="#pills-category-uncategorized" role="tab" aria-controls="pills-category-uncategorized" aria-selected="true">{{ __('interface.status.uncategorized') }}</a>
                            </li>
                            @foreach($categories as $category)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="pills-category-{{ $category->id }}-tab" data-toggle="pill" data-type="category" href="#pills-category-{{ $category->id }}" role="tab" aria-controls="pills-category-{{ $category->id }}" aria-selected="false">{{ __($category->name) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-category-uncategorized" role="tabpanel" aria-labelledby="pills-category-uncategorized-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="pills-category-uncategorized-open-tab" data-toggle="pill" data-type="status" href="#pills-category-uncategorized-open" role="tab" aria-controls="pills-category-uncategorized-open" aria-selected="true">{{ __('interface.status.open') }}</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="pills-category-uncategorized-closed-tab" data-toggle="pill" data-type="status" href="#pills-category-uncategorized-closed" role="tab" aria-controls="pills-category-uncategorized-closed" aria-selected="false">{{ __('interface.status.closed') }}</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="pills-category-uncategorized-unknown-tab" data-toggle="pill" data-type="status" href="#pills-category-uncategorized-unknown" role="tab" aria-controls="pills-category-uncategorized-unknown" aria-selected="false">{{ __('interface.status.locked') }}</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="pills-category-uncategorized-all-tab" data-toggle="pill" data-type="status" href="#pills-category-uncategorized-all" role="tab" aria-controls="pills-category-uncategorized-all" aria-selected="false">{{ __('interface.misc.all') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="tab-content" id="pills-tabContent">
                                            <div class="tab-pane fade show active" id="pills-category-uncategorized-open" role="tabpanel" aria-labelledby="pills-category-uncategorized-open-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <i class="bi bi-question-circle"></i> {{ __('interface.status.open') }}
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="open-tickets-uncategorized" class="table mt-4 w-100">
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
                                            <div class="tab-pane fade" id="pills-category-uncategorized-closed" role="tabpanel" aria-labelledby="pills-category-uncategorized-closed-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <i class="bi bi-check-circle"></i> {{ __('interface.status.closed') }}
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="closed-tickets-uncategorized" class="table mt-4 w-100">
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
                                            <div class="tab-pane fade" id="pills-category-uncategorized-unknown" role="tabpanel" aria-labelledby="pills-category-uncategorized-unknown-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <i class="bi bi-exclamation-circle"></i> {{ __('interface.status.locked') }}
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="locked-tickets-uncategorized" class="table mt-4 w-100">
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
                                            <div class="tab-pane fade" id="pills-category-uncategorized-all" role="tabpanel" aria-labelledby="pills-category-uncategorized-all-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <i class="bi bi-info-circle"></i> {{ __('interface.misc.all') }}
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="all-tickets-uncategorized" class="table mt-4 w-100">
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
                            @foreach($categories as $category)
                                <div class="tab-pane fade" id="pills-category-{{ $category->id }}" role="tabpanel" aria-labelledby="pills-category-{{ $category->id }}-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="pills-category-{{ $category->id }}-open-tab" data-toggle="pill" data-type="status" href="#pills-category-{{ $category->id }}-open" role="tab" aria-controls="pills-category-{{ $category->id }}-open" aria-selected="true">{{ __('interface.status.open') }}</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-category-{{ $category->id }}-closed-tab" data-toggle="pill" data-type="status" href="#pills-category-{{ $category->id }}-closed" role="tab" aria-controls="pills-category-{{ $category->id }}-closed" aria-selected="false">{{ __('interface.status.closed') }}</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-category-{{ $category->id }}-unknown-tab" data-toggle="pill" data-type="status" href="#pills-category-{{ $category->id }}-unknown" role="tab" aria-controls="pills-category-{{ $category->id }}-unknown" aria-selected="false">{{ __('interface.status.locked') }}</a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="pills-category-{{ $category->id }}-all-tab" data-toggle="pill" data-type="status" href="#pills-category-{{ $category->id }}-all" role="tab" aria-controls="pills-category-{{ $category->id }}-all" aria-selected="false">{{ __('interface.misc.all') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="tab-content" id="pills-tabContent">
                                                <div class="tab-pane fade show active" id="pills-category-{{ $category->id }}-open" role="tabpanel" aria-labelledby="pills-category-{{ $category->id }}-open-tab">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <i class="bi bi-question-circle"></i> {{ __('interface.status.open') }}
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="open-tickets-{{ $category->id }}" class="table mt-4 w-100">
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
                                                <div class="tab-pane fade" id="pills-category-{{ $category->id }}-closed" role="tabpanel" aria-labelledby="pills-category-{{ $category->id }}-closed-tab">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <i class="bi bi-check-circle"></i> {{ __('interface.status.closed') }}
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="closed-tickets-{{ $category->id }}" class="table mt-4 w-100">
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
                                                <div class="tab-pane fade" id="pills-category-{{ $category->id }}-unknown" role="tabpanel" aria-labelledby="pills-category-{{ $category->id }}-unknown-tab">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <i class="bi bi-exclamation-circle"></i> {{ __('interface.status.locked') }}
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="locked-tickets-{{ $category->id }}" class="table mt-4 w-100">
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
                                                <div class="tab-pane fade" id="pills-category-{{ $category->id }}-all" role="tabpanel" aria-labelledby="pills-category-{{ $category->id }}-all-tab">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <i class="bi bi-info-circle"></i> {{ __('interface.misc.all') }}
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="all-tickets-{{ $category->id }}" class="table mt-4 w-100">
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
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mt-10">
                    <div class="card-header">
                        <i class="bi bi-arrow-right-circle"></i> {{ __('interface.actions.run') }}
                    </div>
                    <div class="card-body">
                        @if (empty($run))
                            <a href="#" class="btn btn-success w-100" data-toggle="modal" data-target="#runStart"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start') }}</a>
                        @else
                            <label class="font-weight-bold mb-0">{{ __('interface.data.category') }}:</label> {{ ! empty($run->category_id) ? (! empty($run->category) ? $run->category->name : __('interface.status.uncategorized')) : __('interface.misc.all') }}<br>
                            <label class="font-weight-bold mb-3">{{ __('interface.misc.started_at') }}:</label> {{ $run->created_at->format('d.m.Y, H:i') }}
                            <a href="{{ route('admin.support.run.next') }}" class="btn btn-warning w-100 mb-3"><i class="bi bi-skip-forward-fill"></i> {{ __('interface.actions.next') }}</a>
                            <a href="{{ route('admin.support.run.stop') }}" class="btn btn-danger w-100"><i class="bi bi-stop-fill"></i> {{ __('interface.actions.stop') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (empty($run))
        <div class="modal fade" id="runStart" tabindex="-1" aria-labelledby="runStartLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="runStartLabel"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start_ticket_run') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.support.run.start') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="category" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.category') }}</label>

                                <div class="col-md-8">
                                    <select id="category" class="form-control @error('category') is-invalid @enderror" name="category">
                                        <option value="">{{ __('interface.misc.all') }}</option>
                                        <option value="0">{{ __('interface.status.uncategorized') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"{{ $category->id == old('category') ? ' selected' : '' }}></option>
                                        @endforeach
                                    </select>

                                    @error('category')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> {{ __('interface.actions.start') }}</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#open-tickets-uncategorized').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/support/list/0/open',
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

            $('a[data-toggle="pill"][data-type="category"]').on('shown.bs.tab', function (e) {
                let target = $(this).attr('href');

                if (! $.fn.DataTable.isDataTable(target + ' #open-tickets-uncategorized')) {
                    $(target + ' #open-tickets-uncategorized').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/admin/support/list/0/open',
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

                @foreach($categories as $category)
                    if (! $.fn.DataTable.isDataTable(target + ' #open-tickets-{{ $category->id }}')) {
                        $(target + ' #open-tickets-{{ $category->id }}').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/support/list/{{ $category->id }}/open',
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
                @endforeach
            });

            $('a[data-toggle="pill"][data-type="status"]').on('shown.bs.tab', function (e) {
                let target = $(this).attr('href');

                if (! $.fn.DataTable.isDataTable(target + ' #closed-tickets-uncategorized')) {
                    $(target + ' #closed-tickets-uncategorized').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/admin/support/list/0/closed',
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

                if (! $.fn.DataTable.isDataTable(target + ' #locked-tickets-uncategorized')) {
                    $(target + ' #locked-tickets-uncategorized').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/admin/support/list/0/locked',
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

                if (! $.fn.DataTable.isDataTable(target + ' #all-tickets-uncategorized')) {
                    $(target + ' #all-tickets-uncategorized').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '/admin/support/list/0/all',
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

                @foreach($categories as $category)
                    if (! $.fn.DataTable.isDataTable(target + ' #closed-tickets-{{ $category->id }}')) {
                        $(target + ' #closed-tickets-{{ $category->id }}').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/support/list/{{ $category->id }}/closed',
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

                    if (! $.fn.DataTable.isDataTable(target + ' #locked-tickets-{{ $category->id }}')) {
                        $(target + ' #locked-tickets-{{ $category->id }}').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/support/list/{{ $category->id }}/locked',
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

                    if (! $.fn.DataTable.isDataTable(target + ' #all-tickets-{{ $category->id }}')) {
                        $(target + ' #all-tickets-{{ $category->id }}').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/support/list/{{ $category->id }}/all',
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
                @endforeach
            });
        });
    </script>
@endsection
