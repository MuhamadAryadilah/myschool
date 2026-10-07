<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'registration_id'], 'eskul_registrasi_unik');
        });

        // Salin pilihan eskul lama. Kolom lama registrations.extracurricular_id sengaja dibiarkan.
        $rows = DB::table('registrations')
            ->whereNotNull('extracurricular_id')
            ->get(['id', 'extracurricular_id'])
            ->map(fn ($r) => [
                'registration_id' => $r->id,
                'extracurricular_id' => $r->extracurricular_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            DB::table('extracurricular_registration')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_registration');
    }
};