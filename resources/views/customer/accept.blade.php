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

                                <label for="accept_{{ $accept->id }}" class="col-md-11 col-form-label">{!! __('I confirm that I have read, understood the :link (Date: :date) and I agree to be bound to it.', ['link' => '<a href="' . (Route::has($accept->route) ? route($accept->route) : $accept->route) . '" target="_blank">' . __($accept->title) . '</a>', 'date' => $accept->latest->created_at->format('d.m.Y, H:i')]) !!}</label>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> {{ __('Accept and continue') }}</button>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> {{ __('Nothing to accept. Click the button below to continue.') }}
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-right-circle"></i> {{ __('Continue') }}</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
