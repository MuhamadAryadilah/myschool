<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('semester', 10)->nullable()->after('due_date');
            $table->string('academic_year', 9)->nullable()->after('semester');
            $table->index(['academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['academic_year', 'semester']);
            $table->dropColumn(['semester', 'academic_year']);
        });
    }
};