<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table): void {
            $table->timestamp('terms_accepted_at')->nullable()->after('submitted_at');
            $table->string('terms_version', 30)->nullable()->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table): void {
            $table->dropColumn(['terms_accepted_at', 'terms_version']);
        });
    }
};
