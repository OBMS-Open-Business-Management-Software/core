@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-cart-fill"></i> {{ __('Orders') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('User') }}</td>
                                <td>{{ __('Form') }}</td>
                                <td>{{ __('Product Type') }}</td>
                                <td>{{ __('interface.data.amount') }}</td>
                                <td>{{ __('Steps') }}</td>
                                <td>{{ __('History') }}</td>
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
            $('#categories').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/customer/orders/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'user' },
                    { data: 'form' },
                    { data: 'product_type' },
                    { data: 'amount' },
                    { data: 'status' },
                    { data: 'history', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
