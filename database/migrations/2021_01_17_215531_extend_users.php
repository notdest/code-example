<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {


            $table->string('surname',255)       ->after('name')     ->default('');
            $table->string('department',255)    ->after('surname')  ->default('');

            $table->tinyInteger('blocked')      ->default(0);
            $table->tinyInteger('role')         ->default(0);
            $table->tinyInteger('show_posts')   ->default(0);
            $table->tinyInteger('show_articles')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('blocked');
            $table->dropColumn('role');
            $table->dropColumn('surname');
            $table->dropColumn('department');
            $table->dropColumn('show_posts');
            $table->dropColumn('show_articles');
        });
    }
}
