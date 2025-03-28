@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.importer.create') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.misc.importers') }}
                    </div>
                    <div class="card-body">
                        <table id="importers" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.description') }}</td>
                                <td>{{ __('interface.data.log') }}</td>
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
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.category.create') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.invoices.importers.add') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.name') }}</label>

                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">

                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.description') }}</label>

                            <div class="col-md-8">
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}">

                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_host" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.host') }}</label>

                            <div class="col-md-8">
                                <input id="imap_host" type="text" class="form-control @error('imap[host]') is-invalid @enderror" name="imap[host]" value="{{ old('imap[host]') }}">

                                @error('imap[host]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_port" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.port') }}</label>

                            <div class="col-md-8">
                                <input id="imap_port" type="text" class="form-control @error('imap[port]') is-invalid @enderror" name="imap[port]" value="{{ old('imap[port]') }}">

                                @error('imap[port]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_protocol" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.protocol') }}</label>

                            <div class="col-md-8">
                                <select id="imap_protocol" type="text" class="form-control @error('imap[protocol]') is-invalid @enderror" name="imap[protocol]">
                                    <option value="none"{{ ((old('imap[protocol]') ?? '') == 'none' ? ' selected' : '') }}>{{ __('interface.misc.none') }}</option>
                                    <option value="tls"{{ ((old('imap[protocol]') ?? '') == 'tls' ? ' selected' : '') }}>{{ __('interface.misc.tls') }}</option>
                                    <option value="ssl"{{ ((old('imap[protocol]') ?? '') == 'ssl' ? ' selected' : '') }}>{{ __('interface.misc.ssl') }}</option>
                                </select>

                                @error('imap[protocol]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_username" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.username') }}</label>

                            <div class="col-md-8">
                                <input id="imap_username" type="text" class="form-control @error('imap[username]') is-invalid @enderror" name="imap[username]" value="{{ old('imap[username]') }}">

                                @error('imap[username]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_password" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.password') }}</label>

                            <div class="col-md-8">
                                <input id="imap_password" type="password" class="form-control @error('imap[password]') is-invalid @enderror" name="imap[password]">

                                @error('imap[password]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="imap_folder" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.folder') }}</label>

                            <div class="col-md-8">
                                <input id="imap_folder" type="text" class="form-control @error('imap[folder]') is-invalid @enderror" name="imap[folder]" value="{{ old('imap[folder]') ?? 'INBOX' }}">

                                @error('imap[folder]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="imap_validate_cert" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.validate_certificate') }}</label>

                            <div class="col-md-8">
                                <input id="imap_validate_cert" type="checkbox" class="form-control @error('imap[validate_cert]') is-invalid @enderror" name="imap[validate_cert]" value="true" checked>

                                @error('imap[validate_cert]')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label for="delete_after_import" class="col-md-4 col-form-label text-md-right">{{ __('interface.misc.delete_after_import') }}</label>

                            <div class="col-md-8">
                                <input id="delete_after_import" type="checkbox" class="form-control @error('imap[delete_after_import]') is-invalid @enderror" name="imap[delete_after_import]" value="true" checked>

                                @error('imap[delete_after_import]')
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
            $('#importers').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/invoices/importers/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'log', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']]
            })
        });
    </script>
@endsection
