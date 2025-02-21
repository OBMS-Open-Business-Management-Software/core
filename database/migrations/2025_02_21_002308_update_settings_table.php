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
                'setting' => 'passport.private_key',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting' => 'passport.public_key',
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
