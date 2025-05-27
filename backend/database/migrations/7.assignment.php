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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id("assignment_id");
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger("people_id");
            $table->unsignedBigInteger("customer_id");
            $table->date("start_date");
            $table->date("end_date");
            $table->string("role",30);
            $table->string("location",30);
            $table->tinyInteger('status')->default(0);
            $table->enum("type",["part_time","full_time"]);
            $table->timestamps();

            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();

            $table->foreign('people_id')
                    ->references('people_id')
                    ->on('peoples')
                    ->onDelete('cascade');

            $table->foreign('customer_id')
                    ->references('customer_id')
                    ->on('customers')
                    ->onDelete('cascade');

            $table->foreign('organization_id')
                    ->references('organization_id')
                    ->on('organizations')
                    ->onDelete('cascade');

            $table->foreign('company_id')
                    ->references('company_id')
                    ->on('companies');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};