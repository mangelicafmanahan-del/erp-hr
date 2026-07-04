<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->string('payment_method')->default('Bank Transfer')->after('net_pay');
        });
    }

    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
