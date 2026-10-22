<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('member')->index();
            $table->string('membership_number', 40)->nullable()->unique();
            $table->string('phone', 40)->nullable();
            $table->string('status', 20)->default('active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['membership_number']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['role', 'membership_number', 'phone', 'status']);
        });
    }
};
