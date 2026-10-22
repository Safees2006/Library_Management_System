@extends('layouts.app')

@section('title', 'Manage books')

@section('content')
<section class="page-heading page-heading-row"><div><span class="eyebrow">Collection management</span><h1>Books <em>& copies.</em></h1><p>Catalog titles and track every physical copy.</p></div><a class="btn btn-success button button-dark" href="{{ route('admin.books.create') }}">Add a book <span>＋</span></a></section>
<section class="panel card list-panel">
    <div class="list-toolbar"><form class="inline-search" method="GET"><span>⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search title, author, ISBN" class="form-control"><button class="btn btn-outline-success button button-outline button-small" type="submit">Search</button></form><a class="btn button button-quiet button-small" href="{{ route('admin.catalog-data.index') }}">Authors & categories</a><a class="btn button button-quiet button-small" href="{{ route('admin.books.index', request('archived') === '1' ? [] : ['archived' => 1]) }}">{{ request('archived') === '1' ? 'Show active titles' : 'Show archived' }}</a></div>
    @if ($books->isEmpty())<div class="empty-state"><span class="empty-icon">▤</span><h3>No titles here yet</h3><p>Add your first book to start the collection.</p><a class="btn btn-success button button-dark" href="{{ route('admin.books.create') }}">Add a book <span>＋</span></a></div>@else
    <div class="table-scroll table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Title / ISBN</th><th>Author</th><th>Category</th><th>Copies</th><th>Catalog</th><th></th></tr></thead><tbody>
    @foreach ($books as $book)<tr><td><a class="table-primary" href="{{ $book->archived_at ? route('admin.books.edit', $book) : route('catalog.show', $book) }}">{{ $book->title }}</a><small>{{ $book->isbn ?: 'No ISBN' }}</small></td><td>{{ $book->authors->pluck('name')->join(', ') ?: '—' }}</td><td>{{ $book->category?->name ?? 'General' }}</td><td><strong>{{ $book->available_copies_count }}</strong> / {{ $book->copies_count }} <small>available</small></td><td><span @class(['status-pill', 'status-muted' => $book->archived_at, 'status-active' => ! $book->archived_at])>{{ $book->archived_at ? 'Archived' : 'Active' }}</span></td><td class="table-actions"><a class="btn button button-quiet button-small" href="{{ route('admin.books.edit', $book) }}">Edit</a><form method="POST" action="{{ route('admin.books.archive', $book) }}" data-confirm="{{ $book->archived_at ? 'Restore this title to the public catalog?' : 'Archive this title? Existing loan history will be kept.' }}">@csrf @method('PATCH')<button class="btn button button-quiet button-small" type="submit">{{ $book->archived_at ? 'Restore' : 'Archive' }}</button></form></td></tr>@endforeach
    </tbody></table></div><div class="pagination-wrap">{{ $books->links() }}</div>
    @endif
</section>
@endsection
