<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // 1a - personal details & contact info
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('alternate_number')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();

            // government numbers (also referenced later on payslips)
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();
            $table->string('tin_number')->nullable();

            // emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_number')->nullable();

            // 1a/1b - job info & contract type
            $table->string('job_title')->nullable();
            $table->string('contract_type')->nullable(); // Regular, Probationary, Contractual
            $table->string('employment_status')->default('active'); // active, on_leave, inactive, terminated
            $table->date('hire_date')->nullable();

            $table->string('profile_photo_path')->nullable();

            $table->timestamps();
            $table->softDeletes(); // avoid hard-deleting personnel records
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
