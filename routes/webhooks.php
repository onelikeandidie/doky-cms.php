<?php

use App\Http\Controllers\Webhooks\GithubController;
use Illuminate\Support\Facades\Route;

Route::get('/github', [GithubController::class, 'handle']);
Route::post('/github', [GithubController::class, 'handle']);
