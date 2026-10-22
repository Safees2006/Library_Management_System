@extends('layouts.app')

@section('title', 'Explore the catalog')

@section('content')
<section class="catalog-hero">
    <div class="hero-copy">
        <span class="eyebrow eyebrow-light">A little time for a good book</span>
        <h1>Find your next<br><em>favorite</em></h1>
        <p>Browse the shelve discover a new perspective and see whats ready to borrow</p>
    </div>
    <div class="hero-art" aria-hidden="true"><span class="art-sun"></span><span class="art-book art-book-one"></span><span class="art-book art-book-two"></span><span class="art-book art-book-three"></span><span class="art-leaf">✳</span></div>
    <form class="catalog-search" action="{{ route('catalog') }}" method="GET">
        <label class="search-box"><span class="sr-only">Search books, authors ISBN or category</span><span class="search-icon">⌕</span><input name="q" type="search" placeholder="Search title, author, ISBN, or category" value="{{ request('q') }}" class="form-control"><button class="btn button button-cream" type="submit">Search <span>→</span></button></label>
        <div class="search-options">
            <label class="select-wrap"><span class="sr-only">Filter by category</span><select class="form-select" name="category" onchange="this.form.submit()"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label class="check-field check-field-light"><input type="checkbox" name="available" value="1" @checked(request()->boolean('available')) onchange="this.form.submit()" class="form-check-input"><span>Available now</span></label>
            @if (request()->hasAny(['q', 'category', 'available']))<a href="{{ route('catalog') }}" class="search-reset">Clear filters</a>@endif
        </div>
    </form>
</section>

<section class="section-block">
    <div class="section-heading">
        <div><span class="eyebrow">The collection</span><h2>{{ request('q') ? 'Search results' : 'A few good places to begin' }}</h2></div>
        <span class="muted">{{ $books->total() }} {{ $books->total() === 1 ? 'title' : 'titles' }}</span>
    </div>
    @if ($books->isEmpty())
        <div class="empty-state"><span class="empty-icon">⌕</span><h3>No books found</h3><p>Try another title author or category</p><a class="text-link" href="{{ route('catalog') }}">Reset search</a></div>
    @else
    <div class="book-grid row g-3">
            @foreach ($books as $book)
                <article class="book-card col-6 col-md-4 col-lg-3">
                    <a class="book-cover cover-tone-{{ $loop->iteration % 5 }} {{ $book->cover_image ? 'book-cover-with-image' : '' }}" href="{{ route('catalog.show', $book) }}" aria-label="View {{ $book->title }} details">
                        @if ($book->cover_image)<img class="book-cover-photo" src="{{ asset('storage/'.$book->cover_image) }}" alt="">@endif
                        <span class="cover-spark">✳</span><span class="cover-title">{{ $book->title }}</span><span class="cover-rule"></span><span class="cover-author">{{ $book->authors->pluck('name')->join(' · ') }}</span>
                    </a>
                    <div class="book-card-info"><div><span class="book-category">{{ $book->category?->name ?? 'General' }}</span><h3><a href="{{ route('catalog.show', $book) }}">{{ $book->title }}</a></h3><p>{{ $book->authors->pluck('name')->join(', ') ?: 'Author not listed' }}</p></div><span @class(['availability-dot', 'is-unavailable' => $book->available_copies_count === 0]) title="{{ $book->available_copies_count }} copies available"></span></div>
                    <div class="book-card-bottom"><span>{{ $book->publication_year ?: 'Edition details' }}</span><strong>{{ $book->available_copies_count }} <small>available</small></strong></div>
                </article>
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $books->links() }}</div>
    @endif
</section>
@endsection
