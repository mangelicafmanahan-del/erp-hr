<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('company_name')->nullable(); // null/blank = internal record at this company
            $table->string('position');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null = currently holds this position
            $table->string('change_reason')->nullable(); // e.g. Promotion, Transfer, New Hire
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_history');
    }
};
