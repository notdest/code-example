<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleRewrite extends Model
{
    use HasFactory;


    protected $table    = 'articles_rewrite';
    protected $fillable = [
        'title', 'foreign_title', 'link', 'original_text', 'translated_text', 'source'
    ];
}
