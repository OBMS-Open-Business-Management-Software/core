@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#add"><i class="bi bi-plus-circle"></i> {{ __('interface.api.create_oauth_client') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person"></i> {{ __('interface.misc.api_oauth_clients') }}
                    </div>
                    <div class="card-body">
                        <table id="apiclients" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.id') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('interface.data.public') }}</td>
                                <td>{{ __('interface.data.redirect') }}</td>
                                <td>{{ __('interface.data.secret') }}</td>
                                <td>{{ __('interface.data.type') }}</td>
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
                    <h5 class="modal-title" id="addLabel"><i class="bi bi-plus-circle"></i> {{ __('interface.api.create_oauth_client') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form action="{{ route('admin.api.oauth-clients.create') }}" method="post">
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
                        <div class="form-group row mt-4">
                            <label class="col-md-4 col-form-label text-md-right">{{ __('interface.data.client_type') }}</label>

                            <label for="personal" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.personal') }}</label>

                            <div class="col-md-2">
                                <input id="personal" type="radio" class="form-control" name="type" value="personal" checked>
                            </div>

                            <label for="password" class="col-md-2 col-form-label text-md-right">{{ __('interface.data.password') }}</label>

                            <div class="col-md-2">
                                <input id="password" type="radio" class="form-control" name="type" value="password">
                            </div>
                        </div>
                        <div class="form-group row mt-4">
                            <label for="client" class="col-md-6 col-form-label text-md-right">{{ __('interface.data.client') }}</label>

                            <div class="col-md-2">
                                <input id="client" type="radio" class="form-control" name="type" value="client">
                            </div>
                        </div>
                        <div class="form-group row align-items-center mt-4">
                            <label for="type" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.grant_type') }}</label>

                            <div class="col-md-8">
                                <select id="grant_type" type="text" class="form-control @error('grant_type') is-invalid @enderror" name="grant_type">
                                    <option value="authorization_code"{{ old('grant_type') == 'authorization_code' ? ' selected' : '' }}>{{ __('interface.data.grant_type_authorization_code') }}</option>
                                    <option value="client_credentials"{{ old('grant_type') == 'client_credentials ' ? ' selected' : '' }}>{{ __('interface.data.grant_type_client_credentials') }}</option>
                                    <option value="password"{{ old('grant_type') == 'password  ' ? ' selected' : '' }}>{{ __('interface.data.grant_type_password') }}</option>
                                    <option value="personal_access_tokens"{{ old('grant_type') == 'personal_access_tokens  ' ? ' selected' : '' }}>{{ __('interface.data.grant_type_personal_access_tokens') }}</option>
                                    <option value="refresh_token"{{ old('grant_type') == 'refresh_token  ' ? ' selected' : '' }}>{{ __('interface.data.grant_type_refresh_token') }}</option>
                                </select>

                                @error('grant_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row mt-4">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.redirect') }}</label>

                            <div class="col-md-8">
                                <input id="redirect" type="text" class="form-control @error('redirect') is-invalid @enderror" name="redirect" value="{{ old('redirect') }}">

                                @error('redirect')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center mt-4">
                            <label for="public" class="col-md-4 col-form-label text-md-right">{{ __('interface.data.public') }}</label>

                            <div class="col-md-2">
                                <input id="public" type="checkbox" class="form-control @error('public') is-invalid @enderror" name="public" value="true">
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
            $('#apiclients').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/api/oauth-clients/list',
                columns: [
                    { data: 'id', sWidth: '1%' },
                    { data: 'name' },
                    { data: 'public' },
                    { data: 'redirect' },
                    { data: 'secret' },
                    { data: 'type', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
