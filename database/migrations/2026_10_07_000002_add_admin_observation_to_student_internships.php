<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_internships') && ! Schema::hasColumn('student_internships', 'admin_observation')) {
            Schema::table('student_internships', function (Blueprint $table) {
                $table->text('admin_observation')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_internships') && Schema::hasColumn('student_internships', 'admin_observation')) {
            Schema::table('student_internships', function (Blueprint $table) {
                $table->dropColumn('admin_observation');
            });
        }
    }
};
