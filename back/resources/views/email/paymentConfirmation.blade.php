<style>
    .payment-info {
        text-align: center;
    }

    .payment-info p {
        font-size: 14px;
    }

    .payment-info div {
        margin-top: 30px;
    }

    .payment-info .payment-header {
        color: gray;
        text-transform: uppercase;
        font-weight: bold;
    }

    .payment-info .payment-data {
        margin: 7px 0;
    }
</style>
</head>
@extends('email.emailTemplate')
@section('content')
    <div class="payment-info">
        @if ($payment->amount > 0)
            <h3>Thanks for your payment</h3>

            <p>Your payment has been received. Please find confirmation of the details below, and your invoice
                attached.</p>

            <div>
                <p class="payment-header">Payment Amount</p>
                <h3>${{ number_format($payment->amount / 100, 2) }}</h3>
            </div>
        @else
            <h3>Here's your refund</h3>

            <p>Please find confirmation of your refund below, and your invoice attached.</p>

            <div>
                <p class="payment-header">Refund Amount</p>
                <h3 style="color:green;">${{ number_format($payment->amount / 100, 2) }}</h3>
            </div>
        @endif

        <div>
            <p class="payment-header">Invoice Number</p>
            <h3>{{ $payment->full_invoice_number }}</h3>
        </div>

        <div>
            <p class="payment-header">Payment Date</p>
            <h3>{{ $payment->created_at->format('d-m-Y') }}</h3>
        </div>
    </div>
@endsection
