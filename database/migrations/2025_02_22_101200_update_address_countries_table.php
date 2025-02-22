<?php

use App\Models\Address\Country;
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
        Schema::table('address_countries', function (Blueprint $table) {
            $table->string('iso2')->nullable()->after('name');
        });

        Country::find(1)?->update([
            'iso2' => 'DE',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address_countries', function (Blueprint $table) {
            $table->dropColumn('iso2');
        });
    }
};
