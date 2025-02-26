<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('openai_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('external_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('openai_messages', function (Blueprint $table) {
            $table->id();
            $table->string('openai_chat_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->longText('prompt');
            $table->longText('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Setting::insert([
            [
                'setting'    => 'openai.enabled',
                'value'      => encrypt(false),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'openai.api_key',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'openai.organization',
                'value'      => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'setting'    => 'openai.default_model',
                'value'      => encrypt('gpt-4o'),
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
        Setting::where(function ($query) {
            $query->where('setting', '=', 'openai.enabled')
                ->orWhere('setting', '=', 'openai.api_key')
                ->orWhere('setting', '=', 'openai.organization')
                ->orWhere('setting', '=', 'openai.default_model');
        })->forceDelete();

        Schema::dropIfExists('openai_prompts');
    }
};
