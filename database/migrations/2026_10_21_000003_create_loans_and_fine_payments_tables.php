<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('book_copy_id')->constrained('book_copies')->restrictOnDelete();
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->decimal('fine_rate', 10, 2)->default(50);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->timestamps();
            $table->index(['member_id', 'status']);
            $table->index(['status', 'due_date']);
        });

        Schema::create('fine_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loan_id')->constrained()->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['loan_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_payments');
        Schema::dropIfExists('loans');
    }
};
