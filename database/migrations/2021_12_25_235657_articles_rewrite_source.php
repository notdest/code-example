<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArticlesRewriteSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles_rewrite', function (Blueprint $table) {
            $table->string('source',255)->default('')->after('link');
        });

        $articles   = \App\ArticleRewrite::orderBy('id', 'asc')->get()->all();;

        foreach ($articles as $article) {
            if(strpos($article->link,'womenshealthmag.com')){
                $article->source    = 'Womenshealthmag.com';
            }elseif (strpos($article->link,'eatthis.com')){
                $article->source    = 'Eatthis.com';
            }elseif (strpos($article->link,'psychologytoday.com')){
                $article->source    = 'Psychologytoday.com';
            }
            $article->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles_rewrite', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
