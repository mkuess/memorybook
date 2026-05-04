<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->string('subject')->nullable()->after('reporter_email');
            $table->string('category')->default('profile_report')->after('subject');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('memory_page_id')->nullable()->change();
            $table->string('reason')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'subject', 'category']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('memory_page_id')->nullable(false)->change();
            $table->string('reason')->nullable(false)->change();
        });
    }
};
