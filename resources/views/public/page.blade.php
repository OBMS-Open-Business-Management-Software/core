@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="sr-only">{{ __($page->title) }}</h1>
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-file-earmark-text"></i> {{ __($page->title) }}
                    </div>
                    <div class="card-body">
                        {!! $render($page->getAttributes()['latest']->compiled, request()->toArray()) !!}
                        <div class="date mt-3">
                            <label class="mb-0 font-weight-bold">{{ __('interface.units.date') }}:</label> {{ $page->getAttributes()['latest']->created_at->format('d.m.Y, H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
