<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('invoice_number',20)->unique();

            $table->unsignedBigInteger('timesheet_id')->nullable();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('people_id');
            $table->unsignedBigInteger('customer_id');

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');

            $table->date('invoice_date')->nullable();
            $table->date('due_date');
            $table->decimal('total_pay',8,2)->default(0);
            $table->enum('status',['Draft','Sent','Paid','Overdue'])->default('Draft');
            $table->enum('type',['automatic','manual'])->default('automatic');
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);

            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();

            $table->foreign('people_id')->references('people_id')->on('peoples');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('timesheet_id')->references('timesheet_id')->on('timesheets');
            $table->foreign('assignment_id')->references('assignment_id')->on('assignments');
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->foreign('organization_id')->references('organization_id')->on('organizations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice');
    }
};