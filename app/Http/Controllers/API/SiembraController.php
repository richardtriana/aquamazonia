<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EspecieSiembra;
use App\Especie;
use App\Siembra;
use App\Contenedor;
use App\Registro;
use App\RecursoNecesario;
use App\SiembraRecursoDato;
use DB;

class SiembraController extends Controller
{
    public function index(Request $request)
    {
        $contenedor_id   = (int) ($request['contenedor_id'] ?? -1);
        $sign_contenedor = ($contenedor_id <= 0) ? '<>' : '=';

        $siembra_id      = (int) ($request['id_siembra'] ?? -1);
        $sign_siembra_id = ($siembra_id <= 0) ? '<>' : '=';

        $estado_siembra      = $request['estado_siembra'] ?? -1;
        $sign_estado_siembra = ($estado_siembra == "-1") ? '<>' : '=';

        $especie_id      = (int) ($request['especie_id'] ?? -1); // NUEVO: filtro por especie
        $phase       = $request->phase;
        $type        = $request->type;
        $nro_results = $request->nro_results ?? 15;

        // Consulta base
        $query = Siembra::select(
                'siembras.id as id',
                'nombre_siembra',
                'id_contenedor',
                'contenedor',
                'fecha_inicio',
                'ini_descanso',
                'fin_descanso',
                'siembras.estado as estado',
                'fecha_alimento',
                'phase_id',
                'tipo'
            )
            ->join('contenedores', 'siembras.id_contenedor', 'contenedores.id')
            ->where('siembras.id', $sign_siembra_id, $siembra_id)
            ->where('siembras.estado', $sign_estado_siembra, $estado_siembra)
            ->when(
                !is_null($phase) && $phase !== '' && $phase !== 'all' && $phase !== '-1',
                function ($query) use ($phase) {
                    $query->where('phase_id', $phase);
                }
            )
            ->when(
                !is_null($type) && $type !== '' && $type !== 'all',
                function ($query) use ($type) {
                    $query->where('tipo', 'LIKE', "%$type%");
                }
            )
            ->where('id_contenedor', $sign_contenedor, $contenedor_id);

        // NUEVO: Aplicar filtro por especie
        if ($especie_id > 0) {
            // Filtrar siembras que tengan al menos una especie con el id especificado
            $query->whereHas('peces', function ($q) use ($especie_id) {
                $q->where('id_especie', $especie_id);
            });
        }

        $siembras = $query->with(['peces.especie', 'phase'])
            ->orderBy('siembras.id', 'asc')
            ->paginate($nro_results);

        // Calcular cantidades actuales
        $siembras->map(function ($siembra) {
            $siembra->peces->map(function ($pez) {
                $especies_siembra = new EspeciesSiembraController();
                $info             = $especies_siembra->cantidadEspecieSiembra($pez->id_siembra, $pez->id_especie);

                $mortalidad       = $info->mortalidad ?? 0;
                $salida           = $info->cantidad ?? 0;
                $pez->cant_actual = $pez->cantidad - $salida - $mortalidad;
                $pez->mortalidad  = $mortalidad;
                $pez->salida      = $salida;

                return $pez;
            });
            return $siembra;
        });

        $fecha_actual = date('Y-m-d');

        return ["siembra" => $siembras, 'fecha_actual' => $fecha_actual];
    }

    public function campos(Request $request)
    {
        $peces = EspecieSiembra::select(
                'especies_siembra.id as id',
                'id_siembra',
                'id_especie',
                'lote',
                'cantidad',
                'peso_inicial',
                'cant_actual',
                'peso_actual',
                'especies.especie as especie'
            )
            ->join('especies', 'especies_siembra.id_especie', 'especies.id')
            ->orderBy('especie', 'asc')
            ->where('id_siembra', $request->siembra_id)
            ->get()
            ->toArray();

        $campos = array();
        foreach ($peces as $p) {

            $especies_siembra = new EspeciesSiembraController();
            $info             = $especies_siembra->cantidadEspecieSiembra($p['id_siembra'], $p['id_especie']);

            $mortalidad            = $info->mortalidad ?? 0;
            $salida                = $info->cantidad ?? 0;
            $cantidad_actual_pez   = $p['cantidad'] - $salida - $mortalidad;

            $campos[$p['id_siembra']][$p['id']] = array(
                "id_especie"   => $p['id_especie'],
                "id_siembra"   => $p['id_siembra'],
                "peso_ganado"  => '',
                "mortalidad"   => '',
                "biomasa"      => '',
                "cantidad"     => '',
                'cant_actual'  => $cantidad_actual_pez,
                'peso_actual'  => $p['peso_actual'],
            );
        }

        return $campos;
    }

