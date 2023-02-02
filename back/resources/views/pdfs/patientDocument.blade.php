@extends('pdfs.layout')
@section('title', $title)

@section('styles')
    <style>
        .document-section p {
            padding: 0;
            margin: 0;
            line-height: 1.1;
        }
    </style>
@endsection

@section('content')
    @if ($headerImage)
        <header>
            <img src="{{ storage_path('app/' . $headerImage) }}" style="width: 100%;" alt="">
        </header>
    @else
        <header style="padding-bottom:30px;">
            <div style="text-align:right;">
                @if ($organization->logo)
                    <div>
                        <img src="{{ storage_path('app/' . $organization->logo) }}" style="width:150px;margin-left:auto;"
                            alt="">
                    </div>
                @endif
                <p style="margin-top:10px;line-height:1.1;padding-bottom:0;margin-bottom:0;">
                    {{ $organization->name }}<br>
                    {{ $clinic->name }}<br>
                    {{ $clinic->phone_number }}<br>
                    {{ $clinic->address }}

            </div>
            </div>
        </header>
    @endif

    <section>
        <h2 style="margin:0;padding:0;padding-bottom:5px;"><strong>{{ $title }}</strong></h2>

        <p style="line-height:1.1;padding:0;margin:0;">
            <strong>Date:</strong> {{ $date }}<br>
            <strong>RE:</strong> {{ $documentType === 'REFERRAL' ? 'Referral for ' : '' }}{{ $patientName }}<br>
            <strong>Date of Birth:</strong> {{ $patientDob }}
        </p>
    </section>

    @foreach ($documentSections as $section)
        <section class="document-section">
            <h3 style="margin-bottom:7px;"><strong>{{ $section['title'] }}</strong></h3>

            <p>{!! $section['text'] !!}</p>
        </section>
    @endforeach

    @if ($documentType === 'REPORT')
        @if ($proceduresUndertaken !== null || $extraItemsUsed !== null)
            <section class="document-section">
                <h3 style="margin-bottom:7px;"><strong>Procedures Performed</strong></h3>

                <ul>
                    @foreach ($proceduresUndertaken as $procedure)
                        <li>
                            {{ $procedure['name'] }}
                        </li>
                    @endforeach

                    @foreach ($extraItemsUsed as $item)
                        <li>
                            {{ $item['name'] }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif

    <section style="padding-top:10px;">
        <div>
            <p>{{ $signOff }}</p>
        </div>
        @if ($signatureImage)
            <img src="{{ storage_path('app/' . $signatureImage) }}" style="width:150px;padding:10px 0;"
                alt="{{ $fullName }} signature">
        @endif
        <div>
            <p>
                <strong>{{ $fullName }}</strong><br>
                Provider Number {{ $providerNumber }}
            </p>
        </div>
    </section>

    @if ($footerImage)
        <footer>
            <img src="{{ storage_path('app/' . $footerImage) }}" style="width: 100%;" alt="{{ $title }}">
        </footer>
    @endif
@endsection
