<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemUserController;

Route::post('/login', [SystemUserController::class, 'login_User']);
Route::post('/logout', [SystemUserController::class, 'logoutUser']);


