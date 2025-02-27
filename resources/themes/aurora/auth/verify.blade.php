@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-decoration-none">
                    <i class="bi bi-envelope"></i> {{ __('interface.actions.verify_email') }}
                </div>
                <div class="card-body mb-0">
                    {{ __('interface.misc.verify_email_hint') }}
                    <form class="d-block mt-3" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">{{ __('interface.actions.verify_email_retry') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
