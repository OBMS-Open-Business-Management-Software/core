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
        Schema::table('shop_configurator_forms', function (Blueprint $table) {
            $table
                ->enum('type', [
                    'form',
                    'package',
                ])
                ->default('form')
                ->after('contract_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('shop_configurator_forms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
