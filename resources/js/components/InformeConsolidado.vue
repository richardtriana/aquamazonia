<template>
    <div class="container">
        <!-- SPINNER GLOBAL PARA ESTA PÁGINA -->
        <div v-if="isLoading" class="global-spinner">
            <div class="global-spinner-inner">
                <div class="global-spinner-ring"></div>
                <div class="global-spinner-logo">
                    AQUAMAZONIA
                </div>
                <div class="global-spinner-text">
                    Cargando información, por favor espera...
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center text-uppercase">
                        INFORME CONSOLIDADO VARIABLES DE PRODUCCIÓN
                    </div>
                    <div class="card-body">
                        <div class="row text-left mb-2">
                            <h5>Filtrar por:</h5>
                        </div>

                        <!-- BARRA DE FILTROS ORDENADA Y UNIFORME -->
                        <div class="row align-items-end mb-3 filtro-bar">
                            <!-- Estado de siembra -->
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="f_estado">Estado de siembra</label>
                                <select
                                    name="f_estado"
                                    class="form-control filtro-control"
                                    id="f_estado"
                                    v-model="f_estado"
                                >
                                    <option value="-1">Todas</option>
                                    <option value="1">Activas</option>
                                    <option value="0">Inactivas</option>
                                </select>
                            </div>

                            <!-- Siembras (como en siembras.vue, sin mostrar -1) -->
                            <div class="form-group col-md-4 col-lg-3">
                                <label for="siembra_activa">
                                    Siembras
                                    {{
                                        f_estado == "-1"
                                            ? ""
                                            : f_estado == 0
                                            ? "inactivas"
                                            : "activas"
                                    }}
                                    :
                                </label>
                                <template v-if="f_estado != '-1'">
                                    <v-select
                                        :options="filteredItems"
                                        label="nombre_siembra"
                                        :reduce="siembra => siembra.id"
                                        v-model="f_siembra"
                                        placeholder="Todas las siembras"
                                        class="filtro-vselect"
                                    />
                                </template>
                                <template v-else>
                                    <v-select
                                        :options="listadoSiembras"
                                        label="nombre_siembra"
                                        :reduce="siembra => siembra.id"
                                        v-model="f_siembra"
                                        placeholder="Todas las siembras"
                                        class="filtro-vselect"
                                    />
                                </template>
                            </div>

                            <!-- Estanque -->
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="contenedor">Estanque</label>
                                <select
                                    class="form-control filtro-control"
                                    id="contenedor"
                                    v-model="f_contenedor"
                                >
                                    <option value="-1">Todos</option>
                                    <option
                                        :value="cont.id"
                                        v-for="(cont, index) in listadoEstanques"
                                        :key="index"
                                    >
                                        {{ cont.contenedor }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tipo de siembra -->
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="search_type">Tipo de siembra</label>
                                <select
                                    name="search_type"
                                    class="form-control filtro-control"
                                    id="search_type"
                                    v-model="search_type"
                                >
                                    <option value="">Todas</option>
                                    <option value="Monocultivo">Monocultivo</option>
                                    <option value="Policultivo">Policultivo</option>
                                </select>
                            </div>

                            <!-- Resultados por página -->
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="search_nro_results">
                                    Mostrar {{ search_nro_results }} por página
                                </label>
                                <input
                                    id="search_nro_results"
                                    type="number"
                                    min="1"
                                    v-model="search_nro_results"
                                    class="form-control filtro-control"
                                >
                            </div>

                            <!-- Botón filtrar -->
                            <div class="form-group col-md-2 col-lg-1 text-right">
                                <button
                                    class="btn btn-primary btn-block filtro-btn-filtrar"
                                    @click="listar()"
                                >
                                    Filtrar
                                </button>
                            </div>
                        </div>

                        <!-- SEGUNDA FILA: EXCEL + SELECT DE COLUMNAS -->
                        <div class="row mb-3 justify-content-between align-items-center">
                            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                                <downloadexcel
                                    class="btn btn-success w-100"
                                    :fetch="fetchData"
                                    :fields="json_fields"
                                    name="informe-consolidado.xls"
                                    type="xls"
                                >
                                    <i class="fa fa-fw fa-download"></i> Generar Excel
                                </downloadexcel>
                            </div>

                            <!-- “Select” de columnas -->
                            <div class="col-md-4 col-lg-3 position-relative">
                                <button
                                    type="button"
                                    class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn"
                                    @click="toggleColumnPanel"
                                >
                                    <span>
                                        <i class="fas fa-columns mr-1"></i>
                                        Columnas
                                    </span>
                                    <i
                                        :class="[
                                            'fas',
                                            showColumnPanel ? 'fa-chevron-up' : 'fa-chevron-down'
                                        ]"
                                    ></i>
                                </button>

                                <!-- PANEL DESPLEGABLE DE COLUMNAS -->
                                <div
                                    v-if="showColumnPanel"
                                    class="column-panel-dropdown shadow-lg"
                                >
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="column-panel-title">
                                            Marca / desmarca las columnas que deseas ver
                                        </div>
                                        <div class="column-panel-actions">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success mr-1"
                                                @click.stop="selectAllColumns"
                                            >
                                                Todas
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                @click.stop="clearAllColumns"
                                            >
                                                Ninguna
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-12 col-md-4"
                                            v-for="group in [columnGroup1, columnGroup2, columnGroup3]"
                                            :key="group[0].key"
                                        >
                                            <div
                                                class="column-item"
                                                v-for="item in group"
                                                :key="item.key"
                                                @click.stop="toggleColumn(item.key)"
                                            >
                                                <span
                                                    class="column-checkbox"
                                                    :class="{ 'is-checked': columnVisibility[item.key] }"
                                                >
                                                    <i
                                                        v-if="columnVisibility[item.key]"
                                                        class="fas fa-check"
                                                    ></i>
                                                </span>
                                                <span class="column-label">
                                                    {{ item.label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA -->
                        <div
                            class="table-container table-container--wide"
                            id="table-container2"
                        >
                            <table
                                class="table-sticky table table-sm table-hover table-bordered"
                            >
                                <thead class="thead-primary">
                                    <tr>
                                        <th>#</th>
                                        <th class="fixed-column">Siembra</th>

                                        <th v-if="columnVisibility.area">Área (m²)</th>
                                        <th v-if="columnVisibility.tipo">Tipo</th>
                                        <th v-if="columnVisibility.fase">Fase</th>
                                        <th v-if="columnVisibility.inicio_siembra">Inicio siembra</th>
                                        <th v-if="columnVisibility.tiempo_dias">
                                            Tiempo de cultivo <br />
                                            <small>(Días)</small>
                                        </th>
                                        <th v-if="columnVisibility.tiempo_meses">
                                            Tiempo de cultivo <br />
                                            <small>(Meses)</small>
                                        </th>
                                        <th v-if="columnVisibility.cant_inicial">
                                            Cant Inicial (animales)
                                        </th>
                                        <th v-if="columnVisibility.biomasa_inicial">
                                            Biomasa Inicial (kg)
                                        </th>
                                        <th v-if="columnVisibility.peso_inicial">
                                            Peso Inicial (g)
                                        </th>
                                        <th v-if="columnVisibility.carga_inicial">
                                            Carga inicial (kg/m²)
                                        </th>
                                        <th v-if="columnVisibility.cant_final">
                                            Cant final (animales)
                                        </th>
                                        <th v-if="columnVisibility.peso_actual">
                                            Peso Actual (g)
                                        </th>
                                        <th v-if="columnVisibility.biomasa_disponible">
                                            Biomasa disponible (kg)
                                        </th>
                                        <th v-if="columnVisibility.biomasa_cosechada">
                                            Biomasa Cosechada (kg)
                                        </th>
                                        <th v-if="columnVisibility.mortalidad_animales">
                                            Mortalidad (Animales)
                                        </th>
                                        <th v-if="columnVisibility.mortalidad_kg">
                                            Mortalidad (Kg)
                                        </th>
                                        <th v-if="columnVisibility.mortalidad_porcentaje">
                                            % Mortalidad
                                        </th>
                                        <th v-if="columnVisibility.animales_cosechados">
                                            Animales cosechados
                                        </th>
                                        <th v-if="columnVisibility.densidad_inicial">
                                            Densidad Inicial
                                            (Animales/m<sup>2</sup>)
                                        </th>
                                        <th v-if="columnVisibility.densidad_final">
                                            Densidad Final
                                            (Animales/m<sup>2</sup>)
                                        </th>
                                        <th v-if="columnVisibility.carga_final">
                                            Carga Final (Kg/m<sup>2</sup>)
                                        </th>
                                        <th v-if="columnVisibility.horas_hombre">
                                            Horas Hombre
                                        </th>
                                        <th v-if="columnVisibility.costo_horas">
                                            Costo Horas
                                        </th>
                                        <th v-if="columnVisibility.costo_recursos">
                                            Costo Recursos
                                        </th>
                                        <th v-if="columnVisibility.costo_alimentos">
                                            Costo Alimentos
                                        </th>
                                        <th v-if="columnVisibility.total_alimento">
                                            Total alimento (Kg)
                                        </th>
                                        <th v-if="columnVisibility.costo_total">Costo Total</th>
                                        <th v-if="columnVisibility.costo_produccion_final">
                                            Costo producción final
                                        </th>
                                        <th v-if="columnVisibility.conversion_parcial">
                                            Conversión alimenticia parcial
                                        </th>
                                        <th v-if="columnVisibility.conversion_final">
                                            Conversión final
                                        </th>
                                        <th v-if="columnVisibility.ganancia_peso_dia">
                                            Ganancia peso / día <br />
                                            (gr)
                                        </th>
                                        <th v-if="columnVisibility.supervivencia_final">
                                            <b>%</b> Supervivencia final
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(le, index) in listadoExistencias.data"
                                        :key="index"
                                    >
                                        <td v-text="index + 1"></td>
                                        <td v-text="le.nombre_siembra" class="fixed-column"></td>

                                        <td v-if="columnVisibility.area" v-text="le.capacidad"></td>
                                        <td v-if="columnVisibility.tipo">
                                            {{ le.tipo }}
                                        </td>
                                        <td v-if="columnVisibility.fase">
                                            <span v-if="le.phase_id">
                                                {{ le.phase.phase }}
                                            </span>
                                        </td>
                                        <td
                                            v-if="columnVisibility.inicio_siembra"
                                            v-text="le.fecha_inicio"
                                        ></td>
                                        <td
                                            v-if="columnVisibility.tiempo_dias"
                                            v-text="le.intervalo_tiempo"
                                        ></td>
                                        <td v-if="columnVisibility.tiempo_meses">
                                            {{
                                                le.intervalo_tiempo_months | numeral("0.00")
                                            }}
                                            meses
                                        </td>
                                        <td v-if="columnVisibility.cant_inicial">
                                            {{ le.cantidad_inicial | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.biomasa_inicial">
                                            {{ le.biomasa_inicial | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.peso_inicial">
                                            {{ le.peso_inicial | numeral("0.00") }} gr
                                        </td>
                                        <td v-if="columnVisibility.carga_inicial">
                                            {{ le.carga_inicial | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.cant_final">
                                            {{ le.cant_actual | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.peso_actual">
                                            {{ le.peso_actual | numeral("0.00") }} gr
                                        </td>
                                        <td v-if="columnVisibility.biomasa_disponible">
                                            {{ le.biomasa_disponible | numeral("0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibility.biomasa_cosechada">
                                            {{ le.salida_biomasa | numeral("0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibility.mortalidad_animales">
                                            {{ le.mortalidad | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.mortalidad_kg">
                                            {{ le.mortalidad_kg | numeral("0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibility.mortalidad_porcentaje">
                                            {{ le.mortalidad_porcentaje | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.animales_cosechados">
                                            {{
                                                le.salida_animales_sin_mortalidad
                                                    | numeral("0.00")
                                            }}
                                        </td>
                                        <td v-if="columnVisibility.densidad_inicial">
                                            {{ le.densidad_inicial | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.densidad_final">
                                            {{ le.densidad_final | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.carga_final">
                                            {{ le.carga_final | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.horas_hombre">
                                            {{ le.horas_hombre | numeral("0.00") }} horas
                                        </td>
                                        <td v-if="columnVisibility.costo_horas">
                                            {{ le.costo_minutosh | numeral("$0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.costo_recursos">
                                            {{ le.costo_total_recurso | numeral("$0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.costo_alimentos">
                                            {{ le.costo_total_alimento | numeral("$0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.total_alimento">
                                            {{ le.cantidad_total_alimento }}
                                        </td>
                                        <td v-if="columnVisibility.costo_total">
                                            {{ le.costo_tot | numeral("$0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.costo_produccion_final">
                                            {{ le.costo_produccion_final | numeral("$0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.conversion_parcial">
                                            {{
                                                le.conversion_alimenticia_parcial
                                                    | numeral("0.00")
                                            }}
                                        </td>
                                        <td v-if="columnVisibility.conversion_final">
                                            {{ le.conversion_final | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.ganancia_peso_dia">
                                            {{ le.ganancia_peso_dia | numeral("0.00") }}
                                        </td>
                                        <td v-if="columnVisibility.supervivencia_final">
                                            {{
                                                le.porc_supervivencia_final
                                                    | numeral("0.00")
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <pagination
                                :align="'center'"
                                :data="listadoExistencias"
                                :limit="2"
                                @pagination-change-page="listar"
                            >
                                <span slot="prev-nav">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span slot="next-nav">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import downloadexcel from "vue-json-excel";

export default {
    data() {
        return {
            // SPINNER
            isLoading: false,

            json_fields: {
                Siembra: "nombre_siembra",
                'Area (m<sup>2</sup>)': "capacidad",
                "Inicio siembra": "fecha_inicio",
                "Tipo": "tipo",
                "Fase": "phase.phase",
                "Tiempo de cultivo \n (Días)": {
                    field: "intervalo_tiempo",
                    callback: value => numeral(value).format("0.00")
                },
                "Tiempo de cultivo \n (Meses)": {
                    field: "intervalo_tiempo_months",
                    callback: value => numeral(value).format("0.00")
                },
                "Cantidad Inicial \n (Animales) ": {
                    field: "cantidad_inicial",
                    callback: value => numeral(value).format("0.00")
                },
                "Biomasa inicial \n (Kg) ": {
                    field: "biomasa_inicial",
                    callback: value => numeral(value).format("0.00")
                },
                "Peso inicial  \n (g)": {
                    field: "peso_inicial",
                    callback: value => numeral(value).format("0.00")
                },
                "Carga inicial  \n (Kg / m<sup>2</sup> ) ": {
                    field: "carga_inicial",
                    callback: value => numeral(value).format("0.00")
                },
                "Cantidad final \n (Animales)": {
                    field: "cant_actual",
                    callback: value => numeral(value).format("0.00")
                },
                "Peso actual \n (g)": {
                    field: "peso_actual",
                    callback: value => numeral(value).format("0.00")
                },
                "Biomasa disponible \n (kg)": {
                    field: "biomasa_disponible",
                    callback: value => numeral(value).format("0.00")
                },
                "Biomasa cosechada \n (Kg)": {
                    field: "salida_biomasa",
                    callback: value => numeral(value).format("0.00")
                },
                Mortalidad: {
                    field: "mortalidad",
                    callback: value => numeral(value).format("0.00")
                },
                "Mortalidad (kg)": {
                    field: "mortalidad_kg",
                    callback: value => numeral(value).format("0.00")
                },
                "Mortalidad %": {
                    field: "mortalidad_porcentaje",
                    callback: value => numeral(value).format("0.00")
                },
                "Animales cosechados": {
                    field: "salida_animales_sin_mortalidad",
                    callback: value => numeral(value).format("0.00")
                },
                "Densidad inicial (Animales/m2)": {
                    field: "densidad_inicial",
                    callback: value => numeral(value).format("0.00")
                },
                "Densidad final (Animales/m2)": {
                    field: "densidad_final",
                    callback: value => numeral(value).format("0.00")
                },
                "Carga final (Kg/m2)": {
                    field: "carga_final",
                    callback: value => numeral(value).format("0.00")
                },
                "Horas hombre": {
                    field: "horas_hombre",
                    callback: value => numeral(value).format("0.00")
                },
                "Costo Horas": {
                    field: "costo_minutosh",
                    callback: value => numeral(value).format("0.00")
                },
                "Costo total recursos": {
                    field: "costo_total_recurso",
                    callback: value => numeral(value).format("0.00")
                },
                "Costo total alimentos": {
                    field: "costo_total_alimento",
                    callback: value => numeral(value).format("0.00")
                },
                "Total Kg Alimento": {
                    field: "cantidad_total_alimento",
                    callback: value => numeral(value).format("0.00")
                },
                "Costo total Siembra": {
                    field: "costo_tot",
                    callback: value => numeral(value).format("0.00")
                },
                "Costo producccion final": {
                    field: "costo_produccion_final",
                    callback: value => numeral(value).format("0.00")
                },
                "Conversión alimenticia parcial": {
                    field: "conversion_alimenticia_parcial",
                    callback: value => numeral(value).format("0.00")
                },
                "Conversion final": {
                    field: "conversion_final",
                    callback: value => numeral(value).format("0.00")
                },
                "Ganancia peso/dia \n (gr)": {
                    field: "ganancia_peso_dia",
                    callback: value => numeral(value).format("0.00")
                },
                "% Supervivencia final": {
                    field: "porc_supervivencia_final",
                    callback: value => numeral(value).format("0.00")
                }
            },

            listadoExistencias: {},
            listadoEspecies: [],
            listadoSiembras: [],
            listadoEstanques: [],

            // Filtros
            f_siembra: "",
            f_contenedor: "-1",
            f_estado: "1",
            f_inicio_d: "-1",
            f_inicio_h: "-1",
            search_type: "",
            search_nro_results: "15",

            // Panel columnas
            showColumnPanel: false,
            columnVisibility: {
                area: true,
                tipo: true,
                fase: true,
                inicio_siembra: true,
                tiempo_dias: true,
                tiempo_meses: true,
                cant_inicial: true,
                biomasa_inicial: true,
                peso_inicial: true,
                carga_inicial: true,
                cant_final: true,
                peso_actual: true,
                biomasa_disponible: true,
                biomasa_cosechada: true,
                mortalidad_animales: true,
                mortalidad_kg: true,
                mortalidad_porcentaje: true,
                animales_cosechados: true,
                densidad_inicial: true,
                densidad_final: true,
                carga_final: true,
                horas_hombre: true,
                costo_horas: true,
                costo_recursos: true,
                costo_alimentos: true,
                total_alimento: true,
                costo_total: true,
                costo_produccion_final: true,
                conversion_parcial: true,
                conversion_final: true,
                ganancia_peso_dia: true,
                supervivencia_final: true
            }
        };
    },
    components: {
        downloadexcel
    },
    computed: {
        filteredItems() {
            return this.listadoSiembras.filter(
                item => String(item.estado) === String(this.f_estado)
            );
        },
        columnGroup1() {
            return [
                { key: "area", label: "Área (m²)" },
                { key: "tipo", label: "Tipo" },
                { key: "fase", label: "Fase" },
                { key: "inicio_siembra", label: "Inicio siembra" },
                { key: "tiempo_dias", label: "Tiempo cultivo (días)" },
                { key: "tiempo_meses", label: "Tiempo cultivo (meses)" },
                { key: "cant_inicial", label: "Cant. inicial" },
                { key: "biomasa_inicial", label: "Biomasa inicial" },
                { key: "peso_inicial", label: "Peso inicial" },
                { key: "carga_inicial", label: "Carga inicial" }
            ];
        },
        columnGroup2() {
            return [
                { key: "cant_final", label: "Cant. final" },
                { key: "peso_actual", label: "Peso actual" },
                { key: "biomasa_disponible", label: "Biomasa disponible" },
                { key: "biomasa_cosechada", label: "Biomasa cosechada" },
                { key: "mortalidad_animales", label: "Mortalidad (anim.)" },
                { key: "mortalidad_kg", label: "Mortalidad (kg)" },
                { key: "mortalidad_porcentaje", label: "% Mortalidad" },
                { key: "animales_cosechados", label: "Animales cosechados" },
                { key: "densidad_inicial", label: "Densidad inicial" },
                { key: "densidad_final", label: "Densidad final" }
            ];
        },
        columnGroup3() {
            return [
                { key: "carga_final", label: "Carga final" },
                { key: "horas_hombre", label: "Horas hombre" },
                { key: "costo_horas", label: "Costo horas" },
                { key: "costo_recursos", label: "Costo recursos" },
                { key: "costo_alimentos", label: "Costo alimentos" },
                { key: "total_alimento", label: "Total alimento (kg)" },
                { key: "costo_total", label: "Costo total" },
                {
                    key: "costo_produccion_final",
                    label: "Costo producción final"
                },
                {
                    key: "conversion_parcial",
                    label: "Conv. alimenticia parcial"
                },
                { key: "conversion_final", label: "Conversión final" },
                { key: "ganancia_peso_dia", label: "Ganancia peso/día" },
                {
                    key: "supervivencia_final",
                    label: "% Supervivencia final"
                }
            ];
        }
    },
    methods: {
        toggleColumnPanel() {
            this.showColumnPanel = !this.showColumnPanel;
        },
        toggleColumn(key) {
            this.$set(this.columnVisibility, key, !this.columnVisibility[key]);
        },
        selectAllColumns() {
            Object.keys(this.columnVisibility).forEach(k => {
                this.columnVisibility[k] = true;
            });
        },
        clearAllColumns() {
            Object.keys(this.columnVisibility).forEach(k => {
                this.columnVisibility[k] = false;
            });
        },
        async fetchData() {
            return this.listadoExistencias.data;
        },
        listar(page = 1) {
            this.isLoading = true;

            const data = {
                f_siembra: this.f_siembra === "" ? "-1" : this.f_siembra,
                f_contenedor:
                    this.f_contenedor === "-1" ? "-1" : this.f_contenedor,
                f_estado: this.f_estado === "-1" ? "-1" : this.f_estado,
                f_inicio_d:
                    this.f_inicio_d === "-1" ? "-1" : this.f_inicio_d,
                f_inicio_h:
                    this.f_inicio_h === "-1" ? "-1" : this.f_inicio_h,
                type: this.search_type,
                nro_results: this.search_nro_results,
                page
            };

            axios
                .get("api/traer-existencias-detalle", { params: data })
                .then(response => {
                    this.listadoExistencias = response.data.existencias;
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "Ocurrió un problema cargando el informe",
                        "error"
                    );
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        listarEspecies() {
            axios.get("api/especies").then(response => {
                this.listadoEspecies = response.data;
            });
        },
        listarSiembras() {
            axios.get("api/siembras/listado").then(response => {
                this.listadoSiembras = response.data;
            });
        },
        listarEstanques() {
            axios.get("api/contenedores").then(response => {
                this.listadoEstanques = response.data;
            });
        }
    },
    mounted() {
        this.listarEspecies();
        this.listarSiembras();
        this.listarEstanques();
        this.listar();
    }
};
</script>

<style scoped>
/* Barra de filtros: mismos altos, look limpio */
.filtro-bar .form-group {
    margin-bottom: 0.75rem;
}

.filtro-control {
    border-radius: 999px;
    font-size: 0.9rem;
    height: 38px;
    padding: 0 0.85rem;
}

.filtro-vselect >>> .vs__dropdown-toggle {
    border-radius: 999px;
    min-height: 38px;
    padding: 0 0.5rem;
}

.filtro-vselect >>> .vs__selected,
.filtro-vselect >>> .vs__search {
    margin-top: 2px;
    margin-bottom: 2px;
}

.filtro-btn-filtrar {
    border-radius: 999px;
    font-size: 0.9rem;
}

/* Botón de columnas */
.column-toggle-btn {
    border-radius: 999px;
    background-color: #ffffff;
    font-size: 0.85rem;
}

/* Dropdown de columnas */
.column-panel-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 0.35rem;
    z-index: 30;
    background: #ffffff;
    border-radius: 14px;
    padding: 0.75rem 1rem 0.85rem;
    min-width: 320px;
    max-width: 420px;
    max-height: 260px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
}

.column-panel-title {
    font-size: 0.8rem;
    color: #6b7280;
}

.column-panel-actions .btn {
    font-size: 0.75rem;
    padding: 0.15rem 0.5rem;
}

/* Ítems de columnas con “check” custom */
.column-item {
    display: flex;
    align-items: center;
    font-size: 0.82rem;
    margin-bottom: 0.25rem;
    cursor: pointer;
    user-select: none;
    padding: 0.12rem 0;
}

.column-checkbox {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.45rem;
    background-color: #ffffff;
    transition: all 0.12s ease;
    flex-shrink: 0;
}

.column-checkbox.is-checked {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.column-checkbox i {
    font-size: 0.7rem;
}

.column-label {
    line-height: 1.1;
}

/* Ajuste para que el dropdown no se tape con scroll */
.table-container--wide {
    position: relative;
}
</style>
