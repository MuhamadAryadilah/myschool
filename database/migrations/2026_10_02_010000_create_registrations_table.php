<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('menunggu');

            $table->string('student_name');
            $table->string('gender', 1);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->string('nisn', 10)->nullable();
            $table->string('religion');
            $table->text('address');
            $table->string('previous_school');

            $table->string('father_name');
            $table->string('mother_name');
            $table->string('guardian_name');
            $table->string('guardian_relation');
            $table->string('guardian_job')->nullable();
            $table->string('phone', 30);
            $table->string('email');

            $table->string('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};