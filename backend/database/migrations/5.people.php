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
        Schema::create('peoples', function (Blueprint $table) {
            $table->id('people_id');
            $table->string('ni_no',20)->default(null);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('name',50);
            $table->string('email',50)->unique();
            $table->string('job_type',30);
            $table->string('gender',10);
            $table->date('date_of_birth');
            $table->string('address',150);
            $table->string('city',50);
            $table->string('state',50);
            $table->string('country',50);
            $table->integer('pincode');

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
        Schema::dropIfExists('Peoples');
    }
};