<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Informes biomasa por alimento</div>
          <div class="card-body">
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

            <div class="row text-left">
              <h5>Filtrar por: </h5>
            </div>

            <div class="row">
              <div class="form-group col-md-2">
                <label for="f_estado">
                  Estado:
                  <select class="custom-select" name="estado" id="estado" v-model="f_estado">
                    <option value="-1">--Seleccionar--</option>
                    <option value="0">Inactiva</option>
                    <option value="1">Activa</option>
                  </select>
                </label>
              </div>

              <div class="form-group col-md-2">
                <label for="f_especie">Especie:</label>
                <select class="custom-select" v-model="f_especie">
                  <option value="-1">Todas las especies</option>
                  <option v-for="esp in listadoEspecies" :key="esp.id" :value="esp.id">
                    {{ esp.especie }}
                  </option>
                </select>
              </div>

              <div class="form-group col-md-3">
                <label for="siembra_activa">Siembras
                  {{ f_estado == 0 ? "inactivas" : "activas" }} :
                </label>
                <template v-if="f_estado != '-1'">
                  <v-select
                    :options="filteredItems"
                    label="nombre_siembra"
                    :reduce="(siembra) => siembra.id"
                    v-model="f_siembra"
                  />
                </template>
                <template v-if="f_estado == '-1'">
                  <v-select
                    :options="allSiembrasOptions"
                    label="nombre_siembra"
                    :reduce="(siembra) => siembra.id"
                    v-model="f_siembra"
                  />
                </template>
              </div>

              <div class="form-group col-md-2">
                <label for="Fecha desde">Fecha inicio desde: </label>
                <input type="date" class="form-control" id="f_inicio_d" v-model="f_inicio_d">
              </div>

              <div class="form-group col-md-2">
                <label for="fecha hasta">Fecha inicio hasta: </label>
                <input type="date" class="form-control" id="f_inicio_h" v-model="f_inicio_h">
              </div>

              <div class="form-group col-md-2">
                <label for="search_nro_results">
                  Mostrar {{ search_nro_results }} por página
                </label>
                <input
                  type="number"
                  min="1"
                  class="form-control"
                  v-model="search_nro_results"
                >
              </div>

              <div class="form-group col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100" @click="listar()">
                  Filtrar
                </button>
              </div>

              <div class="form-group col-md-2">
                <label>Generar Excel</label>
                <downloadexcel
                  class="btn btn-success form-control"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-biomasa-alimento.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Exportar Excel
                </downloadexcel>
              </div>

              <div class="form-group col-md-3 position-relative mt-2">
                <button
                  type="button"
                  class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn"
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

            </div>

            <div class="table-container" id="table-container2">
              <table class="table-sticky table table-sm table-hover table-bordered">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th v-if="columnVisibility.siembra" class="fixed-column">Siembra</th>
                    <th v-if="columnVisibility.estanque">Estanque</th>
                    <th v-if="columnVisibility.especies">Especies</th>
                    <th v-if="columnVisibility.area">Area (m²)<sup>3</sup> </th>
                    <th v-if="columnVisibility.cant_inicial">Cant Inicial (Animales)</th>
                    <th v-if="columnVisibility.peso_actual">Peso actual (g)</th>
                    <th v-if="columnVisibility.biomasa_inicial">Biomasa Inicial (Kg)</th>
                    <th v-if="columnVisibility.cant_actual">Cantidad actual (Animales)</th>
                    <th v-if="columnVisibility.biomasa_disponible">Biomasa disponible muestreo (Kg)</th>
                    <th v-if="columnVisibility.biomasa_cosechada">Biomasa cosechada (Kg)</th>
                    <th v-if="columnVisibility.mortalidad_animales">
                      Mortalidad <br> (Animales)
                    </th>
                    <th v-if="columnVisibility.mortalidad_kg">Mort. Kg</th>
                    <th v-if="columnVisibility.total_alimento">Total alimento (Kg)</th>
                    <th v-if="columnVisibility.incremento_biomasa">
                      Incremento de biomasa por alimento (Kg)
                    </th>
                    <th v-if="columnVisibility.conversion_alimenticia">Conversión Alimenticia</th>
                    <th v-if="columnVisibility.biomasa_alimento">
                      Biomasa disponible por alimento (Kg)
                    </th>
                    <th v-if="columnVisibility.supervivencia_final">% Supervivencia final</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(le, index) in paginatedExistencias" :key="index">
                    <td>
                      {{ ((currentPage - 1) * perPage) + index + 1 }}
                    </td>

                    <td v-if="columnVisibility.siembra" class="fixed-column" v-text="le.nombre_siembra"></td>
                    <td v-if="columnVisibility.estanque" v-text="le.contenedor"></td>
                    <td v-if="columnVisibility.especies" v-text="le.especies_nombres"></td>

                    <td v-if="columnVisibility.area">
                      {{ le.capacidad }}
                    </td>

                    <td v-if="columnVisibility.cant_inicial">
                      {{ le.cantidad_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.peso_actual" class="text-right">
                      {{ le.peso_actual | numeral('0,0.00') }} gr
                    </td>

                    <td v-if="columnVisibility.biomasa_inicial" class="text-right">
                      {{ le.biomasa_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.cant_actual" class="text-right">
                      {{ le.cantidad_actual | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.biomasa_disponible" class="text-right">
                      {{ le.biomasa_disponible | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.biomasa_cosechada" class="text-right">
                      {{ le.salida_biomasa | numeral('0,0.00') }} kg
                    </td>

                    <td v-if="columnVisibility.mortalidad_animales" class="text-right">
                      {{ le.mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mortalidad_kg" class="text-right">
                      {{ le.mortalidad_kg | numeral('0,0.00') }} kg
                    </td>

                    <td v-if="columnVisibility.total_alimento" class="text-right">
                      {{ le.cantidad_total_alimento | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.incremento_biomasa" class="text-right">
                      {{ le.incr_bio_acum_conver | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.conversion_alimenticia" class="text-right">
                      {{ le.conversion_alimenticia | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.biomasa_alimento" class="text-right">
                      {{ le.bio_dispo_alimen | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.supervivencia_final" class="text-right">
                      {{ le.porc_supervivencia_final | numeral('0,0.00') }}
                    </td>
                  </tr>

                  <tr class="font-weight-bold" v-if="listadoExistencias.length">
                    <td></td>
                    <td v-if="columnVisibility.siembra" class="fixed-column text-right">Totales:</td>
                    <td v-if="columnVisibility.estanque"></td>
                    <td v-if="columnVisibility.especies"></td>
                    <td v-if="columnVisibility.area"></td>
                    <td v-if="columnVisibility.cant_inicial"></td>
                    
                    <td v-if="columnVisibility.peso_actual"></td>

                    <td v-if="columnVisibility.biomasa_inicial" class="text-right">
                      {{ totalizadoSiembras.biomasa_inicial | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.cant_actual"></td>

                    <td v-if="columnVisibility.biomasa_disponible" class="text-right">
                      {{ totalizadoSiembras.biomasa_disponible | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.biomasa_cosechada" class="text-right">
                      {{ totalizadoSiembras.salida_biomasa | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mortalidad_animales" class="text-right">
                      {{ totalizadoSiembras.mortalidad | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.mortalidad_kg" class="text-right">
                      {{ totalizadoSiembras.mortalidad_kg | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.total_alimento" class="text-right">
                      {{ totalizadoSiembras.cantidad_total_alimento | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.incremento_biomasa" class="text-right">
                      {{ totalizadoSiembras.incr_bio_acum_conver | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.conversion_alimenticia"></td>

                    <td v-if="columnVisibility.biomasa_alimento" class="text-right">
                      {{ totalizadoSiembras.bio_dispo_alimen | numeral('0,0.00') }}
                    </td>

                    <td v-if="columnVisibility.supervivencia_final"></td>
                  </tr>
                </tbody>
              </table>

              <nav class="mt-3" v-if="totalPages > 1">
                <ul class="pagination justify-content-center">
                  <li
                    class="page-item"
                    :class="{ disabled: currentPage === 1 }"
                  >
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="goToPage(currentPage - 1)"
                    >
                      Anterior
                    </a>
                  </li>

                  <li
                    v-for="page in pagesToShow"
                    :key="page"
                    class="page-item"
                    :class="{ active: currentPage === page }"
                  >
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="goToPage(page)"
                    >
                      {{ page }}
                    </a>
                  </li>

                  <li
                    class="page-item"
                    :class="{ disabled: currentPage === totalPages }"
                  >
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="goToPage(currentPage + 1)"
                    >
                      Siguiente
                    </a>
                  </li>
                </ul>
              </nav>
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
      // spinner
      isLoading: false,

      json_fields: {
        'Siembra': 'nombre_siembra',
        'Estanque': 'contenedor',
        'Especies': 'especies_nombres',
        'Area / (m<sup>2</sup>)': 'capacidad',
        'Cantidad inicial \n (Animales)': 'cantidad_inicial',
        // NUEVO CAMPO PARA EXCEL
        'Peso actual \n (g)': {
          field: 'peso_actual',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Biomasa inicial \n (Kg)': {
          field: 'biomasa_inicial',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Biomasa disponible muestreo \n (Kg)': {
          field: 'biomasa_disponible',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Biomasa cosechada \n (Kg)': {
          field: 'salida_biomasa',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Mortalidad \n (Animales)': {
          field: 'mortalidad',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Mortalidad (Kg)': {
          field: 'mortalidad_kg',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Total alimento (Kg)': {
          field: 'cantidad_total_alimento',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Incremento de biomasa por alimento  \n (Kg)': {
          field: 'incr_bio_acum_conver',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Conversion alimenticia': {
          field: 'conversion_alimenticia',
          callback: (value) => numeral(value).format('0,0.00')
        },
        'Biomasa disponible por alimento \n (Kg)': {
          field: 'bio_dispo_alimen',
          callback: (value) => numeral(value).format('0,0.00')
        },
        '% Supervievencia final': {
          field: 'porc_supervivencia_final',
          callback: (value) => numeral(value).format('0,0.00')
        }
      },

      listadoExistencias: [],
      listadoEspecies: [],
      listadoSiembras: [],
      imprimirRecursos: [],
      totalizadoSiembras: [],

      f_siembra: -1,
      f_especie: -1,
      f_inicio_d: '',
      f_inicio_h: '',
      f_estado: "1",

      // paginación local
      currentPage: 1,
      perPage: 15,
      search_nro_results: "15",

      // columnas
      showColumnPanel: false,
      columnVisibility: {
        siembra: true,
        estanque: true,
        especies: true,
        area: true,
        cant_inicial: true,
        peso_actual: true, // NUEVA COLUMNA INICIALIZADA
        biomasa_inicial: true,
        cant_actual: true,
        biomasa_disponible: true,
        biomasa_cosechada: true,
        mortalidad_animales: true,
        mortalidad_kg: true,
        total_alimento: true,
        incremento_biomasa: true,
        conversion_alimenticia: true,
        biomasa_alimento: true,
        supervivencia_final: true,
      },
    }
  },
  components: {
    downloadexcel,
  },
  computed: {
    // opciones de siembras con "Todas las siembras"
    filteredItems() {
      const base = this.listadoSiembras.filter(
        (item) => String(item.estado) === String(this.f_estado)
      );
      return [
        { id: -1, nombre_siembra: 'Todas las siembras' },
        ...base
      ];
    },
    allSiembrasOptions() {
      return [
        { id: -1, nombre_siembra: 'Todas las siembras' },
        ...this.listadoSiembras
      ];
    },

    // paginación local
    totalPages() {
      if (!this.listadoExistencias.length) return 1;
      return Math.ceil(this.listadoExistencias.length / this.perPage);
    },
    paginatedExistencias() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.listadoExistencias.slice(start, end);
    },
    pagesToShow() {
      const pages = [];
      const maxButtons = 7;
      let start = Math.max(1, this.currentPage - 3);
      let end = Math.min(this.totalPages, start + maxButtons - 1);
      if (end - start < maxButtons - 1) {
        start = Math.max(1, end - maxButtons + 1);
      }
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },

    columnGroup1() {
      return [
        { key: 'siembra', label: 'Siembra' },
        { key: 'estanque', label: 'Estanque' },
        { key: 'especies', label: 'Especies' },
        { key: 'area', label: 'Área (m²)' },
        { key: 'cant_inicial', label: 'Cant. inicial (Animales)' },
        { key: 'peso_actual', label: 'Peso actual (g)' }, // NUEVA OPCIÓN
        { key: 'biomasa_inicial', label: 'Biomasa inicial (Kg)' },
        { key: 'cant_actual', label: 'Cant. actual (Animales)' },
        { key: 'biomasa_disponible', label: 'Biomasa disponible muestreo (Kg)' },
      ];
    },
    columnGroup2() {
      return [
        { key: 'biomasa_cosechada', label: 'Biomasa cosechada (Kg)' },
        { key: 'mortalidad_animales', label: 'Mortalidad (Animales)' },
        { key: 'mortalidad_kg', label: 'Mortalidad Kg' },
        { key: 'total_alimento', label: 'Total alimento (Kg)' },
        { key: 'incremento_biomasa', label: 'Incremento biomasa por alimento (Kg)' },
        { key: 'conversion_alimenticia', label: 'Conversión alimenticia' },
        { key: 'biomasa_alimento', label: 'Biomasa disponible por alimento (Kg)' },
        { key: 'supervivencia_final', label: '% Supervivencia final' },
      ];
    },
  },
  watch: {
    search_nro_results(newVal) {
      let n = parseInt(newVal, 10);
      if (isNaN(n) || n <= 0) {
        n = this.listadoExistencias.length || 1;
      }
      this.perPage = n;
      this.currentPage = 1;
    }
  },
  methods: {
    // columnas
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

    // paginación
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },

    async fetchData() {
      // Excel siempre exporta TODO, no sólo la página actual
      return this.listadoExistencias;
    },

    listar() {
      let me = this;

      const data = {
        f_siembra: (this.f_siembra === -1 || this.f_siembra === '' ? '-1' : this.f_siembra),
        f_especie: (this.f_especie === -1 || this.f_especie === '' ? '-1' : this.f_especie),
        f_estado: (this.f_estado === '' ? '-1' : this.f_estado),
        f_inicio_d: (this.f_inicio_d === '' ? '-1' : this.f_inicio_d),
        f_inicio_h: (this.f_inicio_h === '' ? '-1' : this.f_inicio_h),
      };

      this.isLoading = true;
      this.currentPage = 1;

      axios
        .get("api/informes-biomasa-alimento", { params: data })
        .then(function (response) {
          me.listadoExistencias = response.data.existencias;
          me.totalizadoSiembras = response.data.totalizadoSiembras;
        })
        .finally(() => {
          me.isLoading = false;
        });
    },

    listarEspecies() {
      let me = this;
      axios.get("api/especies")
        .then(function (response) {
          me.listadoEspecies = response.data
        })
    },

    listarSiembras() {
      let me = this;
      axios.get("api/siembras/listado")
        .then(function (response) {
          me.listadoSiembras = response.data;
        })
    },

    setDefaultLastMonthRange() {
      const today = new Date();
      const from = new Date();
      from.setMonth(from.getMonth() - 1);

      const pad = (n) => (n < 10 ? '0' + n : n);

      const toStr = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

      this.f_inicio_d = toStr(from);
      this.f_inicio_h = toStr(today);
    }
  },
  mounted() {
    this.setDefaultLastMonthRange();
    this.listar();
    this.listarEspecies();
    this.listarSiembras();
  }
}
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

/* --- NUEVOS ESTILOS PARA SOLUCIONAR LA COLUMNA MONTADA Y EL SCROLL --- */

.table-container {
  overflow-x: auto;
  max-width: 100%;
}

.fixed-column {
  position: sticky;
  left: 0;
  box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1); 
}

thead.thead-primary .fixed-column {
  background-color: #173d6b !important; /* Si tu azul es distinto, cámbialo por el hexadecimal exacto */
  color: #ffffff;
  z-index: 3; 
}

tbody .fixed-column {
  background-color: #ffffff !important;
  z-index: 2; 
}
</style>