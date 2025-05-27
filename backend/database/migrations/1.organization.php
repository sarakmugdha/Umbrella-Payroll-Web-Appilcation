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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('organization_id');
            $table->string('organization_name',50);
            $table->string('email',50);
            $table->string('address',150);
            $table->tinyInteger('status')->default(0);
            $table->string('state',50);
            $table->integer('pincode');
            $table->binary('organization_logo')->nullable();
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY organization_logo LONGBLOB');
        Schema::dropIfExists('organization');

    }
};
