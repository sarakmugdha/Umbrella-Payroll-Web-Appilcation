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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id('invoice_detail_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('timesheet_detail_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');



            $table->decimal('hours_worked',8,2);
            $table->decimal('hourly_pay',8,2);
            $table->decimal('vat_percent',8,2)->default(0);
            $table->decimal('total_pay',8,2);
            $table->string('description', 255)->nullable();

            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();


            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');


            $table->foreign('timesheet_detail_id')
            ->references('timesheet_detail_id')
            ->on('timesheet_details')->onDelete('cascade');

            $table->foreign('organization_id')
            ->references('organization_id')
            ->on('organizations')->onDelete('cascade');

            $table->foreign('company_id')
            ->references('company_id')
            ->on('companies')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
