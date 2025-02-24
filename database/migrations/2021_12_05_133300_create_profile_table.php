<?php

declare(strict_types=1);

use App\Models\Address\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
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
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('address_id');
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
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('user_id');
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
            $table->unsignedBigInteger('profile_id');
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

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->string('street');
            $table->string('housenumber');
            $table->string('addition')->nullable();
            $table->string('postalcode');
            $table->string('city');
            $table->string('state');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('address_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('eu')->default(false);
            $table->boolean('reverse_charge')->default(false);
            $table->double('vat_basic');
            $table->double('vat_reduced')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Country::create([
            'name'           => 'Germany',
            'eu'             => true,
            'reverse_charge' => true,
            'vat_basic'      => 19,
            'vat_reduced'    => 7,
        ]);

        Schema::create('user_profile_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id');
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
            $table->unsignedBigInteger('profile_id');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_profile_emails');
        Schema::dropIfExists('user_profile_phone_numbers');
        Schema::dropIfExists('address_countries');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('user_profile_history');
        Schema::dropIfExists('user_profile_addresses');
        Schema::dropIfExists('user_profiles');
    }
};
