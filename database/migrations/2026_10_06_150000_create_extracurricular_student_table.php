<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['extracurricular_id', 'student_id']);
        });

        // Salin pilihan eskul lama. Kolom lama students.extracurricular_id sengaja dibiarkan.
        DB::table('students')
            ->whereNotNull('extracurricular_id')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                DB::table('extracurricular_student')->insert(
                    $rows->map(fn ($r) => [
                        'extracurricular_id' => $r->extracurricular_id,
                        'student_id' => $r->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_student');
    }
};