@extends('layouts.admin')

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-box"></i> {{ __('sample.test') }}
                </div>
                <div class="card-body">
                    <table id="services" class="table mt-4 w-100">
                        <thead>
                        <tr>
                            <td>{{ __('sample.id') }}</td>
                            <td>{{ __('sample.user_id') }}</td>
                            <td>{{ __('sample.status') }}</td>
                            <td>{{ __('sample.view') }}</td>
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
            $('#services').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/services/{{ $handler->technicalName() }}/list',
                columns: [
                    { data: 'id' },
                    { data: 'user_id', sWidth: '20%' },
                    { data: 'status', bSortable: false, sWidth: '10%' },
                    { data: 'view', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
