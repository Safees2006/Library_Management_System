@extends('layouts.app')

@section('title', 'Issue a book')

@section('content')
<div class="breadcrumb"><a href="{{ route('admin.loans.index') }}">Loans</a><span>/</span><span>Issue a book</span></div>
<section class="form-page-heading"><span class="eyebrow">Circulation desk</span><h1>Make a new <em>loan.</em></h1><p>Choose an active member and a title with an available physical copy.</p></section>
<form class="panel card form-panel compact-form" method="POST" action="{{ route('admin.loans.store') }}">
    @csrf
    @if($members->isEmpty() || $books->isEmpty())<div class="flash flash-error">@if($members->isEmpty())Create an active member before issuing a book. @endif @if($books->isEmpty())Add a book with an available copy before issuing. @endif</div>@endif
    <div class="form-fields row g-3">
        <label class="field field-wide col-12"><span>Member <b>*</b></span><select class="form-select" name="member_id" required><option value="">Select member</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->name }} · {{ $member->membership_number }}</option>@endforeach</select><small>Inactive members are not shown.</small></label>
        <label class="field field-wide col-12"><span>Book title <b>*</b></span><select class="form-select" name="book_id" required><option value="">Select an available title</option>@foreach($books as $book)<option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }} · {{ $book->available_copies_count }} available</option>@endforeach</select><small>The system assigns a physical copy and records its barcode.</small></label>
        <label class="field col-12 col-md-6"><span>Issue date <b>*</b></span><input type="date" name="issue_date" value="{{ old('issue_date', $issueDate) }}" required class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Due date <b>*</b></span><input type="date" name="due_date" value="{{ old('due_date', $dueDate) }}" required class="form-control"><small>Default loan period: {{ config('library.loan_duration_days') }} days.</small></label>
    </div>
    <div class="form-note note-box">Borrowing limit: {{ config('library.borrowing_limit') }} active loans per member. Daily overdue fine: {{ config('library.currency') }} {{ number_format(config('library.fine_rate_per_day'), 2) }}.</div>
    <div class="form-actions"><a class="btn button button-quiet" href="{{ route('admin.loans.index') }}">Cancel</a><button class="btn btn-success button button-dark" type="submit" @disabled($members->isEmpty() || $books->isEmpty())>Confirm checkout <span>→</span></button></div>
</form>
@endsection
