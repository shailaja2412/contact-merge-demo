<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\ContactController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Contacts resource routes
    Route::resource('contacts', ContactController::class)
        ->only(['index','create','store','show','edit','update','destroy']);
    
    // Merge routes
    Route::get('contacts/{contact}/merge', [ContactController::class, 'showMergeModal'])->name('contacts.merge.show');
    Route::get('contacts/merge/list', [ContactController::class, 'getContactsForMerge'])->name('contacts.merge.list');
    Route::post('contacts/{contact}/merge', [ContactController::class, 'merge'])->name('contacts.merge');


    // Admin resourceful routes for custom fields (AJAX/REST-friendly) using existing controller
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // admin home -> custom fields index
        Route::get('/', [CustomFieldsController::class, 'index'])->name('index');

        // resource routes: index, store, show, update, destroy
        Route::resource('custom_fields', CustomFieldsController::class)
            ->only(['index','edit','store','show','update','destroy'])
            ->parameters(['custom_fields' => 'custom_field']);
    });
});

require __DIR__.'/auth.php';
