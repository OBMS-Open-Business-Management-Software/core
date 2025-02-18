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
        Schema::create('filemanager_locks', function (Blueprint $table) {
            $table->id();
            $table->string('owner');
            $table->integer('timeout');
            $table->integer('created');
            $table->string('token');
            $table->string('scope');
            $table->integer('depth');
            $table->string('uri');
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
        Schema::dropIfExists('filemanager_locks');
    }
};
