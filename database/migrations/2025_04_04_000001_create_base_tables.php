<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Pulse\Support\PulseMigration;

return new class () extends PulseMigration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting');
            $table->longText('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('invoice_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->double('period');
            $table->double('percentage_amount');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pulse_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql'  => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->mediumText('value');

            $table->index('timestamp');
            $table->index('type');
            $table->unique(['type', 'key_hash']);
        });

        Schema::create('pulse_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql'  => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->bigInteger('value')->nullable();

            $table->index('timestamp');
            $table->index('type');
            $table->index('key_hash');
            $table->index(['timestamp', 'type', 'key_hash', 'value']);
        });

        Schema::create('pulse_aggregates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bucket');
            $table->unsignedMediumInteger('period');
            $table->string('type');
            $table->mediumText('key');
            match ($this->driver()) {
                'mariadb', 'mysql' => $table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))'),
                'pgsql'  => $table->uuid('key_hash')->storedAs('md5("key")::uuid'),
                'sqlite' => $table->string('key_hash'),
            };
            $table->string('aggregate');
            $table->decimal('value', 20, 2);
            $table->unsignedInteger('count')->nullable();

            $table->unique(['bucket', 'period', 'type', 'aggregate', 'key_hash']);
            $table->index(['period', 'bucket']);
            $table->index('type');
            $table->index(['period', 'type', 'aggregate', 'bucket']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->string('title');
            $table->boolean('must_accept')->default(false);
            $table->boolean('navigation_item')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('address_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso2')->nullable();
            $table->boolean('eu')->default(false);
            $table->boolean('reverse_charge')->default(false);
            $table->double('vat_basic');
            $table->double('vat_reduced')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->nullable()->references('id')->on('invoice_discounts');
            $table->string('name');
            $table->string('description');
            $table->enum('type', [
                'normal',
                'auto_revoke',
                'prepaid',
            ]);
            $table->double('period')->nullable();
            $table->boolean('dunning')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'customer',
                'supplier',
                'employee',
                'admin',
                'api',
            ])->default('customer');
            $table->boolean('locked')->default(false);
            $table->boolean('must_change_password')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->boolean('two_factor_confirmed')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_type_id')->references('id')->on('invoice_types');
            $table->string('name');
            $table->string('description');
            $table->enum('type', [
                'contract_pre_pay',
                'contract_post_pay',
                'prepaid_auto',
                'prepaid_manual',
            ])->default('contract_pre_pay');
            $table->double('invoice_period')->nullable();
            $table->double('cancellation_period')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trackers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('vat_type', [
                'basic',
                'reduced',
            ])->default('basic');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->references('id')->on('shop_configurator_categories');
            $table->string('route');
            $table->string('name');
            $table->longText('description');
            $table->boolean('public')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->references('id')->on('shop_configurator_categories');
            $table->foreignId('contract_type_id')->references('id')->on('contract_types');
            $table->foreignId('tracker_id')->nullable()->references('id')->on('trackers');
            $table->enum('type', [
                'form',
                'package',
            ])->default('form');
            $table->string('route');
            $table->string('name');
            $table->longText('description');
            $table->string('product_type');
            $table->boolean('approval')->default(false);
            $table->boolean('public')->default(false);
            $table->enum('vat_type', [
                'basic',
                'reduced',
            ])->default('basic');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->references('id')->on('shop_configurator_forms');
            $table->enum('type', [
                'input_text',
                'input_number',
                'input_range',
                'input_radio',
                'input_radio_image',
                'input_checkbox',
                'input_hidden',
                'select',
                'textarea',
            ]);
            $table->boolean('required')->default(true);
            $table->string('label');
            $table->string('key');
            $table->string('value')->nullable();
            $table->double('amount')->nullable();
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->double('step')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_configurator_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->references('id')->on('shop_configurator_fields');
            $table->string('label');
            $table->string('value');
            $table->double('amount')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('filemanager_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('parent_id')->nullable()->references('id')->on('filemanager_folders');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('filemanager_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('folder_id')->nullable()->references('id')->on('filemanager_folders');
            $table->string('name');
            $table->string('mime');
            $table->bigInteger('size');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE filemanager_files ADD data LONGBLOB AFTER name');

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('client_id')->references('id')->on('oauth_clients');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('client_id')->references('id')->on('oauth_clients');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_token_id')->references('id')->on('oauth_access_tokens');
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('client_id')->references('id')->on('oauth_clients');
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('type_id')->references('id')->on('contract_types');
            $table->double('reserved_prepaid_amount')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_invoice_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('cancellation_revoked_at')->nullable();
            $table->timestamp('cancelled_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('form_id')->references('id')->on('shop_configurator_forms');
            $table->foreignId('contract_id')->nullable()->references('id')->on('contracts');
            $table->foreignId('tracker_id')->nullable()->references('id')->on('trackers');
            $table->string('product_type');
            $table->double('amount');
            $table->double('vat_percentage')->nullable();
            $table->boolean('reverse_charge')->default(false);
            $table->boolean('verified')->default(false);
            $table->boolean('invalid')->default(false);
            $table->boolean('approved')->default(false);
            $table->boolean('disapproved')->default(false);
            $table->boolean('setup')->default(false);
            $table->integer('fails')->default(0);
            $table->boolean('locked')->default(false);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->references('id')->on('shop_order_queue');
            $table->foreignId('product_id')->nullable()->references('id')->on('shop_configurator_forms');
            $table->foreignId('discount_id')->nullable()->references('id')->on('invoice_discounts');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->double('amount');
            $table->integer('vat_percentage')->nullable();
            $table->double('quantity');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->references('id')->on('contracts');
            $table->foreignId('position_id')->references('id')->on('positions');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tracker_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->references('id')->on('contracts');
            $table->foreignId('contract_position_id')->nullable()->references('id')->on('contract_positions');
            $table->foreignId('tracker_id')->references('id')->on('trackers');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tracker_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracker_id')->references('id')->on('trackers');
            $table->enum('type', [
                'string',
                'integer',
                'double',
            ]);
            $table->enum('process', [
                'min',
                'median',
                'average',
                'max',
                'equals',
            ]);
            $table->enum('round', [
                'up',
                'down',
                'regular',
                'none',
            ])->default('none');
            $table->string('step');
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tracker_instance_item_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->references('id')->on('tracker_instances');
            $table->foreignId('item_id')->references('id')->on('tracker_items');
            $table->longText('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('type_id')->nullable()->references('id')->on('invoice_types');
            $table->foreignId('contract_id')->nullable()->references('id')->on('contracts');
            $table->foreignId('file_id')->nullable()->references('id')->on('filemanager_files');
            $table->foreignId('original_id')->nullable()->references('id')->on('invoices');
            $table->string('name')->nullable();
            $table->enum('status', [
                'template',
                'unpaid',
                'paid',
                'refunded',
                'revoked',
                'refund',
            ])->default('template');
            $table->boolean('reverse_charge')->default(false);
            $table->boolean('sent')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_dunning', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->references('id')->on('invoice_types');
            $table->double('after');
            $table->double('period');
            $table->double('fixed_amount')->nullable();
            $table->double('percentage_amount')->nullable();
            $table->double('cancel_contract_regular')->default(false);
            $table->double('cancel_contract_instant')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->references('id')->on('invoices');
            $table->foreignId('position_id')->references('id')->on('positions');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->references('id')->on('invoices');
            $table->foreignId('dunning_id')->references('id')->on('invoice_dunning');
            $table->foreignId('file_id')->nullable()->references('id')->on('filemanager_files');
            $table->timestamp('due_at');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('shop_order_queue');
            $table->foreignId('field_id')->nullable()->references('id')->on('shop_configurator_fields');
            $table->foreignId('option_id')->nullable()->references('id')->on('shop_configurator_field_options');
            $table->string('key');
            $table->string('value');
            $table->string('value_prefix')->nullable();
            $table->string('value_suffix')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_order_queue_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('shop_order_queue');
            $table->string('type');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_product_settings', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');
            $table->string('setting');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('position_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->enum('type', [
                'fixed',
                'percentage',
            ]);
            $table->double('amount');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('invoice_id')->references('id')->on('invoices');
            $table->enum('status', [
                'pay',
                'unpay',
                'refund',
                'revoke',
                'publish',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('contract_id')->references('id')->on('contracts');
            $table->enum('status', [
                'start',
                'stop',
                'cancel',
                'restart',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('prepaid_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('creator_user_id')->nullable()->references('id')->on('users');
            $table->foreignId('contract_id')->nullable()->references('id')->on('contracts');
            $table->foreignId('invoice_id')->nullable()->references('id')->on('invoices');
            $table->double('amount');
            $table->string('transaction_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('setting');
            $table->longText('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('invoice_id')->nullable()->references('id')->on('invoices');
            $table->string('method');
            $table->double('amount');
            $table->string('transaction_id');
            $table->string('transaction_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->references('id')->on('pages');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_acceptance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->references('id')->on('pages');
            $table->foreignId('page_version_id')->references('id')->on('page_versions');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->longText('user_agent')->nullable();
            $table->string('ip')->nullable();
            $table->longText('signature')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('filemanager_locks', function (Blueprint $table) {
            $table->id();
            $table->string('owner');
            $table->integer('timeout');
            $table->integer('created');
            $table->string('token');
            $table->string('scope');
            $table->integer('depth');
            $table->string('uri');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->references('id')->on('address_countries');
            $table->string('street');
            $table->string('housenumber');
            $table->string('addition')->nullable();
            $table->string('postalcode');
            $table->string('city');
            $table->string('state');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('company')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('vat_id')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('primary')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profile_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->references('id')->on('user_profiles');
            $table->foreignId('address_id')->references('id')->on('addresses');
            $table->enum('type', [
                'all',
                'billing',
                'contact',
            ])->default('all')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profile_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->references('id')->on('user_profiles');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->enum('type', [
                'name',
                'address',
                'email',
                'phone',
            ]);
            $table->enum('action', [
                'change',
            ]);
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->references('id')->on('user_profiles');
            $table->string('iban');
            $table->string('bic');
            $table->string('bank');
            $table->string('owner');
            $table->boolean('primary')->default(false);
            $table->string('sepa_mandate')->nullable();
            $table->timestamp('sepa_mandate_signed_at')->nullable();
            $table->text('sepa_mandate_signature')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profile_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->references('id')->on('user_profiles');
            $table->string('phone');
            $table->enum('type', [
                'all',
                'billing',
                'contact',
            ])->default('all')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profile_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->references('id')->on('user_profiles');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('type', [
                'all',
                'billing',
                'contact',
            ])->default('all')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('imap_inboxes', function (Blueprint $table) {
            $table->id();
            $table->text('host')->nullable();
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->text('port')->nullable();
            $table->text('protocol')->nullable();
            $table->text('validate_cert')->nullable();
            $table->text('folder')->nullable();
            $table->boolean('delete_after_import');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imap_inbox_id')->nullable()->references('id')->on('imap_inboxes');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('email_address')->nullable();
            $table->string('email_name')->nullable();
            $table->boolean('email_import')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('support_categories');
            $table->string('imap_email')->nullable();
            $table->string('imap_name')->nullable();
            $table->string('subject');
            $table->enum('status', [
                'open',
                'closed',
                'locked',
            ]);
            $table->boolean('hold')->default(false);
            $table->boolean('escalated')->default(false);
            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'emergency',
            ])->default('low');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->enum('type', [
                'message',
                'priority',
                'assignment',
                'category',
                'escalation',
                'deescalation',
                'lock',
                'unlock',
                'hold',
                'unhold',
                'close',
                'reopen',
                'file',
            ]);
            $table->string('action');
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('support_categories');
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_run_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->references('id')->on('support_runs');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->string('type');
            $table->string('action');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('support_categories');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->enum('role', [
                'admin',
                'employee',
                'supplier',
                'customer',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->longText('message');
            $table->boolean('note')->default(false);
            $table->boolean('external')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->references('id')->on('support_tickets');
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('file_id')->references('id')->on('filemanager_files');
            $table->boolean('external')->default(false);
            $table->boolean('internal')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_importers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imap_input_id')->references('id')->on('imap_inboxes');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_importer_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('importer_id')->references('id')->on('invoice_importers');
            $table->foreignId('invoice_id')->nullable()->references('id')->on('invoices');
            $table->foreignId('file_id')->nullable()->references('id')->on('filemanager_files');
            $table->string('subject');
            $table->string('from');
            $table->string('from_name');
            $table->string('to');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->foreignId('contract_id')->nullable()->references('id')->on('contracts');
            $table->string('domain')->index()->unique();
            $table->text('database_driver')->nullable();
            $table->text('database_url')->nullable();
            $table->text('database_host')->nullable();
            $table->text('database_port')->nullable();
            $table->text('database_database')->nullable();
            $table->text('database_username')->nullable();
            $table->text('database_password')->nullable();
            $table->text('database_unix_socket')->nullable();
            $table->text('database_charset')->nullable();
            $table->text('database_collation')->nullable();
            $table->text('database_prefix')->nullable();
            $table->text('database_prefix_indexes')->nullable();
            $table->text('database_strict')->nullable();
            $table->text('database_engine')->nullable();
            $table->text('redis_prefix')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('invoice_importer_history');
        Schema::dropIfExists('invoice_importers');
        Schema::dropIfExists('support_ticket_files');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_ticket_assignments');
        Schema::dropIfExists('support_category_assignments');
        Schema::dropIfExists('support_run_history');
        Schema::dropIfExists('support_runs');
        Schema::dropIfExists('support_ticket_history');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_categories');
        Schema::dropIfExists('imap_inboxes');
        Schema::dropIfExists('user_profile_emails');
        Schema::dropIfExists('user_profile_phone_numbers');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('user_profile_history');
        Schema::dropIfExists('user_profile_addresses');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('filemanager_locks');
        Schema::dropIfExists('page_acceptance');
        Schema::dropIfExists('page_versions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_gateway_settings');
        Schema::dropIfExists('prepaid_history');
        Schema::dropIfExists('contract_history');
        Schema::dropIfExists('invoice_history');
        Schema::dropIfExists('position_discounts');
        Schema::dropIfExists('shop_product_settings');
        Schema::dropIfExists('shop_order_queue_history');
        Schema::dropIfExists('shop_order_queue_fields');
        Schema::dropIfExists('invoice_reminders');
        Schema::dropIfExists('invoice_positions');
        Schema::dropIfExists('invoice_dunning');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('tracker_instance_item_data');
        Schema::dropIfExists('tracker_items');
        Schema::dropIfExists('tracker_instances');
        Schema::dropIfExists('contract_positions');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('shop_order_queue');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('oauth_personal_access_clients');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('filemanager_files');
        Schema::dropIfExists('filemanager_folders');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('shop_configurator_field_options');
        Schema::dropIfExists('shop_configurator_fields');
        Schema::dropIfExists('shop_configurator_forms');
        Schema::dropIfExists('shop_configurator_categories');
        Schema::dropIfExists('trackers');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('users');
        Schema::dropIfExists('invoice_types');
        Schema::dropIfExists('address_countries');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('pulse_aggregates');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_values');
        Schema::dropIfExists('invoice_discounts');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('settings');
    }
};
