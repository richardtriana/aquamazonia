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

/**
 * Rutas sueltas
 */
Route::get('siembras/listado-lotes', 'API\SiembraController@listadoLotes');
Route::get('siembras/listado', 'API\SiembraController@listadoSiembras');
Route::get('siembras/campos', 'API\SiembraController@campos');

Route::get('phases/get', 'PhaseController@getData');

Route::get('api/alimentos/list', 'API\AlimentosController@list');

/**
 * Recursos REST (sin incluir siembra-alimento-datos aquí)
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
    'recursos-siembras'            => 'API\RecursoSiembraController',
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
    
]);

/**
 * Rutas con namespace API
 */
Route::namespace('API')->group(function () {

    Route::post('registros-siembra/{id}', 'RegistroController@registrosxSiembra');
    Route::post('actualizarEstado/{id}', 'SiembraController@actualizarEstado');
    Route::post('siembras/cambiar-estado/{id}', 'SiembraController@changeStatus');

    Route::post('filtro-siembras', 'InformeSiembraController@filtroSiembras');
    Route::post('searchResults', 'RecursoNecesarioController@searchResults');
    Route::post('informe-recursos-totales', 'InformeController@informeRecursosTotales');

    Route::post('filtro-parametros', 'ParametroCalidadController@filtroParametros');
    Route::post('filtro-parametros-excel', 'ParametroCalidadController@filtroParametrosExcel');

    Route::post('parametro-x-contenedor/{id}', 'ParametroCalidadController@mostrarParametrosxContenedores');
    Route::post('parametro-x-contenedor-excel/{id}', 'ParametroCalidadController@mostrarParametrosxContenedoresExcel');

    Route::post('anadir-especie-siembra', 'SiembraController@anadirEspeciesxSiembra');
    Route::post('siembras-alimentacion/{id}', 'RecursoNecesarioController@siembraxAlimentacion');

    Route::get('listadoContenedores', 'ContenedorController@listadoContenedores');
    Route::get('especies-siembra-edita/{id}', 'SiembraController@getEspeciesSiembra');

    Route::get('lista-alimentacion', 'RecursoNecesarioController@alimentacion');
    Route::get('traer-siembras', 'SiembraController@traerSiembras');
    Route::get('traer-existencias', 'InformeController@traerExistencias');
    Route::get('traer-existencias-detalle', 'InformeController@traerExistenciasDetalle');
    Route::get('parametros-contenedores', 'ParametroCalidadController@listadoParametrosContenedores');

    Route::get('alimentos/list', 'AlimentosController@list');
    Route::get('food/search-item', 'AlimentosController@searchFood');
    Route::post('food/stock-update/{alimento}', 'AlimentosController@updateStockById');
    Route::post('resource/stock-update/{recurso}', 'RecursosController@updateStockById');

    // Asignación de alimentos por siembra
    Route::get('recursos-siembras', 'SiembraAlimentoController@porSiembra');
    Route::post('recursos-siembras', 'SiembraAlimentoController@store');
    Route::get('resource/list', 'RecursosController@list');

    // =============================
    // NUEVAS RUTAS PARA DATOS POR DEFECTO (siembra-alimento-datos)
    // =============================

    // Listar todos los datos por siembra con sus alimentos
    // GET /api/siembra-alimento-datos/por-siembra/{id}
    Route::get(
        'siembra-alimento-datos/por-siembra/{id}',
        'SiembraAlimentoDatoController@porSiembra'
    );

    // Buscar configuración concreta por siembra + alimento
    // GET /api/siembra-alimento-datos/buscar?siembra_id=XX&alimento_id=YY
    Route::get(
        'siembra-alimento-datos/buscar',
        'SiembraAlimentoDatoController@buscarPorSiembraAlimento'
    );

    // Guardar (crear/actualizar) configuración por siembra + alimento
    // POST /api/siembra-alimento-datos/guardar
    Route::post(
        'siembra-alimento-datos/guardar',
        'SiembraAlimentoDatoController@guardar'
    );
});

/**
 * Resource REST para siembra-alimento-datos
 * (lo dejamos DESPUÉS de las rutas especiales para que no choquen)
 */
Route::apiResource(
    'siembra-alimento-datos',
    'API\SiembraAlimentoDatoController'
);

/**
 * Rutas explícitas para registered-users (si quieres mantenerlas)
 * Nota: ya tienes apiResource('registered-users' => 'RegisteredUserController'),
 * pero estas no molestan porque apuntan al mismo controlador.
 */
Route::post('registered-users', 'RegisteredUserController@store');
Route::post('registered-users/login', 'RegisteredUserController@login');
Route::post('registered-users/login-pin', 'RegisteredUserController@loginByPin');

// =============================
// RUTAS PARA PREREGISTROS DE ALIMENTACIÓN Y AIREACIÓN
// =============================

// Preregistros de alimentación (app pública)
Route::apiResource('feeding-prelogs', 'API\FeedingPrelogController')
    ->only(['index', 'store', 'show', 'destroy']);

Route::put('feeding-prelogs/{id}', 'API\FeedingPrelogController@update');
Route::post('feeding-prelogs/sync', 'API\FeedingPrelogController@sync');

// Rutas para obtener totales pendientes de alimentación y aireación
Route::get('feeding-prelogs/total-pendientes/alimentacion', 'API\FeedingPrelogController@totalPendientesAlimentacion');
Route::get('feeding-prelogs/total-pendientes/aireacion', 'API\FeedingPrelogController@totalPendientesAireacion');
Route::get('feeding-prelogs/total-pendientes', 'API\FeedingPrelogController@totalPendientes');

// =============================
// NUEVAS RUTAS PARA DATOS POR DEFECTO (siembra-recurso-datos)
// =============================

// Listar todos los datos por siembra con sus recursos
// GET /api/siembra-recurso-datos/por-siembra/{id}
Route::get('siembra-recurso-datos/por-siembra/{id}', 'API\SiembraRecursoDatoController@porSiembra');

// Buscar configuración concreta por siembra + recurso (+ tipo_actividad_id opcional)
// GET /api/siembra-recurso-datos/buscar?siembra_id=XX&recurso_id=YY
Route::get('siembra-recurso-datos/buscar', 'API\SiembraRecursoDatoController@buscarPorSiembraRecurso');

// Guardar (crear/actualizar) configuración por siembra + recurso
// POST /api/siembra-recurso-datos/guardar
Route::post('siembra-recurso-datos/guardar', 'API\SiembraRecursoDatoController@guardar');

// Resource REST
Route::apiResource('siembra-recurso-datos', 'API\SiembraRecursoDatoController');

// =============================
// NOTIFICACIONES
// =============================
Route::get('notifications/counters', 'API\NotificationsController@counters');

// =============================
// RUTAS PARA PARÁMETROS AVANZADOS DE AGUA
// =============================

// IMPORTANTE: Primero las rutas específicas, luego el resource
Route::get('parametros-agua-avanzados-prelogs/total-pendientes', 'API\ParametrosAguaAvanzadosPrelogController@totalPendientes');
Route::post('parametros-agua-avanzados-prelogs/buscar', 'API\ParametrosAguaAvanzadosPrelogController@buscar');
Route::post('parametros-agua-avanzados-prelogs/sincronizar', 'API\ParametrosAguaAvanzadosPrelogController@sincronizar');

// Resource REST para parámetros de agua avanzados (al final para que no capture las rutas específicas)
Route::apiResource('parametros-agua-avanzados-prelogs', 'API\ParametrosAguaAvanzadosPrelogController')
    ->only(['index', 'store', 'show', 'destroy']);