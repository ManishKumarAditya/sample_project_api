<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\SessionControlller;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/student_register', [StudentController::class, 'store'])->name('student_register.user');
Route::post('/admin_register', [AdminController::class, 'store'])->name('admin_register.admin');
Route::put('/create_user', [UserController::class, 'create_user'])->name('create_user.user');
Route::post('/login', [SessionControlller::class, 'login'])->name('login.api');

Route::middleware('auth:api')->group(function() {
    Route::get('/logout', [SessionControlller::class, 'logout'])->name('logout.api');
    Route::get('/profile', [SessionControlller::class, 'profile'])->name('profile.api');
    
    Route::group(['middleware' => ['UserTypeCheck:Student']], function () {
        Route::put('/profile/update', [UserController::class, 'update'])->name('update.user');
    });

    Route::group(['middleware' => ['UserTypeCheck:Admin']], function () {
        Route::get('/student_list', [AdminController::class, 'index'])->name('student_list.admin');
        Route::get('/user/{id}/delete_user', [AdminController::class, 'delete_user'])->name('delete.admin');
    });
});


