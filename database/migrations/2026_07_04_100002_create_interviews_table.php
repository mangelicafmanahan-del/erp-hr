<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('stage')->nullable(); // HR Interview, Technical Interview, Final Interview
            $table->string('interviewer')->nullable();
            $table->dateTime('interview_date');
            $table->decimal('score', 3, 1)->nullable(); // out of 5.0
            $table->text('feedback')->nullable();
            $table->string('result')->nullable(); // Passed, Failed, Pending
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
