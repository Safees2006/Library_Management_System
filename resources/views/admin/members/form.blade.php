@extends('layouts.app')

@section('title', $isEditing ? 'Edit member' : 'Add a member')

@section('content')
<div class="breadcrumb"><a href="{{ route('admin.members.index') }}">Members</a><span>/</span><span>{{ $isEditing ? 'Edit account' : 'New account' }}</span></div>
<section class="form-page-heading"><span class="eyebrow">Member account</span><h1>@if($isEditing)Update member <em>details.</em>@else Welcome a new <em>reader.</em>@endif</h1><p>Membership numbers and email addresses must be unique.</p></section>
<form class="panel card form-panel compact-form" method="POST" action="{{ $isEditing ? route('admin.members.update', $member) : route('admin.members.store') }}">
    @csrf @if($isEditing) @method('PUT') @endif
    <div class="form-fields row g-3">
        <label class="field field-wide col-12"><span>Full name <b>*</b></span><input name="name" value="{{ old('name', $member->name) }}" required class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Email address <b>*</b></span><input type="email" name="email" value="{{ old('email', $member->email) }}" required class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Membership number <b>*</b></span><input name="membership_number" value="{{ old('membership_number', $member->membership_number) }}" required class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Phone number</span><input name="phone" value="{{ old('phone', $member->phone) }}" placeholder="Optional" class="form-control"></label>
        @if($isEditing)<label class="field col-12 col-md-6"><span>Account status <b>*</b></span><select class="form-select" name="status" required><option value="active" @selected(old('status', $member->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $member->status) === 'inactive')>Inactive — borrowing blocked</option></select></label>@endif
        <label class="field col-12 col-md-6"><span>{{ $isEditing ? 'New password' : 'Temporary password' }} @if(!$isEditing)<b>*</b>@endif</span><input type="password" name="password" autocomplete="new-password" {{ $isEditing ? '' : 'required' }} minlength="8" class="form-control"><small>{{ $isEditing ? 'Leave blank to keep the current password.' : 'At least 8 characters.' }}</small></label>
        <label class="field col-12 col-md-6"><span>Confirm password @if(!$isEditing)<b>*</b>@endif</span><input type="password" name="password_confirmation" autocomplete="new-password" {{ $isEditing ? '' : 'required' }} class="form-control"></label>
    </div>
    <div class="form-actions"><a class="btn button button-quiet" href="{{ route('admin.members.index') }}">Cancel</a><button class="btn btn-success button button-dark" type="submit">{{ $isEditing ? 'Save member' : 'Create member account' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
</form>
@endsection
