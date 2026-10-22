<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->index();
            $table->string('isbn', 30)->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('publisher')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('author_book', function (Blueprint $table): void {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->restrictOnDelete();
            $table->primary(['book_id', 'author_id']);
        });

        Schema::create('book_copies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->string('barcode', 80)->unique();
            $table->string('status', 20)->default('available')->index();
            $table->timestamps();
            $table->index(['book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('author_book');
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('authors');
    }
};
