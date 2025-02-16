<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('title');
            $table->boolean('must_accept')->default(false);
            $table->boolean('navigation_item')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_acceptance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('page_version_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('user_agent')->nullable();
            $table->string('ip')->nullable();
            $table->longText('signature')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('content');
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
        Schema::dropIfExists('page_versions');
        Schema::dropIfExists('page_acceptance');
        Schema::dropIfExists('pages');
    }
}
