@extends('layouts.login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header text-decoration-none">
                    <i class="bi bi-envelope"></i> {{ __('Verify Email') }}
                </div>
                <div class="card-body mb-0">
                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    <form class="d-block mt-3" method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">{{ __('Didn\'t receive an email? Click here.') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
