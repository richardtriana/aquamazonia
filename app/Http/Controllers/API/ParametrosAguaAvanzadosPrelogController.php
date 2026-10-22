<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ParametrosAguaAvanzadosPrelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\CalidadAgua;

class ParametrosAguaAvanzadosPrelogController extends Controller
{
    /**
     * Obtener preregistros
     */
    public function index(Request $request)
    {
        try {
            $query = ParametrosAguaAvanzadosPrelog::query();
            
            // Filtros básicos
            if ($request->has('id_siembra') && $request->id_siembra != -1) {
                $query->where('id_siembra', $request->id_siembra);
            }
            
            if ($request->has('fecha_parametro')) {
                $query->where('fecha_parametro', $request->fecha_parametro);
            }
            
            if ($request->has('registered_user_id')) {
                $query->where('registered_user_id', $request->registered_user_id);
            }
            
            // Filtro por tipo - ACEPTAR AMBOS TIPOS
            $query->where(function($q) {
                $q->where('tipo', 'PARAMETROS_AVANZADOS')
                  ->orWhere('tipo', 'PARAMETROS_CELADOR');
            });
            
            // Orden
            $query->orderBy('fecha_parametro', 'desc')
                  ->orderBy('created_at', 'desc');
            
            // Si no hay paginación (para app celador)
            if ($request->has('no_paginate') && $request->no_paginate) {
                $data = $query->get();
                return response()->json($data);
            }
            
            // Con paginación
            $perPage = $request->get('nro_results', 15);
            $data = $query->paginate($perPage);
            
            return response()->json([
                'data' => $data->items(),
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener preregistros',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar registro específico
     */
    public function buscar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_siembra' => 'required|exists:siembras,id',
                'fecha_parametro' => 'required|date',
                'registered_user_id' => 'required|exists:registered_users,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Buscar por cualquiera de los dos tipos
            $registro = ParametrosAguaAvanzadosPrelog::where('id_siembra', $request->id_siembra)
                ->where('fecha_parametro', $request->fecha_parametro)
                ->where('registered_user_id', $request->registered_user_id)
                ->where(function($q) {
                    $q->where('tipo', 'PARAMETROS_AVANZADOS')
                      ->orWhere('tipo', 'PARAMETROS_CELADOR');
                })
                ->first();
            
            if (!$registro) {
                return response()->json(null, 404);
            }
            
            return response()->json($registro);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar registro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar nuevo preregistro - ACTUALIZADO para nuevas horas
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'registered_user_id' => 'required|exists:registered_users,id',
                'id_siembra' => 'required|exists:siembras,id',
                'fecha_parametro' => 'required|date',
                'ph' => 'nullable|numeric|between:0,14',
                'amonio' => 'nullable|numeric|min:0',
                'nitrito' => 'nullable|numeric|min:0',
                'nitrato' => 'nullable|numeric|min:0',
                'otros' => 'nullable|string',
                'temperatura' => 'nullable|numeric',
                '12_am' => 'nullable|numeric',
                '4_am' => 'nullable|numeric',   // NUEVO: campo agregado
                '7_am' => 'nullable|numeric',   // Mantenido para compatibilidad
                '4_pm' => 'nullable|numeric',   // NUEVO: campo agregado
                '8_pm' => 'nullable|numeric',
                'descripcion' => 'nullable|string',
                'tipo' => 'nullable|string|max:50',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Verificar si ya existe un registro
            $existente = ParametrosAguaAvanzadosPrelog::where('id_siembra', $request->id_siembra)
                ->where('fecha_parametro', $request->fecha_parametro)
                ->where('registered_user_id', $request->registered_user_id)
                ->where('tipo', $request->tipo ?: 'PARAMETROS_AVANZADOS')
                ->first();
            
            $data = $request->all();
            
            // Asegurar que los valores vacíos se conviertan a null
            $horas = ['12_am', '4_am', '7_am', '4_pm', '8_pm', 'temperatura'];
            foreach ($horas as $hora) {
                if (isset($data[$hora]) && $data[$hora] === '') {
                    $data[$hora] = null;
                }
            }
            
            // Asegurar campos del sistema
            $data['tipo'] = $request->tipo ?: 'PARAMETROS_AVANZADOS';
            $data['fecha_registro'] = Carbon::now()->toDateString();
            $data['hora_registro'] = Carbon::now()->toTimeString('minute');
            
            if ($existente) {
                // Actualizar registro existente
                $existente->update($data);
                $registro = $existente;
                $mensaje = 'Registro actualizado';
            } else {
                // Crear nuevo registro
                $registro = ParametrosAguaAvanzadosPrelog::create($data);
                $mensaje = 'Registro creado exitosamente';
            }
            
            DB::commit();
            
            return response()->json([
                'message' => $mensaje,
                'data' => $registro
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error al guardar preregistro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar preregistro específico
     */
    public function show($id)
    {
        try {
            $registro = ParametrosAguaAvanzadosPrelog::findOrFail($id);
            
            return response()->json($registro);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener preregistro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar preregistro - ACTUALIZADO para nuevas horas
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $registro = ParametrosAguaAvanzadosPrelog::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'ph' => 'nullable|numeric|between:0,14',
                'amonio' => 'nullable|numeric|min:0',
                'nitrito' => 'nullable|numeric|min:0',
                'nitrato' => 'nullable|numeric|min:0',
                'otros' => 'nullable|string',
                'temperatura' => 'nullable|numeric',
                '12_am' => 'nullable|numeric',
                '4_am' => 'nullable|numeric',   // NUEVO: campo agregado
                '7_am' => 'nullable|numeric',   // Mantenido para compatibilidad
                '4_pm' => 'nullable|numeric',   // NUEVO: campo agregado
                '8_pm' => 'nullable|numeric',
                'descripcion' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $request->all();
            
            // Asegurar que los valores vacíos se conviertan a null
            $horas = ['12_am', '4_am', '7_am', '4_pm', '8_pm', 'temperatura'];
            foreach ($horas as $hora) {
                if (isset($data[$hora]) && $data[$hora] === '') {
                    $data[$hora] = null;
                }
            }
            
            $registro->update($data);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Registro actualizado exitosamente',
                'data' => $registro
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error al actualizar preregistro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar preregistro
     */
    public function destroy($id)
    {
        try {
            $registro = ParametrosAguaAvanzadosPrelog::findOrFail($id);
            $registro->delete();
            
            return response()->json([
                'message' => 'Preregistro eliminado'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar preregistro',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar preregistros a calidad_agua - ACTUALIZADO para nuevas horas
     */
    public function sincronizar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'error' => 'No se especificaron registros para sincronizar'
                ], 400);
            }
            
            $preregistros = ParametrosAguaAvanzadosPrelog::whereIn('id', $ids)->get();
            
            $sincronizados = 0;
            $errores = [];
            
            foreach ($preregistros as $preregistro) {
                try {
                    // Obtener el id_contenedor desde la siembra
                    $siembra = DB::table('siembras')->where('id', $preregistro->id_siembra)->first();
                    
                    if (!$siembra) {
                        $errores[] = [
                            'id' => $preregistro->id,
                            'error' => 'Siembra no encontrada'
                        ];
                        continue;
                    }
                    
                    $id_contenedor = $siembra->id_contenedor;
                    
                    // Preparar datos para calidad_agua - CON TODAS LAS HORAS
                    $datosCalidadAgua = [
                        'registered_user_id' => $preregistro->registered_user_id,
                        'id_siembra' => $preregistro->id_siembra,
                        'id_contenedor' => $id_contenedor,
                        'fecha_parametro' => $preregistro->fecha_parametro,
                        'ph' => $preregistro->ph,
                        'amonio' => $preregistro->amonio,
                        'nitrito' => $preregistro->nitrito,
                        'nitrato' => $preregistro->nitrato,
                        'otros' => $preregistro->otros,
                        'temperatura' => $preregistro->temperatura,
                        '12_am' => $preregistro->{'12_am'},
                        '4_am' => $preregistro->{'4_am'},     // NUEVO: campo agregado
                        '7_am' => $preregistro->{'7_am'},     // Mantenido
                        '4_pm' => $preregistro->{'4_pm'},     // NUEVO: campo agregado
                        '8_pm' => $preregistro->{'8_pm'},
                        'tipo' => $preregistro->tipo,
                        'descripcion' => $preregistro->descripcion,
                        'fecha_registro' => $preregistro->fecha_registro ?? Carbon::now()->toDateString(),
                        'hora_registro' => $preregistro->hora_registro ?? Carbon::now()->toTimeString('minute'),
                        'created_at' => $preregistro->created_at,
                        'updated_at' => $preregistro->updated_at,
                    ];
                    
                    // Verificar si ya existe un registro en calidad_agua para evitar duplicados
                    $existeCalidadAgua = CalidadAgua::where('id_siembra', $preregistro->id_siembra)
                        ->where('fecha_parametro', $preregistro->fecha_parametro)
                        ->where('registered_user_id', $preregistro->registered_user_id)
                        ->where('tipo', $preregistro->tipo)
                        ->first();
                    
                    if ($existeCalidadAgua) {
                        // Actualizar registro existente
                        $existeCalidadAgua->update($datosCalidadAgua);
                    } else {
                        // Crear nuevo registro
                        CalidadAgua::create($datosCalidadAgua);
                    }
                    
                    // Eliminar preregistro
                    $preregistro->delete();
                    
                    $sincronizados++;
                    
                } catch (\Exception $e) {
                    $errores[] = [
                        'id' => $preregistro->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Sincronización completada',
                'sincronizados' => $sincronizados,
                'errores' => $errores
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error en sincronización',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el total de registros pendientes de parámetros de agua
     */
    public function totalPendientes()
    {
        try {
            // Contar todos los preregistros de parámetros de agua (ambos tipos)
            $total = ParametrosAguaAvanzadosPrelog::where(function($q) {
                $q->where('tipo', 'PARAMETROS_AVANZADOS')
                  ->orWhere('tipo', 'PARAMETROS_CELADOR');
            })->count();
            
            return response()->json([
                'success' => true,
                'total' => $total
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el total de registros pendientes: ' . $e->getMessage(),
                'total' => 0
            ], 500);
        }
    }

    /**
     * Buscar registros por fecha y siembra
     */
    public function buscarPorFecha(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_siembra' => 'required|exists:siembras,id',
                'fecha_parametro' => 'required|date',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $registros = ParametrosAguaAvanzadosPrelog::where('id_siembra', $request->id_siembra)
                ->where('fecha_parametro', $request->fecha_parametro)
                ->where(function($q) {
                    $q->where('tipo', 'PARAMETROS_AVANZADOS')
                      ->orWhere('tipo', 'PARAMETROS_CELADOR');
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json($registros);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar registros',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar todos los preregistros pendientes
     */
    public function sincronizarTodos()
    {
        DB::beginTransaction();
        
        try {
            // Obtener todos los preregistros pendientes
            $preregistros = ParametrosAguaAvanzadosPrelog::where(function($q) {
                $q->where('tipo', 'PARAMETROS_AVANZADOS')
                  ->orWhere('tipo', 'PARAMETROS_CELADOR');
            })->get();
            
            $sincronizados = 0;
            $errores = [];
            
            foreach ($preregistros as $preregistro) {
                try {
                    // Obtener el id_contenedor desde la siembra
                    $siembra = DB::table('siembras')->where('id', $preregistro->id_siembra)->first();
                    
                    if (!$siembra) {
                        $errores[] = [
                            'id' => $preregistro->id,
                            'error' => 'Siembra no encontrada'
                        ];
                        continue;
                    }
                    
                    $id_contenedor = $siembra->id_contenedor;
                    
                    // Preparar datos para calidad_agua - CON TODAS LAS HORAS
                    $datosCalidadAgua = [
                        'registered_user_id' => $preregistro->registered_user_id,
                        'id_siembra' => $preregistro->id_siembra,
                        'id_contenedor' => $id_contenedor,
                        'fecha_parametro' => $preregistro->fecha_parametro,
                        'ph' => $preregistro->ph,
                        'amonio' => $preregistro->amonio,
                        'nitrito' => $preregistro->nitrito,
                        'nitrato' => $preregistro->nitrato,
                        'otros' => $preregistro->otros,
                        'temperatura' => $preregistro->temperatura,
                        '12_am' => $preregistro->{'12_am'},
                        '4_am' => $preregistro->{'4_am'},
                        '7_am' => $preregistro->{'7_am'},
                        '4_pm' => $preregistro->{'4_pm'},
                        '8_pm' => $preregistro->{'8_pm'},
                        'tipo' => $preregistro->tipo,
                        'descripcion' => $preregistro->descripcion,
                        'fecha_registro' => $preregistro->fecha_registro ?? Carbon::now()->toDateString(),
                        'hora_registro' => $preregistro->hora_registro ?? Carbon::now()->toTimeString('minute'),
                        'created_at' => $preregistro->created_at,
                        'updated_at' => $preregistro->updated_at,
                    ];
                    
                    // Verificar si ya existe un registro en calidad_agua
                    $existeCalidadAgua = CalidadAgua::where('id_siembra', $preregistro->id_siembra)
                        ->where('fecha_parametro', $preregistro->fecha_parametro)
                        ->where('registered_user_id', $preregistro->registered_user_id)
                        ->where('tipo', $preregistro->tipo)
                        ->first();
                    
                    if ($existeCalidadAgua) {
                        $existeCalidadAgua->update($datosCalidadAgua);
                    } else {
                        CalidadAgua::create($datosCalidadAgua);
                    }
                    
                    // Eliminar preregistro
                    $preregistro->delete();
                    
                    $sincronizados++;
                    
                } catch (\Exception $e) {
                    $errores[] = [
                        'id' => $preregistro->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Sincronización masiva completada',
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'total_procesados' => count($preregistros)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Error en sincronización masiva',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de preregistros
     */
    public function estadisticas()
    {
        try {
            $total = ParametrosAguaAvanzadosPrelog::count();
            $porTipo = ParametrosAguaAvanzadosPrelog::select('tipo', DB::raw('count(*) as total'))
                ->groupBy('tipo')
                ->get();
            
            $ultimaSemana = ParametrosAguaAvanzadosPrelog::where('created_at', '>=', Carbon::now()->subDays(7))
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_registros' => $total,
                    'por_tipo' => $porTipo,
                    'ultima_semana' => $ultimaSemana,
                    'promedio_diario' => $ultimaSemana > 0 ? round($ultimaSemana / 7, 2) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}