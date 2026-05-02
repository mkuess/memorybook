<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memory_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 8)->unique();
            $table->string('person_name');
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->text('short_bio')->nullable();
            $table->string('visibility')->default('private');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('consent_confirmed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memory_pages');
    }
};
