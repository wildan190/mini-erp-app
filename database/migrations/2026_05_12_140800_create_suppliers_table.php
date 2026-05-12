<?php
/*
 * Created At: 2026-05-12T14:07:44Z
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('pic')->nullable();
            $table->string('contact')->nullable();
            $table->text('address')->nullable();
            $table->string('npwp')->nullable();
            $table->string('category')->nullable(); // e.g., Raw Materials, Services
            $table->string('currency_code')->default('IDR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
