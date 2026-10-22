<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EspecieSiembra;
use App\Siembra;
use App\RecursoNecesario;
use App\Recursos;
use App\Registro;
use App\Actividad;
use Illuminate\Support\Facades\DB;

class InfomeBiomasaAlimentoController extends Controller
{
    public function index(Request $request)
    {
        $id_siembra = "siembras.id";
        $operador_id_siembra = '!=';
        $valorIdSiembra = '-1';

        $op2 = '!=';
        $c4 = '-1';

        if ($request['f_siembra'] != '-1') {
            $operador_id_siembra = '=';
            $valorIdSiembra = $request['f_siembra'];
        }
        
        if ($request['f_estado'] != '-1') {
            $op2 = '=';
            $c4 = $request['f_estado'];
        }

        // Iniciamos la consulta
        $query = Siembra::select(
            'siembras.id as id',
            'capacidad',
            'nombre_siembra',
            'id_contenedor',
            'contenedores.contenedor',
            'fecha_inicio',
            'ini_descanso',
            'siembras.estado',
            'fin_descanso'
        )
        ->join('contenedores', 'siembras.id_contenedor', 'contenedores.id')
        ->where($id_siembra, $operador_id_siembra, $valorIdSiembra)
        ->where('siembras.estado', $op2, $c4);

        // --- FILTRO POR ESPECIE (DIRECTO EN SQL) ---
        if ($request->has('f_especie') && $request['f_especie'] != '-1') {
            $query->whereExists(function ($q) use ($request) {
                $q->select(DB::raw(1))
                  ->from('especies_siembra')
                  ->whereRaw('especies_siembra.id_siembra = siembras.id')
                  ->where('especies_siembra.id_especie', $request['f_especie']);
            });
        }

        $siembras = $query->orderBy('nombre_siembra', 'desc')->get();

        $registros = Registro::select()
            ->join('siembras', 'registros.id_siembra', 'siembras.id')
            ->get();

        $especies_siembra_controller = new EspeciesSiembraController;

        foreach ($siembras as $siembra) {
            $especies = $especies_siembra_controller->generalInfoEspeciesSiembra($siembra->id);

            // Nombres de especies para la columna visual
            $nombresEspeciesArray = \App\EspecieSiembra::where('id_siembra', $siembra->id)
                ->join('especies', 'especies_siembra.id_especie', '=', 'especies.id')
                ->pluck('especies.especie')
                ->toArray();
            
            $siembra->especies_nombres = count($nombresEspeciesArray) > 0 
                ? implode(', ', $nombresEspeciesArray) 
                : 'Sin especie';

            if ($especies) {
                $siembra->contador_esp = $especies->nro_especies;
                $siembra->cantidad_inicial = $especies->cantidad_inicial;
                $siembra->peso_ini = $especies->peso_inicial;
                $siembra->peso_actual = $especies->peso_actual;
                $siembra->peso_incremento = $especies->peso_actual - $especies->peso_inicial;
                $siembra->biomasa_inicial = $especies->biomasa_inicial;
                
                $totales = $especies_siembra_controller->cantidadTotalEspeciesSiembra($siembra->id);
                $siembra->mortalidad = $totales->mortalidad ?? 0;
                $siembra->biomasa = $totales->biomasa ?? 0;
                $siembra->cantidad_SM = $totales->cantidad ?? 0;
                
                $siembra->salida_animales = $siembra->cantidad_SM + $siembra->mortalidad;
                $siembra->salida_animales_sin_mortalidad = $siembra->cantidad_SM;
                $siembra->cantidad_actual = $siembra->cantidad_inicial - $siembra->salida_animales;
                $siembra->biomasa_disponible = ($siembra->peso_actual * $siembra->cantidad_actual) / 1000;
                $siembra->mortalidad_kg = $totales->mortalidad_kg ?? 0;

                // Cálculo de tiempo
                $int_tiempo = Registro::where('id_siembra', $siembra->id)
                    ->orderBy('fecha_registro', 'desc')
                    ->first();

                if ($int_tiempo) {
                    $date1 = new \DateTime($siembra->fecha_inicio);
                    $date2 = new \DateTime($int_tiempo->fecha_registro);
                    $diff = $date1->diff($date2);
                    $siembra->intervalo_tiempo = $diff->days;
                } else {
                    $siembra->intervalo_tiempo = 1;
                }

                $siembra->intervalo_tiempo_months = $siembra->intervalo_tiempo / 30;
                $siembra->ganancia_peso_dia = $siembra->intervalo_tiempo > 0 ? $siembra->peso_incremento / $siembra->intervalo_tiempo : 0;
                $siembra->mortalidad_porcentaje = ($siembra->cantidad_inicial > 0) ? (($siembra->mortalidad * 100) / $siembra->cantidad_inicial) : 0;
                
                $finales = $especies_siembra_controller->cantidadTotalEspeciesSiembraSinMortalidad($siembra->id);
                $siembra->salida_biomasa = $finales->biomasa ?? 0;
                $siembra->porc_supervivencia_final = ($siembra->cantidad_inicial > 0) ? (($siembra->salida_animales_sin_mortalidad * 100) / $siembra->cantidad_inicial) : 0;
            }

            // Recursos y Alimento
            $recursos_necesarios_controller = new RecursoNecesarioController();
            $recursos_necesarios_siembra = $recursos_necesarios_controller->recursosNecesariosPorSiembra($siembra->id);

            foreach ($recursos_necesarios_siembra as $recurso_necesario) {
                $siembra->minutos_hombre = $recurso_necesario->minutos_hombre;
                $siembra->horas_hombre = $siembra->minutos_hombre / 60;
                $siembra->costo_total_recurso = $recurso_necesario->costo_total_recurso;
                $siembra->cantidad_total_alimento = $recurso_necesario->cant_tarde + $recurso_necesario->cant_manana;
                $siembra->costo_total_alimento = $recurso_necesario->costo_manana + $recurso_necesario->costo_tarde;
                $siembra->incr_bio_acum_conver = $recurso_necesario->incr_bio_acum_conver_m + $recurso_necesario->incr_bio_acum_conver_t;
                $siembra->costo_minutos_hombre = $recurso_necesario->costo_minutos_hombre;
            }

            $siembra->costo_total_siembra = ($siembra->costo_minutos_hombre + $siembra->costo_total_alimento + $siembra->costo_total_recurso);
            $siembra->conversion_alimenticia = $siembra->incr_bio_acum_conver > 0 ? ($siembra->cantidad_total_alimento / $siembra->incr_bio_acum_conver) : 0;
            $siembra->bio_dispo_alimen = (($siembra->incr_bio_acum_conver + $siembra->biomasa_inicial) - ($siembra->salida_biomasa + $siembra->mortalidad_kg));
        }

        $siembra_to_array = $siembras->toArray();

        $totalizadoSiembras = [
            'biomasa_inicial' => array_sum(array_column($siembra_to_array, 'biomasa_inicial')),
            'cantidad_actual' => array_sum(array_column($siembra_to_array, 'cantidad_actual')),
            'mortalidad' => array_sum(array_column($siembra_to_array, 'mortalidad')),
            'mortalidad_kg' => array_sum(array_column($siembra_to_array, 'mortalidad_kg')),
            'salida_animales_sin_mortalidad' => array_sum(array_column($siembra_to_array, 'salida_animales_sin_mortalidad')),
            'biomasa_disponible' => array_sum(array_column($siembra_to_array, 'biomasa_disponible')),
            'salida_biomasa' => array_sum(array_column($siembra_to_array, 'salida_biomasa')),
            'cantidad_total_alimento' => array_sum(array_column($siembra_to_array, 'cantidad_total_alimento')),
            'incr_bio_acum_conver' => array_sum(array_column($siembra_to_array, 'incr_bio_acum_conver')),
            'bio_dispo_alimen' => array_sum(array_column($siembra_to_array, 'bio_dispo_alimen')),
            'costo_minutos_hombre' => array_sum(array_column($siembra_to_array, 'costo_minutos_hombre')),
            'costo_total_recurso' => array_sum(array_column($siembra_to_array, 'costo_total_recurso')),
            'costo_total_alimento' => array_sum(array_column($siembra_to_array, 'costo_total_alimento')),
            'costo_total_siembra' => array_sum(array_column($siembra_to_array, 'costo_total_siembra')),
        ];

        return ['existencias' => $siembras, 'totalizadoSiembras' => $totalizadoSiembras];
    }
}