<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address\Country;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default users
        User::create([
            'name'              => 'OBMS Admin',
            'email'             => 'admin@obms.local',
            'password'          => Hash::make('admin'),
            'email_verified_at' => Carbon::now(),
            'role'              => 'admin',
        ]);

        User::create([
            'name'              => 'OBMS Customer',
            'email'             => 'customer@obms.local',
            'password'          => Hash::make('customer'),
            'email_verified_at' => Carbon::now(),
            'role'              => 'customer',
        ]);

        // Create default country
        Country::create([
            'name'           => 'Germany',
            'iso2'           => 'DE',
            'eu'             => true,
            'reverse_charge' => true,
            'vat_basic'      => 19,
            'vat_reduced'    => 7,
        ]);

        // Insert default settings
        $timestamp = Carbon::now();
        Setting::insert([
            [
                'setting'    => 'app.name',
                'value'      => encrypt('OBMS'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'app.url',
                'value'      => encrypt('http://localhost'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.logo',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.name',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.representative',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.oneliner',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.street',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.housenumber',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.addition',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.postalcode',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.city',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.state',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.address.country',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.phone',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.fax',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.email',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.register_court',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.register_number',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.vat_id',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.tax_id',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.bank.iban',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.bank.bic',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.bank.institute',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.bank.owner',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.dunning.default_deadline',
                'value'      => encrypt(7),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.default_country',
                'value'      => encrypt(1),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.default_vat_rate',
                'value'      => encrypt(19),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.transport',
                'value'      => encrypt('smtp'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.host',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.port',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.encryption',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.username',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.password',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.timeout',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'mail.mailers.smtp.auth_mode',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'session.lifetime',
                'value'      => encrypt(120),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.primary',
                'value'      => encrypt('#040E29'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.white',
                'value'      => encrypt('#FFFFFF'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.gray',
                'value'      => encrypt('#F3F9FC'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'app.slogan',
                'value'      => encrypt('Open Business Management Software'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'app.theme',
                'value'      => encrypt('aurora'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'company.favicon',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'passport.private_key',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'passport.public_key',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.success',
                'value'      => encrypt('#0F7038'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.warning',
                'value'      => encrypt('#FFD500'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.danger',
                'value'      => encrypt('#B21E35'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.info',
                'value'      => encrypt('#1464F6'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'theme.body',
                'value'      => encrypt('#3C4858'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'sso.provider',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'sso.client.id',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'sso.client.secret',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], [
                'setting'    => 'sso.tenant',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}
