@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-cart-fill"></i> {{ __('interface.misc.orders') }}
                    </div>
                    <div class="card-body">
                        <table id="categories" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.user') }}</td>
                                <td>{{ __('interface.data.form') }}</td>
                                <td>{{ __('interface.data.product_type') }}</td>
                                <td>{{ __('interface.data.amount') }}</td>
                                <td>{{ __('interface.data.steps') }}</td>
                                <td>{{ __('interface.data.history') }}</td>
                                <td>{{ __('interface.actions.approve') }}</td>
                                <td>{{ __('interface.actions.disapprove') }}</td>
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
@endsection

@section('javascript')
    <script type="text/javascript">
        function initTableOptionRemovalClickListener(table) {
            table.find('.fieldDelete').off();
            table.find('.fieldDelete').on('click', function () {
                $(this).parent().parent().remove();
            });
        }

        $(window).on('load', function () {
            $('#categories').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/shop/orders/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'user' },
                    { data: 'form' },
                    { data: 'product_type' },
                    { data: 'amount' },
                    { data: 'status' },
                    { data: 'history', bSortable: false, sWidth: '1%' },
                    { data: 'approve', bSortable: false, sWidth: '1%' },
                    { data: 'disapprove', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
                fnDrawCallback: function () {
                    $('.options_table').each(function () {
                        let table = $(this);

                        table.find('.fieldAdd').on('click', function () {
                            let key = table.find('.fieldKey').first().val();
                            let value = table.find('.fieldValue').first().val();
                            let timestamp = Date.now();

                            table.find('.fieldKey').first().val('');
                            table.find('.fieldValue').first().val('');
                            table.find('.fieldFees').val('');
                            table.find('.fieldDefault').first().prop('checked', false);

                            table.find('.options_tbody').first().append('<tr><td><input type="text" class="form-control" name="options[' + timestamp + '][key]" value="' + key + '"></td><td><input type="text" class="form-control" name="options[' + timestamp + '][value]" value="' + value + '"></td><td><button type="button" class="btn btn-danger fieldDelete"><i class="bi bi-trash"></i></button></td></tr>');
                        });

                        initTableOptionRemovalClickListener(table);
                    });
                }
            });
        });
    </script>
@endsection
