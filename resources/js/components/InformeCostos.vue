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
            INFORME DE COSTOS
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

              <!-- Siembras -->
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
                    :reduce="(siembra) => siembra.id"
                    v-model="f_siembra"
                    placeholder="Todas las siembras"
                    class="filtro-vselect"
                  />
                </template>
                <template v-else>
                  <v-select
                    :options="listadoSiembras"
                    label="nombre_siembra"
                    :reduce="(siembra) => siembra.id"
                    v-model="f_siembra"
                    placeholder="Todas las siembras"
                    class="filtro-vselect"
                  />
                </template>
              </div>

              <!-- Fecha inicio desde -->
              <div class="form-group col-md-3 col-lg-2">
                <label for="f_inicio_d">Fecha inicio desde</label>
                <input
                  type="date"
                  class="form-control filtro-control"
                  id="f_inicio_d"
                  v-model="f_inicio_d"
                >
              </div>

              <!-- Fecha inicio hasta -->
              <div class="form-group col-md-3 col-lg-2">
                <label for="f_inicio_h">Fecha inicio hasta</label>
                <input
                  type="date"
                  class="form-control filtro-control"
                  id="f_inicio_h"
                  v-model="f_inicio_h"
                >
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
              <div class="form-group col-md-2 col-lg-1 text-right">
                <button
                  class="btn btn-primary btn-block filtro-btn-filtrar"
                  @click="listar()"
                >
                  Filtrar
                </button>
              </div>

              <!-- Botón Excel -->
              <div class="form-group col-md-3 col-lg-2">
                <downloadexcel
                  class="btn btn-success w-100"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-costos.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
              </div>
            </div>

            <!-- SEGUNDA FILA: SELECT DE COLUMNAS -->
            <div class="row mb-3 justify-content-end">
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
                    <div class="col-12">
                      <div
                        class="column-item"
                        v-for="item in columnList"
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
                    <th class="fixed-column">Siembra</th>
                    <th v-if="columnVisibility.costo_horas">Costo Horas</th>
                    <th v-if="columnVisibility.costo_recursos">Costo Recursos</th>
                    <th v-if="columnVisibility.costo_alimentos">Costo Alimentos</th>
                    <th v-if="columnVisibility.costo_total_siembra">Costo total de siembra</th>
                    <th v-if="columnVisibility.costo_produccion_parcial">Costo total de producción parcial</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    class="text-right"
                    v-for="(le, index) in listadoExistencias"
                    :key="index"
                  >
                    <td v-text="index + 1"></td>
                    <td class="text-right fixed-column">
                      {{ le.nombre_siembra }}
                    </td>
                    <td v-if="columnVisibility.costo_horas">
                      {{ le.costo_minutos_hombre | numeral('$0,0.00') }}
                    </td>
                    <td v-if="columnVisibility.costo_recursos">
                      {{ le.costo_total_recurso | numeral('$0,0.00') }}
                    </td>
                    <td v-if="columnVisibility.costo_alimentos">
                      {{ le.costo_total_alimento | numeral('$0,0.00') }}
                    </td>
                    <td v-if="columnVisibility.costo_total_siembra">
                      {{ le.costo_total_siembra | numeral('$0,0.00') }}
                    </td>
                    <td v-if="columnVisibility.costo_produccion_parcial">
                      {{ le.costo_produccion_parcial | numeral('$0,0.00') }}
                    </td>
                  </tr>
                  <tr class="font-weight-bold text-right">
                    <td></td>
                    <td></td>
                    <td v-if="columnVisibility.costo_horas">
                      <strong>
                        {{ totalizadoSiembras.costo_minutos_hombre | numeral('$0,0.00') }}
                      </strong>
                    </td>
                    <td v-if="columnVisibility.costo_recursos">
                      <strong>
                        {{ totalizadoSiembras.costo_total_recurso | numeral('$0,0.00') }}
                      </strong>
                    </td>
                    <td v-if="columnVisibility.costo_alimentos">
                      <strong>
                        {{ totalizadoSiembras.costo_total_alimento | numeral('$0,0.00') }}
                      </strong>
                    </td>
                    <td v-if="columnVisibility.costo_total_siembra">
                      <strong>
                        {{ totalizadoSiembras.costo_total_siembra | numeral('$0,0.00') }}
                      </strong>
                    </td>
                    <td v-if="columnVisibility.costo_produccion_parcial">
                      <strong>
                        {{ totalizadoSiembras.costo_produccion_parcial | numeral('$0,0.00') }}
                      </strong>
                    </td>
                  </tr>
                </tbody>
              </table>
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
        'Siembra': 'nombre_siembra',
        'Costo Horas': {
          field: 'costo_minutos_hombre',
          callback: (value) => {
            return numeral(value).format('0.00');
          }
        },
        'Costo recursos': {
          field: 'costo_total_recurso',
          callback: (value) => {
            return numeral(value).format('0.00');
          }
        },
        'Costo alimentos': {
          field: 'costo_total_alimento',
          callback: (value) => {
            return numeral(value).format('0.00');
          }
        },
        'Costo total siembra': {
          field: 'costo_total_siembra',
          callback: (value) => {
            return numeral(value).format('0.00');
          }
        },
        'Costo total de producción parcial': {
          field: 'costo_produccion_parcial',
          callback: (value) => {
            return numeral(value).format('0.00');
          }
        },
      },
      listadoExistencias: [],
      totalizadoSiembras: [],
      listadoEspecies: [],
      listadoSiembras: [],
      imprimirRecursos: [],

      // Filtros
      f_siembra: '',
      f_inicio_d: '',
      f_inicio_h: '',
      f_estado: "1",
      search_nro_results: "15",

      // Panel columnas
      showColumnPanel: false,
      columnVisibility: {
        costo_horas: true,
        costo_recursos: true,
        costo_alimentos: true,
        costo_total_siembra: true,
        costo_produccion_parcial: true
      }
    }
  },
  components: {
    downloadexcel,
  },
  computed: {
    filteredItems() {
      return this.listadoSiembras.filter(
        (item) => String(item.estado) === String(this.f_estado)
      );
    },
    columnList() {
      return [
        { key: 'costo_horas', label: 'Costo Horas' },
        { key: 'costo_recursos', label: 'Costo Recursos' },
        { key: 'costo_alimentos', label: 'Costo Alimentos' },
        { key: 'costo_total_siembra', label: 'Costo total de siembra' },
        { key: 'costo_produccion_parcial', label: 'Costo total de producción parcial' },
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
      return this.listadoExistencias;
    },
    listar() {
      this.isLoading = true;

      const data = {
        f_siembra: this.f_siembra === '' ? '-1' : this.f_siembra,
        f_estado: this.f_estado === '-1' ? '-1' : this.f_estado,
        f_inicio_d: this.f_inicio_d === '' ? '-1' : this.f_inicio_d,
        f_inicio_h: this.f_inicio_h === '' ? '-1' : this.f_inicio_h,
        nro_results: this.search_nro_results === '' ? 15 : this.search_nro_results,
      };

      axios
        .get("api/informes-biomasa-alimento", { params: data })
        .then((response) => {
          this.listadoExistencias = response.data.existencias;
          this.totalizadoSiembras = response.data.totalizadoSiembras;
        })
        .catch((error) => {
          console.error(error);
          if (typeof Swal !== "undefined") {
            Swal.fire(
              "Error",
              "Ocurrió un problema cargando el informe de costos",
              "error"
            );
          }
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    listarEspecies() {
      axios.get("api/especies")
        .then((response) => {
          this.listadoEspecies = response.data;
        });
    },
    listarSiembras() {
      axios.get("api/siembras/listado")
        .then((response) => {
          this.listadoSiembras = response.data;
        });
    }
  },
  mounted() {
    this.listarEspecies();
    this.listarSiembras();
    this.listar();
  }
}
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
  min-width: 260px;
  max-width: 360px;
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
  overflow-x: auto;
  width: 100%;
}
</style>
