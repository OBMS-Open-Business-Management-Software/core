<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'customer',
                'supplier',
                'employee',
                'admin',
                'api',
            ])->default('customer');
        });

        User::create([
            'name' => 'OBMS Admin',
            'email' => 'admin@obms.local',
            'password' => Hash::make('admin'),
            'email_verified_at' => Carbon::now(),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'OBMS Customer',
            'email' => 'customer@obms.local',
            'password' => Hash::make('customer'),
            'email_verified_at' => Carbon::now(),
            'role' => 'customer',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
