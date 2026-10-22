<?php

namespace App\Http\Controllers\API;

use App\Alimento;
use App\Http\Controllers\Controller;
use App\Http\Controllers\KardexController;
use Illuminate\Http\Request;
use App\RecursoNecesario;
use App\RecursoSiembra;
use App\Recursos;
use App\Siembra;
use App\User;
use Illuminate\Support\Facades\DB;

class RecursoNecesarioController extends Controller
{

    public function index(Request $request)
    {
        $tipo_actividad = "recursos_necesarios.id";
        $filtro_tipo_actividad = "!=";
        $c2 = "-1";
        $fechaRegistroAlimentoInicio = "recursos_necesarios.fecha_ra";
        $signoFechaRegistroAlimentoInicio = "!=";
        $valorFechaRegistroAlimentoInicio = "-3";

        $filtroFechaRegistroAlimentoHasta = "recursos_necesarios.id";
        $signoFechaRegistroAlimentoHasta = "!=";
        $valorFechaRegistroAlimentohasta = "-1";

        $c5 = "recursos_necesarios.id";
        $op3 = "!=";
        $c6 = "-1";
        $filtroIdAlimento = "recursos_necesarios.id";
        $op5 = "!=";
        $c10 = "-1";
        $filtroIdRecurso = 'recursos_necesarios.id';
        $op6 = '!=';
        $c12 = '-1';
        $filtroIdSiembra = 'recursos_necesarios.id';
        $signoIdSiembra = '!=';
        $valorIdSiembra = '-1';

        $filtroEstadoSiembra = 'recursos_necesarios.estado';
        $signoEstadoSiembra = '!=';
        $valorEstadoSiembra = '-1';

        if ($request['tipo_actividad'] != '-1') {
            $tipo_actividad = "tipo_actividad";
            $filtro_tipo_actividad = '=';
            $id_tipo_actividad = $request['tipo_actividad'];
        } elseif ($request['tipo_actividad'] == '-1') {
            $tipo_actividad = "tipo_actividad";
            $filtro_tipo_actividad = '!=';
            $id_tipo_actividad = '-1';
        }

        if ($request['fecha_ra1'] != '-3') {
            $fechaRegistroAlimentoInicio = "fecha_ra";
            $signoFechaRegistroAlimentoInicio = '>=';
            $valorFechaRegistroAlimentoInicio = $request['fecha_ra1'];
        }
        if ($request['fecha_ra2'] != '-1') {
            $filtroFechaRegistroAlimentoHasta = "fecha_ra";
            $signoFechaRegistroAlimentoHasta = '<=';
            $valorFechaRegistroAlimentohasta = $request['fecha_ra2'];
        }

        if (isset($request['alimento_s']) && $request['alimento_s'] != '-1') {
            $filtroIdAlimento = "id_alimento";
            $op5 = '=';
            $c10 = $request['alimento_s'];
        }
        if (isset($request['recurso_s']) && ($request['recurso_s'] != '-1')) {
            $filtroIdRecurso = "id_recurso";
            $op6 = '=';
            $c12 = $request['recurso_s'];
        }
        if ($request['f_siembra'] != '-1') {
            $filtroIdSiembra = "recursos_necesarios.siembra_id";
            $signoIdSiembra = '=';
            $valorIdSiembra = $request['f_siembra'];
        }

        if ($request['f_estado'] != '-1') {
            $filtroEstadoSiembra = "siembras.estado";
            $signoEstadoSiembra = '=';
            $valorEstadoSiembra = $request['f_estado'];
        }

        $recursosNecesarios = RecursoNecesario::select('*', 'recursos_necesarios.id as id');

        if ($request['tipo_actividad'] == 1) {
            $recursosNecesarios = $recursosNecesarios
                ->join('alimentos', 'recursos_necesarios.id_alimento', 'alimentos.id');
        } else {
            $recursosNecesarios = $recursosNecesarios
                ->join('recursos', 'recursos_necesarios.id_recurso', 'recursos.id');
        }

        $recursosNecesarios = $recursosNecesarios
            ->join('siembras', 'recursos_necesarios.siembra_id', 'siembras.id')
            ->join('actividades', 'recursos_necesarios.tipo_actividad', 'actividades.id')
            ->where($tipo_actividad, $filtro_tipo_actividad, $id_tipo_actividad)
            ->where($fechaRegistroAlimentoInicio, $signoFechaRegistroAlimentoInicio, $valorFechaRegistroAlimentoInicio)
            ->where($filtroFechaRegistroAlimentoHasta, $signoFechaRegistroAlimentoHasta, $valorFechaRegistroAlimentohasta)
            ->where($filtroIdAlimento, $op5, $c10)
            ->where($filtroIdRecurso, $op6, $c12)
            ->where($filtroIdSiembra, $signoIdSiembra, $valorIdSiembra);

        if ($valorIdSiembra == '-1') {
            $recursosNecesarios = $recursosNecesarios
                ->where($filtroEstadoSiembra, $signoEstadoSiembra, $valorEstadoSiembra);
        }

        // OBTENER EL NÚMERO DE RESULTADOS POR PÁGINA
        $perPage = 30; // Valor por defecto
        if ($request->has('nro_results') && is_numeric($request->input('nro_results'))) {
            $perPage = (int) $request->input('nro_results');
            // Validar que no sea un número excesivamente grande
            if ($perPage < 1) $perPage = 30;
            if ($perPage > 1000) $perPage = 1000; // Límite máximo razonable
        }

        if ($request['see_all']) {
            $recursosNecesarios = $recursosNecesarios
                ->orderBy('fecha_ra', 'desc')
                ->get();
        } else {
            $recursosNecesarios = $recursosNecesarios
                ->orderBy('fecha_ra', 'desc')
                ->paginate($perPage); // USAR EL VALOR DINÁMICO
        }

        $promedioRecursos = array();

        $counter = count($recursosNecesarios);

        if (count($recursosNecesarios) > 0) {
            for ($i = 0; $i < count($recursosNecesarios); $i++) {
                $recursosNecesarios[$i]->costo_total_recurso =
                    $recursosNecesarios[$i]->cantidad_recurso * $recursosNecesarios[$i]->costo;
                $recursosNecesarios[$i]->total_minutos_hombre =
                    $recursosNecesarios[$i]->minutos_hombre * $recursosNecesarios[$i]->costo_minutos_hombre;
                $recursosNecesarios[$i]->costo_total_alimento =
                    ($recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana) *
                    $recursosNecesarios[$i]->costo_kg;
                $recursosNecesarios[$i]->alimento_dia =
                    $recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana;

                if ($recursosNecesarios[$i]->conv_alimenticia > 0) {
                    $recursosNecesarios[$i]->incr_bio_acum_conver =
                        $recursosNecesarios[$i]->alimento_dia / $recursosNecesarios[$i]->conv_alimenticia;
                    $recursosNecesarios[$i]->conv_alimenticia =
                        number_format($recursosNecesarios[$i]->conv_alimenticia, 2, ',', '');
                }
            }

            $copyRecursosNecesarios = $request['see_all']
                ? $recursosNecesarios->toArray()
                : $recursosNecesarios->toArray()['data'];

            $promedioRecursos['tmh'] = array_sum(array_column($copyRecursosNecesarios, 'minutos_hombre'));
            $promedioRecursos['ttmh'] = array_sum(array_column($copyRecursosNecesarios, 'total_minutos_hombre'));
            $promedioRecursos['tcr'] = array_sum(array_column($copyRecursosNecesarios, 'cantidad_recurso'));
            $promedioRecursos['tc'] = array_sum(array_column($copyRecursosNecesarios, 'costo'));
            $promedioRecursos['ctr'] = array_sum(array_column($copyRecursosNecesarios, 'costo_total_recurso'));
            $promedioRecursos['cman'] = array_sum(array_column($copyRecursosNecesarios, 'cant_manana'));
            $promedioRecursos['ctar'] = array_sum(array_column($copyRecursosNecesarios, 'cant_tarde'));
            $promedioRecursos['alid'] = array_sum(array_column($copyRecursosNecesarios, 'alimento_dia'));
            $promedioRecursos['coskg'] = array_sum(array_column($copyRecursosNecesarios, 'costo_kg'));
            $promedioRecursos['cta'] = array_sum(array_column($copyRecursosNecesarios, 'costo_total_alimento'));
            $promedioRecursos['icb'] = array_sum(array_column($copyRecursosNecesarios, 'incr_bio_acum_conver'));
            $promedioRecursos['tc'] = $promedioRecursos['tc'];
            $promedioRecursos['ctr'] = $promedioRecursos['ctr'];
            $promedioRecursos['coskg'] = $counter > 0 ? ($promedioRecursos['coskg'] / $counter) : 0;
            $promedioRecursos['cta'] = $promedioRecursos['cta'];
        }

        if ($request['see_all']) {
            return [
                'recursosNecesarios' => $recursosNecesarios,
                'promedioRecursos' => $promedioRecursos
            ];
        } else {
            // Retornar la paginación completa de Laravel
            return [
                'recursosNecesarios' => $recursosNecesarios,
                'promedioRecursos' => $promedioRecursos,
            ];
        }
    }

