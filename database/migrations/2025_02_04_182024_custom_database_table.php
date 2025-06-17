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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->integer('order')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('social_network_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->string('url')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('description')->nullable();
            $table->string('sku')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('relation_id')->nullable();
            $table->enum('type', ['company', 'product'])->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('company_filters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->enum('type', ['radio', 'range', 'checkbox', 'color'])->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('company_filter_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_filter_id')->nullable();
            $table->string('name')->nullable();
            $table->text('configuration')->nullable();
            $table->boolean('status')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('product_characteristics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
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
