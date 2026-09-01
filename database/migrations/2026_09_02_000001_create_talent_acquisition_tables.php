<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Job Posts (Manajemen Lowongan)
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->string('employment_type')->default('full-time'); // full-time, part-time, contract, internship
            $table->string('location')->nullable();
            $table->decimal('min_salary', 15, 2)->nullable();
            $table->decimal('max_salary', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('status')->default('draft'); // draft, published, closed
            $table->date('deadline_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Job Applicants (Pelacakan Pelamar / ATS)
        Schema::create('job_applicants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('stage')->default('screening'); 
            // screening, technical_test, interview, offering, hired, rejected
            $table->text('notes')->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->foreignId('converted_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Interviews (Jadwal Wawancara)
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('job_applicant_id')->constrained('job_applicants')->cascadeOnDelete();
            $table->string('title');
            $table->dateTime('scheduled_at');
            $table->string('type')->default('online'); // online, offline
            $table->string('meeting_link_or_location')->nullable();
            $table->string('interviewer_name')->nullable();
            $table->string('interviewer_email')->nullable();
            $table->text('instructions')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, rescheduled
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Interview Evaluations (Penilaian & Umpan Balik)
        Schema::create('interview_evaluations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->string('evaluator_name');
            $table->integer('technical_score')->default(0); // 1-5 or 0-100
            $table->integer('communication_score')->default(0);
            $table->integer('culture_fit_score')->default(0);
            $table->integer('overall_score')->default(0);
            $table->text('feedback_notes')->nullable();
            $table->string('recommendation')->default('consider'); // hire, consider, reject
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Offering Letters (Surat Penawaran Kerja)
        Schema::create('offering_letters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('job_applicant_id')->constrained('job_applicants')->cascadeOnDelete();
            $table->string('offer_number')->unique();
            $table->decimal('basic_salary', 15, 2);
            $table->text('benefits')->nullable();
            $table->date('joining_date');
            $table->date('expiry_date')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offering_letters');
        Schema::dropIfExists('interview_evaluations');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('job_applicants');
        Schema::dropIfExists('job_posts');
    }
};
