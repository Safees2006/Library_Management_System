<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => env('LIBRARY_ADMIN_EMAIL', 'admin@library.test')], [
            'name' => 'Library Administrator',
            'role' => 'admin',
            'status' => 'active',
            'password' => env('LIBRARY_ADMIN_PASSWORD', 'ChangeMe123!'),
        ]);

        foreach ([
            ['name' => 'Amina Perera', 'email' => 'amina@example.test', 'membership_number' => 'RR-1001'],
            ['name' => 'Kavindu Silva', 'email' => 'kavindu@example.test', 'membership_number' => 'RR-1002'],
            ['name' => 'Nimali Fernando', 'email' => 'nimali@example.test', 'membership_number' => 'RR-1003'],
        ] as $member) {
            User::firstOrCreate(['email' => $member['email']], [
                ...$member,
                'role' => 'member',
                'status' => 'active',
                'password' => 'Reader123!',
            ]);
        }

        $books = [
            ['title' => 'The Garden of Small Beginnings', 'isbn' => '978-1-23456-001-1', 'category' => 'Fiction', 'author' => 'Ira Wijesekara', 'publisher' => 'Ceylon House', 'year' => 2021, 'barcodes' => ['RR-2001', 'RR-2002']],
            ['title' => 'An Island of Quiet Mornings', 'isbn' => '978-1-23456-002-8', 'category' => 'Essays', 'author' => 'Maya Senanayake', 'publisher' => 'Paper Kite Press', 'year' => 2023, 'barcodes' => ['RR-2003', 'RR-2004', 'RR-2005']],
            ['title' => 'The Mapmaker’s Lantern', 'isbn' => '978-1-23456-003-5', 'category' => 'Fantasy', 'author' => 'Dilan Peris', 'publisher' => 'Ceylon House', 'year' => 2020, 'barcodes' => ['RR-2006']],
            ['title' => 'A Field Guide to Everyday Birds', 'isbn' => '978-1-23456-004-2', 'category' => 'Nature', 'author' => 'S. Jayawardene', 'publisher' => 'Greenline Books', 'year' => 2022, 'barcodes' => ['RR-2007', 'RR-2008']],
            ['title' => 'The Art of Starting Again', 'isbn' => '978-1-23456-005-9', 'category' => 'Personal Growth', 'author' => 'Leena Rodrigo', 'publisher' => 'Paper Kite Press', 'year' => 2019, 'barcodes' => ['RR-2009']],
            ['title' => 'Letters from the South Coast', 'isbn' => '978-1-23456-006-6', 'category' => 'Fiction', 'author' => 'N. Amarasinghe', 'publisher' => 'Greenline Books', 'year' => 2024, 'barcodes' => ['RR-2010', 'RR-2011']],
        ];

        foreach ($books as $record) {
            $category = Category::firstOrCreate(['name' => $record['category']]);
            $book = Book::firstOrCreate(['isbn' => $record['isbn']], [
                'title' => $record['title'],
                'category_id' => $category->id,
                'publisher' => $record['publisher'],
                'publication_year' => $record['year'],
                'description' => 'A sample catalog entry for the Reading Room library project.',
                'archived_at' => null,
            ]);
            $book->authors()->syncWithoutDetaching([Author::firstOrCreate(['name' => $record['author']])->id]);

            foreach ($record['barcodes'] as $barcode) {
                BookCopy::firstOrCreate(['barcode' => $barcode], [
                    'book_id' => $book->id,
                    'status' => BookCopy::AVAILABLE,
                ]);
            }
        }
    }
}
