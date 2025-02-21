<?php

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $timestamp = Carbon::now();

        Setting::insert([
            [
                'setting' => 'company.favicon',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('setting', '=', 'company.favicon')->forceDelete();
    }
};
