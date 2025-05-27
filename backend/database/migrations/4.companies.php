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
        Schema::create('companies', function (Blueprint $table) {
            $table->id('company_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('company_name',80);
            $table->string('email',50)->unique();
            $table->string('phone_number',15);
            $table->decimal('vat_percent');
            $table->string('domain',50);
            $table->string('address',150);
            $table->string('city',50);
            $table->string('state',50);
            $table->string('country',50);
            $table->integer('pincode');
            $table->binary('company_logo')->nullable();
            $table->timestamps();

            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();


            $table->foreign('organization_id')
                  ->references('organization_id')
                  ->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY company_logo LONGBLOB');
        Schema::dropIfExists('companies');
    }
};