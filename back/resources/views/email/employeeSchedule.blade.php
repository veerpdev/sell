@extends('email.emailTemplate')
@section('content')
    <h3>Please note your published working schedule as bellow</h3>

    @foreach ($period as $day)
        <div class="slot">
            <span class="date">{{ $day->format('Y-m-d') }}</span>
            @php($isShiftFound = false)

            @foreach ($user->hrmWeeklySchedule as $shift)
                @if ($shift->date == $day->format('Y-m-d'))
                    @php($isShiftFound = true)
                    <div class="details">
                        <span>{{ $shift->start_time }} to </span>
                        <span>{{ $shift->end_time }}</span>
                        <br />
                        <span class="clinic-name">{{ $shift->clinic_name }}</span>
                    </div>
                @endif
            @endforeach
            @if (!$isShiftFound)
                <span>Not working</span>
            @endif
        </div>
    @endforeach
@endsection
