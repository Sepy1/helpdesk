<?php
// database/migrations/XXXX_XX_XX_XXXXXX_add_progress_note_to_tickets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('progress_note')->nullable()->after('it_id');
            $table->timestamp('progress_at')->nullable()->after('progress_note');
        });
    }

    public function down(): void {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['progress_note','progress_at']);
        });
    }
};
