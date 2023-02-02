@extends('pdfs.layout')

@section('title', "Invoice #{$full_invoice_number}")

@section('styles')
<style>
    .summary-table .summary-row {
        padding: 6px;
        padding-top: 12px;
        max-width: 180px;
        margin-left: auto;
        text-align: right;
        border-top: 1px solid lightgray;
    }

    .summary-table .summary-row:last-of-type {
        border-bottom: 1px solid lightgray;
    }

    .summary-table .summary-row div {
        display: inline-block;
    }

    .summary-table .summary-row .amount {
        min-width: 80px;
    }

    .item-table th,
    .item-table td {
        padding: 8px;
        line-height: 0.8;
        border: 1px solid #1d1d1d;
    }

    .item-table th {
        background-color: lightgray;
    }

    .payment-table th,
    .payment-table td {
        padding: 7px;
        border-top: 1px solid #aaa;
        border-bottom: 1px solid #aaa;
        text-align: center;
    }

    .payment-table th {
        border-top: 0;
    }
</style>
@endsection

@section('content')
    <section>
        <h1 style="margin-bottom:30px;">Invoice #{{ $full_invoice_number }}</h1>

        <div>
            <div style="display:inline-block;width:48%;padding-right:10px;">
                <div style="margin-bottom: 10px;">
                    @if ($bill_from === 'CLINIC')
                        {{ $organization->name }}<br>
                        <span class="heading">Provider Number:</span> {{ $clinic->hospital_provider_number }}
                    @elseif ($bill_from === 'SPECIALIST')
                        {{ $appointment->specialist_name }}<br>
                        <span class="heading">Provider Number:</span> {{ $provider_number }}
                    @endif
                </div>

                <div>
                    {{ $clinic->name }}<br>
                    {{ $clinic->address }}<br>
                    <span class="heading">Phone:</span> {{ $clinic->phone_number }}<br>
                    <span class="heading">Fax:</span> {{ $clinic->fax_number }}
                </div>
            </div>

            <div style="display:inline-block;width:48%;padding-left:10px;text-align:right;">
                <h4 class="heading upper">Bill To</h4>

                <div>
                    {{ $patient->full_name }}<br>
                    {{ $patient->address }}<br>
                    {{ $patient->suburb }} {{ $patient->postcode }}<br>
                    {{ $patient->email }}
                </div>
            </div>
        </div>
    </section>

    <section style="margin-top:-20px">
        <div>
            <h4 class="heading upper" style="margin-bottom:20px;">Appointment Details</h4>
        </div>

        <div>
            <div style="width:48%;display:inline-block;padding-right:10px;">
                <span class="heading">Patient:</span> {{ $patient->full_name }}<br>
                <span class="heading">DOB:</span> {{ $patient->date_of_birth }}<br>
                <span class="heading">Medicare:</span>
                {{ $medicareCard ? $medicareCard->member_number . $medicareCard->member_reference_number : 'N/A' }}
            </div>

            <div style="width:48%;display:inline-block;padding-left:10px;text-align:right;">
                <span class="heading">Specialist:</span>
                {{ $appointment->specialist_name }}<br>
                <span class="heading">Referred By:</span>
                {{ $referral->doctor_address_book_name }}<br>
                <span class="heading">Appointment Date:</span>
                {{ $appointment->date }}
            </div>
        </div>
    </section>

    <section>
        <table class="item-table" style="width: 100%;border-spacing:0;border-collapse:collapse;" aria-describedby="Item overview table">
            <tr>
                <th>
                    Date
                </th>
                <th style="text-align: left;">
                    Item
                </th>
                <th>
                    Price
                </th>
            </tr>

            @foreach ($items as $item)
                <tr>
                    <td style="text-align: center;">
                        {{ Carbon\Carbon::create($appointment->date)->format('j M y') }}
                    </td>
                    <td>
                        {{ $item['label_name'] }}
                    </td>
                    <td style="text-align: right;">
                        ${{ number_format($item['price'] / 100, 2) }}
                    </td>
                </tr>
            @endforeach
        </table>
    </section>

    <section style="margin-top: 20px">
        <div style="width: 100%;">
            <div class="summary-table">
                <div class="summary-row">
                    <div class="info-header heading">
                        Total Fee
                    </div>
                    <div class="amount">
                        ${{ number_format($totalCost / 100, 2) }}
                    </div>
                </div>

                @if ($total_paid > 0)
                    <div class="summary-row">
                        <div class="info-header heading">
                            Paid to Date
                        </div>
                        <div class="amount">
                            ${{ number_format($total_paid / 100, 2) }}
                        </div>
                    </div>
                @endif

                @if (isset($payment))
                    <div class="summary-row">
                        <div class="info-header heading">
                            This Payment
                        </div>
                        <div class="amount" {!! $payment->amount < 0 ? 'style="color:red;"' : '' !!}>
                                ${{ number_format($payment['amount'] / 100, 2) }}
                        </div>
                    </div>
                @endif

                <div class="summary-row">
                    <div class="info-header heading">
                        Balance Due
                    </div>

                    @if (isset($payment))
                        <div class="amount">
                            ${{ number_format(($totalCost - $total_paid - $payment['amount']) / 100, 2) }}
                        </div>
                    @else
                        <div class="amount">
                            ${{ number_format(($totalCost - $total_paid) / 100, 2) }}
                        </div>
                    @endif
                </div>
                </table>
            </div>
    </section>

    <section>
        <h4 class="gray-heading" style="text-align:center;">Payment Information</h4>

        <table class="payment-table" style="width: 100%;border-spacing:0;border-collapse:collapse;" aria-describedby="Previous payments">
            <tr>
                <th>
                    Date
                </th>
                <th>
                    Type
                </th>
                <th>
                    Payer
                </th>
                <th>
                    Amount
                </th>
            </tr>
            @foreach ($payments as $pay)
                <tr>
                    <td {!! isset($payment) && $pay->id === $payment->id ? 'style="background-color:#ddd;font-weight:bold;"' : '' !!}>
                        {{ Carbon\Carbon::create($pay->created_at)->format('j M y') }}
                    </td>
                    <td {!! isset($payment) && $pay->id === $payment->id ? 'style="background-color:#ddd;font-weight:bold;"' : '' !!}>
                        {{ $pay->amount < 0 ? 'REFUND' : $pay->payment_type }}
                    </td>
                    <td {!! isset($payment) && $pay->id === $payment->id ? 'style="background-color:#ddd;font-weight:bold;"' : '' !!}>
                        Patient
                    </td>
                    <td {!! isset($payment) && $pay->id === $payment->id ? 'style="background-color:#ddd;font-weight:bold;"' : '' !!}>
                        <span {!! $pay->amount < 0 ? 'style="color:red;"' : '' !!}>${{ number_format($pay->amount / 100, 2) }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
    </section>

    <section>
        @if ($bill_from === 'CLINIC')
            <p style="text-align: center; font-size: 12px;">
                {{ strlen($organization->abn_acn) === 9 ? 'ACN:' : 'ABN:' }} {{ $organization->formatted_abn_acn }}
            </p>
        @else
            <p style="text-align: center; font-size: 12px;">
                {{ strlen($specialist->abn_acn) === 9 ? 'ACN:' : 'ABN:' }} {{ $specialist->formatted_abn_acn }}
            </p>
        @endif
    </section>
@endsection