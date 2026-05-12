<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_timesheets', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('project_uuid');
            $table->uuid('task_uuid')->nullable();
            $table->uuid('employee_uuid');
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('notes')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_uuid')->references('uuid')->on('projects')->onDelete('cascade');
            $table->foreign('task_uuid')->references('uuid')->on('project_tasks')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_timesheets');
    }
};
