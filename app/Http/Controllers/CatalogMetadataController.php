<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogMetadataController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('books')->orderBy('name')->get();
        $authors = Author::withCount('books')->orderBy('name')->get();

        return view('admin.catalog-data.index', compact('categories', 'authors'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:categories,name']]);
        Category::create($data);

        return back()->with('status', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)]]);
        $category->update($data);

        return back()->with('status', 'Category renamed. Existing book records were kept.');
    }

    public function storeAuthor(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:authors,name']]);
        Author::create($data);

        return back()->with('status', 'Author added.');
    }

    public function updateAuthor(Request $request, Author $author): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('authors', 'name')->ignore($author->id)]]);
        $author->update($data);

        return back()->with('status', 'Author renamed. Existing book records were kept.');
    }
}
