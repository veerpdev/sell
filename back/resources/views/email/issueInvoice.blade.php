    <style>
        .user td {
            background: #3E7BA0 !important;
            color: #ffffff;
        }

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
    @extends('email.emailTemplate')
    @section('content')
        <div class="payment-info">
            <h3>Here's your invoice</h3>

            <p>Please find attached your invoice for your {{ $appointment->date }} appointment.</p>

            <div>
                <p class="payment-header">Invoice Number</p>
                <h3>{{ $invoice_number }}</h3>
            </div>
        </div>
    @endsection
