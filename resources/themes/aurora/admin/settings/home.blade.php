@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.parameters') }}
                    </div>
                    <div class="card-body">
                        <table id="settings" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.setting') }}</td>
                                <td>{{ __('interface.data.value') }}</td>
                                <td>{{ __('interface.actions.edit') }}</td>
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
            $('#settings').DataTable({
                stateSave: true,
                stateLoadCallback: function (settings) {
                    return JSON.parse(localStorage.getItem('DataTables_adminInstanceSettings'));
                },
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem('DataTables_adminInstanceSettings', JSON.stringify(data));
                },
                processing: true,
                serverSide: true,
                ajax: '/admin/settings/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'setting' },
                    { data: 'value', bSortable: false },
                    { data: 'edit', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
