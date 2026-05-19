<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_period', 50);
            $table->date('review_date');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 scale');
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->text('goals')->nullable();
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('review_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
