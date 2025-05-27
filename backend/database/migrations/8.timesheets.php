<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id('timesheet_id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('company_id');
            $table->string('name',50);
            $table->integer('timesheet_count')->default(0);
            $table->date('period_end_date');
            $table->tinyInteger('invoice_sent')->default(0);
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();
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
        Schema::dropIfExists('timesheets');
    }
};
