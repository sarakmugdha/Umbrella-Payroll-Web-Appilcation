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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name',30);
            $table->string('email',50)->unique();

            $table->string('username',30);
            $table->unsignedBigInteger('organization_id');
            $table->string('password')->nullable();
            $table->unsignedBigInteger('role_id')->default(2);
            $table->rememberToken();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mfa_secret')->nullable();
            $table->boolean('is_mfa_enabled')->default(false);
            $table->timestamps();
            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('created_by')->nullable();
            $table->tinyInteger('updated_by')->nullable();



            $table->foreign('role_id')
                  ->references('role_id')->on('roles')
                  ->onDelete('cascade');


            $table->foreign('organization_id')
                  ->references('organization_id')->on('organizations')
                  ->onDelete('cascade');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
