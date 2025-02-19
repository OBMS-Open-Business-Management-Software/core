@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="sr-only">Error 500</h1>
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-exclamation-triangle"></i> Error 500
                    </div>
                    <div class="card-body">
                        <p>{{ __('interface.misc.500_message') }}</p>
                        <div class="bg-gray rounded p-4">
                            <b>{{ __('interface.misc.what_to_do') }}</b>
                            <ul class="mt-3 mb-0">
                                <li>{{ __('interface.misc.try_again_later') }}</li>
                                <li>{{ __('interface.misc.return_to_home') }}</li>
                                <li>{{ __('interface.misc.visit_helpcenter') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
