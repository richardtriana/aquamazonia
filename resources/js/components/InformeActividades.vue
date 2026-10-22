<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Informes Actividades (Muestreo y Pesca)</div>
          <!-- <a href="informe-excel"><button type="submit" class="btn btn-success" name="infoSiembras"><i class="fa fa-fw fa-download"></i> Generar Excel </button></a> -->
          <div class="card-body">

            <!-- SPINNER GLOBAL -->
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

            <div class="row mb-1">
              <div class="col-md-12">
                <h5>Filtrar por:</h5>
                <form class="row" method="POST" action="informe-excel" target="_blank">
                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="f_estado" class="w-100">
                      Estado:
                      <select class="custom-select w-100" name="estado" id="estado" v-model="f_estado">
                        <option value="-1">--Seleccionar--</option>
                        <option value="0">Inactiva</option>
                        <option value="1">Activa</option>
                      </select>
                    </label>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="siembra_activa">Siembras
                      {{ f_estado == 0 ? "inactivas" : "activas" }} :
                    </label>
                    <template v-if="f_estado != '-1'">
                      <v-select :options="filteredItems" label="nombre_siembra" :reduce="(siembra) => siembra.id"
                        v-model="f_siembra" />
                    </template>
                    <template v-if="f_estado == '-1'">
                      <v-select :options="listadoSiembras" label="nombre_siembra" :reduce="(siembra) => siembra.id"
                        v-model="f_siembra" />
                    </template>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="phase" class="col-form-label">Fase
                    </label>
                    <v-select label="phase" class="w-100" v-model="search_phase" :reduce="(option) => option.id"
                      :filterable="false" :options="listPhases" @search="onSearchPhase">
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
                    <select name="search_type" class="custom-select w-100" id="search_type" v-model="search_type">
                      <option value="">Todas</option>
                      <option value="Monocultivo">Monocultivo</option>
                      <option value="Policultivo">Policultivo</option>
                    </select>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="contenedor">Estanque:</label>
                    <select class="custom-select" id="contenedor" v-model="id_contenedor">
                      <option value="-1">Seleccionar</option>
                      <option :value="cont.id" v-for="(cont, index) in listadoEstanques" :key="index">
                        {{ cont.contenedor }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="lote">Lotes:</label>
                    <select class="custom-select" id="lote" v-model="f_lote">
                      <option value="-1">Seleccionar</option>
                      <option :value="lote.lote" v-for="(lote, index) in listadoLotes" :key="index">
                        {{ lote.lote }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="Especie">Especie</label>
                    <select class="form-control" id="f_especie" v-model="f_especie">
                      <option value="-1" selected>Seleccionar</option>
                      <option :value="especie.id" v-for="especie in listadoEspecies" :key="especie.id">
                        {{ especie.especie }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="actividad">Tipo actividad: </label>
                    <select class="form-control" id="actividad" v-model="search_activity" name="tipo_actividad">
                      <option selected value="">Seleccionar</option>
                      <option value="0">Muestreo</option>
                      <option value="1">Pesca</option>
                      <option value="2">Mortalidad Inicial</option>
                      <option value="3">Peso Inicial</option>
                    </select>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="peso desde">peso desde (gr): </label>
                    <input type="number" step="any" class="form-control" id="f_peso_d" v-model="f_peso_d" />
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="peso hasta">peso hasta (gr): </label>
                    <input type="number" step="any" class="form-control" id="f_peso_h" v-model="f_peso_h" />
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="search">Desde: </label>
                    <input class="form-control" type="date" placeholder="Search" aria-label="search_from"
                      v-model="search_from" />
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="search">Hasta: </label>
                    <input class="form-control" type="date" placeholder="Search" aria-label="search_to"
                      v-model="search_to" />
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="search_nro_results">Mostrar {{ search_nro_results }} por página</label>
                    <input type="number" v-model="search_nro_results" class="form-control">
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <label for="">Buscar</label>
                    <button class="btn btn-primary form-control" type="button" @click="listarRegistros()">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>

                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                    <downloadexcel class="btn btn-success btn-block w-100 mt-4" :fetch="fetchData" :fields="json_fields"
                      name="informe-muestreos.xls" type="xls">
                      <i class="fa fa-fw fa-download"></i> Generar Excel
                    </downloadexcel>
                  </div>

                  <!-- BOTÓN COLUMNAS -->
                  <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3 position-relative">
                    <label class="d-none d-md-block">&nbsp;</label>
                    <button
                      type="button"
                      class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn mt-1"
                      @click="toggleColumnPanel"
                    >
                      <span>
                        <i class="fas fa-columns mr-1"></i>
                        Columnas
                      </span>
                      <i :class="['fas', showColumnPanel ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                    </button>

                    <div v-if="showColumnPanel" class="column-panel-dropdown shadow-lg">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="column-panel-title">
                          Deseas ver
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
                        <div class="col-12 col-md-6">
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
                              <i v-if="columnVisibility[item.key]" class="fas fa-check"></i>
                            </span>
                            <span class="column-label">
                              {{ item.label }}
                            </span>
                          </div>
                        </div>
                        <div class="col-12 col-md-6">
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
                              <i v-if="columnVisibility[item.key]" class="fas fa-check"></i>
                            </span>
                            <span class="column-label">
                              {{ item.label }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <div class="table-container" id="table-container2">
              <table class="table-sticky table table-sm table-hover table-bordered">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th v-if="columnVisibility.siembra">Siembra</th>
                    <th v-if="columnVisibility.tipo">Tipo</th>
                    <th v-if="columnVisibility.fase">Fase</th>
                    <th v-if="columnVisibility.lote">Lote</th>
                    <th v-if="columnVisibility.fecha_registro">Fecha de registro</th>
                    <th v-if="columnVisibility.especie">Especie</th>
                    <th v-if="columnVisibility.tipo_actividad">Tipo <br />actividad</th>
                    <th v-if="columnVisibility.peso_actual">Peso actual (g) </th>
                    <th v-if="columnVisibility.biomasa_cosechada">Biomasa cosechada Kg</th>
                    <th v-if="columnVisibility.biomasa_muestreo">Biomasa muestreo (kg)</th>
                    <th v-if="columnVisibility.animales_actuales">Animales Actuales</th>
                    <th v-if="columnVisibility.biomasa_alimento">Biomasa disponible por alimento</th>
                    <th v-if="columnVisibility.animales_cosechados">Animales Cosechados</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- FILAS -->
                  <tr v-for="(lr, index) in listadoRegistros.data" :key="index">
                    <td v-text="index + 1"></td>

                    <td v-if="columnVisibility.siembra" v-text="lr.nombre_siembra"></td>

                    <td v-if="columnVisibility.tipo">
                      {{ lr.siembra.tipo }}
                    </td>

                    <td v-if="columnVisibility.fase">
                      <span v-if="lr.siembra.phase_id">
                        {{ lr.siembra.phase.phase }}
                      </span>
                    </td>

                    <td v-if="columnVisibility.lote" v-text="lr.lote"></td>
                    <td v-if="columnVisibility.fecha_registro" v-text="lr.fecha_registro"></td>
                    <td v-if="columnVisibility.especie" v-text="lr.especie"></td>
                    <td v-if="columnVisibility.tipo_actividad" v-text="lr.nombre_registro"></td>

                    <td v-if="columnVisibility.peso_actual" class="text-right">
                      {{ lr.peso_ganado | numeral("0,0.00") }}
                    </td>

                    <td v-if="columnVisibility.biomasa_cosechada" class="text-right">
                      {{ lr.biomasa_general | numeral("0,0.00") }}
                      <!-- {{ lr.biomasa | numeral("0,0.00") }} -->
                    </td>

                    <td v-if="columnVisibility.biomasa_muestreo" class="text-right">
                      {{ lr.biomasa_disponible | numeral("0,0.00") }}
                    </td>

                    <td v-if="columnVisibility.animales_actuales" class="text-right">
                      {{ lr.cantidad_actual | numeral("0,0.00") }}
                    </td>

                    <td v-if="columnVisibility.biomasa_alimento" class="text-right">
                      {{ lr.bio_dispo_alimen | numeral("0,0.00") }}
                    </td>

                    <td v-if="columnVisibility.animales_cosechados" class="text-right">
                      {{ lr.salida_animales_general | numeral("0,0.00") }}
                    </td>
                  </tr>

                  <!-- TOTALES -->
                  <tr v-if="listadoRegistros.data && listadoRegistros.data.length"
                      class="font-weight-bold">
                    <td></td>
                    <td v-if="columnVisibility.siembra"></td>
                    <td v-if="columnVisibility.tipo"></td>
                    <td v-if="columnVisibility.fase"></td>
                    <td v-if="columnVisibility.lote"></td>
                    <td v-if="columnVisibility.fecha_registro"></td>
                    <td v-if="columnVisibility.especie"></td>
                    <td v-if="columnVisibility.tipo_actividad" class="text-right">
                      Totales:
                    </td>

                    <td v-if="columnVisibility.peso_actual" class="text-right">
                      {{ totalsRegistros.peso_ganado | numeral("0,0.00") }}
                    </td>
                    <td v-if="columnVisibility.biomasa_cosechada" class="text-right">
                      {{ totalsRegistros.biomasa_general | numeral("0,0.00") }}
                    </td>
                    <td v-if="columnVisibility.biomasa_muestreo" class="text-right">
                      {{ totalsRegistros.biomasa_disponible | numeral("0,0.00") }}
                    </td>
                    <td v-if="columnVisibility.animales_actuales" class="text-right">
                      {{ totalsRegistros.cantidad_actual | numeral("0,0.00") }}
                    </td>
                    <td v-if="columnVisibility.biomasa_alimento" class="text-right">
                      {{ totalsRegistros.bio_dispo_alimen | numeral("0,0.00") }}
                    </td>
                    <td v-if="columnVisibility.animales_cosechados" class="text-right">
                      {{ totalsRegistros.salida_animales_general | numeral("0,0.00") }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <pagination :align="'center'" :data="listadoRegistros" :limit="8" @pagination-change-page="listarRegistros">
                <span slot="prev-nav">&lt; Anterior</span>
                <span slot="next-nav">Siguiente &gt;</span>
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
        "Tipo": "siembra.tipo",
        "Fase": "siembra.phase.phase",
        Lote: "lote",
        "Fecha de registro": "fecha_registro",
        Especie: "especie",
        "Tipo actividad": "nombre_registro",
        "Peso Actual \n (g)": {
          field: "peso_ganado",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
        "Biomasa cosechada \n Kg": {
          field: "biomasa",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
        "Biomasa muestreo\n (Kg)": {
          field: "biomasa_disponible",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
        "Animales Actuales": {
          field: "cantidad_actual",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
        "Biomasa por alimento": {
          field: "bio_dispo_alimen",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
        "Animales Cosechados": {
          field: "salida_animales",
          callback: (value) => {
            return numeral(value).format("0,0.00");
          },
        },
      },

      listadoSiembras: [],
      listadoRegistros: {},
      listadoEspecies: [],
      listadoLotes: [],
      listadoEstanques: [],
      listPhases: [],
      // filtros
      f_siembra: "",
      f_lote: "",
      f_estado: "1",
      f_especie: "",
      search_activity: "",
      search_from: "",
      search_to: "",
      f_peso_d: 0,
      f_peso_h: 0,
      id_contenedor: "-1",
      search_phase: "",
      search_type: "",
      search_nro_results: "15",

      // columnas
      showColumnPanel: false,
      columnVisibility: {
        siembra: true,
        tipo: true,
        fase: true,
        lote: true,
        fecha_registro: true,
        especie: true,
        tipo_actividad: true,
        peso_actual: true,
        biomasa_cosechada: true,
        biomasa_muestreo: true,
        animales_actuales: true,
        biomasa_alimento: true,
        animales_cosechados: true,
      },
    };
  },
  components: {
    downloadexcel,
  },
  computed: {
    filteredItems() {
      return this.listadoSiembras.filter((item) => item.estado == this.f_estado);
    },

    columnGroup1() {
      return [
        { key: "siembra", label: "Siembra" },
        { key: "tipo", label: "Tipo" },
        { key: "fase", label: "Fase" },
        { key: "lote", label: "Lote" },
        { key: "fecha_registro", label: "Fecha de registro" },
        { key: "especie", label: "Especie" },
        { key: "tipo_actividad", label: "Tipo actividad" },
      ];
    },
    columnGroup2() {
      return [
        { key: "peso_actual", label: "Peso actual (g)" },
        { key: "biomasa_cosechada", label: "Biomasa cosechada Kg" },
        { key: "biomasa_muestreo", label: "Biomasa muestreo (kg)" },
        { key: "animales_actuales", label: "Animales actuales" },
        {
          key: "biomasa_alimento",
          label: "Biomasa disponible por alimento",
        },
        { key: "animales_cosechados", label: "Animales cosechados" },
      ];
    },

    // TOTALES
    totalsRegistros() {
      const rows = (this.listadoRegistros && this.listadoRegistros.data) || [];
      const sum = (field) =>
        rows.reduce((acc, r) => acc + (parseFloat(r[field]) || 0), 0);

      return {
        peso_ganado: sum("peso_ganado"),
        biomasa_general: sum("biomasa_general"),
        biomasa_disponible: sum("biomasa_disponible"),
        cantidad_actual: sum("cantidad_actual"),
        bio_dispo_alimen: sum("bio_dispo_alimen"),
        salida_animales_general: sum("salida_animales_general"),
      };
    },
  },
  methods: {
    toggleColumnPanel() {
      this.showColumnPanel = !this.showColumnPanel;
    },
    toggleColumn(key) {
      this.$set(this.columnVisibility, key, !this.columnVisibility[key]);
    },
    selectAllColumns() {
      Object.keys(this.columnVisibility).forEach((k) => {
        this.columnVisibility[k] = true;
      });
    },
    clearAllColumns() {
      Object.keys(this.columnVisibility).forEach((k) => {
        this.columnVisibility[k] = false;
      });
    },

    async fetchData() {
      return this.listadoRegistros.data || [];
    },

    listarRegistros(page = 1) {
      let me = this;

      if (this.f_siembra == "") {
        this.smb = "-1";
      } else {
        this.smb = this.f_siembra;
      }

      if (this.f_estado == "") {
        this.est = "-1";
      } else {
        this.est = this.f_estado;
      }
      if (this.f_lote == "") {
        this.lot = "-1";
      } else {
        this.lot = this.f_lote;
      }
      if (this.f_especie == "") {
        this.f_e = "-1";
      } else {
        this.f_e = this.f_especie;
      }
      if (this.search_activity == "") {
        this.act = "-1";
      } else {
        this.act = this.search_activity;
      }
      if (this.f_peso_d == "") {
        this.pesod = "-1";
      } else {
        this.pesod = this.f_peso_d;
      }
      if (this.f_peso_h == "") {
        this.pesoh = "-1";
      } else {
        this.pesoh = this.f_peso_h;
      }
      if (this.search_from == "") {
        this.fec1 = "-1";
      } else {
        this.fec1 = this.search_from;
      }
      if (this.search_to == "") {
        this.fec2 = "-1";
      } else {
        this.fec2 = this.search_to;
      }

      const data = {
        f_siembra: this.smb,
        f_estado: this.est,
        f_lote: this.lot,
        f_especie: this.f_e,
        search_activity: this.act,
        f_peso_d: this.pesod,
        f_peso_h: this.pesoh,
        search_from: this.fec1,
        search_to: this.fec2,
        id_contenedor: this.id_contenedor,
        phase: this.search_phase,
        type: this.search_type,
        nro_results: this.search_nro_results,
        page: page
      };

      this.isLoading = true;

      axios
        .get(`api/informes-registros?page=${page}`, { params: data })
        .then(function (response) {
          me.listadoRegistros = response.data;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    listarSiembras() {
      let me = this;
      axios.get("api/siembras/listado").then(function (response) {
        me.listadoSiembras = response.data;
      });
    },
    listarEspecies() {
      let me = this;
      axios.get("api/especies").then(function (response) {
        me.listadoEspecies = response.data;
      });
    },
    listarLotes() {
      let me = this;
      axios.get("api/siembras/listado-lotes").then(function (response) {
        me.listadoLotes = response.data;
      });
    },

    listarEstanques() {
      let me = this;
      axios.get("api/contenedores").then(function (response) {
        me.listadoEstanques = response.data;
      });
    },
    onSearchPhase(search, loading) {
      if (search.length) {
        loading(true);
        let data = {
          phase: search
        };

        axios.get(`api/phases/get`, {
          params: data
        })
          .then((response) => {
            this.listPhases = (response.data.phases.data);
            loading(false)
          })
          .catch(e => {
            console.log(e);
            loading(false);
          })
      }
    },
  },
  mounted() {
    this.listarSiembras();
    this.listarRegistros();
    this.listarEspecies();
    this.listarLotes();
    this.listarEstanques();
  },
};
</script>

<style scoped>
.column-toggle-btn {
  border-radius: 999px;
  background-color: #ffffff;
  font-size: 0.85rem;
}

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
