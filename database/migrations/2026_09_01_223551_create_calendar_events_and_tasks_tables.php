<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Calendar Events Table
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('category')->default('meeting'); // meeting, interview, milestone, holiday, general
            $table->string('color')->default('#3B82F6'); // HEX color for visual calendar badge
            $table->string('status')->default('scheduled'); // scheduled, ongoing, completed, cancelled
            $table->string('attendees')->nullable(); // comma-separated or json of tagged employees
            $table->timestamps();
        });

        // 2. Calendar Tasks Table
        Schema::create('calendar_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->string('category')->default('general'); // recruitment, finance, crm, dev, general
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_tasks');
        Schema::dropIfExists('calendar_events');
    }
};
