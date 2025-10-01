<?php
// database/migrations/xxxx_xx_xx_add_attachment_to_ticket_comments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('body');
        });
    }

    public function down(): void {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
