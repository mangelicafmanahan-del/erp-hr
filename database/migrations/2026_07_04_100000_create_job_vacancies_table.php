<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('employment_type')->nullable(); // Full-time, Part-time, Contractual
            $table->string('location')->nullable();
            $table->string('status')->default('open'); // open, closed
            $table->date('posted_date');
            $table->date('closing_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
