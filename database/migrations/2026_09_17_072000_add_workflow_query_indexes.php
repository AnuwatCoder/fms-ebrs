<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->index(['active', 'status'], 'equipment_active_status_index');
        });

        Schema::table('borrow_requests', function (Blueprint $table): void {
            $table->index(['status', 'submitted_at'], 'borrow_requests_status_submitted_index');
            $table->index(['status', 'borrow_date'], 'borrow_requests_status_borrow_date_index');
        });

        Schema::table('equipment_incidents', function (Blueprint $table): void {
            $table->index(['type', 'resolved_at'], 'equipment_incidents_type_resolved_index');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['causer_id', 'created_at'], 'audit_logs_causer_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_causer_created_index');
        });

        Schema::table('equipment_incidents', function (Blueprint $table): void {
            $table->dropIndex('equipment_incidents_type_resolved_index');
        });

        Schema::table('borrow_requests', function (Blueprint $table): void {
            $table->dropIndex('borrow_requests_status_submitted_index');
            $table->dropIndex('borrow_requests_status_borrow_date_index');
        });

        Schema::table('equipment', function (Blueprint $table): void {
            $table->dropIndex('equipment_active_status_index');
        });
    }
};
