<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_configurator_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('route');
            $table->string('name');
            $table->longText('description');
            $table->boolean('public')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->enum('type', [
                'input_text',
                'input_number',
                'input_range',
                'input_radio',
                'input_radio_image',
                'input_checkbox',
                'input_hidden',
                'select',
                'textarea',
            ]);
            $table->boolean('required')->default(true);
            $table->string('label');
            $table->string('key');
            $table->string('value')->nullable();
            $table->double('amount')->nullable();
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->double('step')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->string('label');
            $table->string('value');
            $table->double('amount')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('contract_type_id');
            $table->unsignedBigInteger('tracker_id')->nullable();
            $table->string('route');
            $table->string('name');
            $table->longText('description');
            $table->string('product_type');
            $table->boolean('approval')->default(false);
            $table->boolean('public')->default(false);
            $table->enum('vat_type', [
                'basic',
                'reduced',
            ])->default('basic');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('tracker_id')->nullable();
            $table->string('product_type');
            $table->double('amount');
            $table->double('vat_percentage')->nullable();
            $table->boolean('reverse_charge')->default(false);
            $table->boolean('verified')->default(false);
            $table->boolean('invalid')->default(false);
            $table->boolean('approved')->default(false);
            $table->boolean('disapproved')->default(false);
            $table->boolean('setup')->default(false);
            $table->integer('fails')->default(0);
            $table->boolean('locked')->default(false);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('field_id')->nullable();
            $table->unsignedBigInteger('option_id')->nullable();
            $table->string('key');
            $table->string('value');
            $table->string('value_prefix')->nullable();
            $table->string('value_suffix')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('type');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_order_queue_history');
        Schema::dropIfExists('shop_order_queue_fields');
        Schema::dropIfExists('shop_order_queue');
        Schema::dropIfExists('shop_configurator_forms');
        Schema::dropIfExists('shop_configurator_field_options');
        Schema::dropIfExists('shop_configurator_fields');
        Schema::dropIfExists('shop_configurator_categories');
    }
}
