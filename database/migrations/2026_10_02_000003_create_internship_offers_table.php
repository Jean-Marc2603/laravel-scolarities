<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('internship_offers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('company');
            $table->string('domain');
            $table->string('location');
            $table->string('duration');
            $table->text('description');
            $table->json('skills');
            $table->date('deadline');
            $table->text('details');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'deadline']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('internship_offers');
    }
};
