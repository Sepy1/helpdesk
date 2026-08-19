<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_announcements', function (Blueprint $table) {
            $table->boolean('desktop_active')->default(true)->after('is_active');
            $table->boolean('mobile_active')->default(true)->after('desktop_active');
        });

        \DB::table('popup_announcements')->whereNull('desktop_active')->update(['desktop_active' => true]);
        \DB::table('popup_announcements')->whereNull('mobile_active')->update(['mobile_active' => true]);
    }

    public function down(): void
    {
        Schema::table('popup_announcements', function (Blueprint $table) {
            $table->dropColumn(['desktop_active', 'mobile_active']);
        });
    }
};
