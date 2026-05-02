<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memory_page_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('short_code', 8)->unique();
            $table->integer('scan_count')->default(0);
            $table->string('png_path')->nullable();
            $table->string('svg_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
