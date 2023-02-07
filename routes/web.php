<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MultimediaController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\SearchController;

// Homepage routing
Route::get('/', [HomeController::class, 'showHome'])->name('home');

// Individual Multimedia routing
Route::get('/multimedia/{irn}', [MultimediaController::class, 'showMultimedia'])->name('multimedia');

// Subset page routing
Route::get('/subset/{type}/{taxonomyirn}', [MultimediaController::class, 'showSubset'])->name('subset');

// Catalogue page routing
Route::get('/catalogue/{irn}', [CatalogController::class, 'showCatalog'])->name('catalog');

// Search pages
Route::get('/search', [SearchController::class, 'showSearch'])->name('search');
Route::post('/search-handle', [SearchController::class, 'handleSearch'])->name('handlesearch');
Route::get('/search-results/genus/{genus}/species/{species}/keywords/{keywords}', [SearchController::class, 'showSearchResults'])->name('searchresults');
