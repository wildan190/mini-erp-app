<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');    // e.g. 'midtrans', 'email', 'general'
            $table->string('key')->unique();               // e.g. 'midtrans_iris.api_key'
            $table->text('value')->nullable();             // encrypted for sensitive keys
            $table->boolean('is_secret')->default(false);  // if true, value is encrypted & masked on read
            $table->string('label')->nullable();           // human-readable label
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
