<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\SiembraRecursoDato;
use Illuminate\Http\Request;

class SiembraRecursoDatoController extends Controller
{
    /**
     * Listar configuraciones.
     * GET /api/siembra-recurso-datos?siembra_id=1&recurso_id=45
     */
    public function index(Request $request)
    {
        $query = SiembraRecursoDato::with(['siembra', 'recurso']);

        if ($request->filled('siembra_id')) {
            $query->where('siembra_id', $request->siembra_id);
        }

        if ($request->filled('recurso_id')) {
            $query->where('recurso_id', $request->recurso_id);
        }

        if ($request->filled('tipo_actividad_id')) {
            $query->where('tipo_actividad_id', $request->tipo_actividad_id);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Crear / actualizar configuración por (siembra, recurso, tipo_actividad).
     * POST /api/siembra-recurso-datos
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'siembra_id'        => 'required|integer|exists:siembras,id',
            'recurso_id'        => 'required|integer|exists:recursos,id',
            'tipo_actividad_id' => 'nullable|integer',
            'minutos_hombre'    => 'nullable|numeric',
            'detalles'          => 'nullable|string',
        ]);

        if (empty($data['tipo_actividad_id'])) {
            $data['tipo_actividad_id'] = 12; // Aireación Mecánica
        }

        $registro = SiembraRecursoDato::updateOrCreate(
            [
                'siembra_id'        => $data['siembra_id'],
                'recurso_id'        => $data['recurso_id'],
                'tipo_actividad_id' => $data['tipo_actividad_id'],
            ],
            [
                'minutos_hombre' => $data['minutos_hombre'] ?? null,
                'detalles'       => $data['detalles'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Datos por recurso/siembra guardados correctamente.',
            'data'    => $registro,
        ], 201);
    }

    /**
     * Alias por si llamas a /guardar desde el front.
     * POST /api/siembra-recurso-datos/guardar
     */
    public function guardar(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Mostrar un registro puntual por ID.
     */
    public function show($id)
    {
        $registro = SiembraRecursoDato::with(['siembra', 'recurso'])
            ->findOrFail($id);

        return $registro;
    }

    /**
     * Actualizar un registro puntual por ID.
     */
    public function update(Request $request, $id)
    {
        $registro = SiembraRecursoDato::findOrFail($id);

        $data = $request->validate([
            'siembra_id'        => 'sometimes|integer|exists:siembras,id',
            'recurso_id'        => 'sometimes|integer|exists:recursos,id',
            'tipo_actividad_id' => 'sometimes|integer',
            'minutos_hombre'    => 'nullable|numeric',
            'detalles'          => 'nullable|string',
        ]);

        $registro->update($data);

        return response()->json([
            'message' => 'Datos actualizados correctamente.',
            'data'    => $registro,
        ], 200);
    }

    /**
     * Eliminar configuración (por ID)
     */
    public function destroy($id)
    {
        $registro = SiembraRecursoDato::findOrFail($id);
        $registro->delete();

        return response()->json([
            'message' => 'Registro eliminado correctamente.',
        ], 200);
    }

    /**
     * Obtener todos los datos por siembra.
     * GET /api/siembra-recurso-datos/por-siembra/{siembra_id}
     */
    public function porSiembra($siembraId)
    {
        $registros = SiembraRecursoDato::with('recurso')
            ->where('siembra_id', $siembraId)
            ->get();

        return $registros;
    }

    /**
     * Buscar configuración por (siembra_id, recurso_id, tipo_actividad_id=12)
     * GET /api/siembra-recurso-datos/buscar?siembra_id=XX&recurso_id=YY
     */
    public function buscarPorSiembraRecurso(Request $request)
    {
        $data = $request->validate([
            'siembra_id'        => 'required|integer|exists:siembras,id',
            'recurso_id'        => 'required|integer|exists:recursos,id',
            'tipo_actividad_id' => 'nullable|integer',
        ]);

        $tipo = $data['tipo_actividad_id'] ?? 12;

        $registro = SiembraRecursoDato::where('siembra_id', $data['siembra_id'])
            ->where('recurso_id', $data['recurso_id'])
            ->where('tipo_actividad_id', $tipo)
            ->first();

        if (!$registro) {
            return response()->json(null, 204);
        }

        return response()->json($registro, 200);
    }
}
