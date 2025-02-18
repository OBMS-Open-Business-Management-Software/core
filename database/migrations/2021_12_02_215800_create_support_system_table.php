<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imap_inbox_id')->index()->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('email_address')->nullable();
            $table->string('email_name')->nullable();
            $table->boolean('email_import')->default(false);
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

        Schema::create('support_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('role', [
                'admin',
                'employee',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
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

        Schema::create('support_ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->enum('role', [
                'admin',
                'employee',
                'supplier',
                'customer',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('file_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->boolean('external')->default(false);
            $table->boolean('internal')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->longText('message');
            $table->boolean('note')->default(false);
            $table->boolean('external')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->enum('type', [
                'status',
                'assignment',
                'hold',
                'escalate',
                'run',
                'category',
                'priority',
                'file',
            ]);
            $table->enum('action', [
                'open',
                'close',
                'reopen',
                'lock',
                'unlock',
                'assign',
                'unassign',
                'hold',
                'unhold',
                'escalate',
                'deescalate',
                'opened', // Usable in regard to type = run
                'move', // Usable in regard to type = category
                'set', // Usable in regard to type = priority
                'add', // Usable in regard to type = file
                'remove', // Usable in regard to type = file
            ]);
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index()->nullable();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_run_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('ticket_id')->index()->nullable();
            $table->enum('type', [
                'status',
                'message',
            ]);
            $table->enum('action', [
                'start', // Usable in regard to type = status
                'stop',
                'rotate', // Usable in regard to type = message
                'skip',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_run_history');
        Schema::dropIfExists('support_runs');
        Schema::dropIfExists('support_ticket_history');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_ticket_files');
        Schema::dropIfExists('support_ticket_assignments');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_category_assignments');
        Schema::dropIfExists('imap_inboxes');
        Schema::dropIfExists('support_categories');
    }
};
