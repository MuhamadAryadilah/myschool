<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('extracurricular_id')->nullable()->after('kelas')->constrained()->nullOnDelete();
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('extracurricular_id')->nullable()->after('previous_school')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('extracurricular_id');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('extracurricular_id');
        });
    }
};