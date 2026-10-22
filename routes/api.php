<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Reparación de registros de aireación (ya sincronizados)
Route::get('recursos-necesarios/repair-aireacion',  'API\RecursoNecesarioController@analyzeRepairAireacion');
Route::post('recursos-necesarios/repair-aireacion', 'API\RecursoNecesarioController@executeRepairAireacion');

// Reparación de preregistros de aireación pendientes de sincronizar
Route::get('feeding-prelogs/repair-aireacion',  'API\FeedingPrelogController@analyzeRepairPrelogAireacion');
Route::post('feeding-prelogs/repair-aireacion', 'API\FeedingPrelogController@executeRepairPrelogAireacion');

// Rutas de respaldo BD (acceso web admin con sesión)
Route::get('backups',          'BackupController@index');
Route::post('backups',         'BackupController@store');
Route::delete('backups/{id}',  'BackupController@destroy');

/**
 * Rutas sueltas de configuración y fases
 */
Route::get('siembras/listado-lotes', 'API\SiembraController@listadoLotes');
Route::get('siembras/listado', 'API\SiembraController@listadoSiembras');
Route::get('siembras/campos', 'API\SiembraController@campos');
Route::get('phases/get', 'PhaseController@getData');

/**
 * GRUPO DE RUTAS CRÍTICAS (Namespace API)
 * Se definen ANTES de los apiResources para evitar conflictos de jerarquía (NotFoundHttpException)
 */
Route::namespace('API')->group(function () {

    // --- RUTAS ESPECÍFICAS DE ALIMENTOS ---
    Route::get('alimentos/list', 'AlimentosController@list');
    Route::get('food/search-item', 'AlimentosController@searchFood');
    Route::post('food/stock-update/{alimento}', 'AlimentosController@updateStockById');
    
    // --- RUTAS ESPECÍFICAS DE RECURSOS ---
    Route::get('resource/list', 'RecursosController@list');
    Route::post('resource/stock-update/{recurso}', 'RecursosController@updateStockById');

    // --- ACCIONES DE SIEMBRA ---
    Route::post('registros-siembra/{id}', 'RegistroController@registrosxSiembra');
    Route::post('actualizarEstado/{id}', 'SiembraController@actualizarEstado');
    Route::post('siembras/cambiar-estado/{id}', 'SiembraController@changeStatus');
    Route::post('anadir-especie-siembra', 'SiembraController@anadirEspeciesxSiembra');
    Route::post('siembras-alimentacion/{id}', 'RecursoNecesarioController@siembraxAlimentacion');
    Route::get('especies-siembra-edita/{id}', 'SiembraController@getEspeciesSiembra');
    Route::get('traer-siembras', 'SiembraController@traerSiembras');

    // --- INFORMES Y FILTROS ---
    Route::post('filtro-siembras', 'InformeSiembraController@filtroSiembras');
    Route::post('searchResults', 'RecursoNecesarioController@searchResults');
    Route::post('informe-recursos-totales', 'InformeController@informeRecursosTotales');
    Route::post('filtro-parametros', 'ParametroCalidadController@filtroParametros');
    Route::post('filtro-parametros-excel', 'ParametroCalidadController@filtroParametrosExcel');
    Route::post('parametro-x-contenedor/{id}', 'ParametroCalidadController@mostrarParametrosxContenedores');
    Route::post('parametro-x-contenedor-excel/{id}', 'ParametroCalidadController@mostrarParametrosxContenedoresExcel');
    
    // --- LISTADOS GENERALES ---
    Route::get('listadoContenedores', 'ContenedorController@listadoContenedores');
    Route::get('lista-alimentacion', 'RecursoNecesarioController@alimentacion');
    Route::get('traer-existencias', 'InformeController@traerExistencias');
    Route::get('traer-existencias-detalle', 'InformeController@traerExistenciasDetalle');
    Route::get('parametros-contenedores', 'ParametroCalidadController@listadoParametrosContenedores');

    // --- ASIGNACIÓN DE ALIMENTOS ---
    Route::get('recursos-siembras', 'SiembraAlimentoController@porSiembra');
    Route::post('recursos-siembras', 'SiembraAlimentoController@store');

    // --- SIEMBRA-ALIMENTO-DATOS (Rutas Especiales) ---
    Route::get('siembra-alimento-datos/por-siembra/{id}', 'SiembraAlimentoDatoController@porSiembra');
    Route::get('siembra-alimento-datos/buscar', 'SiembraAlimentoDatoController@buscarPorSiembraAlimento');
    Route::post('siembra-alimento-datos/guardar', 'SiembraAlimentoDatoController@guardar');

    // --- SIEMBRA-RECURSO-DATOS (Rutas Especiales) ---
    Route::get('siembra-recurso-datos/por-siembra/{id}', 'SiembraRecursoDatoController@porSiembra');
    Route::get('siembra-recurso-datos/buscar', 'SiembraRecursoDatoController@buscarPorSiembraRecurso');
    Route::post('siembra-recurso-datos/guardar', 'SiembraRecursoDatoController@guardar');

    // --- PREREGISTROS Y TOTALES PENDIENTES ---
    Route::get('feeding-prelogs/total-pendientes/alimentacion', 'FeedingPrelogController@totalPendientesAlimentacion');
    Route::get('feeding-prelogs/total-pendientes/aireacion', 'FeedingPrelogController@totalPendientesAireacion');
    Route::get('feeding-prelogs/total-pendientes', 'FeedingPrelogController@totalPendientes');
    Route::post('feeding-prelogs/sync', 'FeedingPrelogController@sync');

    // --- PARÁMETROS AGUA AVANZADOS ---
    Route::get('parametros-agua-avanzados-prelogs/total-pendientes', 'ParametrosAguaAvanzadosPrelogController@totalPendientes');
    Route::post('parametros-agua-avanzados-prelogs/buscar', 'ParametrosAguaAvanzadosPrelogController@buscar');
    Route::post('parametros-agua-avanzados-prelogs/sincronizar', 'ParametrosAguaAvanzadosPrelogController@sincronizar');

    // --- NOTIFICACIONES ---
    Route::get('notifications/counters', 'NotificationsController@counters');
});

