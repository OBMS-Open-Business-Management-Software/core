<?php

declare(strict_types=1);

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        $timestamp = Carbon::now();

        Setting::insert([
            [
                'setting'    => 'app.theme',
                'value'      => encrypt('aurora'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Setting::where('setting', '=', 'app.theme')->forceDelete();
    }
};
