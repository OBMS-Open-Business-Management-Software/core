@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('admin.employees') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.actions.back_to_list') }}</a>
                <a class="btn btn-primary float-right ml-1" data-toggle="modal" data-target="#edit"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</a>
                @if ($user->locked)
                    <a href="{{ route('admin.employees.lock', $user->id) }}" class="btn btn-success float-right ml-1"><i class="bi bi-unlock"></i> {{ __('interface.actions.unlock') }}</a>
                @else
                    <a href="{{ route('admin.employees.lock', $user->id) }}" class="btn btn-warning float-right ml-1"><i class="bi bi-lock"></i> {{ __('interface.actions.lock') }}</a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> {{ __('interface.data.user_details') }}
                            </div>
                            <div class="card-body">
                                <label class="font-weight-bold mb-0">{{ __('interface.data.name') }}:</label> {{ $user->name }}<br>
                                <label class="font-weight-bold mb-0">{{ __('interface.data.email') }}:</label> {{ $user->email }} {!! $user->hasVerifiedEmail() ? '<span class="badge badge-success">' . __('interface.status.verified') . '</span>' : '<span class="badge badge-warning">' . __('interface.status.unverified') . '</span>' !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editLabel"><i class="bi bi-pencil-square"></i> {{ __('interface.misc.edit_user_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.employees.profile.update', $user->id) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row align-items-center">
                            <label for="role" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.role') }}</label>

                            <div class="col-md-8">
                                <select id="role" class="form-control @error('role') is-invalid @enderror" name="role">
                                    <option value="employee"{{ $user->role === 'employee' ? ' selected' : '' }}>{{ __('interface.data.employee') }}</option>
                                    <option value="admin"{{ $user->role === 'admin' ? ' selected' : '' }}>{{ __('interface.data.administrator') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') ?? $user->name }}">

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8 offset-md-4">
                                <div class="alert alert-primary mt-4 mb-1">
                                    <i class="bi bi-info-circle"></i> {{ __('interface.misc.email_change_notice') }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.email') }}</label>

                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('name') is-invalid @enderror" name="email" value="{{ old('email') ?? $user->email }}">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email-confirm" class="col-md-4 col-form-label text-md-right">{{ __('interface.actions.confirm_email') }}</label>

                            <div class="col-md-8">
                                <input id="email-confirm" type="email" class="form-control" name="email_confirmation">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i> {{ __('interface.actions.edit') }}</button>
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
            @if (! empty($user->profile))
                $('#emailaddresses').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/admin/suppliers/{{ $user->id }}/list/emailaddresses?user_id={{ $user->id }}',
                    columns: [
                        { data: 'email' },
                        { data: 'type', sWidth: '20%' },
                        { data: 'status', sWidth: '20%' },
                        { data: 'resend', bSortable: false, sWidth: '1%' },
                        { data: 'edit', bSortable: false, sWidth: '1%' },
                        { data: 'delete', bSortable: false, sWidth: '1%' }
                    ],
                    order: [[0, 'desc']]
                });

                $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                    let target = $(this).attr('href');

                    if (! $.fn.DataTable.isDataTable(target + ' #phonenumbers')) {
                        $(target + ' #phonenumbers').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/suppliers/{{ $user->id }}/list/phonenumbers?user_id={{ $user->id }}',
                            columns: [
                                { data: 'phone' },
                                { data: 'type', sWidth: '20%' },
                                { data: 'edit', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }

                    if (! $.fn.DataTable.isDataTable(target + ' #addresses')) {
                        $(target + ' #addresses').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/suppliers/{{ $user->id }}/list/addresses?user_id={{ $user->id }}',
                            columns: [
                                { data: 'address' },
                                { data: 'type', sWidth: '20%' },
                                { data: 'edit', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }

                    if (! $.fn.DataTable.isDataTable(target + ' #bankaccounts')) {
                        $(target + ' #bankaccounts').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '/admin/suppliers/{{ $user->id }}/list/bankaccounts?user_id={{ $user->id }}',
                            columns: [
                                { data: 'iban' },
                                { data: 'bic' },
                                { data: 'bank' },
                                { data: 'owner' },
                                { data: 'sepa', bSortable: false, sWidth: '1%' },
                                { data: 'primary', bSortable: false, sWidth: '1%' },
                                { data: 'delete', bSortable: false, sWidth: '1%' }
                            ],
                            order: [[0, 'desc']]
                        });
                    }
                });
            @endif

            $('#invoices').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/suppliers/list/{{ $user->id }}',
                columns: [
                    { data: 'id' },
                    { data: 'type', sWidth: '20%' },
                    { data: 'status', sWidth: '10%' },
                    { data: 'date', sWidth: '20%' },
                    { data: 'due', sWidth: '20%' },
                    { data: 'view', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            });

            $('#transactions').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/suppliers/{{ $user->id }}/list/transactions',
                columns: [
                    { data: 'date' },
                    { data: 'invoice_id' },
                    { data: 'amount', sWidth: '10%' },
                    { data: 'transaction_method', sWidth: '20%' },
                    { data: 'transaction_id', sWidth: '20%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' },
                ],
                order: [[0, 'desc']]
            });

            function initTableOptionRemovalClickListener(table) {
                table.find('.fieldDelete').off();
                table.find('.fieldDelete').on('click', function () {
                    $(this).parent().parent().remove();
                });
            }
        });
    </script>
@endsection
