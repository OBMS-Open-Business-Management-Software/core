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

        Setting::where('setting', '=', 'theme.secondary-dark')->forceDelete();
        Setting::where('setting', '=', 'theme.secondary')->update([
            'value' => encrypt('#FF6B00'),
        ]);

        Setting::insert([
            [
                'setting' => 'theme.success',
                'value' => encrypt('#0F7038'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting' => 'theme.warning',
                'value' => encrypt('#FFD500'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting' => 'theme.danger',
                'value' => encrypt('#B21E35'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting' => 'theme.info',
                'value' => encrypt('#1464F6'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting' => 'theme.body',
                'value' => encrypt('#3C4858'),
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
        Setting::where(function ($query) {
            $query->where('setting', '=', 'theme.body')
                ->orWhere('setting', '=', 'theme.info')
                ->orWhere('setting', '=', 'theme.danger')
                ->orWhere('setting', '=', 'theme.warning')
                ->orWhere('setting', '=', 'theme.success');
        })->forceDelete();

        Setting::where('setting', '=', 'theme.secondary')->update([
            'value' => encrypt('#3C4858'),
        ]);

        Setting::insert([
            [
                'setting' => 'theme.secondary-dark',
                'value' => encrypt('#2B3747'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
};
