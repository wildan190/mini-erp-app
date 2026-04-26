<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_forecasts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('forecast_date');
            $table->decimal('predicted_inflow', 15, 2);
            $table->decimal('predicted_outflow', 15, 2);
            $table->decimal('predicted_balance', 15, 2);
            $table->decimal('confidence_score', 5, 2); // 0.00 to 100.00
            $table->string('model_type'); // e.g. linear_regression, knn
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_forecasts');
    }
};
