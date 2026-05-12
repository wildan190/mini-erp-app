<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('project_uuid');
            $table->uuid('parent_task_uuid')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('assigned_employee_uuid')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->string('status')->default('todo'); // todo, in_progress, review, done
            $table->boolean('is_milestone')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_uuid')->references('uuid')->on('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_tasks');
    }
};
