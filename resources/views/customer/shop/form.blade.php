@extends('layouts.public')

@section('content')
    <div class="container-fluid my-4">
        @if (empty($user = Auth::user()))
            <div class="alert alert-warning mb-4">
                <i class="bi bi-exclamation-triangle"></i> {{ __('You are currently logged out. Only logged in users can order new products.') }}<br>
                <br>
                <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> {{ __('Log in') }}</a>
            </div>
        @elseif ($user->role !== 'customer')
            <div class="alert alert-warning mb-4">
                <i class="bi bi-exclamation-triangle"></i> {{ __('You don\'t have the proper role to place an order. Only customers can order new products.') }}<br>
                <br>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-left"></i> {{ __('Log out') }}</button>
                </form>
            </div>
        @endif
        @if (! empty($category = $form->category) && ! empty($parent = $category->category))
            <div class="row mb-4">
                <div class="col-md-12">
                    <a href="{{ $parent->fullRoute }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to category') }}</a>
                </div>
            </div>
        @else
            <div class="row mb-4">
                <div class="col-md-12">
                    <a href="{{ route('public.shop') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left-circle"></i> {{ __('Back to category') }}</a>
                </div>
            </div>
        @endif
        @if (! empty($form->fields->isNotEmpty()))
            <form action="{{ route('customer.shop.process') }}" method="post">
                @csrf
                <input type="hidden" name="form_id" value="{{ $form->id }}">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header text-decoration-none">
                                <i class="bi bi-info-circle"></i> {{ __($form->name) }}
                            </div>
                            <div class="card-body mb-0">
                                {!! __($form->description) !!}
                            </div>
                        </div>
                        @if ($form->type == 'form')
                            <div class="card mb-4">
                                <div class="card-header text-decoration-none">
                                    <i class="bi bi-wrench"></i> {{ __('Configuration') }}
                                </div>
                                <div class="card-body mb-0">
                                    @foreach ($form->fields as $field)
                                        @if (! in_array($field->type, [
                                            'input_checkbox',
                                            'input_hidden',
                                        ]))
                                            <label class="mt-2" for="input_{{ $field->id }}">{{ __($field->label) }} {{ $field->required ? __('*') : '' }}</label>
                                        @endif
                                        @switch ($field->type)
                                            @case ('input_text')
                                                <input type="text" class="form-control" id="input_{{ $field->id }}" name="{{ $field->key }}" placeholder="{{ $field->value }}" value="{{ $field->value }}">
                                                @break
                                            @case ('input_number')
                                                <input type="number" class="form-control" id="input_{{ $field->id }}" name="{{ $field->key }}" placeholder="{{ $field->value }}" value="{{ $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                @break
                                            @case ('input_range')
                                                <input type="range" class="form-control" id="input_{{ $field->id }}" name="{{ $field->key }}" placeholder="{{ $field->value }}" value="{{ ! empty($field->defaultOption) ? $field->defaultOption->value : $field->value }}" min="{{ $field->min }}" max="{{ $field->max }}" step="{{ $field->step }}">
                                                @break
                                            @case ('input_radio')
                                                <div id="input_{{ $field->id }}">
                                                    @foreach ($field->options as $option)
                                                        <div class="form-group row align-items-center">
                                                            <div class="col-md-1">
                                                                <input id="{{ $field->key }}{{ $option->id }}" type="radio" class="form-control @error($field->key) is-invalid @enderror" name="{{ $field->key }}" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
                                                            </div>
                                                            <label for="{{ $field->key }}{{ $option->id }}" class="col-md-11 col-form-label text-md-left">{{ __($option->label) }} <span class="small">[{{ __('+') }} {{ number_format($option->amount, 2) }} €]</span></label>
                                                        </div>
                                                        @error($field->key)
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    @endforeach
                                                </div>
                                                @break
                                            @case ('input_radio_image')
                                                <div id="input_{{ $field->id }}">
                                                    @foreach ($field->options as $option)
                                                        <div class="form-group row align-items-center">
                                                            <div class="col-md-1">
                                                                <input id="{{ $field->key }}" type="radio" class="form-control has-image @error($field->key) is-invalid @enderror" name="{{ $field->key }}" value="{{ $option->value }}" {{ $option->default ? ' checked' : '' }}>
                                                            </div>
                                                            <label for="{{ $field->key }}" class="col-md-11 col-form-label text-md-left">
                                                                <img src="{{ $option->label }}" class="radio-image">
                                                            </label>
                                                        </div>
                                                        @error($field->key)
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    @endforeach
                                                </div>
                                                @break
                                            @case ('input_checkbox')
                                                <div class="form-group row align-items-center" id="input_{{ $field->id }}">
                                                    <div class="col-md-1">
                                                        <input id="{{ $field->key }}" type="checkbox" class="form-control @error($field->key) is-invalid @enderror" name="{{ $field->key }}" value="{{ $field->value }}">
                                                    </div>
                                                    <label for="{{ $field->key }}" class="col-md-11 col-form-label text-md-left">{{ __($field->label) }} <span class="small">[{{ __('+') }} {{ number_format($field->amount, 2) }} €]</span> {{ $field->required ? __('*') : '' }}</label>
                                                </div>
                                                @error($field->key)
                                                <span class="invalid-feedback d-block mb-3" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                                @break
                                            @case ('input_hidden')
                                                <input type="hidden" id="input_{{ $field->id }}" name="{{ $field->key }}" value="{{ $field->value }}">
                                                @break
                                            @case ('select')
                                                <select class="form-control" id="input_{{ $field->id }}" name="{{ $field->key }}">
                                                    @foreach ($field->options as $option)
                                                        <option value="{{ $option->value }}"{{ $option->default ? ' selected' : '' }}>{{ $option->label }} [{{ __('+') }} {{ number_format($option->amount, 2) }} €]</option>
                                                    @endforeach
                                                </select>
                                                @break
                                            @case ('textarea')
                                                <textarea class="form-control" id="input_{{ $field->id }}" name="{{ $field->key }}" placeholder="{{ $field->value }}">{{ $field->value }}</textarea>
                                                @break
                                        @endswitch
                                        @if ($field->type == 'input_range' || $field->type == 'input_radio' || $field->type == 'input_radio_image' || ($field->type == 'input_checkbox' && $field->amount > 0) || $field->type == 'select')
                                            @if ($field->type !== 'input_checkbox')
                                                <span class="badge badge-light badge-pill">{{ __('+') }} <span id="field{{ $field->key }}Amount">{{ ! empty($field->defaultOption) ? number_format($field->defaultOption->amount, 2) : number_format($field->amount, 2) }} €</span></span>
                                                <span class="badge badge-primary badge-pill">{{ __($field->value_prefix) }}<span id="field{{ $field->key }}Value">{{ ! empty($field->defaultOption) ? $field->defaultOption->value : $field->value }}</span>{{ __($field->value_suffix) }}</span>
                                            @else
                                                <span class="badge badge-light badge-pill">{{ __('+') }} <span id="field{{ $field->key }}Amount">0.00 €</span></span>
                                                <span class="badge badge-primary badge-pill">{{ __($field->value_prefix) }}<span id="field{{ $field->key }}Value">{{ $field->type == 'input_checkbox' ? __('No') : __('N/A') }}</span>{{ __($field->value_suffix) }}</span>
                                            @endif
                                            <br>
                                            <br>
                                        @else
                                            <br>
                                        @endif
                                    @endforeach
                                    <span class="d-block small">
                                        {{ __('* Required field') }}
                                    </span>
                                </div>
                            </div>
                        @elseif ($form->type)
                            <div class="alert alert-primary mb-0">
                                <i class="bi bi-info-circle"></i> {{ __('This product is pre-configured.') }}
                            </div>
                            @foreach ($form->fields as $field)
                                <input type="hidden" id="input_{{ $field->id }}" name="{{ $field->key }}" value="{{ $field->value }}">
                            @endforeach
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header text-decoration-none">
                                <i class="bi bi-file-earmark-text"></i> {{ __('Contract') }}
                            </div>
                            <div class="card-body mb-0">
                                @if ($form->contractType->type == 'prepaid_auto')
                                    <div class="alert alert-primary mb-3">
                                        <i class="bi bi-info-circle"></i> {{ __('A prepaid contract automatically renews for as long as you have sufficient funds on your account. It automatically ends as soon as your account has insufficient funds.') }}
                                    </div>
                                @endif
                                <div class="row">
                                    <label class="col-md-5 col-form-label text-md-right font-weight-bold">{{ __('interface.data.type') }}</label>

                                    <div class="col-md-7 col-form-label">
                                        @if ($form->contractType->type == 'contract_pre_pay')
                                            {{ __('Contract (pre-paid)') }}
                                        @elseif ($form->contractType->type == 'contract_post_pay')
                                            {{ __('Contract (post-paid)') }}
                                        @elseif ($form->contractType->type == 'prepaid_auto')
                                            {{ __('Prepaid (auto-renew)') }}
                                        @elseif ($form->contractType->type == 'prepaid_manual')
                                            {{ __('Prepaid (manual renew)') }}
                                        @else
                                            {{ __('Unknown') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-5 col-form-label text-md-right font-weight-bold">{{ __('Payment Cycle') }}</label>

                                    <div class="col-md-7 col-form-label">
                                        {{ $form->contractType->invoice_period }} {{ __('interface.units.days') }}
                                    </div>
                                </div>
                                @if ($form->contractType->type == 'contract_pre_pay' || $form->contractType->type == 'contract_post_pay')
                                    <div class="row">
                                        <label class="col-md-5 col-form-label text-md-right font-weight-bold">{{ __('Notice Period') }}</label>

                                        <div class="col-md-7 col-form-label">
                                            {{ $form->contractType->cancellation_period }} {{ __('interface.units.days') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header text-decoration-none">
                                <i class="bi bi-list"></i> {{ __('Summary') }}
                            </div>
                            <div class="card-body mb-0">
                                <table class="table">
                                    <thead class="font-weight-bold">
                                    <tr>
                                        <td>{{ __('interface.data.description') }}</td>
                                        <td>{{ __('Price') }}</td>
                                    </tr>
                                    </thead>
                                    <tbody id="positions">
                                    <tr>
                                        <td><span class="d-block font-weight-bold">{{ __($form->name) }}</span>{{ __($form->description) }}</td>
                                        <td>{{ number_format($form->baseAmount, 2) }} €</td>
                                    </tr>
                                    @foreach ($form->fields as $field)
                                        @if (! empty($option = $field->defaultOption))
                                            <tr class="position">
                                                <td><span class="d-block font-weight-bold">{{ __($field->label) }}</span>{{ $option->label }}</td>
                                                <td>{{ number_format($option->amount, 2) }} €</td>
                                            </tr>
                                        @endif
                                        @if ($field->type == 'input_range')
                                            <tr class="position">
                                                <td><span class="d-block font-weight-bold">{{ __($field->label) }}</span></td>
                                                <td>{{ number_format($field->value / $field->step * $field->amount, 2) }} €</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    <tfoot id="summary" class="bg-primary text-white">
                                    <tr>
                                        <td>{{ __('Net Amount') }}</td>
                                        <td id="netAmount">{{ number_format($form->defaultAmount, 2) }} €</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0">{{ __('VAT Amount') }} ({{ $form->vatRate }} %)</td>
                                        <td id="vatAmount" class="border-0">{{ number_format($form->defaultAmount * ($form->vatRate / 100), 2) }} €</td>
                                    </tr>
                                    <tr>
                                        <td class="border-0">{{ __('Gross Amount') }}</td>
                                        <td id="grossAmount" class="border-0">{{ number_format($form->defaultAmount * ((100 + $form->vatRate) / 100), 2) }} €</td>
                                    </tr>
                                    </tfoot>
                                </table>
                                @if (! empty($user = Auth::user()) && $user->role == 'customer')
                                    @php
                                        $acceptable = \App\Models\Content\Page::acceptable()->get();
                                    @endphp

                                    @if ($acceptable->isNotEmpty())
                                        @foreach ($acceptable as $accept)
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group bg-white border rounded py-2 px-3">
                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                <input id="accept_{{ $accept->id }}" type="checkbox" class="form-control" name="accept_{{ $accept->id }}" value="true">
                                                            </div>

                                                            <label for="accept_{{ $accept->id }}" class="col-md-10 col-form-label">{!! __('I confirm that I have read, understood the :link (Date: :date) and I agree to be bound to it.', ['link' => '<a href="' . (Route::has($accept->route) ? route($accept->route) : $accept->route) . '" target="_blank">' . __($accept->title) . '</a>', 'date' => $accept->latest->created_at->format('d.m.Y, H:i')]) !!} {{ __('*') }}</label>
                                                        </div>
                                                        @error('accept_' . $accept->id)
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-cart"></i> {{ __('Order now') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> {{ __('This product has no fields linked yet. It cannot be ordered.') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        function regenerateSummary() {
            let baseAmount = <?= $form->baseAmount ?>;
            let netAmount = baseAmount;

            $('.position').remove();

            @foreach ($form->fields as $field)
                @if ($field->type == 'input_checkbox' || $field->type == 'input_radio' || $field->type == 'input_radio_image')
                    let field{{ $field->key }}Value = $('[name="{{ $field->key }}"]:checked').val();
                @else
                    let field{{ $field->key }}Value = $('[name="{{ $field->key }}"]').val();
                @endif

                @if ($field->type == 'input_range')
                    let field{{ $field->key }}Multiplicator = field{{ $field->key }}Value / {{ $field->step }};
                    let field{{ $field->key }}Amount = field{{ $field->key }}Multiplicator * {{ $field->amount }};
                    let field{{ $field->key }}BadgeValue = field{{ $field->key }}Multiplicator * {{ $field->value }};

                    if (field{{ $field->key }}Amount > 0) {
                        netAmount = netAmount + field{{ $field->key }}Amount;

                        $('#positions').append('<tr class="position"><td><span class="d-block font-weight-bold">{{ __($field->label) }}</span></td><td>' + (field{{ $field->key }}Amount).toFixed(2) + ' €</td></tr>');
                    }

                    $('#field{{ $field->key }}Amount').html((field{{ $field->key }}Amount).toFixed(2) + ' €');
                    $('#field{{ $field->key }}Value').html(field{{ $field->key }}BadgeValue);
                @elseif ($field->type == 'input_checkbox')
                    if (typeof field{{ $field->key }}Value !== 'undefined' && {{ $field->amount }} > 0) {
                        $('#positions').append('<tr class="position"><td><span class="d-block font-weight-bold">{{ __($field->label) }}</span></td><td>' + ({{ $field->amount }}).toFixed(2) + ' €</td></tr>');
                        $('#field{{ $field->key }}Amount').html(({{ $field->amount }}).toFixed(2) + ' €');
                        $('#field{{ $field->key }}Value').html('{{ __('Yes') }}');
                        netAmount = netAmount + {{ $field->amount }};
                    } else {
                        $('#field{{ $field->key }}Amount').html((0).toFixed(2) + ' €');
                        $('#field{{ $field->key }}Value').html('{{ __('No') }}');
                    }
                @else
                    @if ($field->options->isNotEmpty())
                        @foreach ($field->options as $option)
                            if (field{{ $field->key }}Value === '{{ $option->value }}') {
                                if ({{ $option->amount }} > 0) {
                                    $('#positions').append('<tr class="position"><td><span class="d-block font-weight-bold">{{ __($field->label) }}</span>{{ $option->label }}</td><td>{{ number_format($option->amount, 2) }} €</td></tr>');
                                    netAmount = netAmount + {{ $option->amount }};
                                }

                                $('#field{{ $field->key }}Amount').html(({{ $option->amount }}).toFixed(2) + ' €');
                                $('#field{{ $field->key }}Value').html('{{ $option->value }}');
                            }
                        @endforeach
                    @endif
                @endif
            @endforeach

            netAmount = (Math.round(netAmount * 100) / 100).toFixed(2);
            let vatAmount = (Math.round(netAmount * {{ $form->vatRate }}) / 100).toFixed(2);
            let grossAmount = (Math.round(netAmount * (100 + {{ $form->vatRate }})) / 100).toFixed(2);

            $('#netAmount').html(netAmount + ' ' + '€');
            $('#vatAmount').html(vatAmount + ' ' + '€');
            $('#grossAmount').html(grossAmount + ' ' + '€');
        }

        $(window).on('load', function () {
            @if (! empty($form->fields->isNotEmpty()))
                @foreach ($form->fields as $field)
                    $('[name="{{ $field->key }}"]').on('change', function () {
                        regenerateSummary();
                    });

                    @if ($field->type == 'input_range')
                        $('[name="{{ $field->key }}"]').on('input', function () {
                            console.log('SLIDE');
                            regenerateSummary();
                        });
                    @endif
                @endforeach
            @endif
        });
    </script>
@endsection