    public function listadoLotes()
    {
        return EspecieSiembra::select('lote')
            ->orderBy('lote', 'asc')
            ->distinct()
            ->get();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $id_contenedor = $request->siembra['id_contenedor'];

            $siembra = new Siembra();
            $siembra->id_contenedor  = $request->siembra['id_contenedor'];
            $siembra->nombre_siembra = $request->siembra['nombre_siembra'];
            $siembra->fecha_inicio   = $request->siembra['fecha_inicio'];
            $siembra->tipo           = $request->siembra['tipo'];
            $siembra->phase_id       = $request->siembra['phase_id'] ?? null;
            $siembra->estado         = 1;
            $siembra->save();

            // actualizar estado del contenedor
            $contenedor = Contenedor::findOrFail($id_contenedor);
            $contenedor->estado = 2;
            $contenedor->save();

            // especies
            foreach ($request->especies as $especie) {
                $especieSiembra               = new EspecieSiembra();
                $especieSiembra->id_siembra   = $siembra->id;
                $especieSiembra->id_especie   = $especie['id_especie'];
                $especieSiembra->cantidad     = $especie['cantidad'];
                $especieSiembra->lote         = $especie['lote'];
                $especieSiembra->peso_inicial = $especie['peso_inicial'];
                $especieSiembra->cant_actual  = $especie['cantidad'];
                $especieSiembra->peso_actual  = $especie['peso_inicial'];
                $especieSiembra->save();

                Registro::create([
                    'id_especie'     => $especie['id_especie'],
                    'id_siembra'     => $siembra->id,
                    'fecha_registro' => $request->siembra['fecha_inicio'],
                    'tipo_registro'  => 3,
                    'peso_ganado'    => $especie['peso_inicial'],
                ]);
            }

            /** GUARDAR RECURSOS INICIALES */
            if ($request->has('recursos_iniciales')) {
                foreach ($request->recursos_iniciales as $r) {

                    $minutos = $r['minutos_hombre'] !== "" ? $r['minutos_hombre'] : 0;
                    $cantidad = $r['cantidad_recurso'] !== "" ? $r['cantidad_recurso'] : 0;

                    RecursoNecesario::create([
                        'registered_user_id' => null,
                        'id_recurso' => $r['recurso_id'],
                        'id_alimento' => null,
                        'tipo_actividad' => $r['tipo_actividad_id'],
                        'fecha_ra' => $request->siembra['fecha_inicio'],
                        'horas_hombre' => $minutos / 60,
                        'minutos_hombre' => $minutos,
                        'cantidad_recurso' => $cantidad,
                        'cant_manana' => 0,
                        'cant_tarde' => 0,
                        'conv_alimenticia' => null,
                        'detalles' => $r['detalles'],
                        'costo_minutos_hombre' => 0,
                        'costo_recurso' => 0,
                        'costo_alimento' => 0,
                        'siembra_id' => $siembra->id
                    ]);
                }
            }

            DB::commit();

            return [
                'ok' => true,
                'msg' => "Siembra creada correctamente",
                'id' => $siembra->id,
                'siembra' => $siembra
            ];

        } catch (\Exception $e) {

            DB::rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function anadirEspeciesxSiembra(Request $request)
    {
        foreach ($request['especies'] as $especie) {
            if (!isset($especie['es_edita'])) {
                $especieSiembra               = new EspecieSiembra();
                $especieSiembra->id_siembra   = $request->siembra['siembra_id'];
                $especieSiembra->id_especie   = $especie['id_especie'];
                $especieSiembra->cantidad     = $especie['cantidad'];
                $especieSiembra->lote         = $especie['lote'];
                $especieSiembra->peso_inicial = $especie['peso_inicial'];
                $especieSiembra->cant_actual  = $especie['cantidad'];
                $especieSiembra->peso_actual  = $especie['peso_inicial'];
                $especieSiembra->save();

                Registro::create([
                    'id_especie'     => $especie['id_especie'],
                    'id_siembra'     => $request->siembra['siembra_id'],
                    'fecha_registro' => $request->siembra['fecha_inicio'],
                    'tipo_registro'  => 3,
                    'peso_ganado'    => $especie['peso_inicial'],
                ]);
            }
        }

        $siembra              = Siembra::findOrFail($request->siembra['siembra_id']);
        $siembra->nombre_siembra = $request->siembra['nombre_siembra'];
        $siembra->phase_id       = $request->siembra['phase_id'];
        $siembra->save();
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $especieSiembras = EspecieSiembra::findOrFail($id);
        $especieSiembras->update($request->all());

        $registro = Registro::where('id_siembra', $especieSiembras->id_siembra)
            ->where('id_especie', $especieSiembras->id_especie)
            ->where('tipo_registro', 3)
            ->first();

        $registro->peso_ganado = $request->peso_inicial;
        $registro->save();

        return $especieSiembras;
    }

    public function actualizarEstado(Request $request, $id)
    {
        $siembra              = Siembra::findOrFail($id);
        $siembra->ini_descanso = $request['ini_descanso'];

        if (isset($request['fin_descanso'])) {
            $siembra->fin_descanso = $request['fin_descanso'];
        }

        $siembra->estado = $request['estado'];
        $siembra->save();

        return $request;
    }

    public function destroy($id)
    {
        Siembra::destroy($id);
        EspecieSiembra::where('id_siembra', $id)->delete();
        Registro::where('id_siembra', $id)->delete();

        return 'eliminado';
    }

    public function filtroSiembras(Request $request)
    {
        $c1  = "siembras.id";
        $op1 = '!=';
        $c2  = '-1';
        $c3  = "siembras.id";
        $op2 = '!=';
        $c4  = '-1';
        $c5  = "siembras.id";
        $op3 = '!=';
        $c6  = '-1';
        $c7  = "siembras.id";
        $op4 = '!=';
        $c8  = '-1';
        $c9  = "siembras.id";
        $op5 = '!=';
        $c10 = '-1';

        if ($request['f_siembra'] != '-1') {
            $c1  = "siembras.id";
            $op1 = '=';
            $c2  = $request['f_siembra'];
        }
        if ($request['f_especie'] != '-1') {
            $c3  = "especies.id";
            $op2 = '=';
            $c4  = $request['f_especie'];
        }
        if ($request['f_lote'] != '-1') {
            $c5  = "lote";
            $op3 = '=';
            $c6  = $request['f_lote'];
        }
        if ($request['f_inicio_d'] != '-1') {
            $c7  = "fecha_inicio";
            $op4 = '>=';
            $c8  = $request['f_inicio_d'];
        }
        if ($request['f_inicio_h'] != '-1') {
            $c9  = "fecha_inicio";
            $op5 = '<=';
            $c10 = $request['f_inicio_h'];
        }

        $filtrarSiembras = Siembra::select(
                'siembras.id as id',
                'nombre_siembra',
                'id_contenedor',
                'contenedor',
                'fecha_inicio',
                'ini_descanso',
                'fin_descanso',
                'siembras.estado as estado',
                'lote',
                'especies.id',
                'especie',
                'cantidad',
                'peso_inicial',
                'cant_actual',
                'peso_actual',
                'fecha_alimento'
            )
            ->join('contenedores', 'siembras.id_contenedor', 'contenedores.id')
            ->join('especies_siembra', 'siembras.id', 'especies_siembra.id_siembra')
            ->join('especies', 'especies_siembra.id_especie', 'especies.id')
            ->where($c1, $op1, $c2)
            ->where($c3, $op2, $c4)
            ->where($c5, $op3, $c6)
            ->where($c7, $op4, $c8)
            ->where($c9, $op5, $c10)
            ->orderBy('siembras.id', 'desc')
            ->get();

        return ['filtrarSiembras' => $filtrarSiembras];
    }

    public function traerSiembras()
    {
        $filtrarSiembras = Siembra::select(
                'siembras.id as id',
                'nombre_siembra',
                'id_contenedor',
                'contenedor',
                'fecha_inicio',
                'ini_descanso',
                'fin_descanso',
                'siembras.estado as estado',
                'lote',
                'especies.id',
                'especie',
                'cantidad',
                'peso_inicial',
                'cant_actual',
                'peso_actual',
                'fecha_alimento'
            )
            ->join('contenedores', 'siembras.id_contenedor', 'contenedores.id')
            ->join('especies_siembra', 'siembras.id', 'especies_siembra.id_siembra')
            ->join('especies', 'especies_siembra.id_especie', 'especies.id')
            ->orderBy('siembras.id', 'desc')
            ->get();

        return ['filtrarSiembras' => $filtrarSiembras];
    }

    public function getEspeciesSiembra(Request $request, $id)
    {
        $espxsiembra = EspecieSiembra::select(
                'especies_siembra.id as id',
                'cantidad',
                'id_especie',
                'lote',
                'peso_inicial',
                'cant_actual'
            )
            ->join('especies', 'especies_siembra.id_especie', 'especies.id')
            ->where('id_siembra', $id)
            ->orderBy('especies.especie')
            ->get();

        $aux_id_es = [];
        $aux_es    = [];

        foreach ($espxsiembra as $axs) {
            $aux_id_es[] = $axs->id_especie;
            $aux_es[]    = array(
                'id'          => $axs->id,
                'cantidad'    => $axs->cantidad,
                'cant_actual' => $axs->cant_actual,
                'id_especie'  => $axs->id_especie,
                'lote'        => $axs->lote,
                'peso_inicial'=> $axs->peso_inicial,
                'es_edita'    => '1'
            );
        }

        if (count($aux_id_es) > 0) {
            $especies = Especie::whereNotIn('id', $aux_id_es)
                ->orderBy('especie')
                ->get();
        } else {
            $especies = Especie::orderBy('especie')->get();
        }

        return ['espxsiembra' => $aux_es, 'especies' => $especies];
    }

    public function listadoSiembras()
    {
        return Siembra::select()->get();
    }
}