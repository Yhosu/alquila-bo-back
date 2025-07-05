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
        Schema::create('image_folders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('extension', ['jpg','png','gif'])->default('jpg');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('image_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->default(1);
            $table->string('code');
            $table->enum('type', ['original','resize','fit'])->default('original');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->foreign('parent_id')->references('id')->on('image_folders')->onDelete('cascade');
        });
        Schema::create('sectors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sector_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->integer('order')->nullable();
            $table->string('image')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('social_networks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('icon')->nullable();
            $table->string('url')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
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
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('description')->nullable();
            $table->string('sku')->nullable();
            $table->string('image')->nullable();
            $table->boolean('top')->nullable();
            $table->integer('order')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('relation_id')->nullable();
            $table->enum('type', ['company', 'product', 'banner'])->nullable();
            $table->string('image')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('company_filters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->enum('type', ['radio', 'range', 'checkbox', 'color'])->nullable();
            $table->boolean('enabled')->nullable()->default(1);
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
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('product_characteristics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('product_filters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('product_id')->nullable();
            $table->string('company_filter_id')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('faqs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('order')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('characteristics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('about_us', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            // $table->string('image')->nullable();
            // $table->string('video_url')->nullable();
            $table->string('order')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('our_team', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role')->nullable();
            $table->string('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });
        Schema::create('add_information', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('email')->nullable();
            $table->text('opening_hour')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });

        /** TODO: GASPER FALTA AGREGAR NUESTRO EQUIPO Y ABOUT US */
        /** TODO: RAMI AGREGAR LA TABLA SUBSCRIBE */
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('subscription_status')->nullable();
            $table->string('confirmation_token')->unique();
            $table->timestamp('confirmation_date')->useCurrent();
            $table->boolean('enabled')->nullable()->default(1);
            $table->timestamp('date_of_creation')->nullable();
            $table->timestamp('last_modification')->nullable();
            $table->string('creator_id')->nullable();
            $table->string('modificator_id')->nullable();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->string('cod_notification')->primary();
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->string('subject')->nullable();
            $table->text('template')->nullable();
            $table->boolean('enabled')->nullable()->default(1);
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
