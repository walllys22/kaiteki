<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\MicroServiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\KumiteController;
use App\Http\Controllers\TableroKataController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AlumnoHistorialController;
use Illuminate\Support\Facades\Artisan;
use TCG\Voyager\Facades\Voyager;


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



Route::get('login', function () {
    return redirect('admin/login');
})->name('login');

Route::get('/', function () {
    return redirect('admin');
});

Route::get('/info/{id?}', [ErrorController::class , 'error'])->name('errors');
// Route::get('/development', [ErrorController::class , 'error503'])->name('development');

Route::group(['prefix' => 'admin', 'middleware' => ['loggin', 'system']], function () {
    Voyager::routes();

    // Rutas Alumnos
    Route::get('alumnos', [AlumnoController::class, 'index'])->name('voyager.alumnos.index');
    Route::get('alumnos/ajax/list', [AlumnoController::class, 'list']);
    Route::get('alumnos/create', [AlumnoController::class, 'create'])->name('voyager.alumnos.create');
    Route::post('alumnos/store', [AlumnoController::class, 'store'])->name('voyager.alumnos.store');
    Route::get('alumnos/{id}/edit', [AlumnoController::class, 'edit'])->name('voyager.alumnos.edit');
    Route::put('alumnos/{id}', [AlumnoController::class, 'update'])->name('voyager.alumnos.update');
    Route::get('alumnos/{id}', [AlumnoController::class, 'show'])->name('voyager.alumnos.show');




    //Rutas Alumnos Historial
    Route::get('alumnos/{id}/historial/browse', [AlumnoHistorialController::class, 'show'])->name('alumnos.historial.show');
    




    // Rutas Alumnos Tutores                                            metodo del controlador
    Route::get('alumnos/{id}/parentesco/list', [AlumnoController::class, 'tutorList']);
    Route::post('alumnos/tutores/store', [AlumnoController::class, 'storeAlumnoTutor'])->name('alumno.tutores.store');
    Route::delete('alumnos/tutores/{id}/delete', [AlumnoController::class, 'tutorDestroy'])->name('alumno.tutores.destroy');

    // Rutas Alumno Enfermedad
    Route::get('alumnos/{id}/enfermedade/list', [AlumnoController::class, 'enfermedadList']);
    Route::post('alumnos/enfermedade/store', [AlumnoController::class, 'storeAlumnoEnfermedad'])->name('alumno.enfermedade.store');
    Route::delete('alumnos/enfermedade/{id}/delete', [AlumnoController::class, 'enfermedadDestroy'])->name('alumno.enfermedade.destroy');



    // Rutas Torneos
    Route::get('torneos', [TorneoController::class, 'index'])->name('voyager.torneos.index');
    Route::get('torneos/ajax/list', [TorneoController::class, 'list']);
    Route::get('torneos/create', [TorneoController::class, 'create'])->name('voyager.torneos.create');
    Route::post('torneos/store', [TorneoController::class, 'store'])->name('voyager.torneos.store');
    Route::get('torneos/{id}/edit', [TorneoController::class, 'edit'])->name('voyager.torneos.edit');
    Route::get('torneos/{id}', [TorneoController::class, 'show'])->name('voyager.torneos.show');
    Route::put('torneos/{id}', [TorneoController::class, 'update'])->name('voyager.torneos.update');
    // Nota: El index de torneos usualmente lo maneja el BREAD de Voyager

    Route::get('torneos/{id}/categories/list', [TorneoController::class, 'categoryList'])->name('torneos.categories.list');
    Route::post('torneos/categories/store', [TorneoController::class, 'categoryStore'])->name('torneos.categories.store');
    Route::delete('torneos/categories/{id}/delete', [TorneoController::class, 'categoryDestroy'])->name('torneos.categories.destroy');

    // rutas del Toreno Dojos
    Route::get('torneos/{id}/dojos/list', [TorneoController::class, 'listDojos']);
    Route::post('torneos/dojos/store', [TorneoController::class, 'torneosDojosStore'])->name('torneos.torneosDojosStore.store'); 
    Route::delete('torneos/dojos/{id}/delete', [TorneoController::class, 'dojoDestroy'])->name('torneos.dojos.destroy');   

    Route::get('people', [PersonController::class, 'index'])->name('voyager.people.index');
    Route::get('people/ajax/list', [PersonController::class, 'list']);
    Route::post('people', [PersonController::class, 'store'])->name('voyager.people.store');
    Route::put('people/{id}', [PersonController::class, 'update'])->name('voyager.people.update');
    Route::get('people/{id}', [PersonController::class, 'show'])->name('voyager.people.show');
    
    Route::get('whatsapp', [MicroServiceController::class, 'message'])->name('whatsapp.message');

    // Users
    Route::get('users/ajax/list', [UserController::class, 'list']);
    Route::post('users/store', [UserController::class, 'store'])->name('voyager.users.store');
    Route::put('users/{id}', [UserController::class, 'update'])->name('voyager.users.update');
    Route::delete('users/{id}/deleted', [UserController::class, 'destroy'])->name('voyager.users.destroy');

    // Roles
    Route::get('roles/ajax/list', [RoleController::class, 'list']);


    Route::get('ajax/personList', [AjaxController::class, 'personList']);
    Route::post('ajax/person/store', [AjaxController::class, 'personStore']);

});

Route::get('/kumite-temporizador', [KumiteController::class, 'index'])->name('kumite.temporizador');
Route::get('/tablero-kata', [TableroKataController::class, 'index'])->name('tablero.kata');

// Clear cache
Route::get('/admin/clear-cache', function() {
    Artisan::call('optimize:clear');

    // Artisan::call('db:seed', ['--class' => 'UpdateBreadSeeder']);
    // Artisan::call('db:seed', ['--class' => 'UpdatePermissionsSeeder']);
    
    return redirect('/admin/profile')->with(['message' => 'Cache eliminada.', 'alert-type' => 'success']);
})->name('clear.cache');