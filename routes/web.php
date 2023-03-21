<?php

// Controllers
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\GestionnaireController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\RolePermission;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
// Packages

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

require __DIR__ . '/auth.php';

Route::get('/storage', function () {
    Artisan::call('storage:link');
});


//UI Pages Routs
Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/month', [HomeController::class, 'agenda_month'])->name('agenda_month');
Route::get('/week', [HomeController::class, 'agenda_week'])->name('agenda_week');
Route::get('/day', [HomeController::class, 'agenda_day'])->name('agenda_day');
Route::get('/calendarevent', [HomeController::class, 'calendarevent'])->name('calendarevent');

//Icons Page Routs
Route::group(['prefix' => 'icons'], function() {
    Route::get('solid', [HomeController::class, 'solid'])->name('icons.solid');
    Route::get('outline', [HomeController::class, 'outline'])->name('icons.outline');
    Route::get('dualtone', [HomeController::class, 'dualtone'])->name('icons.dualtone');
    Route::get('colored', [HomeController::class, 'colored'])->name('icons.colored');
});
Route::group(['middleware' => 'auth'], function () {
    // Permission Module
    Route::get('/role-permission', [RolePermission::class, 'index'])->name('role.permission.list');
    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);
    // Dashboard Routes
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/myreservation', [HomeController::class, 'myreservation'])->name('myreservation');
    Route::get('/listreservation', [HomeController::class, 'listreservation'])->name('listreservation');
    Route::get('/waitreservation', [HomeController::class, 'waitreservation'])->name('waitreservation');
    Route::post('reservation/denier', [HomeController::class, 'annulerreservation'])->name('annulerreservation');
    Route::get('reservation/{id}/activate', [HomeController::class, 'activatereservation'])->name('activatereservation');
    Route::get('reservation/{id}/delete', [HomeController::class, 'deletereservation'])->name('deletereservation');
    // Users Module
    Route::resource('users', UserController::class);

    Route::resource('gestionnaires', GestionnaireController::class);
    Route::post('gestionnaires/store', [GestionnaireController::class, 'store'])->name('gestionnaires.store');

    Route::resource('personnels', PersonnelController::class);
    Route::post('personnels/store', [PersonnelController::class, 'store'])->name('personnels.store');
    Route::resource('config', ConfigController::class);

    Route::get('/indexgrouplocal', [ConfigController::class, 'indexgrouplocal'])->name('config.indexgrouplocal');
    Route::get('/indexlocal', [ConfigController::class, 'indexlocal'])->name('config.indexlocal');
    Route::get('/indextypesalle', [ConfigController::class, 'indextypesalle'])->name('config.indextypesalle');
    Route::get('/indextypejour', [ConfigController::class, 'indextypejour'])->name('config.indextypejour');
    Route::get('/indexperiode', [ConfigController::class, 'indexperiode'])->name('config.indexperiode');
    Route::get('/indextypeaccessoire', [ConfigController::class, 'indextypeaccessoire'])->name('config.indextypeaccessoire');

    Route::get('/createlocal', [ConfigController::class, 'createlocal'])->name('config.createlocal');
    Route::get('/creategrouplocal', [ConfigController::class, 'creategrouplocal'])->name('config.creategrouplocal');
    Route::get('/createtypesalle', [ConfigController::class, 'createtypesalle'])->name('config.createtypesalle');
    Route::get('/createtypejour', [ConfigController::class, 'createtypejour'])->name('config.createtypejour');
    Route::get('/createperiode', [ConfigController::class, 'createperiode'])->name('config.createperiode');
    Route::get('/createtypeaccessoire', [ConfigController::class, 'createtypeaccessoire'])->name('config.createtypeaccessoire');

    Route::post('config/storecreneaux', [ConfigController::class, 'storecreneaux'])->name('config.storecreneaux');
    Route::post('config/storetypeaccessoire', [ConfigController::class, 'storetypeaccessoire'])->name('config.storetypeaccessoire');
    Route::post('config/storetypejour', [ConfigController::class, 'storetypejour'])->name('config.storetypejour');
    Route::post('config/storetypesalle', [ConfigController::class, 'storetypesalle'])->name('config.storetypesalle');
    Route::post('config/storelocal', [ConfigController::class, 'storelocal'])->name('config.storelocal');
    Route::post('config/storegrouplocal', [ConfigController::class, 'storegrouplocal'])->name('config.storegrouplocal');
    Route::post('config/storeperiode', [ConfigController::class, 'storeperiode'])->name('config.storeperiode');
/// Reservation routes
    Route::get('reservation/start', [HomeController::class, 'startreservation'])->name('startreservation');
    Route::get('reservation/add', [HomeController::class, 'addreservation'])->name('addreservation');
    Route::get('reservation/addhome', [HomeController::class, 'addreservation_home'])->name('addreservation_home');
    Route::post('ajax/postreservation', [HomeController::class, 'ajaxpostreservation'])->name('ajaxpostreservation');
    Route::get('ajax/getsalle', [HomeController::class, 'ajaxgetsalle'])->name('ajaxgetsalle');
});
//Auth pages Routs
Route::group(['prefix' => 'auth'], function() {
    Route::get('signin', [HomeController::class, 'signin'])->name('auth.signin');
    Route::get('signup', [HomeController::class, 'signup'])->name('auth.signup');
    Route::get('confirmmail', [HomeController::class, 'confirmmail'])->name('auth.confirmmail');
    Route::get('lockscreen', [HomeController::class, 'lockscreen'])->name('auth.lockscreen');
    Route::get('recoverpw', [HomeController::class, 'recoverpw'])->name('auth.recoverpw');
    Route::get('userprivacysetting', [HomeController::class, 'userprivacysetting'])->name('auth.userprivacysetting');
// La redirection vers le provider
    Route::get('redirect/{provider}', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
// Le callback du provider
    Route::get('callback/{provider}', [SocialiteController::class, 'callback'])->name('socialite.callback');
});