    public function alimentacion(Request $request)
    {
        $tipo_actividad = "recursos_necesarios.id";
        $filtro_tipo_actividad = "!=";
        $c2 = "-1";

        $filtroFechaRegistroAlimentoDesde = "recursos_necesarios.id";
        $signoFechaRegistroAlimentoDesde = "!=";
        $valorFechaRegistroAlimentoDesde = "-3";

        $filtroFechaRegistroAlimentoHasta = "recursos_necesarios.id";
        $signoFechaRegistroAlimentoHasta = "!=";
        $valorFechaRegistroAlimentohasta = "-1";

        $filtroIdAlimento = "recursos_necesarios.id_alimento";
        $signoIdAlimento = "!=";
        $valorIdAlimento = "-1";

        $filtroIdSiembra = 'recursos_necesarios.id';
        $signoIdSiembra = '!=';
        $valorIdSiembra = '-1';

        if ($request['tipo_actividad'] != '-1') {
            $tipo_actividad = "tipo_actividad";
            $filtro_tipo_actividad = '=';
            $id_tipo_actividad = $request['tipo_actividad'];
        } elseif ($request['tipo_actividad'] == '-1') {
            $tipo_actividad = "tipo_actividad";
            $filtro_tipo_actividad = '!=';
            $id_tipo_actividad = '-1';
        }

        if ($request['fecha_ra1'] != '-3') {
            $filtroFechaRegistroAlimentoDesde = "fecha_ra";
            $signoFechaRegistroAlimentoDesde = '>=';
            $valorFechaRegistroAlimentoDesde = $request['fecha_ra1'];
        }
        if ($request['fecha_ra2'] != '-1') {
            $filtroFechaRegistroAlimentoHasta = "fecha_ra";
            $signoFechaRegistroAlimentoHasta = '<=';
            $valorFechaRegistroAlimentohasta = $request['fecha_ra2'];
        }

        if (isset($request['alimento_s']) && $request['alimento_s'] != '-1') {
            $filtroIdAlimento = "id_alimento";
            $signoIdAlimento = '=';
            $valorIdAlimento = $request['alimento_s'];
        }

        if ($request['f_siembra'] != '-1') {
            $filtroIdSiembra = "recursos_necesarios.siembra_id";
            $signoIdSiembra = '=';
            $valorIdSiembra = $request['f_siembra'];
        }

        $recursosNecesarios = RecursoNecesario::orderBy('fecha_ra', 'desc')
            ->select('*', 'recursos_necesarios.id as id')
            ->join('alimentos', 'recursos_necesarios.id_alimento', 'alimentos.id')
            ->join('siembras', 'recursos_necesarios.siembra_id', 'siembras.id')
            ->join('actividades', 'recursos_necesarios.tipo_actividad', 'actividades.id')
            ->where($filtroIdSiembra, $signoIdSiembra, $valorIdSiembra)
            ->where($filtroIdAlimento, $signoIdAlimento, $valorIdAlimento)
            ->where($filtroFechaRegistroAlimentoDesde, $signoFechaRegistroAlimentoDesde, $valorFechaRegistroAlimentoDesde)
            ->where($filtroFechaRegistroAlimentoHasta, $signoFechaRegistroAlimentoHasta, $valorFechaRegistroAlimentohasta)
            ->where('tipo_actividad', '=', '1');

        $recursosNecesarios = $request['see_all']
            ? $recursosNecesarios->get()
            : $recursosNecesarios->paginate(20);

        $promedioRecursos = array();

        $counter = count($recursosNecesarios);

        for ($i = 0; $i < count($recursosNecesarios); $i++) {
            $recursosNecesarios[$i]->total_minutos_hombre =
                $recursosNecesarios[$i]->minutos_hombre * $recursosNecesarios[$i]->costo_minutos_hombre;
            $recursosNecesarios[$i]->costo_total_alimento =
                ($recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana) *
                $recursosNecesarios[$i]->costo_kg;
            $recursosNecesarios[$i]->alimento_dia =
                $recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana;

            if ($recursosNecesarios[$i]->conv_alimenticia > 0) {
                $recursosNecesarios[$i]->incr_bio_acum_conver =
                    $recursosNecesarios[$i]->alimento_dia / $recursosNecesarios[$i]->conv_alimenticia;
            }
        }

        $copyRecursosNecesarios = $request['see_all']
            ? $recursosNecesarios->toArray()
            : $recursosNecesarios->toArray()['data'];

        $promedioRecursos['tmh']  = array_sum(array_column($copyRecursosNecesarios, 'minutos_hombre'));
        $promedioRecursos['ttmh'] = array_sum(array_column($copyRecursosNecesarios, 'total_minutos_hombre'));
        $promedioRecursos['tc']   = array_sum(array_column($copyRecursosNecesarios, 'costo'));
        $promedioRecursos['ctr']  = array_sum(array_column($copyRecursosNecesarios, 'costo_total_recurso'));
        $promedioRecursos['cman'] = array_sum(array_column($copyRecursosNecesarios, 'cant_manana'));
        $promedioRecursos['ctar'] = array_sum(array_column($copyRecursosNecesarios, 'cant_tarde'));
        $promedioRecursos['alid'] = array_sum(array_column($copyRecursosNecesarios, 'alimento_dia'));
        $promedioRecursos['coskg'] = array_sum(array_column($copyRecursosNecesarios, 'costo_kg'));
        $promedioRecursos['cta']   = array_sum(array_column($copyRecursosNecesarios, 'costo_total_alimento'));
        $promedioRecursos['icb']   = array_sum(array_column($copyRecursosNecesarios, 'incr_bio_acum_conver'));
        $promedioRecursos['tc']    = $promedioRecursos['tc'];
        $promedioRecursos['ctr']   = $promedioRecursos['ctr'];
        $promedioRecursos['coskg'] = $counter > 0 ? ($promedioRecursos['coskg'] / $counter): 0;
        $promedioRecursos['cta']   = $promedioRecursos['cta'];

        if ($request['see_all']) {
            return [
                'recursosNecesarios' => $recursosNecesarios,
                'promedioRecursos'   => $promedioRecursos
            ];
        } else {
            return [
                'recursosNecesarios' => $recursosNecesarios,
                'promedioRecursos'   => $promedioRecursos,
                'pagination'         => [
                    'total'        => $recursosNecesarios->total(),
                    'current_page' => $recursosNecesarios->currentPage(),
                    'per_page'     => $recursosNecesarios->perPage(),
                    'last_page'    => $recursosNecesarios->lastPage(),
                    'from'         => $recursosNecesarios->firstItem(),
                    'to'           => $recursosNecesarios->lastItem(),
                ],
            ];
        }
    }

