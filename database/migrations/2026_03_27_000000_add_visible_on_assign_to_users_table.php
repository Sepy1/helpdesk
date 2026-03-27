<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'visible_on_assign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('visible_on_assign')->default(true)->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'visible_on_assign')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('visible_on_assign');
            });
        }
    }
};
