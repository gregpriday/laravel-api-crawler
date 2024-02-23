<?php

use Illuminate\Support\Facades\Route;
use GregPriday\APICrawler\Tests\App\Article;

// Routes that should be cached.

Route::middleware(['bindings'])->group(function(){
    Route::get('/', function(){
        return view('home');
    })->name('home');

    Route::get('articles', function(){
        return view('articles.index', [
            'articles' => Article::paginate(2)
        ]);
    })->name('articles.index');

    Route::get('articles/{article}', function(Article $article){
        return view('articles.show', [
            'article' => $article
        ]);
    })->name('articles.show');
});
