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
use App\Http\Controllers\AlumnoGradoController;
use App\Http\Controllers\AlumnoHorarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\HorarioController;
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
    Route::get('alumnos/{id}', [AlumnoController::class, 'show'])->name('voyager.alumnos.show');




    // Rutas auxiliares de alumnos
    Route::get('alumnos/{id}/check-historial', [AlumnoController::class, 'checkHistorial'])->name('alumnos.check_historial');
    Route::post('alumnos/{id}/status', [AlumnoController::class, 'updateStatus'])->name('alumnos.status.update');
    Route::get('alumnos/check-registration/{person_id}', [AlumnoController::class, 'checkRegistration'])->name('alumnos.check_registration');
    Route::get('alumnos/imprimir/reporte', [AlumnoController::class, 'print'])->name('alumnos.print');
    




    // Rutas Alumnos Tutores                                            metodo del controlador
    Route::get('alumnos/{id}/parentesco/list', [AlumnoController::class, 'tutorList']);
    Route::get('alumnos/{id}/grados/list', [AlumnoController::class, 'gradoList']);
    Route::get('alumnos/{id}/asistencias/list', [AlumnoController::class, 'asistenciaList']);
    Route::post('alumnos/tutores/store', [AlumnoController::class, 'storeAlumnoTutor'])->name('alumno.tutores.store');
    Route::post('alumnos/tutores/{id}/status', [AlumnoController::class, 'tutorUpdateStatus'])->name('alumno.tutores.status.update');
    Route::delete('alumnos/tutores/{id}/delete', [AlumnoController::class, 'tutorDestroy'])->name('alumno.tutores.destroy');

    // Rutas Alumno Enfermedad
    Route::get('alumnos/{id}/enfermedade/list', [AlumnoController::class, 'enfermedadList']);
    Route::post('alumnos/enfermedade/store', [AlumnoController::class, 'storeAlumnoEnfermedad'])->name('alumno.enfermedade.store');
    Route::delete('alumnos/enfermedade/{id}/delete', [AlumnoController::class, 'enfermedadDestroy'])->name('alumno.enfermedade.destroy');

    // Rutas Alumno Horarios
    Route::get('alumnos/{id}/horarios/list', [AlumnoController::class, 'horarioList'])->name('alumno.horario.list');
    Route::post('alumnos/horario/store', [AlumnoHorarioController::class, 'store'])->name('alumno.horario.store');
    Route::delete('alumnos/horario/{id}/delete', [AlumnoHorarioController::class, 'destroy'])->name('alumno.horario.destroy');

    // Rutas Asistencias
    Route::get('asistencias', [AsistenciaController::class, 'index'])->name('voyager.asistencias.index');
    Route::get('asistencias/ajax/list', [AsistenciaController::class, 'list']);
    Route::get('asistencias/create', [AsistenciaController::class, 'create'])->name('voyager.asistencias.create');
    Route::get('asistencias/alumnos', [AsistenciaController::class, 'loadAlumnos'])->name('asistencias.load_alumnos');
    Route::post('asistencias/store', [AsistenciaController::class, 'store'])->name('voyager.asistencias.store');
    Route::get('asistencias/{id}', [AsistenciaController::class, 'show'])->name('voyager.asistencias.show');
    Route::delete('asistencias/{id}/delete', [AsistenciaController::class, 'destroy'])->name('voyager.asistencias.destroy');
    Route::put('asistencias/{id}/update', [AsistenciaController::class, 'update'])->name('asistencias.update');

    // Rutas Alumno Grados
    Route::post('alumnos/grado/store', [AlumnoGradoController::class, 'storeGrado'])->name('alumno.grado.store');
    Route::post('alumnos/grado/repaso/store', [AlumnoGradoController::class, 'storeRepaso'])->name('alumno.grado.repaso.store');
    Route::delete('alumnos/grado/repaso/{id}/delete', [AlumnoGradoController::class, 'destroyRepaso'])->name('alumno.grado.repaso.destroy');
    Route::post('alumnos/grado/examen/store', [AlumnoGradoController::class, 'storeExamen'])->name('alumno.grado.examen.store');
    Route::delete('alumnos/grado/examen/{id}/delete', [AlumnoGradoController::class, 'destroyExamen'])->name('alumno.grado.examen.destroy');



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

    Route::get('grados', [GradoController::class, 'index'])->name('voyager.grados.index');
    Route::get('grados/ajax/list', [GradoController::class, 'list']);

    Route::get('horarios', [HorarioController::class, 'index'])->name('voyager.horarios.index');
    Route::get('horarios/ajax/list', [HorarioController::class, 'list']);
    Route::get('horarios/{id}', [HorarioController::class, 'show'])->name('voyager.horarios.show');
    Route::post('horarios/responsables/store', [HorarioController::class, 'storeResponsible'])->name('horarios.responsables.store');
    
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