    public function siembraxAlimentacion($id)
    {
        $recursosNecesarios = RecursoNecesario::orderBy('fecha_ra', 'desc')
            ->join('alimentos', 'recursos_necesarios.id_alimento', 'alimentos.id')
            ->join('siembras', 'recursos_necesarios.siembra_id', 'siembras.id')
            ->join('actividades', 'recursos_necesarios.tipo_actividad', 'actividades.id')
            ->where('recursos_necesarios.siembra_id', '=', $id)
            ->where('tipo_actividad', '=', '1')
            ->select(
                '*',
                'recursos_necesarios.id as id_registro',
                'recursos_necesarios.siembra_id as id_siembra'
            )
            ->get();

        if (count($recursosNecesarios) > 0) {
            for ($i = 0; $i < count($recursosNecesarios); $i++) {
                $recursosNecesarios[$i]->costo_total_alimento =
                    ($recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana) *
                    $recursosNecesarios[$i]->costo_kg;
                $recursosNecesarios[$i]->total_minutos_hombre =
                    $recursosNecesarios[$i]->minutos_hombre * $recursosNecesarios[$i]->costo_minutos_hombre;
                $recursosNecesarios[$i]->alimento_dia =
                    $recursosNecesarios[$i]->cant_tarde + $recursosNecesarios[$i]->cant_manana;
            }
        }

        return ['recursosNecesarios' => $recursosNecesarios];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // último registro para usar conv_alimenticia por defecto
        $c_alim = RecursoNecesario::select()->orderBy('id', 'desc')->first();

        $recurso   = Recursos::select('id', 'costo')->find($request['id_recurso']);
        $alimento  = Alimento::select('id', 'costo_kg')->find($request['id_alimento']);
        $minutos_hombre = Recursos::select()
            ->where('recurso', 'Minutos hombre')
            ->orWhere('recurso', 'Minuto hombre')
            ->orWhere('recurso', 'Minutos')
            ->first();

        // -----------------------------
        // Manejo seguro de registered_user_id
        // -----------------------------
        $registeredUserId = $request['registered_user_id'] ?? null;

        if ($registeredUserId) {
            $userExists = User::where('id', $registeredUserId)->exists();
            if (!$userExists) {
                // Si no existe en users, se deja null para no romper la FK
                $registeredUserId = null;
            }
        }

        $recursoNecesario = new RecursoNecesario();
        $recursoNecesario->registered_user_id = $registeredUserId;
        $recursoNecesario->id_recurso         = $request['id_recurso'];
        $recursoNecesario->id_alimento        = $request['id_alimento'];
        $recursoNecesario->tipo_actividad     = $request['tipo_actividad'];
        $recursoNecesario->fecha_ra           = $request['fecha_ra'];
        $recursoNecesario->minutos_hombre     = $request['minutos_hombre'];
        $recursoNecesario->horas_hombre       = ($request['minutos_hombre'] / 60);
        $recursoNecesario->cantidad_recurso   = $request['cantidad_recurso'];
        $recursoNecesario->cant_manana        = $request['cant_manana'];
        $recursoNecesario->cant_tarde         = $request['cant_tarde'];
        $recursoNecesario->costo_recurso      = $recurso ? $recurso->costo : 0;
        $recursoNecesario->costo_alimento     = $alimento ? $alimento->costo_kg : 0;
        $recursoNecesario->costo_minutos_hombre = $minutos_hombre ? $minutos_hombre->costo : 0;
        $recursoNecesario->conv_alimenticia   =
            $request['conv_alimenticia'] == '' && $c_alim
                ? $c_alim->conv_alimenticia
                : $request['conv_alimenticia'];

        $recursoNecesario->detalles = $request['detalles'];

        if (!is_array($request['id_siembra'])) {
            if ($request['id_siembra']) {
                $recursoNecesario->siembra_id = $request['id_siembra'];
                $recursoNecesario->save();

                $siembras = Siembra::findOrFail($request['id_siembra']);
                $siembras->fecha_alimento = $request['fecha_ra'];
                $siembras->save();
            }
        } else {
            foreach ($request->id_siembra as $siembra_id) {
                if ($siembra_id) {
                    $recursoNecesario->siembra_id = $siembra_id;
                    $recursoNecesario->save();

                    $siembras = Siembra::findOrFail($siembra_id);
                    $siembras->fecha_alimento = $request['fecha_ra'];
                    $siembras->save();
                }
            }
        }

        // AIREACION: los aeradores son máquinas, no insumos consumibles.
        // No deben afectar inventario ni generar movimientos en Kardex.
        if (($request['tipo'] ?? '') !== 'AIREACION') {
            $dataStock = (object) [
                'alimento_id' => $request['id_alimento'],
                'recurso_id'  => $request['id_recurso'],
                'tipo'        => $request['tipo'],
                'cantidad'    => $request['cantidad_recurso'] + $request['cant_manana'] + $request['cant_tarde'],
                'descripcion' => $request['descripcion'] ?: "Actualización de stock",
                'proccess'    => 'sub'
            ];

            $kardexController = new KardexController();
            $kardexController->updateKardexAndItem($dataStock);
        }

        return ($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $recurso  = Recursos::select('id', 'costo')->find($request['id_recurso']);
        $alimento = Alimento::select('id', 'costo_kg')->find($request['id_alimento']);
        $minutos_hombre = Recursos::select()
            ->where('recurso', 'Minutos hombre')
            ->orWhere('recurso', 'Minuto hombre')
            ->orWhere('recurso', 'Minutos')
            ->first();

        $minutos = $request['minutos_hombre'] / 60;

        $recursoNecesario = RecursoNecesario::findOrFail($id);
        $recursoNecesario->update([
            'fecha_ra'            => $request['fecha_ra'],
            'cant_manana'         => $request['cant_manana'],
            'cant_tarde'          => $request['cant_tarde'],
            'id_alimento'         => $request['id_alimento'],
            'id_recurso'          => $request['id_recurso'],
            'detalles'            => $request['detalles'],
            'minutos_hombre'      => $request['minutos_hombre'],
            'horas_hombre'        => $minutos,
            'cantidad_recurso'    => $request['cantidad_recurso'],
            'costo_recurso'       => $recurso ? $recurso->costo : 0,
            'costo_alimento'      => $alimento ? $alimento->costo_kg : 0,
            'costo_minutos_hombre'=> $minutos_hombre ? $minutos_hombre->costo : 0,
        ]);

        return ['recursoNecesario' => $recursoNecesario];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        RecursoNecesario::destroy($id);
        $rxs = RecursoSiembra::where('id_registro', $id)->delete();
        return 'eliminado';
    }

    public function analyzeRepairAireacion()
    {
        // Traer todos los registros de aireación con cantidad=0 (sin filtro LIKE para evitar
        // problemas de encoding con caracteres acentuados en MySQL/XAMPP Windows).
        // El regex de PHP filtra los que tienen la duración en el campo detalles.
        $records = DB::table('recursos_necesarios as rn')
            ->leftJoin('siembras as s', 's.id', '=', 'rn.siembra_id')
            ->leftJoin('recursos as r',  'r.id', '=', 'rn.id_recurso')
            ->where('rn.tipo_actividad', '12')
            ->where('rn.cantidad_recurso', 0)
            ->orderBy('rn.fecha_ra', 'desc')
            ->select('rn.id', 'rn.fecha_ra', 'rn.detalles', 'rn.cantidad_recurso', 'rn.conv_alimenticia',
                     's.nombre_siembra', 'r.recurso', 'r.costo as costo_recurso')
            ->get();

        $toRepair = $records->map(function ($row) {
            $detalles = $row->detalles ?? '';
            // Buscar "Duración: X min" o "Duracion: X min" con o sin acento
            if (!preg_match('/Duraci[oó]n:\s*(\d+)\s*min/iu', $detalles, $m)) return null;
            $duration = (int) $m[1];
            if ($duration <= 0) return null;
            return [
                'id'                      => $row->id,
                'siembra'                 => $row->nombre_siembra ?? '-',
                'fecha_ra'                => $row->fecha_ra,
                'recurso'                 => $row->recurso ?? '-',
                'detalles'                => $detalles,
                'cantidad_recurso_actual' => $row->cantidad_recurso,
                'cantidad_recurso_nueva'  => $duration,
                'costo_total_nuevo'       => $duration * ($row->costo_recurso ?? 0),
                'conv_alimenticia_actual'  => $row->conv_alimenticia,
            ];
        })->filter()->values();

        return response()->json(['total' => $toRepair->count(), 'records' => $toRepair]);
    }

    public function executeRepairAireacion()
    {
        $records = DB::table('recursos_necesarios as rn')
            ->leftJoin('recursos as r', 'r.id', '=', 'rn.id_recurso')
            ->where('rn.tipo_actividad', '12')
            ->where('rn.cantidad_recurso', 0)
            ->select('rn.id', 'rn.detalles', 'r.recurso', 'r.costo as costo_recurso')
            ->get();

        $repaired = [];
        DB::beginTransaction();
        try {
            foreach ($records as $row) {
                $detalles = $row->detalles ?? '';
                if (!preg_match('/Duraci[oó]n:\s*(\d+)\s*min/iu', $detalles, $m)) continue;
                $duration = (int) $m[1];
                if ($duration <= 0) continue;
                DB::table('recursos_necesarios')->where('id', $row->id)->update([
                    'cantidad_recurso' => $duration,
                    'conv_alimenticia' => null,
                    'updated_at'       => now(),
                ]);
                $repaired[] = [
                    'id'               => $row->id,
                    'recurso'          => $row->recurso ?? '-',
                    'cantidad_recurso' => $duration,
                    'costo_total'      => $duration * ($row->costo_recurso ?? 0),
                ];
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message'  => count($repaired) . ' registros reparados correctamente.',
            'repaired' => count($repaired),
            'records'  => $repaired,
        ]);
    }

    public function recursosNecesariosPorSiembra($siembra_id)
    {
        $recursos_necesarios_siembra = RecursoNecesario::select(
            DB::raw("SUM(cantidad_recurso) AS cantidad_recurso"),
            DB::raw("SUM(minutos_hombre) AS minutos_hombre"),
            DB::raw("SUM(horas_hombre) AS horas_hombre"),
            DB::raw("SUM(cantidad_recurso*costo) AS costo_total_recurso"),
            DB::raw("SUM(minutos_hombre*costo_minutos_hombre) AS costo_minutos_hombre"),
            DB::raw("SUM(cant_manana) AS cant_manana"),
            DB::raw("SUM(cant_tarde) AS cant_tarde"),
            DB::raw("SUM((cant_manana ) / conv_alimenticia) AS incr_bio_acum_conver_m"),
            DB::raw("SUM((cant_tarde) / conv_alimenticia) AS incr_bio_acum_conver_t"),
            DB::raw("SUM(cant_manana * costo_kg) AS costo_manana"),
            DB::raw("SUM(cant_tarde* costo_kg) AS costo_tarde")
        )
            ->leftJoin('recursos', 'recursos_necesarios.id_recurso', 'recursos.id')
            ->leftJoin('alimentos', 'recursos_necesarios.id_alimento', 'alimentos.id')
            ->groupBy('siembra_id')
            ->where('siembra_id', $siembra_id)
            ->get();

        return $recursos_necesarios_siembra;
    }

    public function alimentosPorSiembra($siembra_id)
    {
        $alimentos_siembra = RecursoNecesario::select(
            DB::raw("SUM(cant_manana) AS cant_manana"),
            DB::raw("SUM(cant_tarde) AS cant_tarde"),
            DB::raw("SUM((cant_manana + cant_tarde) / conv_alimenticia) AS incr_bio_acum_conver"),
            DB::raw("SUM(cant_manana * costo_kg) AS costo_manana"),
            DB::raw("SUM(cant_tarde* costo_kg) AS costo_tarde")
        )
            ->join('alimentos', 'recursos_necesarios.id_alimento', 'alimentos.id')
            ->groupBy('siembra_id')
            ->where('siembra_id', $siembra_id)
            ->where('tipo_actividad', '1')
            ->get();

        return $alimentos_siembra;
    }
}
