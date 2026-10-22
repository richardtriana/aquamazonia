<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\SiembraAlimentoDato;
use Illuminate\Http\Request;

class SiembraAlimentoDatoController extends Controller
{
    /**
     * Listar configuraciones.
     * Soporta filtros por siembra_id y alimento_id:
     * GET /api/siembra-alimento-datos?siembra_id=1&alimento_id=2
     */
    public function index(Request $request)
    {
        $query = SiembraAlimentoDato::with(['siembra', 'alimento']);

        if ($request->filled('siembra_id')) {
            $query->where('siembra_id', $request->siembra_id);
        }

        if ($request->filled('alimento_id')) {
            $query->where('alimento_id', $request->alimento_id);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Crear / actualizar configuración por (siembra, alimento).
     * Si ya existe el par (siembra_id, alimento_id), se actualiza.
     * POST /api/siembra-alimento-datos
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'siembra_id'        => 'required|integer|exists:siembras,id',
            'alimento_id'       => 'required|integer|exists:alimentos,id',
            'minutos_hombre'    => 'nullable|numeric',
            'conv_alimenticia'  => 'nullable|numeric',
            'detalles'          => 'nullable|string',
        ]);

        $registro = SiembraAlimentoDato::updateOrCreate(
            [
                'siembra_id'  => $data['siembra_id'],
                'alimento_id' => $data['alimento_id'],
            ],
            [
                'minutos_hombre'   => $data['minutos_hombre']   ?? null,
                'conv_alimenticia' => $data['conv_alimenticia'] ?? null,
                'detalles'         => $data['detalles']         ?? null,
            ]
        );

        return response()->json([
            'message' => 'Datos por alimento/siembra guardados correctamente.',
            'data'    => $registro,
        ], 201);
    }

    /**
     * Alias por si estás llamando a /guardar desde el front
     * POST /api/siembra-alimento-datos/guardar
     */
    public function guardar(Request $request)
    {
        // Simplemente reutiliza store()
        return $this->store($request);
    }

    /**
     * Mostrar un registro puntual por ID
     */
    public function show($id)
    {
        $registro = SiembraAlimentoDato::with(['siembra', 'alimento'])
            ->findOrFail($id);

        return $registro;
    }

    /**
     * Actualizar un registro puntual por ID.
     */
    public function update(Request $request, $id)
    {
        $registro = SiembraAlimentoDato::findOrFail($id);

        $data = $request->validate([
            'siembra_id'        => 'sometimes|integer|exists:siembras,id',
            'alimento_id'       => 'sometimes|integer|exists:alimentos,id',
            'minutos_hombre'    => 'nullable|numeric',
            'conv_alimenticia'  => 'nullable|numeric',
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
        $registro = SiembraAlimentoDato::findOrFail($id);
        $registro->delete();

        return response()->json([
            'message' => 'Registro eliminado correctamente.',
        ], 200);
    }

    /**
     * Obtener todos los datos por siembra.
     * GET /api/siembra-alimento-datos/por-siembra/{siembra_id}
     */
    public function porSiembra($siembraId)
    {
        $registros = SiembraAlimentoDato::with('alimento')
            ->where('siembra_id', $siembraId)
            ->get();

        return $registros;
    }

    /**
     * Buscar configuración por (siembra_id, alimento_id)
     * GET /api/siembra-alimento-datos/buscar?siembra_id=XX&alimento_id=YY
     */
    public function buscarPorSiembraAlimento(Request $request)
    {
        $data = $request->validate([
            'siembra_id'  => 'required|integer|exists:siembras,id',
            'alimento_id' => 'required|integer|exists:alimentos,id',
        ]);

        $registro = SiembraAlimentoDato::where('siembra_id', $data['siembra_id'])
            ->where('alimento_id', $data['alimento_id'])
            ->first();

        // Si no hay configuración, devolvemos 204 sin contenido
        if (!$registro) {
            return response()->json(null, 204);
        }

        return response()->json($registro, 200);
    }
}
