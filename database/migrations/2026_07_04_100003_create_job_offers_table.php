<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('offered_position')->nullable();
            $table->string('employment_type')->nullable();
            $table->decimal('salary_offered', 12, 2);
            $table->date('offer_date');
            $table->date('start_date')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
