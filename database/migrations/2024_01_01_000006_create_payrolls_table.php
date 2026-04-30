<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('period_month'); // e.g. "2024-01"
            $table->integer('year');
            $table->integer('month');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('tax_deduction', 12, 2)->default(0);
            $table->decimal('sss_deduction', 12, 2)->default(0);
            $table->decimal('philhealth_deduction', 12, 2)->default(0);
            $table->decimal('pagibig_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2);
            $table->decimal('net_salary', 12, 2);
            $table->integer('days_worked')->default(0);
            $table->integer('days_absent')->default(0);
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->date('pay_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
