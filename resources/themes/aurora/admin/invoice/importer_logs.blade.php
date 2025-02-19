@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.invoices.importers') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.importer_log') }}
                    </div>
                    <div class="card-body">
                        <table id="importers" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.date') }}</td>
                                <td>{{ __('interface.data.subject') }}</td>
                                <td>{{ __('interface.time.from') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.time.to') }}</td>
                                <td>{{ __('interface.actions.download') }}</td>
                                <td>{{ __('interface.misc.invoice') }}</td>
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
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#importers').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/importers/{{ $importer->id }}/log/list',
                columns: [
                    { data: 'created_at' },
                    { data: 'subject', sWidth: '1%' },
                    { data: 'from', sWidth: '1%' },
                    { data: 'from_name', sWidth: '1%' },
                    { data: 'to', sWidth: '1%' },
                    { data: 'download', bSortable: false, sWidth: '1%' },
                    { data: 'invoice', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            })
        });
    </script>
@endsection
