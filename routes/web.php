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

// use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'DonationController@index')->name('donation.index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/donation', function(){
    return view('donation');
});

Route::post('/donation', 'DonationController@store')->name('donation.store');
