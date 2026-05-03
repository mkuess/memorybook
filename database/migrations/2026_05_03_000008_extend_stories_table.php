<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('content');
            $table->string('visitor_email')->nullable()->after('image_path');
            $table->string('visitor_token', 100)->nullable()->unique()->after('visitor_email');
            $table->timestamp('visitor_token_expires_at')->nullable()->after('visitor_token');
            $table->boolean('is_visitor_submission')->default(false)->after('visitor_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'visitor_email',
                'visitor_token',
                'visitor_token_expires_at',
                'is_visitor_submission',
            ]);
        });
    }
};
