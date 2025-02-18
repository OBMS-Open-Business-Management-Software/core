<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracker_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('contract_position_id')->nullable();
            $table->unsignedBigInteger('tracker_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trackers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('vat_type', [
                'basic',
                'reduced',
            ])->default('basic');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tracker_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tracker_id');
            $table->enum('type', [
                'string',
                'integer',
                'double',
            ]);
            $table->enum('process', [
                'min',
                'median',
                'average',
                'max',
                'equals',
            ]);
            $table->enum('round', [
                'up',
                'down',
                'regular',
                'none',
            ])->default('none');
            $table->string('step');
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tracker_instance_item_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instance_id');
            $table->unsignedBigInteger('item_id');
            $table->longText('data')->nullable();
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
        Schema::dropIfExists('tracker_instance_item_data');
        Schema::dropIfExists('tracker_items');
        Schema::dropIfExists('trackers');
        Schema::dropIfExists('tracker_instances');
    }
};
