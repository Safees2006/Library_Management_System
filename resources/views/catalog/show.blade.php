@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="breadcrumb"><a href="{{ route('catalog') }}">Catalog</a><span>/</span><span>{{ $book->title }}</span></div>
<section class="detail-layout">
    <div class="detail-cover book-cover cover-tone-{{ $book->id % 5 }} {{ $book->cover_image ? 'book-cover-with-image' : '' }}">
        @if ($book->cover_image)<img class="book-cover-photo" src="{{ asset('storage/'.$book->cover_image) }}" alt="Cover of {{ $book->title }}">@endif
        <i class="cover-spark bi bi-stars" aria-hidden="true"></i><span class="cover-title">{{ $book->title }}</span><span class="cover-rule"></span><span class="cover-author">{{ $book->authors->pluck('name')->join(' &middot; ') }}</span>
    </div>
    <div class="detail-copy">
        <span class="eyebrow">{{ $book->category?->name ?? 'From the collection' }}{{ $book->publication_year ? ' · '.$book->publication_year : '' }}</span>
        <h1>{{ $book->title }}</h1>
        <p class="detail-author">by {{ $book->authors->pluck('name')->join(', ') ?: 'Author not listed' }}</p>
        <div class="detail-availability"><span class="availability-large {{ $book->available_copies_count ? '' : 'is-unavailable' }}"></span><div><strong>{{ $book->available_copies_count ? 'Ready to borrow' : 'Currently all out' }}</strong><span>{{ $book->available_copies_count }} of {{ $book->copies_count }} copies available</span></div></div>
        @if ($book->description)<p class="detail-description">{{ $book->description }}</p>@else<p class="detail-description muted">A little more about this title will be added soon.</p>@endif
        <dl class="book-facts"><div><dt>Publisher</dt><dd>{{ $book->publisher ?: 'Not listed' }}</dd></div><div><dt>ISBN</dt><dd>{{ $book->isbn ?: 'Not listed' }}</dd></div><div><dt>Category</dt><dd>{{ $book->category?->name ?? 'General' }}</dd></div></dl>
        @guest<a class="btn btn-success button button-dark" href="{{ route('login') }}">Member sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@else<a class="btn btn-success button button-dark" href="{{ auth()->user()->isAdmin() ? route('admin.loans.create') : route('member.account') }}">{{ auth()->user()->isAdmin() ? 'Issue a copy' : 'View my account' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endguest
        <p class="form-note">Book checkout and returns are handled at the library desk.</p>
    </div>
</section>
@endsection
