<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_incidents', function (Blueprint $table): void {
            $table->foreignId('resolved_by')
                ->nullable()
                ->after('resolved_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_incidents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('resolved_by');
        });
    }
};
