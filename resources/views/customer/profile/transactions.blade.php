@extends('layouts.customer')

@section('content')
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body h1 mb-0">
                        {{ number_format(Auth::user()->prepaidAccountBalance, 2) }} €
                    </div>
                    <div class="card-footer text-decoration-none">
                        <i class="bi bi-bank2"></i> {{ __('interface.data.account_balance') }}
                        <i class="bi bi-arrow-right float-right"></i>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-plus-circle"></i> {{ __('interface.actions.deposit_money') }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('customer.profile.transactions.deposit') }}" method="post">
                            @csrf
                            <div class="form-group row mb-4">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input id="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="typeSuffix">€</span>
                                        </div>
                                    </div>

                                    @error('amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @foreach ($paymentMethods as $paymentMethod)
                                <div class="payment-method-container mb-3">
                                    <input type="radio" name="payment_method" id="payment_method_{{ $paymentMethod->technicalName() }}" value="{{ $paymentMethod->technicalName() }}">
                                    @if (! empty($icon = $paymentMethod->icon()))
                                        <label for="payment_method_{{ $paymentMethod->technicalName() }}">
                                            <img src="{{ $icon }}" class="icon"></label>
                                        </label>
                                    @else
                                        <label for="payment_method_{{ $paymentMethod->technicalName() }}">{{ $paymentMethod->name() }}</label>
                                    @endif
                                </div>
                            @endforeach
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> {{ __('interface.actions.deposit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <i class="bi bi-list"></i> {{ __('interface.data.account_transactions') }}
                    </div>
                    <div class="card-body">
                        <table id="transactions" class="table mt-4 w-100">
                            <thead>
                            <tr>
                                <td>{{ __('interface.data.date') }}</td>
                                <td>{{ __('interface.data.contract_number') }}</td>
                                <td>{{ __('interface.misc.invoice_no') }}</td>
                                <td>{{ __('interface.data.amount') }}</td>
                                <td>{{ __('interface.data.transaction_method') }}</td>
                                <td>{{ __('interface.data.transaction_no') }}</td>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(window).on('load', function () {
            $('#transactions').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/customer/profile/list/transactions',
                columns: [
                    { data: 'date' },
                    { data: 'contract_id' },
                    { data: 'invoice_id' },
                    { data: 'amount', sWidth: '10%' },
                    { data: 'transaction_method', sWidth: '20%' },
                    { data: 'transaction_id', sWidth: '20%' },
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endsection
