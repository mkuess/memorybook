<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_page_id')->constrained()->cascadeOnDelete();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email');
            $table->string('reason');
            $table->text('description');
            $table->string('status')->default('open');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
