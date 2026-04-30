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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropUnique('payrolls_employee_id_year_month_unique');
            $table->unique(['employee_id', 'year', 'month', 'pay_period_type'], 'payrolls_full_unique');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropUnique('payrolls_full_unique');
            $table->unique(['employee_id', 'year', 'month'], 'payrolls_employee_id_year_month_unique');
        });
    }
};
