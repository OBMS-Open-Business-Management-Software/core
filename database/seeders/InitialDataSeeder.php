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
        // Insert default users
        if (User::count() === 0) {
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
        }

        // Insert default country
        if (Country::count() === 0) {
            Country::create([
                'name'           => 'Germany',
                'iso2'           => 'DE',
                'eu'             => true,
                'reverse_charge' => true,
                'vat_basic'      => 19,
                'vat_reduced'    => 7,
            ]);
        }

        // Insert default settings
        if (! Setting::where('setting', '=', 'app.name')->exists()) {
            Setting::create([
                'setting' => 'app.name',
                'value'   => 'OBMS',
            ]);
        }

        if (! Setting::where('setting', '=', 'app.url')->exists()) {
            Setting::create([
                'setting' => 'app.url',
                'value'   => 'http://localhost',
            ]);
        }

        if (! Setting::where('setting', '=', 'app.slogan')->exists()) {
            Setting::create([
                'setting' => 'app.slogan',
                'value'   => 'Open Business Management Software',
            ]);
        }

        if (! Setting::where('setting', '=', 'app.theme')->exists()) {
            Setting::create([
                'setting' => 'app.theme',
                'value'   => 'aurora',
            ]);
        }

        if (! Setting::where('setting', '=', 'company.name')->exists()) {
            Setting::create([
                'setting' => 'company.name',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.logo')->exists()) {
            Setting::create([
                'setting' => 'company.logo',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.representative')->exists()) {
            Setting::create([
                'setting' => 'company.representative',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.oneliner')->exists()) {
            Setting::create([
                'setting' => 'company.address.oneliner',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.street')->exists()) {
            Setting::create([
                'setting' => 'company.address.street',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.housenumber')->exists()) {
            Setting::create([
                'setting' => 'company.address.housenumber',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.addition')->exists()) {
            Setting::create([
                'setting' => 'company.address.addition',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.postalcode')->exists()) {
            Setting::create([
                'setting' => 'company.address.postalcode',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.city')->exists()) {
            Setting::create([
                'setting' => 'company.address.city',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.state')->exists()) {
            Setting::create([
                'setting' => 'company.address.state',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.address.country')->exists()) {
            Setting::create([
                'setting' => 'company.address.country',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.phone')->exists()) {
            Setting::create([
                'setting' => 'company.phone',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.fax')->exists()) {
            Setting::create([
                'setting' => 'company.fax',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.email')->exists()) {
            Setting::create([
                'setting' => 'company.email',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.register_court')->exists()) {
            Setting::create([
                'setting' => 'company.register_court',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.register_number')->exists()) {
            Setting::create([
                'setting' => 'company.register_number',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.vat_id')->exists()) {
            Setting::create([
                'setting' => 'company.vat_id',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.tax_id')->exists()) {
            Setting::create([
                'setting' => 'company.tax_id',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.bank.iban')->exists()) {
            Setting::create([
                'setting' => 'company.bank.iban',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.bank.bic')->exists()) {
            Setting::create([
                'setting' => 'company.bank.bic',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.bank.institute')->exists()) {
            Setting::create([
                'setting' => 'company.bank.institute',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.bank.owner')->exists()) {
            Setting::create([
                'setting' => 'company.bank.owner',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.dunning.default_deadline')->exists()) {
            Setting::create([
                'setting' => 'company.dunning.default_deadline',
                'value'   => 7,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.default_country')->exists()) {
            Setting::create([
                'setting' => 'company.default_country',
                'value'   => 1,
            ]);
        }

        if (! Setting::where('setting', '=', 'company.default_vat_rate')->exists()) {
            Setting::create([
                'setting' => 'company.default_vat_rate',
                'value'   => 19,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.transport')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.transport',
                'value'   => 'smtp',
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.host')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.host',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.port')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.port',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.encryption')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.encryption',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.username')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.username',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.password')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.password',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.timeout')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.timeout',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'mail.mailers.smtp.auth_mode')->exists()) {
            Setting::create([
                'setting' => 'mail.mailers.smtp.auth_mode',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'session.lifetime')->exists()) {
            Setting::create([
                'setting' => 'session.lifetime',
                'value'   => 120,
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.primary')->exists()) {
            Setting::create([
                'setting' => 'theme.primary',
                'value'   => '#040E29',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.white')->exists()) {
            Setting::create([
                'setting' => 'theme.white',
                'value'   => '#FFFFFF',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.gray')->exists()) {
            Setting::create([
                'setting' => 'theme.gray',
                'value'   => '#F3F9FC',
            ]);
        }

        if (! Setting::where('setting', '=', 'company.favicon')->exists()) {
            Setting::create([
                'setting' => 'company.favicon',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'passport.private_key')->exists()) {
            Setting::create([
                'setting' => 'passport.private_key',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'passport.public_key')->exists()) {
            Setting::create([
                'setting' => 'passport.public_key',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.success')->exists()) {
            Setting::create([
                'setting' => 'theme.success',
                'value'   => '#0F7038',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.warning')->exists()) {
            Setting::create([
                'setting' => 'theme.warning',
                'value'   => '#FFD500',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.danger')->exists()) {
            Setting::create([
                'setting' => 'theme.danger',
                'value'   => '#B21E35',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.info')->exists()) {
            Setting::create([
                'setting' => 'theme.info',
                'value'   => '#1464F6',
            ]);
        }

        if (! Setting::where('setting', '=', 'theme.body')->exists()) {
            Setting::create([
                'setting' => 'theme.body',
                'value'   => '#3C4858',
            ]);
        }

        if (! Setting::where('setting', '=', 'sso.provider')->exists()) {
            Setting::create([
                'setting' => 'sso.provider',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'sso.client.id')->exists()) {
            Setting::create([
                'setting' => 'sso.client.id',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'sso.client.secret')->exists()) {
            Setting::create([
                'setting' => 'sso.client.secret',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'sso.tenant')->exists()) {
            Setting::create([
                'setting' => 'sso.tenant',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\User.date.prepend')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\User.date.prepend',
                'value'   => false,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\User.date.format')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\User.date.format',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\User.increment.group_by')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\User.increment.group_by',
                'value'   => null,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\User.increment.reserved')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\User.increment.reserved',
                'value'   => 10000,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\User.prefix')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\User.prefix',
                'value'   => 'U',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.date.prepend')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.date.prepend',
                'value'   => true,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.date.format')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.date.format',
                'value'   => 'Ymd',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.increment.group_by')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.increment.group_by',
                'value'   => 'day',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.increment.reserved')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.increment.reserved',
                'value'   => 0,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.prefix')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Shop\OrderQueue\ShopOrderQueue.prefix',
                'value'   => 'O',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.date.prepend')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.date.prepend',
                'value'   => true,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.date.format')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.date.format',
                'value'   => 'Ymd',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.increment.group_by')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.increment.group_by',
                'value'   => 'day',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.increment.reserved')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.increment.reserved',
                'value'   => 0,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.prefix')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\InvoiceReminder.prefix',
                'value'   => 'R',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\Invoice.date.prepend')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\Invoice.date.prepend',
                'value'   => true,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\Invoice.date.format')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\Invoice.date.format',
                'value'   => 'Ymd',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\Invoice.increment.group_by')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\Invoice.increment.group_by',
                'value'   => 'day',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\Invoice.increment.reserved')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\Invoice.increment.reserved',
                'value'   => 0,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Invoice\Invoice.prefix')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Invoice\Invoice.prefix',
                'value'   => 'I',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Contract\Contract.date.prepend')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Contract\Contract.date.prepend',
                'value'   => true,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Contract\Contract.date.format')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Contract\Contract.date.format',
                'value'   => 'Ymd',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Contract\Contract.increment.group_by')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Contract\Contract.increment.group_by',
                'value'   => 'day',
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Contract\Contract.increment.reserved')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Contract\Contract.increment.reserved',
                'value'   => 0,
            ]);
        }

        if (! Setting::where('setting', '=', 'number_ranges.App\Models\Accounting\Contract\Contract.prefix')->exists()) {
            Setting::create([
                'setting' => 'number_ranges.App\Models\Accounting\Contract\Contract.prefix',
                'value'   => 'C',
            ]);
        }
    }
}
