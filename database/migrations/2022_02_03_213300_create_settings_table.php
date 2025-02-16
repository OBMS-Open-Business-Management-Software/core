<?php

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $timestamp = Carbon::now();

        Setting::insert([
            [
                'setting' => 'app.name',
                'value' => encrypt('New instance'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'app.url',
                'value' => encrypt('https://localhost:8000'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.logo',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.name',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.representative',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.oneliner',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.street',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.housenumber',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.addition',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.postalcode',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.city',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.state',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.address.country',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.phone',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.fax',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.email',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.register_court',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.register_number',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.vat_id',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.tax_id',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.bank.iban',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.bank.bic',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.bank.institute',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.bank.owner',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.dunning.default_deadline',
                'value' => encrypt(7),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.default_country',
                'value' => encrypt(1),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'company.default_vat_rate',
                'value' => encrypt(19),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.transport',
                'value' => encrypt('smtp'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.host',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.port',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.encryption',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.username',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.password',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.timeout',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'mail.mailers.smtp.auth_mode',
                'value' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting' => 'session.lifetime',
                'value' => encrypt(120),
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
        Schema::dropIfExists('settings');
    }
}
