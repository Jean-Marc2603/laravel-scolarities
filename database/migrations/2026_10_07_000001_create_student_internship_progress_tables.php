<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_application_id')->unique()->constrained('internship_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('internship_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_internship_id')->constrained('student_internships')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('planned_date')->nullable();
            $table->timestamps();

            $table->unique(['student_internship_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_tasks');
        Schema::dropIfExists('student_internships');
    }
};
