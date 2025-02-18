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
                'setting' => 'theme.primary',
                'value' => encrypt('#040E29'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'theme.secondary',
                'value' => encrypt('#3C4858'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'theme.secondary-dark',
                'value' => encrypt('#2B3747'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'theme.white',
                'value' => encrypt('#FFFFFF'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'theme.gray',
                'value' => encrypt('#F3F9FC'),
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
            $query->where('setting', '=', 'theme.primary')
                ->orWhere('setting', '=', 'theme.secondary')
                ->orWhere('setting', '=', 'theme.secondary-dark')
                ->orWhere('setting', '=', 'theme.white')
                ->orWhere('setting', '=', 'theme.gray');
        })->forceDelete();
    }
};
