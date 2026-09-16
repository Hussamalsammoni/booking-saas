<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('primary_color')->default('#4f46e5');
            $table->text('description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'cover_path', 'primary_color', 'description']);
        });
    }
};