/**
 * RECURSOS REST (apiResources)
 * Se definen DESPUÉS de las rutas específicas para asegurar que /list o /buscar funcionen.
 */
Route::apiResources([
    'contenedores'                 => 'API\ContenedorController',
    'actividades'                  => 'API\ActividadController',
    'alimentos'                    => 'API\AlimentosController',
    'recursos'                     => 'API\RecursosController',
    'especies'                     => 'API\EspeciesController',
    'usuarios'                     => 'API\UsuarioController',
    'siembras'                     => 'API\SiembraController',
    'registros'                    => 'API\RegistroController',
    'recursos-necesarios'          => 'API\RecursoNecesarioController',
    //'recursos-siembras'            => 'API\RecursoSiembraController',
    'informes'                     => 'API\InformeController',
    'parametros-calidad'           => 'API\ParametroCalidadController',
    'informes-siembras'            => 'API\InformeSiembraController',
    'informes-registros'           => 'API\InformeRegistroController',
    'informes-recursos-necesarios' => 'API\InformeRecursosNecesariosController',
    'informes-alimentos'           => 'API\InformeAlimentosController',
    'informes-biomasa-alimento'    => 'API\InfomeBiomasaAlimentoController',
    'phases'                       => 'PhaseController',
    'historial-alimentos-costos'   => 'API\HistorialAlimentoController',
    'historial-recursos-costos'    => 'API\HistorialRecursoController',
    'especies-siembra'             => 'API\EspeciesSiembraController',
    'warehouses'                   => 'WarehouseController',
    'resource-categories'          => 'ResourceCategoryController',
    'food-categories'              => 'FoodCategoryController',
    'providers'                    => 'ProviderController',
    'purchases'                    => 'PurchaseController',
    'brands'                       => 'BrandController',
    'kardex'                       => 'KardexController',
    'registered-users'             => 'RegisteredUserController',
    'siembra-alimento-datos'       => 'API\SiembraAlimentoDatoController',
    'siembra-recurso-datos'        => 'API\SiembraRecursoDatoController',
    'feeding-prelogs'              => 'API\FeedingPrelogController',
    'parametros-agua-avanzados-prelogs' => 'API\ParametrosAguaAvanzadosPrelogController',
]);

/**
 * RUTAS DE AUTENTICACIÓN Y LOGIN (RegisteredUserController)
 */
Route::post('registered-users', 'RegisteredUserController@store');
Route::post('registered-users/login', 'RegisteredUserController@login');
Route::post('registered-users/login-pin', 'RegisteredUserController@loginByPin');

// Rutas de actualización de preregistros (Fuera del resource por método PUT)
Route::put('feeding-prelogs/{id}', 'API\FeedingPrelogController@update');