@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<section class="page-heading page-heading-row">
    <div>
        <span class="eyebrow">Administrator account</span>
        <h1>My <em>profile.</em></h1>
        <p>Your account details for The Reading Room library.</p>
    </div>
    <a class="btn button button-quiet" href="{{ route('admin.dashboard') }}">Back to dashboard</a>
</section>

<section class="admin-panel admin-account-profile" aria-labelledby="profile-details-title">
    <div class="admin-panel-heading">
        <div><span class="admin-section-kicker">ACCOUNT DETAILS</span><h2 id="profile-details-title">Profile information</h2></div>
        <span class="admin-profile-role">Administrator</span>
    </div>
    <dl class="admin-profile-details">
        <div><dt>Name</dt><dd>{{ auth()->user()->name }}</dd></div>
        <div><dt>Email address</dt><dd>{{ auth()->user()->email }}</dd></div>
        <div><dt>Account type</dt><dd>Library administrator</dd></div>
    </dl>
</section>
@endsection
