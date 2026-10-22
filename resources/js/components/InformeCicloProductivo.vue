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
            INFORMES CICLO PRODUCTIVO
          </div>

          <div class="card-body">
            <!-- TÍTULO FILTROS -->
            <div class="row text-left mb-2">
              <h5>Filtrar por:</h5>
            </div>

            <!-- BARRA DE FILTROS ORDENADA -->
            <div class="row align-items-end filtro-bar">
              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="estado_siembta">Estado de siembra</label>
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

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="siembra_activa">
                  Siembras
                  {{
                    f_estado === "-1"
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

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="categoria" class="col-form-label">Fase</label>
                <v-select
                  label="phase"
                  class="w-100 filtro-vselect"
                  v-model="search_phase"
                  :reduce="option => option.id"
                  :filterable="false"
                  :options="listPhases"
                  @search="onSearchPhase"
                >
                  <template slot="no-options">
                    Escribe para iniciar la búsqueda
                  </template>
                  <template slot="option" slot-scope="option">
                    <div class="d-center">
                      {{ option.phase }}
                    </div>
                  </template>
                  <template slot="selected-option" slot-scope="option">
                    <div class="selected d-center">
                      {{ option.phase }}
                    </div>
                  </template>
                </v-select>
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="search_type">Tipo de siembra</label>
                <select
                  name="search_type"
                  class="form-control filtro-control w-100"
                  id="search_type"
                  v-model="search_type"
                >
                  <option value="">Todas</option>
                  <option value="Monocultivo">Monocultivo</option>
                  <option value="Policultivo">Policultivo</option>
                </select>
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="lote">Lotes:</label>
                <select
                  class="form-control filtro-control"
                  id="lote"
                  v-model="f_lote"
                >
                  <option value="-1">Seleccionar</option>
                  <option
                    :value="lote.lote"
                    v-for="(lote, index) in listadoLotes"
                    :key="index"
                  >
                    {{ lote.lote }}
                  </option>
                </select>
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="especie">Especies</label>
                <select
                  class="form-control filtro-control"
                  id="especie"
                  v-model="f_especie"
                >
                  <option value="-1">Seleccionar</option>
                  <option
                    :value="les.id"
                    v-for="(les, index) in listadoEspecies"
                    :key="index"
                  >
                    {{ les.especie }}
                  </option>
                </select>
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="f_peso_d">Peso desde (gr)</label>
                <input
                  type="number"
                  step="any"
                  class="form-control filtro-control"
                  id="f_peso_d"
                  v-model="f_peso_d"
                >
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="f_peso_h">Peso hasta (gr)</label>
                <input
                  type="number"
                  step="any"
                  class="form-control filtro-control"
                  id="f_peso_h"
                  v-model="f_peso_h"
                >
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="f_inicio_d">Fecha inicio desde</label>
                <input
                  type="date"
                  class="form-control filtro-control"
                  id="f_inicio_d"
                  v-model="f_inicio_d"
                >
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="f_inicio_h">Fecha inicio hasta</label>
                <input
                  type="date"
                  class="form-control filtro-control"
                  id="f_inicio_h"
                  v-model="f_inicio_h"
                >
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <label for="search_nro_results">
                  Mostrar {{ search_nro_results }} resultados por página
                </label>
                <input
                  type="number"
                  v-model="search_nro_results"
                  id="search_nro_results"
                  min="1"
                  class="form-control filtro-control"
                >
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <button
                  class="btn btn-primary btn-block filtro-btn-filtrar"
                  @click="listar()"
                  type="button"
                >
                  Filtrar resultados
                </button>
              </div>

              <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                <downloadexcel
                  class="btn btn-success form-control"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-ciclo-productivo.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
              </div>

              <!-- BOTÓN / DROPDOWN DE COLUMNAS -->
              <div
                class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3 position-relative"
              >
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

                <div
                  v-if="showColumnPanel"
                  class="column-panel-dropdown shadow-lg"
                >
                  <div
                    class="d-flex justify-content-between align-items-center mb-2"
                  >
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
                    <div class="col-12 col-md-4">
                      <div
                        class="column-item"
                        v-for="item in columnGroup1"
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
                    <div class="col-12 col-md-4">
                      <div
                        class="column-item"
                        v-for="item in columnGroup2"
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
                    <div class="col-12 col-md-4">
                      <div
                        class="column-item"
                        v-for="item in columnGroup3"
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
            <div class="table-container table-container--wide">
              <table
                class="table table-bordered table-striped table-sm table-sticky"
              >
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th class="fixed-column">Siembra</th>

                    <th v-if="columnVisibility.tipo">Tipo</th>
                    <th v-if="columnVisibility.fase">Fase</th>
                    <th v-if="columnVisibility.lote">Lote</th>
                    <th v-if="columnVisibility.area">Area</th>
                    <th v-if="columnVisibility.especie">Especie</th>
                    <th v-if="columnVisibility.inicio_siembra">Inicio siembra</th>
                    <th v-if="columnVisibility.cant_inicial">
                      Cant Inicial <br />
                      <small>(Animales)</small>
                    </th>
                    <th v-if="columnVisibility.peso_inicial">
                      Peso Inicial <br />
                      <small>(g)</small>
                    </th>
                    <th v-if="columnVisibility.animales_finales">
                      Animales finales
                    </th>
                    <th v-if="columnVisibility.peso_actual">
                      Peso Actual <br />
                      <small>(g)</small>
                    </th>
                    <th v-if="columnVisibility.fecha_ultimo_registro">
                      Fecha último registro
                    </th>
                    <th v-if="columnVisibility.tiempo_dias">
                      Tiempo de cultivo <br />
                      <small>(Días)</small>
                    </th>
                    <th v-if="columnVisibility.tiempo_meses">
                      Tiempo de cultivo <br />
                      <small>(Meses)</small>
                    </th>
                    <th v-if="columnVisibility.biomasa_muestreo">
                      Biomasa muestreo <br />
                      <small>(Kg)</small>
                    </th>
                    <th v-if="columnVisibility.biomasa_cosechada">
                      Biomasa cosechada <br />
                      <small>(Kg)</small>
                    </th>
                    <th v-if="columnVisibility.mortalidad_animales">
                      Mortalidad Animales
                    </th>
                    <th v-if="columnVisibility.mort_kg">Mort. Kg</th>
                    <th v-if="columnVisibility.porcentaje_mortalidad">
                      % Mortalidad
                    </th>
                    <th v-if="columnVisibility.animales_cosechados">
                      Animales cosechados
                    </th>
                    <th v-if="columnVisibility.ganancia_peso_dia">
                      Ganancia de peso por día <br />
                      <small>(g / día)</small>
                    </th>
                    <th v-if="columnVisibility.densidad_parcial">
                      Densidad parcial <br />
                      <small>(Animales/m<sup>2</sup>)</small>
                    </th>
                    <th v-if="columnVisibility.carga_parcial">
                      Carga parcial <br />
                      <small>(Kg/m<sup>2</sup>)</small>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(le, index) in listadoExistencias.data" :key="index">
                    <td v-text="index + 1"></td>

                    <td class="fixed-column">
                      {{ le.nombre_siembra }}
                    </td>

                    <td v-if="columnVisibility.tipo">{{ le.siembra.tipo }}</td>

                    <td v-if="columnVisibility.fase">
                      <span v-if="le.siembra.phase_id">
                        {{ le.siembra.phase.phase }}
                      </span>
                    </td>

                    <td v-if="columnVisibility.lote">
                      {{ le.lote }}
                    </td>

                    <td v-if="columnVisibility.area">
                      {{ le.capacidad }}
                    </td>

                    <td v-if="columnVisibility.especie">
                      {{ le.especie.especie }}
                    </td>

                    <td v-if="columnVisibility.inicio_siembra">
                      {{ le.fecha_inicio }}
                    </td>

                    <td v-if="columnVisibility.cant_inicial">
                      {{ le.cantidad_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.peso_inicial">
                      {{ le.peso_inicial | numeral('0,0.00') }} gr
                    </td>

                    <td v-if="columnVisibility.animales_finales">
                      {{ le.cantidad_actual | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.peso_actual">
                      {{ le.peso_actual | numeral('0,0.00') }} gr
                    </td>

                    <td v-if="columnVisibility.fecha_ultimo_registro">
                      {{ le.fecha_registro }}
                    </td>

                    <td v-if="columnVisibility.tiempo_dias">
                      {{ le.intervalo_tiempo }} días
                    </td>

                    <td v-if="columnVisibility.tiempo_meses">
                      {{ le.intervalo_tiempo_months | numeral('0,0.00') }} meses
                    </td>

                    <td v-if="columnVisibility.biomasa_muestreo">
                      {{ le.biomasa_disponible | numeral('0,0.00') }} kg
                    </td>

                    <td v-if="columnVisibility.biomasa_cosechada">
                      {{ le.salida_biomasa | numeral('0,0.00') }} kg
                    </td>

                    <td v-if="columnVisibility.mortalidad_animales">
                      {{ le.mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mort_kg">
                      {{ le.mortalidad_kg | numeral('0,0.00') }} kg
                    </td>

                    <td v-if="columnVisibility.porcentaje_mortalidad">
                      {{ le.mortalidad_porcentaje | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.animales_cosechados">
                      {{ le.salida_animales_sin_mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.ganancia_peso_dia">
                      {{ le.ganancia_peso_dia | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.densidad_parcial">
                      {{ le.densidad_parcial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.carga_parcial">
                      {{ le.carga_parcial | numeral('0,0.00') }}
                    </td>
                  </tr>

                  <!-- TOTALES -->
                  <tr class="h6 font-weight-bold text-right">
                    <td></td>
                    <td></td>

                    <td v-if="columnVisibility.tipo"></td>
                    <td v-if="columnVisibility.fase"></td>
                    <td v-if="columnVisibility.lote"></td>
                    <td v-if="columnVisibility.area"></td>
                    <td v-if="columnVisibility.especie"></td>
                    <td v-if="columnVisibility.inicio_siembra"></td>

                    <td v-if="columnVisibility.cant_inicial">
                      {{ totalizadoEspeciesSiembras.cantidad_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.peso_inicial">
                      {{ totalizadoEspeciesSiembras.peso_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.animales_finales">
                      {{ totalizadoEspeciesSiembras.cantidad_actual | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.peso_actual"></td>
                    <td v-if="columnVisibility.fecha_ultimo_registro"></td>
                    <td v-if="columnVisibility.tiempo_dias"></td>
                    <td v-if="columnVisibility.tiempo_meses"></td>

                    <td v-if="columnVisibility.biomasa_muestreo">
                      {{ totalizadoEspeciesSiembras.biomasa_disponible | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.biomasa_cosechada">
                      {{ totalizadoEspeciesSiembras.salida_biomasa | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mortalidad_animales">
                      {{ totalizadoEspeciesSiembras.mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mort_kg">
                      {{ totalizadoEspeciesSiembras.mortalidad_kg | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.porcentaje_mortalidad"></td>

                    <td v-if="columnVisibility.animales_cosechados">
                      {{ totalizadoEspeciesSiembras.salida_animales_sin_mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.ganancia_peso_dia"></td>
                    <td v-if="columnVisibility.densidad_parcial"></td>
                    <td v-if="columnVisibility.carga_parcial"></td>
                  </tr>
                </tbody>
              </table>

              <pagination
                :align="'center'"
                :data="listadoExistencias"
                :limit="2"
                @pagination-change-page="listar"
              >
                <span slot="prev-nav"><i class="fas fa-arrow-left"></i></span>
                <span slot="next-nav"><i class="fas fa-arrow-right"></i></span>
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
      json_fields: {
        Siembra: "nombre_siembra",
        Tipo: "siembra.tipo",
        Fase: "siembra.phase.phase",
        Lote: "lote",
        Area: "capacidad",
        Especie: "especie.especie",
        "Inicio siembra": "fecha_inicio",
        "Cantidad Inicial \n (Animales)": "cantidad_inicial",
        "Peso inicial \n (g)": "peso_inicial",
        "Animales finales": {
          field: "cantidad_actual",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Peso actual \n (g)": {
          field: "peso_actual",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Fecha último registro": "fecha_registro",
        "Tiempo de cultivo \n (Días) ": "intervalo_tiempo",
        "Tiempo de cultivo \n (Meses) ": {
          field: "intervalo_tiempo_months",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Biomasa muestreo \n (Kg)": {
          field: "biomasa_disponible",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Biomasa cosechada \n (Kg)": {
          field: "salida_biomasa",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Mortalidad Animales": {
          field: "mortalidad",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Mortalidad kg": "mortalidad_kg",
        "% Mortalidad": "mortalidad_porcentaje",
        "Animales cosechados": {
          field: "salida_animales_sin_mortalidad",
          callback: value => {
            return numeral(value).format("0.00");
          }
        },
        "Gananacia de peso por día \n (g/día)": "ganancia_peso_dia",
        "Densidad parcial (Animales/m<sup>2</sup>)": "densidad_parcial",
        "Carga parcial (Kg/m<sup>2</sup>)": {
          field: "carga_parcial",
          callback: value => {
            return numeral(value).format("0.00");
          }
        }
      },
      listadoExistencias: {},
      totalizadoEspeciesSiembras: [],
      listadoEspecies: [],
      listadoSiembras: [],
      imprimirRecursos: [],
      listadoLotes: [],
      listPhases: [],
      f_siembra: "",
      f_estado: "1",
      f_lote: "-1",
      f_especie: "-1",
      f_inicio_d: "-1",
      f_inicio_h: "-1",
      f_peso_d: 0,
      f_peso_h: 1000,
      search_phase: "",
      search_type: "",
      search_nro_results: "15",
      // SPINNER
      isLoading: false,

      // Columnas
      showColumnPanel: false,
      columnVisibility: {
        tipo: true,
        fase: true,
        lote: true,
        area: true,
        especie: true,
        inicio_siembra: true,
        cant_inicial: true,
        peso_inicial: true,
        animales_finales: true,
        peso_actual: true,
        fecha_ultimo_registro: true,
        tiempo_dias: true,
        tiempo_meses: true,
        biomasa_muestreo: true,
        biomasa_cosechada: true,
        mortalidad_animales: true,
        mort_kg: true,
        porcentaje_mortalidad: true,
        animales_cosechados: true,
        ganancia_peso_dia: true,
        densidad_parcial: true,
        carga_parcial: true
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
        { key: "tipo", label: "Tipo" },
        { key: "fase", label: "Fase" },
        { key: "lote", label: "Lote" },
        { key: "area", label: "Área" }
      ];
    },
    columnGroup2() {
      return [
        { key: "especie", label: "Especie" },
        { key: "inicio_siembra", label: "Inicio siembra" },
        { key: "cant_inicial", label: "Cant. Inicial (Animales)" },
        { key: "peso_inicial", label: "Peso Inicial (g)" },
        { key: "animales_finales", label: "Animales finales" },
        { key: "peso_actual", label: "Peso Actual (g)" }
      ];
    },
    columnGroup3() {
      return [
        { key: "fecha_ultimo_registro", label: "Fecha último registro" },
        { key: "tiempo_dias", label: "Tiempo cultivo (días)" },
        { key: "tiempo_meses", label: "Tiempo cultivo (meses)" },
        { key: "biomasa_muestreo", label: "Biomasa muestreo (Kg)" },
        { key: "biomasa_cosechada", label: "Biomasa cosechada (Kg)" },
        { key: "mortalidad_animales", label: "Mortalidad animales" },
        { key: "mort_kg", label: "Mort. Kg" },
        { key: "porcentaje_mortalidad", label: "% Mortalidad" },
        { key: "animales_cosechados", label: "Animales cosechados" },
        { key: "ganancia_peso_dia", label: "Ganancia peso/día" },
        { key: "densidad_parcial", label: "Densidad parcial" },
        { key: "carga_parcial", label: "Carga parcial" }
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
        f_estado: this.f_estado === "-1" ? "-1" : this.f_estado,
        f_lote: this.f_lote === "-1" ? "-1" : this.f_lote,
        f_especie: this.f_especie === "-1" ? "-1" : this.f_especie,
        f_inicio_d: this.f_inicio_d === "-1" ? "-1" : this.f_inicio_d,
        f_inicio_h: this.f_inicio_h === "-1" ? "-1" : this.f_inicio_h,
        f_peso_d: this.f_peso_d === "" ? "-1" : this.f_peso_d,
        f_peso_h: this.f_peso_h === "" ? "-1" : this.f_peso_h,
        phase: this.search_phase,
        type: this.search_type,
        nro_results: this.search_nro_results,
        page: page
      };

      const me = this;

      axios
        .get("api/traer-existencias", { params: data })
        .then(function(response) {
          me.listadoExistencias = response.data.existencias;
          me.totalizadoEspeciesSiembras =
            response.data.totalizadoEspeciesSiembras;
        })
        .catch(function(error) {
          console.error(error);
          if (typeof Swal !== "undefined") {
            Swal.fire(
              "Error",
              "Ocurrió un problema cargando el informe de ciclo productivo",
              "error"
            );
          }
        })
        .finally(() => {
          me.isLoading = false;
        });
    },

    listarEspecies() {
      const me = this;
      axios.get("api/especies").then(function(response) {
        me.listadoEspecies = response.data;
      });
    },
    listarSiembras() {
      const me = this;
      axios.get("api/siembras/listado").then(function(response) {
        me.listadoSiembras = response.data;
      });
    },
    listarLotes() {
      const me = this;
      axios.get("api/siembras/listado-lotes").then(function(response) {
        me.listadoLotes = response.data;
      });
    },
    onSearchPhase(search, loading) {
      if (search.length) {
        loading(true);
        const data = {
          phase: search
        };

        axios
          .get("api/phases/get", {
            params: data
          })
          .then(response => {
            this.listPhases = response.data.phases.data;
            loading(false);
          })
          .catch(e => {
            console.log(e);
            loading(false);
          });
      }
    }
  },
  mounted() {
    this.listar();
    this.listarEspecies();
    this.listarSiembras();
    this.listarLotes();
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

/* Tabla ancha con scroll horizontal si es necesario */
.table-container--wide {
  position: relative;
  overflow-x: auto;
  width: 100%;
}

/* Columna fija Siembra (que no tape la columna Tipo) */
.fixed-column {
  position: sticky;
  left: 0;
  background-color: #ffffff;
  z-index: 2;
  min-width: 180px;
  box-shadow: 4px 0 6px rgba(0, 0, 0, 0.06);
}
thead .fixed-column {
  z-index: 3;
}

/* Botón de columnas */
.column-toggle-btn {
  border-radius: 999px;
  background-color: #ffffff;
  font-size: 0.85rem;
}

/* Dropdown de columnas (mismo estilo que otros informes) */
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
  max-width: 480px;
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
</style>
