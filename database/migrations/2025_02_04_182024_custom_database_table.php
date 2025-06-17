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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
