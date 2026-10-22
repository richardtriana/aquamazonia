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
            Informes recursos necesarios
          </div>

          <div class="card-body">
            <!-- TÍTULO FILTROS -->
            <div class="row text-left mb-2">
              <h5>Filtrar por:</h5>
            </div>

            <!-- BARRA DE FILTROS ORDENADA -->
            <div class="row align-items-end mb-3 filtro-bar">
              <!-- Estado de siembra -->
              <div class="form-group col-md-3">
                <label for="estado">Estado de siembra</label>
                <select
                  class="form-control filtro-control"
                  name="estado"
                  id="estado"
                  v-model="f_estado"
                >
                  <option value="-1">Todas</option>
                  <option value="1">Activas</option>
                  <option value="0">Inactivas</option>
                </select>
              </div>

              <!-- Siembras -->
              <div class="form-group col-md-4">
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

              <!-- Estanque -->
              <div class="form-group col-md-2">
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

              <!-- Tipo de actividad -->
              <div class="form-group col-md-2">
                <label for="search_activity">Tipo de actividad</label>
                <select
                  class="form-control filtro-control"
                  id="search_activity"
                  v-model="search_activity"
                  @change="cambiarActividad"
                >
                  <option value="-1">Todas</option>
                  <option
                    v-for="(actividad, index) in listadoActividades"
                    :key="index"
                    :value="actividad.id"
                  >
                    {{ actividad.actividad }}
                  </option>
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
                  class="form-control filtro-control"
                  v-model="search_nro_results"
                >
              </div>

              <!-- Botón filtrar -->
              <div class="form-group col-md-1 d-flex">
                <button
                  class="btn btn-primary btn-block filtro-btn-filtrar"
                  type="button"
                  @click="listar()"
                >
                  Filtrar
                </button>
              </div>
            </div>

            <!-- SEGUNDA FILA: EXCEL + SELECT DE COLUMNAS -->
            <div class="row mb-3 justify-content-end">
              <!-- Botón Excel -->
              <div class="form-group col-md-3 col-lg-2 mb-2 mb-md-0">
                <downloadexcel
                  class="btn btn-success w-100"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-consolidado-recursos-necesarios.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
              </div>

              <!-- Botón / dropdown columnas -->
              <div class="form-group col-md-4 col-lg-3 position-relative">
                <label class="d-block d-md-none">&nbsp;</label>
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

                <!-- PANEL DESPLEGABLE DE COLUMNAS (MISMO DISEÑO QUE INFORME CONSOLIDADO) -->
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
            <div class="table-container table-container--wide" id="table-container2">
              <table class="table-sticky table table-sm table-hover table-bordered">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th v-if="columnVisibility.siembra">Siembra</th>
                    <th v-if="columnVisibility.estado">Estado</th>
                    <th v-if="columnVisibility.tipo_actividad">Tipo actividad</th>
                    <th v-if="columnVisibility.minutos_hombre">
                      Minutos hombre
                    </th>
                    <th v-if="columnVisibility.costo_minutos">
                      Costo minutos hombre
                    </th>
                    <th
                      v-if="columnVisibility.cantidad_recurso && tipoActividad !== 'Alimentación'"
                    >
                      Cantidad recurso
                    </th>
                    <th
                      v-if="columnVisibility.costo_recurso && tipoActividad !== 'Alimentación'"
                    >
                      Costo recurso
                    </th>
                    <th
                      v-if="columnVisibility.cantidad_alimento && tipoActividad === 'Alimentación'"
                    >
                      Cantidad alimento
                    </th>
                    <th
                      v-if="columnVisibility.costo_alimento && tipoActividad === 'Alimentación'"
                    >
                      Costo alimento
                    </th>
                    <th v-if="columnVisibility.costo_total_actividad">
                      Costo total actividad
                    </th>
                    <th v-if="columnVisibility.porcentaje_total_produccion">
                      % Costo total de producción
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(lrn, index) in listado.data" :key="index">
                    <td>
                      {{ listado.from ? listado.from + index : index + 1 }}
                    </td>

                    <td v-if="columnVisibility.siembra">
                      {{ lrn.nombre_siembra }}
                    </td>

                    <td v-if="columnVisibility.estado">
                      {{ lrn.estado == 1 ? "Activa" : "Inactiva" }}
                    </td>

                    <td v-if="columnVisibility.tipo_actividad">
                      {{ lrn.actividad }}
                    </td>

                    <td v-if="columnVisibility.minutos_hombre">
                      {{ lrn.minutos_hombre | numeral("0,0.00") }} min
                    </td>

                    <td v-if="columnVisibility.costo_minutos" class="text-right">
                      {{ lrn.costo_minutos | numeral("$0,0.00") }}
                    </td>

                    <td
                      v-if="columnVisibility.cantidad_recurso && tipoActividad !== 'Alimentación'"
                      class="text-right"
                    >
                      {{ lrn.cantidad_recurso | numeral("0,0.00") }}
                    </td>

                    <td
                      v-if="columnVisibility.costo_recurso && tipoActividad !== 'Alimentación'"
                      class="text-right"
                    >
                      {{ lrn.costo_recurso | numeral("$0,0.00") }}
                    </td>

                    <td
                      v-if="columnVisibility.cantidad_alimento && tipoActividad === 'Alimentación'"
                      class="text-right"
                    >
                      {{ lrn.cantidad_alimento | numeral("0,0.00") }}
                    </td>

                    <td
                      v-if="columnVisibility.costo_alimento && tipoActividad === 'Alimentación'"
                      class="text-right"
                    >
                      {{ lrn.costo_alimento | numeral("$0,0.00") }}
                    </td >

                    <td
                      v-if="columnVisibility.costo_total_actividad"
                      class="text-right"
                    >
                      {{ lrn.costo_total_actividad | numeral("$0,0.00") }}
                    </td>

                    <td
                      v-if="columnVisibility.porcentaje_total_produccion"
                      class="text-right"
                    >
                      <span v-if="lrn.porcentaje_total_produccion">
                        {{
                          lrn.porcentaje_total_produccion | numeral("0,0.00")
                        }} %
                      </span>
                    </td>
                  </tr>

                  <!-- FILA DE TOTALES -->
                  <tr class="font-weight-bold text-right">
                    <td></td>
                    <td v-if="columnVisibility.siembra"></td>
                    <td v-if="columnVisibility.estado"></td>
                    <td v-if="columnVisibility.tipo_actividad"></td>

                    <td v-if="columnVisibility.minutos_hombre">
                      <strong>
                        {{ totalizadoRecursos.minutos_hombre | numeral("0,0.00") }} min
                      </strong>
                    </td>

                    <td v-if="columnVisibility.costo_minutos">
                      <strong>
                        {{ totalizadoRecursos.costo_minutos | numeral("$0,0.00") }}
                      </strong>
                    </td>

                    <td
                      v-if="columnVisibility.cantidad_recurso && tipoActividad !== 'Alimentación'"
                    >
                      <strong>
                        {{ totalizadoRecursos.cantidad_recurso | numeral("0,0.00") }}
                      </strong>
                    </td>

                    <td
                      v-if="columnVisibility.costo_recurso && tipoActividad !== 'Alimentación'"
                    >
                      <strong>
                        {{ totalizadoRecursos.costo_recurso | numeral("$0,0.00") }}
                      </strong>
                    </td>

                    <td
                      v-if="columnVisibility.cantidad_alimento && tipoActividad === 'Alimentación'"
                    >
                      <strong>
                        {{ totalizadoRecursos.cantidad_alimento | numeral("0,0.00") }}
                      </strong>
                    </td>

                    <td
                      v-if="columnVisibility.costo_alimento && tipoActividad === 'Alimentación'"
                    >
                      <strong>
                        {{ totalizadoRecursos.costo_alimento | numeral("$0,0.00") }}
                      </strong>
                    </td>

                    <td v-if="columnVisibility.costo_total_actividad">
                      <strong>
                        {{ totalizadoRecursos.costo_total_actividad | numeral("$0,0.00") }}
                      </strong>
                    </td>

                    <td v-if="columnVisibility.porcentaje_total_produccion">
                      <strong v-if="totalizadoRecursos.porcentaje_total_produccion">
                        {{ totalizadoRecursos.porcentaje_total_produccion | numeral("0,0.00") }} %
                      </strong>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- PAGINACIÓN -->
              <pagination
                :align="'center'"
                :data="listado"
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
          </div> <!-- card-body -->
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
        Estado: "estado",
        "Tipo actividad": "actividad",
        "Minutos hombre": {
          field: "minutos_hombre",
          callback: value => numeral(value).format("0.00")
        },
        "Costo total minutos": {
          field: "costo_minutos",
          callback: value => numeral(value).format("0.00")
        },
        "Cantidad total recurso": {
          field: "cantidad_recurso",
          callback: value => numeral(value).format("0.00")
        },
        "Costo total recurso": {
          field: "costo_recurso",
          callback: value => numeral(value).format("0.00")
        },
        "Cantidad total alimento": {
          field: "cantidad_alimento",
          callback: value => numeral(value).format("0.00")
        },
        "Costo total alimento": {
          field: "costo_alimento",
          callback: value => numeral(value).format("0.00")
        },
        "Costo total actividad": {
          field: "costo_total_actividad",
          callback: value => numeral(value).format("0.00")
        },
        "% Costo total producción": {
          field: "porcentaje_total_produccion",
          callback: value => numeral(value).format("0.00")
        }
      },

      tipoActividad: "",
      search_activity: "-1",
      f_siembra: "",
      f_contenedor: "-1",
      f_estado: "1",
      search_nro_results: "15",

      listado: { data: [] },
      totalizadoRecursos: {},

      listadoSiembras: [],
      listadoActividades: [],
      listadoEstanques: [],

      // Columnas
      showColumnPanel: false,
      columnVisibility: {
        siembra: true,
        estado: true,
        tipo_actividad: true,
        minutos_hombre: true,
        costo_minutos: true,
        cantidad_recurso: true,
        costo_recurso: true,
        cantidad_alimento: true,
        costo_alimento: true,
        costo_total_actividad: true,
        porcentaje_total_produccion: true
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
        { key: "siembra", label: "Siembra" },
        { key: "estado", label: "Estado" },
        { key: "tipo_actividad", label: "Tipo actividad" }
      ];
    },
    columnGroup2() {
      return [
        { key: "minutos_hombre", label: "Minutos hombre" },
        { key: "costo_minutos", label: "Costo minutos hombre" },
        { key: "costo_total_actividad", label: "Costo total actividad" }
      ];
    },
    columnGroup3() {
      return [
        { key: "cantidad_recurso", label: "Cantidad recurso" },
        { key: "costo_recurso", label: "Costo recurso" },
        { key: "cantidad_alimento", label: "Cantidad alimento" },
        { key: "costo_alimento", label: "Costo alimento" },
        {
          key: "porcentaje_total_produccion",
          label: "% Costo total producción"
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
      return this.listado.data || [];
    },
    cambiarActividad() {
      if (this.search_activity == 1) {
        this.tipoActividad = "Alimentación";
      } else {
        this.tipoActividad = "";
      }
    },
    listar(page = 1) {
      this.isLoading = true;

      const f_s = this.f_siembra === "" ? "-1" : this.f_siembra;
      const f_e = this.f_estado === "" ? "-1" : this.f_estado;
      const cont = this.f_contenedor === "" ? "-1" : this.f_contenedor;
      const actividad =
        this.search_activity === "" || this.search_activity === "-1"
          ? "-1"
          : this.search_activity;

      const data = {
        f_siembra: f_s,
        f_estado: f_e,
        search_activity: actividad,
        f_contenedor: cont,
        nro_results: this.search_nro_results === "" ? 15 : this.search_nro_results,
        page
      };

      axios
        .get("api/informes-recursos-necesarios", { params: data })
        .then(response => {
          this.listado = response.data.recursosNecesarios;
          this.totalizadoRecursos = response.data.totalizadoRecursos || {};
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
    listarSiembras() {
      axios.get("api/siembras/listado").then(response => {
        this.listadoSiembras = response.data;
      });
    },
    listarActividades() {
      axios.get("api/actividades").then(response => {
        this.listadoActividades = response.data;
      });
    },
    listarEstanques() {
      axios.get("api/contenedores").then(response => {
        this.listadoEstanques = response.data;
      });
    }
  },
  mounted() {
    this.listar();
    this.listarSiembras();
    this.listarActividades();
    this.listarEstanques();
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

/* Dropdown de columnas (mismo estilo que InformeConsolidado) */
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

/* Scroll horizontal si la tabla es muy ancha */
.table-container--wide {
  position: relative;
  overflow-x: auto;
  width: 100%;
}
</style>
