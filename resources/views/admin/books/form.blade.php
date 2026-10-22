@extends('layouts.app')

@section('title', $isEditing ? 'Edit book' : 'Add a book')

@section('content')
<div class="breadcrumb"><a href="{{ route('admin.books.index') }}">Books</a><span>/</span><span>{{ $isEditing ? 'Edit title' : 'Add a title' }}</span></div>
<section class="form-page-heading"><span class="eyebrow">Catalog record</span><h1>@if($isEditing)Edit book <em>details.</em>@else Add to the <em>collection.</em>@endif</h1><p>Enter the title information and give each physical copy its own barcode.</p></section>
<form class="panel card form-panel" method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.books.update', $book) : route('admin.books.store') }}">
    @csrf @if($isEditing) @method('PUT') @endif
    <div class="form-section"><div><span class="form-step">01</span><div><h2>About this title</h2><p>Bibliographic information shown in the public catalog.</p></div></div><div class="form-fields row g-3">
        <label class="field field-wide col-12"><span>Book title <b>*</b></span><input name="title" value="{{ old('title', $book->title) }}" maxlength="255" required class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Author or authors <b>*</b></span><input name="authors" value="{{ old('authors', $book->exists ? $book->authors->pluck('name')->join(', ') : '') }}" placeholder="e.g. Virginia Woolf, James Joyce" required class="form-control"><small>Separate multiple authors with a comma.</small></label>
        <label class="field col-12 col-md-6"><span>Category</span><input name="category_name" list="category-options" value="{{ old('category_name', $book->category?->name) }}" placeholder="e.g. Fiction" class="form-control"><datalist id="category-options">@foreach(\App\Models\Category::orderBy('name')->get() as $category)<option value="{{ $category->name }}">@endforeach</datalist><small>Choose an existing category or type a new one.</small></label>
        <label class="field col-12 col-md-6"><span>ISBN</span><input name="isbn" value="{{ old('isbn', $book->isbn) }}" maxlength="30" placeholder="Optional" class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Publisher</span><input name="publisher" value="{{ old('publisher', $book->publisher) }}" maxlength="255" placeholder="Optional" class="form-control"></label>
        <label class="field col-12 col-md-6"><span>Publication year</span><input name="publication_year" type="number" min="1450" max="2100" value="{{ old('publication_year', $book->publication_year) }}" placeholder="Optional" class="form-control"></label>
        <label class="field field-wide col-12"><span>Description</span><textarea class="form-control" name="description" rows="4" maxlength="5000" placeholder="A short description for readers">{{ old('description', $book->description) }}</textarea></label>
        <div class="field field-wide col-12 cover-upload-field">
            <label for="cover-image"><span>Book cover image</span></label>
            <div class="cover-upload-row">
                <div class="cover-upload-preview" id="cover-upload-preview" @if(!$book->cover_image) hidden @endif>
                    <img id="cover-preview-image" src="{{ $book->cover_image ? asset('storage/'.$book->cover_image) : '' }}" alt="Book cover preview">
                </div>
                <div class="cover-upload-control">
                    <input id="cover-image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
                    <small>Optional. JPG, PNG, or WebP, up to 5 MB. On edit, selecting a new image replaces the current cover.</small>
                </div>
            </div>
        </div>
    </div></div>
    <div class="form-section"><div><span class="form-step">02</span><div><h2>Physical copies</h2><p>Use a distinct barcode for each copy so checkouts can be tracked accurately.</p></div></div><div class="form-fields row g-3">
        @if($isEditing)<div class="field field-wide col-12 stock-summary"><span>Current stock</span><strong>{{ $book->copies()->where('status', 'available')->count() }} available of {{ $book->copies()->count() }} total</strong><small>Existing copy records are preserved. Add new barcodes below as needed.</small></div>@endif
        <label class="field field-wide col-12"><span>{{ $isEditing ? 'Add more copy barcodes' : 'Copy barcodes' }} @if(!$isEditing)<b>*</b>@endif</span><textarea class="form-control" name="copy_barcodes" rows="4" placeholder="RR-000101&#10;RR-000102">{{ old('copy_barcodes') }}</textarea><small>One barcode per line. {{ $isEditing ? 'Leave blank if no copies are being added.' : 'At least one barcode is required.' }}</small></label>
    </div></div>
    <div class="form-actions"><a class="btn button button-quiet" href="{{ route('admin.books.index') }}">Cancel</a><button class="btn btn-success button button-dark" type="submit">{{ $isEditing ? 'Save changes' : 'Add book to catalog' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
</form>
@endsection
