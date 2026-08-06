<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. DYNAMIC RBAC TABLES ──────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module'); // hrm, crm, finance, purchasing, project, inventory, system
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('model_type'); // e.g. App\Models\User
            $table->uuid('model_uuid');
            $table->primary(['role_id', 'model_type', 'model_uuid']);
            $table->index(['model_type', 'model_uuid']);
        });

        // ── 2. MULTI-TIER APPROVAL ENGINE TABLES ───────────────────────────
        Schema::create('approval_chains', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('module'); // purchasing, hrm, inventory, finance, project
            $table->string('model_type'); // e.g. App\Domain\Purchasing\Models\PurchaseRequest
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('approval_chain_id')->constrained('approval_chains')->onDelete('cascade');
            $table->integer('step_order');
            $table->enum('approver_type', ['role', 'user', 'department_manager'])->default('role');
            $table->uuid('approver_uuid')->nullable(); // Role UUID, User UUID, or null for manager
            $table->boolean('is_final_step')->default(false);
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('approval_chain_id')->constrained('approval_chains')->onDelete('cascade');
            $table->string('approvable_type');
            $table->uuid('approvable_uuid');
            $table->foreignId('requester_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('current_step_order')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_uuid']);
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->onDelete('cascade');
            $table->integer('step_order');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['approved', 'rejected', 'commented']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_chains');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
