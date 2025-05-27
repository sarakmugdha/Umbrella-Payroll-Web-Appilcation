<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Reference\Reference;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timesheet_details', function (Blueprint $table) {
            $table->id('timesheet_detail_id');
            $table->unsignedBigInteger('timesheet_id');
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('people_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('people_name',50);
            $table->string('customer_name',75);
            $table->decimal('hours_worked',8,2);
            $table->decimal('hourly_pay',8,2);
            $table->decimal('total_pay',8,2);
            $table->string('description', 255)->nullable();
            $table->date('date_worked')->nullable();
            $table->tinyInteger('is_mapped')->default(1);
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();

            $table->foreign('people_id')->references('people_id')->on('peoples');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('timesheet_id')
            ->references('timesheet_id')
            ->on('timesheets')->onDelete('cascade');

            $table->foreign('assignment_id')
            ->references('assignment_id')
            ->on('assignments')->onDelete('cascade');
            $table->foreign('company_id')
                  ->references('company_id')
                  ->on('companies');

            $table->foreign('organization_id')
                  ->references('organization_id')
                  ->on('organizations')
                  ->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_details');
    }
};
