@extends('layouts.admin')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                @if (isset($parent))
                    <a href="{{ $parent > 0 ? route('admin.filemanager.folder', $parent) : route('admin.filemanager') }}" class="btn btn-outline-primary mb-4"><i class="bi bi-arrow-left-circle"></i> {{ __('Parent folder') }}</a>
                @endif
                <a class="btn btn-primary float-right mb-4 ml-1" data-toggle="modal" data-target="#addFolder"><i class="bi bi-plus-circle"></i> {{ __('Create Folder') }}</a>
                <a class="btn btn-primary float-right mb-4" data-toggle="modal" data-target="#addFile"><i class="bi bi-plus-circle"></i> {{ __('Upload File') }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">{{ __('Path:') }}</div>
                    </div>
                    <input class="form-control" value="{{ $folder->path ?? '/' }}" readonly>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-files"></i> {{ __('Filemanager') }}
                    </div>
                    <div class="card-body">
                        <table id="filemanager" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.type') }}</td>
                                <td>{{ __('interface.data.name') }}</td>
                                <td>{{ __('Size') }}</td>
                                <td>{{ __('Private') }}</td>
                                <td>{{ __('interface.actions.edit') }}</td>
                                <td>{{ __('Action') }}</td>
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
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-link"></i> {{ __('Webdav') }}
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">{{ __('Webdav URL:') }}</div>
                            </div>
                            <input class="form-control" value="{{ config('app.url') }}/api/webdav" readonly>
                        </div>
                        <div class="alert alert-primary mt-4 mb-0">
                            <i class="bi bi-info-circle"></i> {{ __('WebDAV is an easy way of connecting the virtual filesystem of the software directly to your computer. WebDAV filesystems can usually be connected by using the network drive functionality of the operating system.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addFile" tabindex="-1" aria-labelledby="addFileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addFileLabel"><i class="bi bi-upload"></i> {{ __('Upload File') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.filemanager.file.create') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $folder->id ?? '' }}">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('File') }}</label>

                            <div class="col-md-8">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="customFile" name="file">
                                    <label class="custom-file-label" for="customFile">{{ __('Choose file') }}</label>
                                </div>

                                @error('file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row align-items-center">
                            <label for="primary" class="col-md-4 col-form-label text-md-right">{{ __('Private') }}</label>

                            <div class="col-md-8">
                                <input id="private" type="checkbox" class="form-control @error('private') is-invalid @enderror" name="private" value="true">

                                @error('private')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> {{ __('Upload') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('interface.actions.close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addFolder" tabindex="-1" aria-labelledby="addFolderLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addFolderLabel"><i class="bi bi-plus-circle"></i> {{ __('Create Folder') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.filemanager.folder.create') }}" method="post">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $folder->id ?? '' }}">
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

                        <div class="form-group row align-items-center">
                            <label for="primary" class="col-md-4 col-form-label text-md-right">{{ __('Private') }}</label>

                            <div class="col-md-8">
                                <input id="private" type="checkbox" class="form-control @error('private') is-invalid @enderror" name="private" value="true">

                                @error('private')
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
            $('#filemanager').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/filemanager/list/{{ $folder->id ?? '0' }}',
                columns: [
                    { data: 'icon', bSortable: false, sWidth: '15%' },
                    { data: 'name' },
                    { data: 'size', bSortable: false, sWidth: '10%' },
                    { data: 'private', bSortable: false, sWidth: '1%' },
                    { data: 'edit', bSortable: false, sWidth: '1%' },
                    { data: 'action', bSortable: false, sWidth: '1%' },
                    { data: 'delete', bSortable: false, sWidth: '1%' }
                ],
                order: [[0, 'desc']],
            });
        });
    </script>
@endsection
