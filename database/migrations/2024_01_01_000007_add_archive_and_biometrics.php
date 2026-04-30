<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add archived_at to users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->string('default_password')->nullable()->after('archived_at');
        });

        // Add archived_at to employees
        Schema::table('employees', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('updated_at');
            $table->string('biometric_id')->nullable()->after('employee_id');
        });

        // Add archived_at to departments
        Schema::table('departments', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('updated_at');
        });

        // Add payroll schedule fields
        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('pay_period', ['monthly', 'semi_monthly'])->default('semi_monthly')->after('month');
            $table->enum('pay_period_type', ['first', 'second', 'full'])->default('full')->after('pay_period');
        });

        // Add biometric log table
        Schema::create('biometric_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('biometric_id')->nullable();
            $table->timestamp('log_time');
            $table->enum('log_type', ['time_in', 'time_out'])->default('time_in');
            $table->string('device_id')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });

        // Add job_postings table for recruiter
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->integer('slots')->default(1);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // Add job_applications table
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'interview', 'hired', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('biometric_logs');
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['pay_period', 'pay_period_type']);
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'biometric_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'default_password']);
        });
    }
};
