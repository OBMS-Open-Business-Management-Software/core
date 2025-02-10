<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->unsignedBigInteger('contract_id')->index()->nullable();
            $table->string('domain')->index()->unique();
            $table->text('database_driver')->nullable();
            $table->text('database_url')->nullable();
            $table->text('database_host')->nullable();
            $table->text('database_port')->nullable();
            $table->text('database_database')->nullable();
            $table->text('database_username')->nullable();
            $table->text('database_password')->nullable();
            $table->text('database_unix_socket')->nullable();
            $table->text('database_charset')->nullable();
            $table->text('database_collation')->nullable();
            $table->text('database_prefix')->nullable();
            $table->text('database_prefix_indexes')->nullable();
            $table->text('database_strict')->nullable();
            $table->text('database_engine')->nullable();
            $table->text('redis_prefix')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
}
