<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // ساعات عمل المحل العامة (تظهر بصفحة الحجز): [{day, enabled, start, end}]
            $table->json('working_hours')->nullable()->after('gallery_paths');

            // موقع المحل
            $table->string('map_address')->nullable()->after('working_hours');
            $table->string('map_url')->nullable()->after('map_address'); // رابط جوجل مابس (مشاركة الموقع)

            // روابط السوشال ميديا: {facebook, instagram, tiktok, snapchat}
            $table->json('social_links')->nullable()->after('map_url');

            // تقييمات الزبائن: [{name, text, rating}]
            $table->json('testimonials')->nullable()->after('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['working_hours', 'map_address', 'map_url', 'social_links', 'testimonials']);
        });
    }
};