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
                'setting'    => 'sso.provider',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'sso.client.id',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'sso.client.secret',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'sso.tenant',
                'value'      => null,
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
        $timestamp = Carbon::now();

        Setting::where(function ($query) {
            $query->where('setting', '=', 'sso.tenant')
                ->orWhere('setting', '=', 'sso.client.secret')
                ->orWhere('setting', '=', 'sso.client.id')
                ->orWhere('setting', '=', 'sso.provider');
        })->forceDelete();
    }
};
