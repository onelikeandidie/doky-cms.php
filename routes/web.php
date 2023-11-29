<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleSettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Create article routes
Route::get('/articles/create', [ArticleController::class, 'create'])
    ->middleware(['auth', 'permission:article.create'])
    ->name('articles.create.orphan');
Route::get('/articles/{article}/create', [ArticleController::class, 'create'])
    ->middleware(['auth', 'permission:article.create'])
    ->name('articles.create');
// Store article routes
Route::post('/articles', [ArticleController::class, 'store'])
    ->middleware(['auth', 'permission:article.create'])
    ->name('articles.store.orphan');
Route::post('/articles/{article}', [ArticleController::class, 'store'])
    ->middleware(['auth', 'permission:article.create'])
    ->name('articles.store');
// Typical article routes
Route::resource('articles', ArticleController::class)
    ->scoped([
        // This makes it so that the article slug is used in the route
        // making a SEO friendly URL like /articles/my-first-article
        'article' => 'slug',
    ])
    ->except(['create', 'store']);

Route::resource('articles.settings', ArticleSettingsController::class)
    ->scoped([
        'article' => 'slug',
    ])
    ->only(['edit', 'update']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->post('/dark-mode/toggle', function (Request $request) {
    session()->put('dark_mode', $request->input('dark_mode'));
    dump($request->input('dark_mode'));
})->name('dark-mode');

require __DIR__ . '/auth.php';
