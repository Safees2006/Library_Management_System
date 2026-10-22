# The Reading Room — Library Management System

A Laravel and MySQL library app with a public book catalog, administrator tools, member accounts, circulation, and fine tracking. The interface uses Blade, HTML, CSS, and a small amount of JavaScript.

## Requirements

- PHP 8.2 or newer with the PDO MySQL extension enabled
- Composer
- MySQL or MariaDB (XAMPP includes MariaDB)
- Node.js and npm for compiling the frontend assets

This repository currently uses Laravel 12. Check `composer.json` before changing framework or PHP versions.

## Local setup on Windows / XAMPP

1. Start MySQL from the XAMPP Control Panel and create a database named `library_management_system` in phpMyAdmin.
2. If this is a fresh checkout, copy `.env.example` to `.env`. If `.env` already exists, keep it and update its database settings instead of overwriting it.
3. Set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=library_management_system`, `DB_USERNAME=root`, and the local MySQL password in `.env`.
4. Install dependencies and initialize the app:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   php artisan serve
   ```

5. Open `http://127.0.0.1:8000` to browse the catalog. For frontend changes, run `npm run dev` in a second terminal while the Laravel server is running.

Never copy real credentials into `.env.example` or commit `.env` to a public repository.

## Sample accounts

The database seeder creates sample accounts for local development:

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@library.test` | `ChangeMe123!` |
| Member | `amina@example.test` | `Reader123!` |
| Member | `kavindu@example.test` | `Reader123!` |
| Member | `nimali@example.test` | `Reader123!` |

Set `LIBRARY_ADMIN_EMAIL` and `LIBRARY_ADMIN_PASSWORD` in `.env` to override the administrator credentials. These demo credentials are for local development only. Change them before using a non-local environment.

## Current features

- Public catalog search by title, author, ISBN, and category, with availability filters.
- Administrator dashboard, book and physical-copy management, member accounts, loans, returns, and fine-payment history.
- Member pages showing that member’s active and past loans, due dates, and fine balances.
- Server-side role checks, form validation, CSRF-protected write routes, and hashed passwords.
- Stock changes and loan creation inside database transactions.
- Books are archived instead of deleted, so their loan history remains available.

## Library defaults

Defaults are in `config/library.php` and can be overridden in `.env`:

- Maximum active loans per member: 3 (`LIBRARY_BORROWING_LIMIT`)
- Standard loan duration: 14 days (`LIBRARY_LOAN_DURATION_DAYS`)
- Daily overdue fine: LKR 50.00 (`LIBRARY_FINE_RATE_PER_DAY`)
- Currency display: LKR (`LIBRARY_CURRENCY`)

Each physical copy has a unique barcode and availability status. The loan stores the daily fine rate that applied when it was issued. The due date itself is not late; an overdue fine counts calendar days after that date. Active loans show an estimate, and the fine amount is finalized on return. Payments are stored as separate records and reduce the unpaid balance without erasing history.

## Data model

- `users` stores administrator and member accounts, including membership number, role, contact number, and active status.
- `books` stores title-level catalog information; authors and categories are separate related records.
- `book_copies` stores each physical copy and its barcode.
- `loans` connects a member to one physical copy and records issue, due, return, and fine details.
- `fine_payments` stores payment amount, date, receiving administrator, and optional note.

Online reservations, online payment gateways, barcode hardware, messaging, multi-branch support, and third-party integrations are not part of this version.
