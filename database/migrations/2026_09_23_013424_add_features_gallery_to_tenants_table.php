<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // مزايا المحل: قائمة عناصر [{icon, title}]
            $table->json('features')->nullable()->after('description');
            // معرض الصور: قائمة مسارات صور مخزنة بـ storage/public
            $table->json('gallery_paths')->nullable()->after('cover_path');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['features', 'gallery_paths']);
        });
    }
};