<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Homepage routing
Route::get('/', 'HomeController@showHome')->name('home');

// Individual Multimedia routing
Route::get('/multimedia/{irn}', 'MultimediaController@showMultimedia')->name('multimedia');

// Subset page routing
Route::get('/subset/{type}/{taxonomyirn}', 'MultimediaController@showSubset')->name('subset');

// Catalogue page routing
Route::get('/catalogue/{irn}', 'CatalogController@showCatalog')->name('catalog');

// Search pages
Route::get('/search', 'SearchController@showSearch')->name('search');
Route::post('/search-handle', 'SearchController@handleSearch')->name('handlesearch');
Route::get('/search-results/genus/{genus}/species/{species}/keywords/{keywords}', [
        'as' => 'searchresults',
        'uses' => 'SearchController@showSearchResults'
    ]
);
