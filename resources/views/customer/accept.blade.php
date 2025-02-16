@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('customer.accept.submit') }}" method="post">
                    @csrf
                    @if ($acceptable->isNotEmpty())
                        @foreach ($acceptable as $accept)
                            <div class="form-group row">
                                <div class="col-md-1">
                                    <input id="accept_{{ $accept->id }}" type="checkbox" class="form-control" name="accept_{{ $accept->id }}" value="true">
                                </div>

                                <label for="accept_{{ $accept->id }}" class="col-md-11 col-form-label">{!! __('interface.misc.accept_notice', ['link' => '<a href="' . (Route::has($accept->route) ? route($accept->route) : $accept->route) . '" target="_blank">' . __($accept->title) . '</a>', 'date' => $accept->latest->created_at->format('d.m.Y, H:i')]) !!}</label>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> {{ __('interface.actions.accept_and_continue') }}</button>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> {{ __('interface.misc.no_documents_hint') }}
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-right-circle"></i> {{ __('interface.actions.continue') }}</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
