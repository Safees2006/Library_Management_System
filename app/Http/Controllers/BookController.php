<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BookController extends Controller
{
    public function catalog(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'available' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $query = Book::active()
            ->with(['authors', 'category'])
            ->withCount('copies')
            ->withCount(['copies as available_copies_count' => fn ($copies) => $copies->where('status', BookCopy::AVAILABLE)]);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($books) use ($search): void {
                $books->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('publisher', 'like', "%{$search}%")
                    ->orWhereHas('authors', fn ($authors) => $authors->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn ($categories) => $categories->where('name', 'like', "%{$search}%"));
            });
        }

        if ($category = $request->integer('category')) {
            $query->where('category_id', $category);
        }

        if ($request->boolean('available')) {
            $query->whereHas('copies', fn ($copies) => $copies->where('status', BookCopy::AVAILABLE));
        }

        $books = $query->orderBy('title')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('catalog.index', compact('books', 'categories'));
    }

    public function show(Book $book): View
    {
        abort_if($book->archived_at, 404);
        $book->load(['authors', 'category']);
        $book->loadCount('copies');
        $book->loadCount(['copies as available_copies_count' => fn ($copies) => $copies->where('status', BookCopy::AVAILABLE)]);

        return view('catalog.show', compact('book'));
    }

    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'archived' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $query = Book::query()
            ->with(['authors', 'category'])
            ->withCount('copies')
            ->withCount(['copies as available_copies_count' => fn ($copies) => $copies->where('status', BookCopy::AVAILABLE)]);

        if ($request->query('archived') !== '1') {
            $query->whereNull('archived_at');
        } else {
            $query->whereNotNull('archived_at');
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($books) => $books->where('title', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhereHas('authors', fn ($authors) => $authors->where('name', 'like', "%{$search}%")));
        }

        $books = $query->orderBy('title')->paginate(10)->withQueryString();

        return view('admin.books.index', compact('books'));
    }

    public function create(): View
    {
        return view('admin.books.form', ['book' => new Book(), 'isEditing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $barcodes = $this->parseBarcodes($data['copy_barcodes'] ?? '', true);
        $this->ensureBarcodesAreUnique($barcodes);
        $coverImage = $this->storeCoverImage($request);
        $data['cover_image'] = $coverImage;

        try {
            DB::transaction(function () use ($data, $barcodes): void {
                $book = Book::create($this->bookAttributes($data));
                $this->saveAuthors($book, $data['authors']);
                $this->saveCopies($book, $barcodes);
            });
        } catch (Throwable $exception) {
            if ($coverImage) {
                Storage::disk('public')->delete($coverImage);
            }

            throw $exception;
        }

        return redirect()->route('admin.books.index')->with('status', 'Book and copies added to the catalog.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', ['book' => $book->load(['authors', 'category']), 'isEditing' => true]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate($this->rules($book));
        $barcodes = $this->parseBarcodes($data['copy_barcodes'] ?? '', false);
        $this->ensureBarcodesAreUnique($barcodes);
        $previousCoverImage = $book->cover_image;
        $newCoverImage = $this->storeCoverImage($request);
        $data['cover_image'] = $newCoverImage ?? $previousCoverImage;

        try {
            DB::transaction(function () use ($data, $barcodes, $book): void {
                $book->update($this->bookAttributes($data));
                $this->saveAuthors($book, $data['authors']);
                $this->saveCopies($book, $barcodes);
            });
        } catch (Throwable $exception) {
            if ($newCoverImage) {
                Storage::disk('public')->delete($newCoverImage);
            }

            throw $exception;
        }

        if ($newCoverImage && $previousCoverImage) {
            Storage::disk('public')->delete($previousCoverImage);
        }

        return redirect()->route('admin.books.index')->with('status', 'Book details updated.');
    }

    public function archive(Book $book): RedirectResponse
    {
        $wasArchived = $book->archived_at !== null;
        $book->update(['archived_at' => $wasArchived ? null : now()]);

        return back()->with('status', $wasArchived ? 'Book restored to the catalog.' : 'Book archived. Loan history was kept.');
    }

    private function rules(?Book $book = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:30', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'category_name' => ['nullable', 'string', 'max:100'],
            'authors' => ['required', 'string', 'max:1000'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'between:1450,2100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'copy_barcodes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    private function storeCoverImage(Request $request): ?string
    {
        if (! $request->hasFile('cover_image')) {
            return null;
        }

        $path = $request->file('cover_image')->store('book-covers', 'public');

        if (! $path) {
            throw ValidationException::withMessages(['cover_image' => 'The cover image could not be saved. Please try again.']);
        }

        return $path;
    }

    private function bookAttributes(array $data): array
    {
        $categoryName = trim((string) ($data['category_name'] ?? ''));
        $categoryId = $categoryName === '' ? null : Category::firstOrCreate(['name' => $categoryName])->id;

        return [
            'title' => trim($data['title']),
            'isbn' => filled($data['isbn'] ?? null) ? trim($data['isbn']) : null,
            'category_id' => $categoryId,
            'publisher' => filled($data['publisher'] ?? null) ? trim($data['publisher']) : null,
            'publication_year' => $data['publication_year'] ?? null,
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'cover_image' => $data['cover_image'] ?? null,
        ];
    }

    private function saveAuthors(Book $book, string $authorText): void
    {
        $names = collect(preg_split('/[,\r\n]+/', $authorText) ?: [])
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => Str::lower($name));

        if ($names->isEmpty()) {
            throw ValidationException::withMessages(['authors' => 'Enter at least one author name.']);
        }

        $ids = $names->map(fn (string $name) => Author::firstOrCreate(['name' => $name])->id)->all();
        $book->authors()->sync($ids);
    }

    private function parseBarcodes(string $text, bool $required): array
    {
        $barcodes = collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn (string $barcode) => trim($barcode))
            ->filter()
            ->values();

        if ($required && $barcodes->isEmpty()) {
            throw ValidationException::withMessages(['copy_barcodes' => 'Add at least one physical copy barcode.']);
        }

        if ($barcodes->count() !== $barcodes->unique(fn (string $barcode) => Str::lower($barcode))->count()) {
            throw ValidationException::withMessages(['copy_barcodes' => 'Each barcode must be unique.']);
        }

        if ($barcodes->contains(fn (string $barcode) => mb_strlen($barcode) > 80)) {
            throw ValidationException::withMessages(['copy_barcodes' => 'Each barcode must be 80 characters or fewer.']);
        }

        if ($barcodes->count() > 100) {
            throw ValidationException::withMessages(['copy_barcodes' => 'Add no more than 100 copies in one request.']);
        }

        return $barcodes->all();
    }

    private function ensureBarcodesAreUnique(array $barcodes): void
    {
        if ($barcodes && BookCopy::whereIn('barcode', $barcodes)->exists()) {
            throw ValidationException::withMessages(['copy_barcodes' => 'A barcode is already in use.']);
        }
    }

    private function saveCopies(Book $book, array $barcodes): void
    {
        foreach ($barcodes as $barcode) {
            $book->copies()->create(['barcode' => $barcode, 'status' => BookCopy::AVAILABLE]);
        }
    }
}
