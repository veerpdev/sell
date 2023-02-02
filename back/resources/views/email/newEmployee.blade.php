@extends('email.emailTemplate')
@section('content')
    <div style="background: #3E7BA0; color: #ffffff">
        <h1>Hi, {{ $user->full_name }}</h1>
        <h3>On behalf of {{ $organizationName }}, welcome to Aurora medical software.</h3>
    </div>
    To get started, please follow the the link below to sign in:<br />
    <a href="https://aurorasw.com.au/">https://aurorasw.com.au/</a><br /><br />
    <strong>Username:</strong> {{ $user->username }}<br />
    <strong>Password:</strong> {{ $rawPassword }}<br />
@endsection
