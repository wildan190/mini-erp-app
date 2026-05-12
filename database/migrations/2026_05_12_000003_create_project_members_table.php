<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('project_uuid');
            $table->uuid('employee_uuid');
            $table->string('role')->nullable(); // Developer, Designer, etc.
            $table->integer('allocation_percentage')->default(100);
            $table->timestamps();

            $table->foreign('project_uuid')->references('uuid')->on('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_members');
    }
};
