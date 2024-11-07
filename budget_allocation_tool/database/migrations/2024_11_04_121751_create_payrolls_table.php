<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Foreign key to employees
            $table->date('date');
            $table->string('fund_no')->nullable();
            $table->string('gl_account')->nullable();
            $table->string('type'); // Type of payroll entry (salary, pension, etc.)
            $table->decimal('amount_birr', 15, 2); // Amount (positive or negative for deductions)
            $table->decimal('amount_usd', 15, 2); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
};
