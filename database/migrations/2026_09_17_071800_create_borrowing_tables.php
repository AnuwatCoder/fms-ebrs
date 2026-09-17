<?php

use App\Enums\BorrowItemStatus;
use App\Enums\BorrowRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_sequences', function (Blueprint $table) {
            $table->string('period', 6)->primary();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('purpose');
            $table->string('usage_location')->nullable();
            $table->date('borrow_date')->index();
            $table->date('expected_return_date')->index();
            $table->string('status', 40)->default(BorrowRequestStatus::Draft->value)->index();
            $table->text('note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expected_return_date']);
        });

        Schema::create('borrow_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->string('status', 40)->default(BorrowItemStatus::Draft->value)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['borrow_request_id', 'equipment_id']);
            $table->index(['equipment_id', 'status']);
        });

        Schema::create('borrow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->string('action', 30);
            $table->text('comment')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['borrow_request_id', 'acted_at']);
        });

        Schema::create('equipment_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_item_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('checked_out_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('checked_out_at')->index();
            $table->text('condition_before');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrow_request_item_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('returned_at')->index();
            $table->text('condition_after');
            $table->string('return_status', 40)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('equipment_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->foreignId('borrow_request_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 30)->index();
            $table->text('description');
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('reported_at')->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->index(['equipment_id', 'resolved_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100)->index();
            $table->nullableMorphs('subject');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('equipment_incidents');
        Schema::dropIfExists('equipment_returns');
        Schema::dropIfExists('equipment_checkouts');
        Schema::dropIfExists('borrow_approvals');
        Schema::dropIfExists('borrow_request_items');
        Schema::dropIfExists('borrow_requests');
        Schema::dropIfExists('request_sequences');
    }
};
