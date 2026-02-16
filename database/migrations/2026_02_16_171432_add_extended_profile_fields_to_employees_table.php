<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable()->after('emp_code');
            $table->string('place_of_birth')->nullable()->after('nik');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('religion')->nullable()->after('marital_status');
            $table->text('address')->nullable()->after('religion');
            $table->string('phone')->nullable()->after('address');
            $table->string('emergency_contact_name')->nullable()->after('phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'place_of_birth',
                'date_of_birth',
                'gender',
                'marital_status',
                'religion',
                'address',
                'phone',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
