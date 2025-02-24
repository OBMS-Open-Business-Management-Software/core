<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id');
            $table->double('reserved_prepaid_amount')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_invoice_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('cancellation_revoked_at')->nullable();
            $table->timestamp('cancelled_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('position_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_type_id');
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

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('original_id')->nullable();
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
            $table->unsignedBigInteger('type_id');
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
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('position_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('dunning_id');
            $table->unsignedBigInteger('file_id')->nullable();
            $table->timestamp('due_at');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('name');
            $table->string('description');
            $table->enum('type', [
                'normal',
                'auto_revoke', // Automatically revoke if not paid
                'prepaid',
            ]);
            $table->double('period')->nullable();
            $table->boolean('dunning')->default(false);
            $table->timestamps();
            $table->softDeletes();
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

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->double('amount');
            $table->integer('vat_percentage')->nullable();
            $table->double('quantity');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('position_discounts');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('invoice_discounts');
        Schema::dropIfExists('invoice_types');
        Schema::dropIfExists('invoice_reminders');
        Schema::dropIfExists('invoice_positions');
        Schema::dropIfExists('invoice_dunning');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('contract_positions');
        Schema::dropIfExists('contracts');
    }
};
