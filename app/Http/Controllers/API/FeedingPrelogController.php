<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\FeedingPrelog;
use App\Http\Controllers\API\RecursoNecesarioController;
use Illuminate\Support\Facades\DB;


class FeedingPrelogController extends Controller
{
    /**
     * Listado de preregistros.
     * Soporta filtros por fecha, siembra, usuario y alimento.
     * Si se envía no_paginate=1, devuelve un array plano sin paginación.
     */
    public function index(Request $request)
    {
        $query = FeedingPrelog::query();

        // Filtro por fecha
        if ($request->filled('fecha_ra')) {
            $query->whereDate('fecha_ra', $request->fecha_ra);
        }

        // Filtro por siembra
        if ($request->filled('id_siembra')) {
            $query->where('id_siembra', $request->id_siembra);
        }

        // Filtro por registrador
        if ($request->filled('registered_user_id')) {
            $query->where('registered_user_id', $request->registered_user_id);
        }

        // Filtro por alimento (solo para alimentación)
        if ($request->filled('id_alimento')) {
            $query->where('id_alimento', $request->id_alimento);
        }

        // Filtro por recurso (solo para aireación)
        if ($request->filled('id_recurso')) {
            $query->where('id_recurso', $request->id_recurso);
        }

        // Filtro por tipo_actividad
        if ($request->filled('tipo_actividad')) {
            $query->where('tipo_actividad', $request->tipo_actividad);
        }

        // Filtro por tipo (alimentacion/recursos)
        if ($request->filled('tipo')) {
            if ($request->tipo === 'alimentacion') {
                // Para alimentación: excluir AIREACION y mostrar los que tienen alimento
                $query->where(function($q) {
                    $q->whereNull('tipo')
                    ->orWhere('tipo', '!=', 'AIREACION');
                });
            } elseif ($request->tipo === 'recursos') {
                // Para recursos/aireación: solo AIREACION
                $query->where('tipo', 'AIREACION');
            } else {
                // Si se pasa otro valor, filtrar por ese tipo específico
                $query->where('tipo', $request->tipo);
            }
        }

        // Ordenamiento
        $query->orderBy('fecha_ra', 'desc')
            ->orderBy('id_siembra')
            ->orderBy('created_at', 'desc');

        // Sin paginación (para exportar Excel)
        if ($request->boolean('no_paginate')) {
            $prelogs = $query->get();

            // Opcional: cargar relaciones si es necesario
            $prelogs->load(['siembra', 'alimento', 'recurso', 'registeredUser']);

            return response()->json($prelogs);
        }

        // Paginación normal
        $perPage = (int) $request->get('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }

        // Cargar relaciones con la paginación
        $query->with(['siembra', 'alimento', 'recurso', 'registeredUser']);
        
        $prelogs = $query->paginate($perPage);

        return response()->json($prelogs);
    }

    /**
     * Guarda o actualiza un preregistro de alimentación.
     *
     * Regla: solo se permite UN registro por combinación
     * (fecha_ra, id_siembra, id_alimento, id_recurso).
     * Para AIREACIÓN, el id_alimento puede ser null pero debe tener id_recurso.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'registered_user_id' => 'required|integer|exists:registered_users,id',
            'id_siembra'         => 'required|integer',
            'id_alimento'        => 'nullable|integer',
            'id_recurso'         => 'nullable|integer', // NUEVO: campo para recursos (aireación)
            'fecha_ra'           => 'required|date',

            'cant_manana'        => 'nullable|numeric',
            'cant_tarde'         => 'nullable|numeric',
            'minutos_hombre'     => 'nullable|numeric',
            'conv_alimenticia'   => 'nullable|numeric',
            'detalles'           => 'nullable|string',

            'tipo_actividad'     => 'nullable|integer',
            'tipo'               => 'nullable|string|max:100',
            'descripcion'        => 'nullable|string',
        ]);

        // Validar que al menos uno de los dos esté presente (alimento o recurso)
        if (empty($data['id_alimento']) && empty($data['id_recurso'])) {
            return response()->json([
                'message' => 'Debe proporcionar al menos un id_alimento o id_recurso.',
            ], 422);
        }

        // Para AIREACIÓN, verificar que tenga id_recurso
        if (($data['tipo'] ?? '') === 'AIREACION' && empty($data['id_recurso'])) {
            return response()->json([
                'message' => 'Para registros de AIREACIÓN debe proporcionar id_recurso.',
            ], 422);
        }

        // Buscar registro existente considerando tanto alimento como recurso
        $query = FeedingPrelog::where('id_siembra', $data['id_siembra'])
            ->whereDate('fecha_ra', $data['fecha_ra'])
            ->where('tipo', $data['tipo'] ?? 'ALIMENTACION');
            
        // Condición para alimento
        if (isset($data['id_alimento'])) {
            $query->where('id_alimento', $data['id_alimento']);
        } else {
            $query->whereNull('id_alimento');
        }
        
        // Condición para recurso
        if (isset($data['id_recurso'])) {
            $query->where('id_recurso', $data['id_recurso']);
        } else {
            $query->whereNull('id_recurso');
        }

        $prelog = $query->first();

        if ($prelog) {
            $prelog->fill($data);
            $prelog->save();

            return response()->json([
                'message' => 'Preregistro de alimentación actualizado correctamente.',
                'data'    => $prelog,
            ]);
        }

        $prelog = FeedingPrelog::create($data);

        return response()->json([
            'message' => 'Preregistro de alimentación creado correctamente.',
            'data'    => $prelog,
        ], 201);
    }

    /**
     * Actualiza un preregistro puntual (edición desde el panel interno).
     */
    public function update(Request $request, $id)
    {
        $prelog = FeedingPrelog::findOrFail($id);

        $data = $request->validate([
            'registered_user_id' => 'sometimes|integer|exists:registered_users,id',
            'id_siembra'         => 'sometimes|integer',
            'id_alimento'        => 'nullable|integer',
            'id_recurso'         => 'nullable|integer', // NUEVO
            'fecha_ra'           => 'sometimes|date',

            'cant_manana'        => 'nullable|numeric',
            'cant_tarde'         => 'nullable|numeric',
            'minutos_hombre'     => 'nullable|numeric',
            'conv_alimenticia'   => 'nullable|numeric',
            'detalles'           => 'nullable|string',

            'tipo_actividad'     => 'nullable|integer',
            'tipo'               => 'nullable|string|max:100',
            'descripcion'        => 'nullable|string',
        ]);

        // Validar que al menos uno de los dos esté presente
        if (empty($data['id_alimento']) && empty($data['id_recurso'])) {
            return response()->json([
                'message' => 'Debe proporcionar al menos un id_alimento o id_recurso.',
            ], 422);
        }

        $prelog->fill($data);
        $prelog->save();

        return response()->json([
            'message' => 'Preregistro de alimentación actualizado correctamente.',
            'data'    => $prelog,
        ]);
    }

    /**
     * Muestra un preregistro puntual.
     */
    public function show($id)
    {
        $prelog = FeedingPrelog::findOrFail($id);

        return response()->json($prelog);
    }

    /**
     * Elimina un preregistro.
     */
    public function destroy($id)
    {
        $prelog = FeedingPrelog::findOrFail($id);
        $prelog->delete();

        return response()->json([
            'message' => 'Preregistro eliminado correctamente.',
        ]);
    }

    /**
     * Sincroniza preregistros con recursos_necesarios:
     * - Crea un registro en RecursoNecesario por cada preregistro
     * - Actualiza stock (Kardex) a través de RecursoNecesarioController@store
     * - Elimina los preregistros ya sincronizados
     *
     * POST /api/feeding-prelogs/sync
     * Body:
     * {
     *   "ids": [1, 4, 7]
     * }
     */
    public function sync(Request $request)
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:feeding_prelogs,id',
        ]);

        $ids = $data['ids'];

        if (empty($ids)) {
            return response()->json([
                'message' => 'No se recibieron preregistros para sincronizar.',
            ], 422);
        }

        $prelogs = FeedingPrelog::whereIn('id', $ids)->get();

        if ($prelogs->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron preregistros con los IDs enviados.',
            ], 404);
        }

        $recursoController = new RecursoNecesarioController();
        $createdCount = 0;

        DB::beginTransaction();

        try {
            foreach ($prelogs as $prelog) {
                // Para AIREACIÓN, el id_alimento puede ser null
                $idAlimento = $prelog->tipo === 'AIREACION' ? null : $prelog->id_alimento;
                
                // Para AIREACION: cant_manana contiene la duración en minutos.
                // Fallback: extraer del campo detalles si cant_manana es 0 (registros anteriores).
                if ($prelog->tipo === 'AIREACION') {
                    $duracion = (int) ($prelog->cant_manana ?? 0);
                    if ($duracion <= 0 && preg_match('/Duraci[oó]n:\s*(\d+)\s*min/iu', $prelog->detalles ?? '', $m)) {
                        $duracion = (int) $m[1];
                    }
                    $cantidadRecurso = $duracion;
                    $cantManana      = 0;
                    $cantTarde       = 0;
                } else {
                    $cantidadRecurso = 0;
                    $cantManana      = $prelog->cant_manana ?? 0;
                    $cantTarde       = $prelog->cant_tarde  ?? 0;
                }

                // Construimos el payload que espera RecursoNecesarioController@store
                $payload = [
                    'registered_user_id' => $prelog->registered_user_id,
                    'id_recurso'        => $prelog->id_recurso,
                    'id_alimento'       => $idAlimento,
                    'tipo_actividad'    => $prelog->tipo_actividad ?? 1,
                    'fecha_ra'          => $prelog->fecha_ra,
                    'minutos_hombre'    => $prelog->minutos_hombre ?? 0,
                    'cantidad_recurso'  => $cantidadRecurso,
                    'cant_manana'       => $cantManana,
                    'cant_tarde'        => $cantTarde,
                    'conv_alimenticia'  => $prelog->conv_alimenticia,
                    'detalles'          => $prelog->detalles,
                    'id_siembra'        => $prelog->id_siembra,
                    'tipo'              => $prelog->tipo ?: 'ALIMENTACION',
                    'descripcion'       => $prelog->descripcion ?: 'ALIMENTACION SIEMBRAS (sincronizada)',
                ];

                $fakeRequest = new Request($payload);

                $recursoController->store($fakeRequest);

                $createdCount++;
            }

            FeedingPrelog::whereIn('id', $ids)->delete();

            DB::commit();

            return response()->json([
                'status'        => 'success',
                'message'       => "Se sincronizaron {$createdCount} preregistros correctamente.",
                'synced_count'  => $createdCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error al sincronizar los preregistros.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener el total de registros pendientes de alimentación (excluyendo AIREACION)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalPendientesAlimentacion()
    {
        try {
            // Contar solo registros de alimentación (excluyendo AIREACION)
            $total = FeedingPrelog::where(function($query) {
                $query->whereNull('tipo')
                      ->orWhere('tipo', '!=', 'AIREACION');
            })->count();
            
            return response()->json([
                'success' => true,
                'total' => $total
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el total de registros de alimentación pendientes: ' . $e->getMessage(),
                'total' => 0
            ], 500);
        }
    }

    /**
     * Obtener el total de registros pendientes de aireación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalPendientesAireacion()
    {
        try {
            // Contar solo registros de AIREACION
            $total = FeedingPrelog::where('tipo', 'AIREACION')->count();
            
            return response()->json([
                'success' => true,
                'total' => $total
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el total de registros de aireación pendientes: ' . $e->getMessage(),
                'total' => 0
            ], 500);
        }
    }

    /**
     * Obtener el total de todos los registros pendientes (alimentación + aireación)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalPendientes()
    {
        try {
            // Contar todos los registros
            $total = FeedingPrelog::count();
            
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

    public function analyzeRepairPrelogAireacion()
    {
        // Solo prelogs VIEJOS: cant_manana nulo o cero pero con duración en detalles
        $prelogs = DB::table('feeding_prelogs as fp')
            ->leftJoin('siembras as s',  's.id',  '=', 'fp.id_siembra')
            ->leftJoin('recursos as r',  'r.id',  '=', 'fp.id_recurso')
            ->where('fp.tipo', 'AIREACION')
            ->where(function($q) {
                $q->whereNull('fp.cant_manana')->orWhere('fp.cant_manana', '<=', 0);
            })
            ->orderBy('fp.fecha_ra', 'desc')
            ->select('fp.id', 'fp.fecha_ra', 'fp.detalles', 'fp.minutos_hombre',
                     'fp.cant_manana', 'fp.cant_tarde',
                     's.nombre_siembra', 'r.recurso', 'r.costo as costo_recurso')
            ->get();

        $toRepair = $prelogs->map(function ($row) {
            $detalles = $row->detalles ?? '';
            if (!preg_match('/Duraci[oó]n:\s*(\d+)\s*min/iu', $detalles, $m)) return null;
            $duration = (int) $m[1];
            if ($duration <= 0) return null;
            return [
                'id'                      => $row->id,
                'siembra'                 => $row->nombre_siembra ?? '-',
                'fecha_ra'                => $row->fecha_ra,
                'recurso'                 => $row->recurso ?? '-',
                'detalles'                => $detalles,
                'cantidad_recurso_actual' => 0,
                'cantidad_recurso_nueva'  => $duration,
                'costo_total_nuevo'       => $duration * ($row->costo_recurso ?? 0),
            ];
        })->filter()->values();

        return response()->json(['total' => $toRepair->count(), 'records' => $toRepair]);
    }

    public function executeRepairPrelogAireacion()
    {
        // Solo los viejos (cant_manana nulo o cero)
        $prelogs = FeedingPrelog::where('tipo', 'AIREACION')
            ->where(function($q) {
                $q->whereNull('cant_manana')->orWhere('cant_manana', '<=', 0);
            })->get();

        if ($prelogs->isEmpty()) {
            return response()->json(['message' => '0 registros sincronizados.', 'repaired' => 0, 'records' => []]);
        }

        $recursoController = new RecursoNecesarioController();
        $repaired = [];

        DB::beginTransaction();
        try {
            foreach ($prelogs as $prelog) {
                $duracion = (int) ($prelog->cant_manana ?? 0);
                if ($duracion <= 0) {
                    preg_match('/Duraci[oó]n:\s*(\d+)\s*min/iu', $prelog->detalles ?? '', $m);
                    $duracion = isset($m[1]) ? (int) $m[1] : 0;
                }

                $payload = [
                    'registered_user_id' => $prelog->registered_user_id,
                    'id_recurso'         => $prelog->id_recurso,
                    'id_alimento'        => null,
                    'tipo_actividad'     => $prelog->tipo_actividad ?? 12,
                    'fecha_ra'           => $prelog->fecha_ra,
                    'minutos_hombre'     => $prelog->minutos_hombre ?? 0,
                    'cantidad_recurso'   => $duracion,
                    'cant_manana'        => 0,
                    'cant_tarde'         => 0,
                    'conv_alimenticia'   => null,
                    'detalles'           => $prelog->detalles,
                    'id_siembra'         => $prelog->id_siembra,
                    'tipo'               => 'AIREACION',
                    'descripcion'        => $prelog->descripcion ?: 'Aireación sincronizada',
                ];

                $fakeRequest = new \Illuminate\Http\Request($payload);
                $recursoController->store($fakeRequest);

                $repaired[] = ['id' => $prelog->id, 'cantidad_recurso' => $duracion];
            }

            FeedingPrelog::whereIn('id', collect($repaired)->pluck('id'))->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message'  => count($repaired) . ' preregistros sincronizados y reparados correctamente.',
            'repaired' => count($repaired),
            'records'  => $repaired,
        ]);
    }
}