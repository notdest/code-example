<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesRewrite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_rewrite', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title',255);
            $table->string('foreign_title',255);
            $table->string('link',800);
            $table->text('original_text');
            $table->text('translated_text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_rewrite');
    }
}
