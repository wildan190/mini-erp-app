<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_costs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('project_uuid');
            $table->string('type'); // material, manpower, vendor, operational
            $table->string('description');
            $table->decimal('amount', 20, 2);
            $table->date('date');
            $table->uuid('reference_uuid')->nullable(); // link to PO or Invoice
            $table->timestamps();

            $table->foreign('project_uuid')->references('uuid')->on('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_costs');
    }
};
