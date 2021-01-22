<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FirstAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::create([
            'name'          => 'Admin',
            'email'         => 'admin@admin.admin',
            'password'      => Hash::make('zQT1MVOasPOg'),
            'blocked'       => 0,
            'role'          => User::ROLE_ADMIN,
            'surname'       => '',
            'show_posts'    => 1,
            'show_articles' => 1,
            'department'    => ''
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        User::where('email', 'admin@admin.admin')->delete();
    }
}
