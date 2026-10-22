<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\SiembraAlimento;

class SiembraAlimentoController extends Controller
{
    /**
     * GET /api/recursos-siembras?id_siembra=XX&tipo=ALIMENTACION
     */
    public function porSiembra(Request $request)
    {
        $request->validate([
            'id_siembra' => 'required|integer',
            'tipo'       => 'required|string'
        ]);

        $id = $request->id_siembra;

        $asignados = SiembraAlimento::where('siembra_id', $id)
            ->pluck('alimento_id')
            ->toArray();

        return response()->json([
            'id_siembra' => $id,
            'alimentos'  => $asignados,
            'tipo'       => $request->tipo
        ]);
    }

    /**
     * POST /api/recursos-siembras
     * Body:
     * {
     *   "id_siembra": 198,
     *   "alimentos": [1,3],
     *   "tipo": "ALIMENTACION"
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_siembra' => 'required|integer',
            'alimentos'  => 'required|array',
        ]);

        $id = $request->id_siembra;

        // Eliminamos asignaciones anteriores
        SiembraAlimento::where('siembra_id', $id)->delete();

        // Insertamos las nuevas
        foreach ($request->alimentos as $alimento_id) {
            SiembraAlimento::create([
                'siembra_id'  => $id,
                'alimento_id' => $alimento_id
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Alimentos asignados correctamente',
        ]);
    }
}
