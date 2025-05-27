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
        schema::create('customers',function(Blueprint $table){
            $table->id('customer_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('customer_name',50);
            $table->string('email',50);
            $table->string('tax_no',15)->nullable();
            $table->string('address',150);
            $table->string('state',50);
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
        //
        schema::dropIfExists('customers');
    }
};

