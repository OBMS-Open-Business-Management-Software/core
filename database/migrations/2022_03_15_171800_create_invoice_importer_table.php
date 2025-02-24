<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('invoice_importers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('imap_inbox_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_importer_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('importer_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->string('subject');
            $table->string('from');
            $table->string('from_name');
            $table->string('to');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('invoice_importer_history');
        Schema::dropIfExists('invoice_importers');
    }
};
