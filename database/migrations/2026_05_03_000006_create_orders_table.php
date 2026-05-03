<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('memory_page_id')->constrained()->cascadeOnDelete();
            $table->string('package');
            $table->string('status')->default('requested');
            $table->string('billing_name');
            $table->string('billing_email');
            $table->text('billing_address');
            $table->string('billing_postal_code');
            $table->string('billing_city');
            $table->string('billing_country')->default('Österreich');
            $table->timestamp('consent_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
