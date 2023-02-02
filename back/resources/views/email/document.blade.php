@extends('email.emailTemplate')
@section('content')
<h3>Please see attached patient correspondence.</h3>

Sent Via Aurora Medical Software on behalf of {{ $organizationName }}
@endsection