@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-brush"></i> {{ __('interface.data.branding') }}
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.assets') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border border-gray p-3 rounded mb-3 img-preview__wrapper">
                                <img src="{{ config('company.logo') ?? theme_asset('aurora', 'images/full.logo.svg') }}" class="img-preview">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label for="logo" class="input-group-text">{{ __('interface.data.logo') }}</label>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="logoInput" name="logo">
                                    <label class="custom-file-label" for="logoInput">{{ __('interface.actions.choose_file') }}</label>
                                </div>
                                @if (! empty(config('company.logo')))
                                    <a href="{{ route('admin.settings.assets.remove', ['setting' => 'company.logo']) }}" class="btn btn-danger ml-3">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border border-gray p-3 rounded mb-3 img-preview__wrapper">
                                <img src="{{ config('company.favicon') ?? theme_asset('aurora', 'images/favicon.logo.svg') }}" class="img-preview">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <label for="favicon" class="input-group-text">{{ __('interface.data.favicon') }}</label>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="faviconInput" name="favicon">
                                    <label class="custom-file-label" for="faviconInput">{{ __('interface.actions.choose_file') }}</label>
                                </div>
                                @if (! empty(config('company.favicon')))
                                    <a href="{{ route('admin.settings.assets.remove', ['setting' => 'company.favicon']) }}" class="btn btn-danger ml-3">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">{{ __('interface.actions.save_branding') }}</button>
                    </div>
                </form>
            </div>
        </div>
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
