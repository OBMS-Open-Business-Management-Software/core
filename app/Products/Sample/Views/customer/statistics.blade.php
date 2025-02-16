@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-box"></i> {{ __('sample.test') }}
                    </div>
                    <div class="card-body">
                        {{ $service }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
