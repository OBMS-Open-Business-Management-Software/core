<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AddRoleColumn extends Migration
{
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
            'name' => 'Marcel Menk',
            'email' => 'marcel.menk@ipvx.io',
            'password' => Hash::make('mha2u2gMuh'),
            'email_verified_at' => Carbon::now(),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Marcel Menk',
            'email' => 'marcel.menk2@ipvx.io',
            'password' => Hash::make('mha2u2gMuh'),
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
}
