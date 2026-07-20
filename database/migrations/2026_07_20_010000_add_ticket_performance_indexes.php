<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'tickets_status_created_at_idx');
            $table->index(['user_id', 'created_at'], 'tickets_user_created_at_idx');
            $table->index(['it_id', 'status', 'created_at'], 'tickets_it_status_created_at_idx');
            $table->index(['vendor_id', 'status', 'created_at'], 'tickets_vendor_status_created_at_idx');
            $table->index(['category_id', 'subcategory_id', 'created_at'], 'tickets_category_subcategory_created_at_idx');
            $table->index(['seen_by_reporter_at'], 'tickets_seen_by_reporter_at_idx');
            $table->index(['seen_by_it_at'], 'tickets_seen_by_it_at_idx');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at'], 'ticket_comments_ticket_created_at_idx');
        });

        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->index(['ticket_id', 'created_at'], 'ticket_histories_ticket_created_at_idx');
            $table->index(['action', 'created_at'], 'ticket_histories_action_created_at_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_at_idx');
            $table->index(['type', 'created_at'], 'notifications_type_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['kode_kantor'], 'users_kode_kantor_idx');
            $table->index(['role', 'visible_on_assign'], 'users_role_visible_on_assign_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_visible_on_assign_idx');
            $table->dropIndex('users_kode_kantor_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_type_created_at_idx');
            $table->dropIndex('notifications_notifiable_read_at_idx');
        });

        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->dropIndex('ticket_histories_action_created_at_idx');
            $table->dropIndex('ticket_histories_ticket_created_at_idx');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex('ticket_comments_ticket_created_at_idx');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_seen_by_it_at_idx');
            $table->dropIndex('tickets_seen_by_reporter_at_idx');
            $table->dropIndex('tickets_category_subcategory_created_at_idx');
            $table->dropIndex('tickets_vendor_status_created_at_idx');
            $table->dropIndex('tickets_it_status_created_at_idx');
            $table->dropIndex('tickets_user_created_at_idx');
            $table->dropIndex('tickets_status_created_at_idx');
        });
    }
};
