<?php

use App\Http\Controllers\Admin\PanelController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
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
    return redirect()->route('articles.index');
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
    ->name('articles.store.orphan');
Route::post('/articles/{article}', [ArticleController::class, 'store'])
    ->name('articles.store');
// Typical article routes
Route::resource('articles', ArticleController::class)
    ->where([
        'article' => '.*',
    ])
    ->scoped([
        // This makes it so that the article slug is used in the route
        // making a SEO friendly URL like /articles/my-first-article
        'article' => 'slug',
    ])
    ->except(['create', 'store', 'show']);
Route::get('/articles/{article}', [ArticleController::class, 'show'])
    ->where('article', '.*')
    ->setBindingFields([
        'article' => 'slug',
    ])
    ->name('articles.show');

Route::get('/dashboard', [PanelController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/dashboard/sync/download', [PanelController::class, 'syncDownload'])
    ->middleware(['auth', 'verified'])->name('dashboard.sync.download');
Route::post('/dashboard/sync/upload', [PanelController::class, 'syncUpload'])
    ->middleware(['auth', 'verified'])->name('dashboard.sync.upload');
Route::get('/dashboard/sync/init', [PanelController::class, 'syncInit'])
    ->middleware(['auth', 'verified'])->name('dashboard.sync.init');

Route::post('/dark-mode/toggle', function (Request $request) {
    session()->put('dark_mode', $request->input('dark_mode'));
    dump($request->input('dark_mode'));
})->name('dark-mode');

Route::middleware(['auth', 'permission:article.create'])
    ->post('/upload/image', [UploadController::class, 'storeImage'])
    ->name('upload.image');

require __DIR__ . '/auth.php';
