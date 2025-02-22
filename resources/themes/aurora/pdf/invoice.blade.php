<html>
<head>
    <style>
        @page {
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 0.9rem;
            padding: 25mm 0;
        }

        header {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            text-align: right;
        }

        header h1 {
            margin-top: 0;
        }

        header img.logo {
            height: 10mm;
        }

        address {
            font-style: normal;
            margin-top: 20mm;
            width: 85mm;
        }

        h1 {
            font-size: 1.4rem;
            margin-top: 15mm;
            margin-bottom: 10mm;
        }

        p.top {
            margin-bottom: 10mm;
        }

        p.bottom {
            margin-top: 10mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td {
            padding: 0.75rem;
            vertical-align: top;
            border: none;
            border-top: 1px solid {{ config('theme.gray', '#F3F9FC') }};
        }

        table tfoot {
            page-break-inside: avoid;
        }

        table tfoot > * {
            page-break-after: avoid;
            page-break-before: avoid;
        }

        table tfoot > *:first-child {
            page-break-before: auto;
        }

        .bg-primary {
            background-color: {{ config('theme.primary', '#040E29') }};
        }

        .text-white {
            color: #fff !important;
        }

        .border-0 {
            border: 0 !important;
        }

        .bg-disabled {
            background-color: {{ config('theme.gray', '#F3F9FC') }};
        }

        .d-block {
            display: block;
        }

        small, .small {
            font-size: 0.7rem;
        }

        .mb-3 {
            margin-bottom:1rem!important
        }

        .font-weight-bold {
            font-weight: bold;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        footer table td {
            width: 33.33%;
            padding: 0;
            border-top: none;
        }

        .sepa-qr .image,
        .sepa-qr .sepa-qr-col {
            width: 30mm !important;
        }

        .sepa-qr {
            border-left: 1px solid {{ config('theme.gray', '#F3F9FC') }};
            border-right: 1px solid {{ config('theme.gray', '#F3F9FC') }};
        }

        .sepa-qr tr td {
            border-bottom: 1px solid {{ config('theme.gray', '#F3F9FC') }};
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <header>
        @if (! empty($logo = config('company.logo')))
            <img src="{{ $logo }}" class="logo">
        @else
            <h1>{{ config('company.name') }}</h1>
        @endif
    </header>

    <footer class="small">
        <table>
            <tbody>
            <tr>
                <td>
                    <span class="font-weight-bold">{{ config('company.name') }}</span><br>
                    {{ config('company.address.street') }} {{ config('company.address.housenumber') }}<br>
                    @if (! empty($addition = config('company.address.addition')))
                        {{ $addition }}<br>
                    @endif
                    {{ config('company.address.postalcode') }} {{ config('company.address.city') }}<br>
                    {{ config('company.address.state') }}, {{ config('company.address.country') }}<br>
                </td>
                <td>
                    <span class="font-weight-bold">{{ __('interface.documents.register_court') }}:</span> {{ config('company.register_court') }}<br>
                    <span class="font-weight-bold">{{ __('interface.documents.register_number') }}:</span> {{ config('company.register_number') }}<br>
                    <span class="font-weight-bold">{{ __('interface.documents.tax_number') }}:</span> {{ config('company.tax_id') }}<br>
                    <span class="font-weight-bold">{{ __('interface.documents.vat_number') }}:</span> {{ config('company.vat_id') }}
                </td>
                <td>
                    <span class="font-weight-bold">{{ __('interface.bank_account.iban') }}:</span> {{ config('company.bank.iban') }}<br>
                    <span class="font-weight-bold">{{ __('interface.bank_account.bic') }}:</span> {{ config('company.bank.bic') }}<br>
                    <span class="font-weight-bold">{{ __('interface.bank_account.institute') }}:</span> {{ config('company.bank.institute') }}
                </td>
            </tr>
            </tbody>
        </table>
    </footer>

    <main>
        @if (! empty($profile = $invoice->user->profile) && ! empty($address = $profile->billingPostalAddress))
            <address>
                <span class="d-block small mb-3">{{ config('company.address.oneliner') }}</span>
                @if (! empty($profile->company))
                    {{ $profile->company }}<br>
                @endif
                {{ $profile->firstname }} {{ $profile->lastname }}<br>
                {{ $address->street }} {{ $address->housenumber }}<br>
                @if (! empty($address->addition))
                    {{ $address->addition }}<br>
                @endif
                {{ $address->postalcode }} {{ $address->city }}<br>
                {{ $address->state }}, {{ $address->country->name }}
            </address>
        @else
            <address>
                <span class="d-block small mb-3">{{ config('company.address.oneliner') }}</span>
                {{ $invoice->user->name }}<br>
                {{ $invoice->user->email }}<br>
                <br>
            </address>
        @endif
        <h1>{{ $invoice->status == 'refund' ? __('interface.misc.refund_invoice') : __('interface.misc.invoice') }} {{ $invoice->number }}</h1>
        <p class="top">
            <span class="font-weight-bold">{{ __('interface.units.date') }}:</span> {{ $invoice->archived_at->format('d.m.Y') }}
            @if (! empty($invoice->original) && $invoice->status == 'refund')
                <br>
                <span class="font-weight-bold">{{ __('interface.documents.invoice_number') }}:</span> {{ $invoice->original->number }}<br>
                <span class="font-weight-bold">{{ __('interface.documents.invoice_date') }}:</span> {{ $invoice->original->archived_at->format('d.m.Y') }}
            @endif
        </p>
        <table id="dunnings" class="table mt-4 w-100">
            <thead>
            <tr>
                <td>{{ __('interface.documents.position') }}</td>
                <td style="width: 10%">{{ __('interface.documents.net_unit') }}</td>
                <td style="width: 10%">{{ __('interface.documents.units') }}</td>
                <td style="width: 10%">{{ __('interface.documents.net_position') }}</td>
                <td style="width: 10%">{{ __('interface.documents.vat') }}</td>
                <td style="width: 10%">{{ __('interface.documents.gross_position') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach ($invoice->positionLinks as $link)
                <tr>
                    <td>
                        <span class="font-weight-bold">{{ $link->position->name }}</span><br>
                        {{ $link->position->description }}
                        <small class="d-block">
                            @if (isset($link->started_at))
                                {{ __('interface.time.from') }}: {{ $link->started_at->format('d.m.Y H:i') }}
                            @endif
                            @if (isset($link->started_at, $link->ended_at))
                                |
                            @endif
                            @if (isset($link->ended_at))
                                {{ __('interface.time.to') }}: {{ $link->ended_at->format('d.m.Y H:i') }}
                            @endif
                        </small>
                    </td>
                    <td style="width: 10%">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($link->position->amount, 2) }} €</td>
                    <td style="width: 10%">{{ $link->position->quantity }}</td>
                    <td style="width: 10%">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($link->position->netSum, 2) }} €</td>
                    <td style="width: 10%" class="bg-disabled">{{ $link->position->vat_percentage }} %</td>
                    <td style="width: 10%" class="bg-disabled">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($link->position->grossSum, 2) }} €</td>
                </tr>
                @if (! empty($discount = $link->position->discount))
                    @switch ($discount->type)
                        @case ('percentage')
                        <tr>
                            <td>
                                <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                {{ number_format($discount->amount, 2) }} %
                            </td>
                            <td style="width: 10%">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($link->position->amount * ($discount->amount / 100), 2) }} €</td>
                            <td style="width: 10%">{{ $link->position->quantity }}</td>
                            <td style="width: 10%">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($link->position->netSum * ($discount->amount / 100), 2) }} €</td>
                            <td style="width: 10%" class="bg-disabled">{{ $link->position->vat_percentage }} %</td>
                            <td style="width: 10%" class="bg-disabled">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($link->position->grossSum * ($discount->amount / 100), 2) }} €</td>
                        </tr>
                        @break
                        @case ('fixed')
                        @default
                        <tr>
                            <td>
                                <span class="font-weight-bold">{{ __('interface.data.discount') }}</span><br>
                                {{ number_format($discount->amount, 2) }} €
                            </td>
                            <td style="width: 10%">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($discount->amount, 2) }} €</td>
                            <td style="width: 10%">{{ $link->position->quantity }}</td>
                            <td style="width: 10%">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($discount->amount * $link->position->quantity, 2) }} €</td>
                            <td style="width: 10%" class="bg-disabled">{{ $link->position->vat_percentage }} %</td>
                            <td style="width: 10%" class="bg-disabled">{{ $invoice->status == 'refund' ? '' : '-' }}{{ number_format($discount->amount * $link->position->quantity * (1 + ($link->position->vat_percentage / 100)), 2) }} €</td>
                        </tr>
                        @break
                    @endswitch
                @endif
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td colspan="2" class="bg-primary text-white">
                    {{ __('interface.documents.net_sum') }}
                </td>
                <td class="bg-primary text-white">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($invoice->netSum, 2) }} €</td>
                <td style="width: 10%"></td>
                <td style="width: 10%"></td>
            </tr>
            @foreach ($invoice->vatPositions as $percentage => $amount)
                <tr>
                    <td class="border-0"></td>
                    <td colspan="2" class="bg-primary text-white border-0">
                        {{ $percentage }} % {{ __('interface.documents.vat') }}
                    </td>
                    <td class="bg-primary text-white border-0">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($amount, 2) }} €</td>
                </tr>
            @endforeach
            <tr>
                <td class="border-0"></td>
                <td colspan="2" class="bg-primary text-white border-0">
                    {{ __('interface.documents.gross_sum') }}
                </td>
                <td class="bg-primary text-white border-0">{{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($invoice->grossSum, 2) }} €</td>
            </tr>
            </tfoot>
        </table>
        <p class="bottom">
            <span class="font-weight-bold">{{ __('interface.documents.deadline') }}:</span> {{ $invoice->type->period }} {{ __('interface.units.days') }}<br>
            <span class="font-weight-bold">{{ $invoice->status == 'refund' ? __('interface.documents.refunded_until') : __('interface.documents.payable_until') }}:</span> {{ $invoice->archived_at->addDays($invoice->type->period)->format('d.m.Y') }}<br>
            @if (! empty($discount = $invoice->type->discount))
                <span class="font-weight-bold">{{ __('interface.data.discount') }}</span> {{ $discount->percentage_amount }} % {{ __('interface.documents.invoice_discount_when') }} {{ $discount->period }} {{ __('interface.units.days') }}<br>
            @endif
            @if ($invoice->reverse_charge)
                <br><span class="font-weight-bold">{{ __('interface.documents.reverse_charge_notice') }}:</span> {{ __('interface.documents.reverse_charge_liability') }}<br>
            @endif
        </p>
        @if (! empty($sepaQr))
            <div class="sepa-qr">
                <table>
                    <tr>
                        <td class="sepa-qr-col"><img src="{{ $sepaQr }}" class="image"></td>
                        <td>
                            <span class="font-weight-bold">{{ __('interface.bank_account.iban') }}:</span> {{ config('company.bank.iban') }}<br>
                            <span class="font-weight-bold">{{ __('interface.bank_account.bic') }}:</span> {{ config('company.bank.bic') }}<br>
                            <span class="font-weight-bold">{{ __('interface.bank_account.institute') }}:</span> {{ config('company.bank.institute') }}<br>
                            <span class="font-weight-bold">{{ __('interface.bank_account.recipient') }}:</span> {{ config('company.bank.owner') }}<br>
                            <span class="font-weight-bold">{{ __('interface.bank_account.purpose') }}:</span> {{ $invoice->number }}<br>
                            <span class="font-weight-bold">{{ __('interface.data.amount') }}:</span> {{ $invoice->status !== 'refund' ? '' : '-' }}{{ number_format($invoice->grossSum, 2) }} €
                        </td>
                    </tr>
                </table>
            </div>
        @endif
    </main>
</body>
</html>
