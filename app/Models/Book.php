<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title', 'isbn', 'category_id', 'publisher', 'publication_year', 'description', 'cover_image', 'archived_at',
    ];

    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }
}
