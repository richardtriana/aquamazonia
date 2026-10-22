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
            Informes recursos y actividades Aquamazonia
          </div>

          <div class="card-body">
            <!-- TÍTULO FILTROS -->
            <div class="row text-left mb-2">
              <h5>Filtrar por:</h5>
            </div>

            <!-- FILTROS PRINCIPALES -->
            <div class="row align-items-end mb-3">
              <!-- Estado siembra -->
              <div class="form-group col-md-2">
                <label for="estado_s">Estado de siembra</label>
                <select
                  id="estado_s"
                  name="estado_s"
                  class="custom-select"
                  v-model="estado_s"
                >
                  <option value="-1">Todos</option>
                  <option value="1">Activas</option>
                  <option value="0">Inactivas</option>
                </select>
              </div>

              <!-- Siembras -->
              <div class="form-group col-md-3">
                <label for="siembra_activa">
                  Siembras
                  {{
                    estado_s === "-1"
                      ? ""
                      : estado_s === "0"
                      ? "inactivas"
                      : "activas"
                  }}
                  :
                </label>

                <template v-if="estado_s !== '-1'">
                  <v-select
                    :options="filteredItems"
                    label="nombre_siembra"
                    :reduce="siembra => siembra.id"
                    v-model="f_siembra"
                    placeholder="Todas las siembras"
                  />
                </template>
                <template v-else>
                  <v-select
                    :options="listadoSiembras"
                    label="nombre_siembra"
                    :reduce="siembra => siembra.id"
                    v-model="f_siembra"
                    placeholder="Todas las siembras"
                  />
                </template>
              </div>

              <!-- Estanque -->
              <div class="form-group col-md-2">
                <label for="contenedor">Estanque</label>
                <select
                  id="contenedor"
                  class="custom-select"
                  v-model="f_contenedor"
                >
                  <option value="-1">Todos</option>
                  <option
                    v-for="(cont, index) in listadoEstanques"
                    :key="index"
                    :value="cont.id"
                  >
                    {{ cont.contenedor }}
                  </option>
                </select>
              </div>

              <!-- Tipo actividad -->
              <div class="form-group col-md-2">
                <label for="actividad">Tipo actividad</label>
                <select
                  id="actividad"
                  class="custom-select"
                  v-model="actividad_s"
                  name="tipo_actividad"
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

              <!-- Alimento -->
              <div class="form-group col-md-2">
                <label for="alimento">Alimento</label>
                <select
                  id="alimento"
                  class="custom-select"
                  v-model="alimento_s"
                >
                  <option value="-1">Todos</option>
                  <option
                    v-for="(alimento, index) in listadoAlimentos"
                    :key="index"
                    :value="alimento.id"
                  >
                    {{ alimento.alimento }}
                  </option>
                </select>
              </div>
            </div>

            <!-- SEGUNDA FILA FILTROS -->
            <div class="row align-items-end mb-3">
              <!-- Recurso -->
              <div class="form-group col-md-2">
                <label for="recurso">Recurso</label>
                <select
                  id="recurso"
                  class="custom-select"
                  v-model="recurso_s"
                >
                  <option value="-1">Todos</option>
                  <option
                    v-for="(recurso, index) in listadoRecursos"
                    :key="index"
                    :value="recurso.id"
                  >
                    {{ recurso.recurso }}
                  </option>
                </select>
              </div>

              <!-- Fecha desde -->
              <div class="form-group col-md-2">
                <label for="fecha_ra1">Desde</label>
                <input
                  id="fecha_ra1"
                  type="date"
                  class="form-control"
                  v-model="fecha_ra1"
                >
              </div>

              <!-- Fecha hasta -->
              <div class="form-group col-md-2">
                <label for="fecha_ra2">Hasta</label>
                <input
                  id="fecha_ra2"
                  type="date"
                  class="form-control"
                  v-model="fecha_ra2"
                >
              </div>

              <!-- Botón filtrar -->
              <div class="form-group col-md-2 d-flex">
                <button
                  type="button"
                  class="btn btn-primary w-100"
                  @click="listar(1)"
                >
                  Filtrar
                </button>
              </div>

              <!-- Botón Excel -->
              <div class="form-group col-md-2">
                <downloadexcel
                  class="btn btn-success w-100"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-recursos.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
              </div>

              <!-- Botón columnas -->
              <div class="form-group col-md-2 position-relative">
                <button
                  type="button"
                  class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn"
                  @click="toggleColumnPanel"
                >
                  <span>
                    <i class="fas fa-columns mr-1"></i>
                    Mostrar / ocultar columnas
                  </span>
                  <i
                    :class="[
                      'fas',
                      showColumnPanel ? 'fa-chevron-up' : 'fa-chevron-down'
                    ]"
                  ></i>
                </button>

                <!-- PANEL DESPLEGABLE COLUMNAS -->
                <div
                  v-if="showColumnPanel"
                  class="column-panel-dropdown shadow-lg"
                >
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <div class="small text-muted">
                        Marca / desmarca las columnas que deseas ver
                      </div>
                    </div>
                    <div class="ml-2">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-success mr-1"
                        @click="selectAllColumns"
                      >
                        Todas
                      </button>
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        @click="clearAllColumns"
                      >
                        Ninguna
                      </button>
                    </div>
                  </div>

                  <hr class="my-2">

                  <div class="column-panel-list">
                    <div
                      v-for="item in columnList"
                      :key="item.key"
                      class="column-panel-item"
                    >
                      <label
                        class="column-check-label"
                        :for="'col-' + item.key"
                      >
                        <input
                          :id="'col-' + item.key"
                          type="checkbox"
                          v-model="columnVisibility[item.key]"
                        >
                        <span class="column-check-box"></span>
                        <span class="column-check-text">{{ item.label }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLA CON SCROLL HORIZONTAL -->
            <div class="table-container table-container--wide">
              <table class="table table-bordered table-hover table-sticky table-sm">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>

                    <th v-if="columnVisibility.siembra">Siembra</th>
                    <th v-if="columnVisibility.estado_siembra">Estado siembras</th>
                    <th v-if="columnVisibility.tipo_actividad">Tipo actividad</th>
                    <th v-if="columnVisibility.fecha">Fecha</th>
                    <th v-if="columnVisibility.minutos_hombre">
                      Minutos <br> hombre
                    </th>
                    <th v-if="columnVisibility.costo_minutos">
                      Costo minutos
                    </th>

                    <!-- Recursos (cuando no es alimentación) -->
                    <template v-if="tipoActividad !== 'Alimentación'">
                      <th v-if="columnVisibility.recurso">Recursos</th>
                      <th v-if="columnVisibility.cantidad_recurso">Cantidad</th>
                      <th v-if="columnVisibility.costo_recurso">Costo Recurso</th>
                    </template>

                    <!-- Alimentación -->
                    <template v-else>
                      <th v-if="columnVisibility.alimento">Alimentos</th>
                      <th v-if="columnVisibility.cant_manana">
                        Cantidad Mañana (KG)
                      </th>
                      <th v-if="columnVisibility.cant_tarde">
                        Cantidad Tarde (KG)
                      </th>
                      <th v-if="columnVisibility.costo_alimento">
                        Costo Alimento
                      </th>
                    </template>

                    <th v-if="columnVisibility.costo_actividad">
                      Costo actividad
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(lrn, index) in listadorn" :key="index">
                    <th>{{ index + 1 }}</th>

                    <td v-if="columnVisibility.siembra">
                      {{ lrn.nombre_siembra }}
                    </td>

                    <td v-if="columnVisibility.estado_siembra">
                      {{ estados[lrn.estado] }}
                    </td>

                    <td v-if="columnVisibility.tipo_actividad">
                      {{ lrn.actividad }}
                    </td>

                    <td v-if="columnVisibility.fecha">
                      {{ lrn.fecha_ra }}
                    </td>

                    <td v-if="columnVisibility.minutos_hombre">
                      {{ lrn.minutos_hombre + "min" }}
                    </td>

                    <td v-if="columnVisibility.costo_minutos">
                      {{ lrn.costo_minutosh | numeral("$0,0.00") }}
                    </td>

                    <!-- Recursos -->
                    <template v-if="tipoActividad !== 'Alimentación'">
                      <td v-if="columnVisibility.recurso">
                        {{ lrn.recurso }}
                      </td>
                      <td v-if="columnVisibility.cantidad_recurso">
                        {{ lrn.cantidad_recurso }}
                      </td>
                      <td v-if="columnVisibility.costo_recurso">
                        {{ lrn.costo_total_recurso | numeral("$0,0.00") }}
                      </td>
                    </template>

                    <!-- Alimentación -->
                    <template v-else>
                      <td v-if="columnVisibility.alimento">
                        {{ lrn.alimento }}
                      </td>
                      <td v-if="columnVisibility.cant_manana">
                        {{ lrn.cant_manana }}
                      </td>
                      <td v-if="columnVisibility.cant_tarde">
                        {{ lrn.cant_tarde }}
                      </td>
                      <td v-if="columnVisibility.costo_alimento">
                        {{ lrn.costo_total_alimento | numeral("$0,0.00") }}
                      </td>
                    </template>

                    <td v-if="columnVisibility.costo_actividad">
                      {{ lrn.costo_total_actividad | numeral("$0,0.00") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- PAGINACIÓN -->
            <nav v-show="showPagination" class="mt-4 navigation">
              <ul class="pagination justify-content-center">
                <li
                  class="page-item"
                  v-if="pagination.current_page > 1"
                >
                  <a
                    href="#"
                    class="page-link"
                    @click.prevent="cambiarPagina(pagination.current_page - 1)"
                  >Ant</a>
                </li>

                <li
                  v-for="page in pagesNumber"
                  :key="page"
                  class="page-item"
                  :class="{ active: page === isActived }"
                >
                  <a
                    href="#"
                    class="page-link"
                    @click.prevent="cambiarPagina(page)"
                  >
                    {{ page }}
                  </a>
                </li>

                <li
                  class="page-item"
                  v-if="pagination.current_page < pagination.last_page"
                >
                  <a
                    href="#"
                    class="page-link"
                    @click.prevent="cambiarPagina(pagination.current_page + 1)"
                  >Sig</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import downloadexcel from "vue-json-excel";

export default {
  components: {
    downloadexcel
  },
  data() {
    return {
      // SPINNER
      isLoading: false,

      json_fields: {
        "Nombre Siembra": "nombre_siembra",
        "Estado Siembra": "estado",
        "Tipo de Actividad": "actividad",
        "Fecha Registro": "fecha_ra",
        "Minutos hombre": {
          field: "minutos_hombre",
          callback: value => numeral(value).format("0.00")
        },
        "Costo minutos hombre": {
          field: "costo_minutosh",
          callback: value => numeral(value).format("0.00")
        },
        Recurso: "recurso",
        "Cantidad Recurso": {
          field: "cantidad_recurso",
          callback: value => numeral(value).format("0.00")
        },
        "Costo Recurso": {
          field: "costo_total_recurso",
          callback: value => numeral(value).format("0.00")
        },
        Alimento: "alimento",
        "Cantidad KG mañana": {
          field: "cant_manana",
          callback: value => numeral(value).format("0.00")
        },
        "Cantidad KG tarde": {
          field: "cant_tarde",
          callback: value => numeral(value).format("0.00")
        },
        "Costo Alimento": {
          field: "costo_total_alimento",
          callback: value => numeral(value).format("0.00")
        },
        "Costo Actividad": {
          field: "costo_total_actividad",
          callback: value => numeral(value).format("0.00")
        }
      },

      listadorn: [],
      listadoActividades: [],
      listadoAlimentos: [],
      listadoSiembras: [],
      listadoRecursos: [],
      listadoEstanques: [],
      imprimirRecursos: [],
      estados: [],

      // FILTROS
      f_siembra: "",
      f_contenedor: "-1",
      estado_s: "1",
      actividad_s: "-1",
      alimento_s: "-1",
      recurso_s: "-1",
      fecha_ra1: "",
      fecha_ra2: "",
      tipoActividad: "",

      // Paginación
      offset: 10,
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0
      },
      showPagination: 1,

      // Panel columnas
      showColumnPanel: false,
      columnVisibility: {
        siembra: true,
        estado_siembra: true,
        tipo_actividad: true,
        fecha: true,
        minutos_hombre: true,
        costo_minutos: true,
        recurso: true,
        cantidad_recurso: true,
        costo_recurso: true,
        alimento: true,
        cant_manana: true,
        cant_tarde: true,
        costo_alimento: true,
        costo_actividad: true
      }
    };
  },
  computed: {
    isActived() {
      return this.pagination.current_page;
    },
    pagesNumber() {
      if (!this.pagination.to) {
        return [];
      }

      let from = this.pagination.current_page - this.offset;
      if (from < 1) {
        from = 1;
      }

      let to = from + this.offset * 2;
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page;
      }

      const pagesArray = [];
      while (from <= to) {
        pagesArray.push(from);
        from++;
      }
      return pagesArray;
    },
    filteredItems() {
      return this.listadoSiembras.filter(
        item => String(item.estado) === String(this.estado_s)
      );
    },
    columnList() {
      return [
        { key: "siembra", label: "Siembra" },
        { key: "estado_siembra", label: "Estado siembras" },
        { key: "tipo_actividad", label: "Tipo actividad" },
        { key: "fecha", label: "Fecha" },
        { key: "minutos_hombre", label: "Minutos hombre" },
        { key: "costo_minutos", label: "Costo minutos" },
        { key: "recurso", label: "Recurso" },
        { key: "cantidad_recurso", label: "Cantidad recurso" },
        { key: "costo_recurso", label: "Costo recurso" },
        { key: "alimento", label: "Alimento" },
        { key: "cant_manana", label: "Cantidad mañana (kg)" },
        { key: "cant_tarde", label: "Cantidad tarde (kg)" },
        { key: "costo_alimento", label: "Costo alimento" },
        { key: "costo_actividad", label: "Costo actividad" }
      ];
    }
  },
  methods: {
    toggleColumnPanel() {
      this.showColumnPanel = !this.showColumnPanel;
    },
    selectAllColumns() {
      Object.keys(this.columnVisibility).forEach(key => {
        this.columnVisibility[key] = true;
      });
    },
    clearAllColumns() {
      Object.keys(this.columnVisibility).forEach(key => {
        this.columnVisibility[key] = false;
      });
    },
    async fetchData() {
      return this.listadorn;
    },
    cambiarActividad() {
      if (String(this.actividad_s) === "1") {
        this.tipoActividad = "Alimentación";
      } else {
        this.tipoActividad = "";
      }
    },
    listar(page = 1) {
      this.isLoading = true;

      const params = {
        f_siembra: this.f_siembra === "" ? "-1" : this.f_siembra,
        estado_s: this.estado_s === "" ? "-1" : this.estado_s,
        f_contenedor: this.f_contenedor === "-1" ? "-1" : this.f_contenedor,
        actividad_s: this.actividad_s === "-1" ? "-1" : this.actividad_s,
        alimento_s: this.alimento_s === "-1" ? "-1" : this.alimento_s,
        recurso_s: this.recurso_s === "-1" ? "-1" : this.recurso_s,
        fecha_ra1: this.fecha_ra1 === "" ? "-1" : this.fecha_ra1,
        fecha_ra2: this.fecha_ra2 === "" ? "-1" : this.fecha_ra2,
        page
      };

      axios
        .get("api/informes", { params })
        .then(response => {
          this.listadorn = response.data.recursosNecesarios.data;
          this.pagination = response.data.pagination;
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
    cambiarPagina(page) {
      this.pagination.current_page = page;
      this.listar(page);
    },
    listarActividades() {
      axios.get("api/actividades").then(response => {
        this.listadoActividades = response.data;
      });
    },
    listarAlimentos() {
      axios.get("api/alimentos").then(response => {
        this.listadoAlimentos = response.data;
      });
    },
    listarRecursos() {
      axios.get("api/recursos").then(response => {
        this.listadoRecursos = response.data;
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
    this.listar(1);
    this.listarSiembras();
    this.listarAlimentos();
    this.listarRecursos();
    this.listarActividades();
    this.listarEstanques();
    this.estados[0] = "Inactivo";
    this.estados[1] = "Activo";
  }
};
</script>

<style scoped>
.table-container--wide {
  width: 100%;
  overflow-x: auto;
}

/* Botón de columnas */
.column-toggle-btn {
  border-radius: 999px;
  background-color: #ffffff;
  font-size: 0.85rem;
}

/* Dropdown columnas */
.column-panel-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.35rem;
  z-index: 30;
  background: #ffffff;
  border-radius: 14px;
  padding: 0.75rem 1rem;
  min-width: 280px;
  max-width: 360px;
  max-height: 280px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.column-panel-list {
  max-height: 220px;
  overflow-y: auto;
  padding-right: 4px;
}

.column-panel-item + .column-panel-item {
  margin-top: 0.25rem;
}

/* Checkbox personalizado (igual que en el otro informe) */
.column-check-label {
  display: flex;
  align-items: center;
  font-size: 0.85rem;
  cursor: pointer;
  user-select: none;
}

.column-check-label input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.column-check-box {
  width: 14px;
  height: 14px;
  border-radius: 4px;
  border: 1px solid #9ca3af;
  margin-right: 8px;
  box-sizing: border-box;
}

.column-check-label input[type="checkbox"]:checked + .column-check-box {
  background-color: #198754;
  border-color: #198754;
}

.column-check-text {
  line-height: 1.1;
}
</style>
