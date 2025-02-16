@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        @if (! empty($category))
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card{{ $categories->isNotEmpty() ? ' mb-4' : '' }}">
                                <div class="card-header text-decoration-none">
                                    <i class="bi bi-info-circle"></i> {{ __($category->name) }}
                                </div>
                                <div class="card-body mb-0">
                                    {{ __($category->description) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card{{ $categories->isNotEmpty() ? ' mb-4' : '' }}">
                                <div class="card-header text-decoration-none">
                                    <i class="bi bi-info-circle"></i> {{ __('interface.misc.shop_information') }}
                                </div>
                                <div class="card-body mb-0">
                                    {{ __('interface.misc.shop_welcome') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (! empty($category) && ! empty($parent = $category->category))
            <div class="row my-4">
                <div class="col-md-12">
                    <a href="{{ $parent->fullRoute }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.misc.previous_category') }}</a>
                </div>
            </div>
        @elseif (! empty($category))
            <div class="row my-4">
                <div class="col-md-12">
                    <a href="{{ route('public.shop') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('interface.misc.previous_category') }}</a>
                </div>
            </div>
        @endif
        @if (! empty($user = Auth::user()) && ! $user->validProfile)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        @if ($user->role == 'customer')
                            {{ __('interface.misc.missing_data_notice') }}
                        @else
                            {{ __('interface.misc.order_role_missing_hint') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif
        @if ($categories->isNotEmpty() || $forms->isNotEmpty())
            @if ($categories->isNotEmpty())
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-decoration-none">
                                <i class="bi bi-list"></i> {{ __('interface.misc.categories') }}
                            </div>
                            <div class="card-body py-3">
                                @foreach ($categories as $category)
                                    <a href="{{ $category->fullRoute }}" class="btn btn-outline-primary my-1"><i class="bi bi-arrow-right-circle"></i> {{ __($category->name) }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($forms->isNotEmpty())
                @php
                $productCount = 0;
                @endphp
                @foreach ($forms as $form)
                    @php
                    $productCount = $productCount + 1;
                    @endphp
                    @if ($productCount === 1 || $productCount % 3 === 1)
                        <div class="row my-4">
                    @endif
                    <div class="col-md-4">
                        <a href="{{ $form->fullRoute }}" class="text-decoration-none">
                            <div class="card pricing">
                                <div class="card-header text-decoration-none">
                                    @if ($form->type == 'package')
                                        <i class="bi bi-box"></i>
                                    @elseif ($form->type == 'form')
                                        <i class="bi bi-cart"></i>
                                    @endif
                                    {{ __($form->name) }}
                                </div>
                                <div class="card-body">
                                    {!! __($form->description) !!}
                                </div>
                                <div class="card-footer">
                                     {{ __('interface.time.from') }}:
                                    <span>{{ number_format($form->minAmount, 2) }} €</span>
                                    {{ __('interface.misc.excl_vat') }}
                                </div>
                            </div>
                        </a>
                    </div>
                    @if ($productCount % 3 === 0 || $productCount === $forms->count())
                        </div>
                    @endif
                @endforeach
            @endif
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> {{ __('interface.misc.no_category_product_notice') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
