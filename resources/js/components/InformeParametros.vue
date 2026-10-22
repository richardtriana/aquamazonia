<template>
  <div class="container">
    <!-- SPINNER GLOBAL PARA ESTA PÁGINA -->
    <div v-if="isLoading" class="global-spinner">
      <div class="global-spinner-inner">
        <div class="global-spinner-ring"></div>
        <div class="global-spinner-logo">AQUAMAZONIA</div>
        <div class="global-spinner-text">Cargando información, por favor espera...</div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">PARÁMETROS DE CALIDAD DEL AGUA</div>

          <div class="card-body">
            <!-- FILTROS -->
            <div class="row mb-1">
              <div class="col-md-12">
                <h5>Filtrar por:</h5>
                <div class="row align-items-end filtro-bar">
                  
                  <!-- Estanque -->
                  <div class="form-group col-md-3">
                    <label>Estanque</label>
                    <select class="form-control filtro-control" v-model="f_contenedor">
                      <option value="-1">Todos</option>
                      <option v-for="(lc,index) in listadoEstanques" :key="index" :value="lc.id">
                        {{ lc.contenedor }}
                      </option>
                    </select>
                  </div>

                  <!-- Fecha inicio -->
                  <div class="form-group col-md-3">
                    <label>Fecha inicio desde</label>
                    <input type="date" class="form-control filtro-control" v-model="f_inicio_d">
                  </div>

                  <!-- Fecha fin -->
                  <div class="form-group col-md-3">
                    <label>Fecha inicio hasta</label>
                    <input type="date" class="form-control filtro-control" v-model="f_inicio_h">
                  </div>

                  <!-- Resultados -->
                  <div class="form-group col-md-2">
                    <label>Mostrar {{ search_nro_results }} por página</label>
                    <input type="number" min="1" class="form-control filtro-control" v-model="search_nro_results">
                  </div>

                  <!-- Botón buscar -->
                  <div class="form-group col-md-1 d-flex">
                    <button class="btn btn-primary btn-block filtro-btn-filtrar" @click="filtrarParametros(1)">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>

                </div>

                <!-- Exportar Excel -->
                <div class="row mt-2">
                  <div class="form-group col-md-3">
                    <label>Generar Excel</label>
                    <downloadexcel
                      class="btn btn-success form-control"
                      :fetch="fetchData"
                      :fields="json_fields"
                      name="informe-parametros-calidad-agua.xls"
                      type="xls"
                    >
                      <i class="fa fa-fw fa-download"></i> Exportar Excel
                    </downloadexcel>
                  </div>
                </div>
              </div>
            </div>

            <!-- TABLA -->
            <div class="table-container table-container--wide" id="table-container2">
              <table class="table-sticky table table-sm table-hover table-bordered">
                <thead class="thead-primary">
                  <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">ID registro</th>
                    <th rowspan="2">Estanque</th>
                    <th rowspan="2">Fecha</th>
                    <th colspan="5" class="text-center">% Saturación de oxígeno</th>
                    <th rowspan="2">Temperatura</th>
                    <th rowspan="2">PH</th>
                    <th rowspan="2">Amonio</th>
                    <th rowspan="2">Nitrito</th>
                    <th rowspan="2">Nitrato</th>
                    <th rowspan="2">Otros</th>
                  </tr>
                  <tr>
                    <th>12 am</th>
                    <th>4 am</th>
                    <th>7 am</th>
                    <th>4 pm</th>
                    <th>8 pm</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(lp,index) in paginatedParametros" :key="index">
                    <th>{{ pagination.from ? pagination.from + index : index + 1 }}</th>
                    <th>{{ lp.id }}</th>
                    <td>{{ lp.contenedor }}</td>

                    <!-- FECHA CORTA -->
                    <td>{{ formatDate(lp.fecha_parametro) }}</td>

                    <td>{{ lp['12_am'] }}</td>
                    <td>{{ lp['4_am'] }}</td>
                    <td>{{ lp['7_am'] }}</td>
                    <td>{{ lp['4_pm'] }}</td>
                    <td>{{ lp['8_pm'] }}</td>
                    <td>{{ lp.temperatura }}</td>
                    <td>{{ lp.ph }}</td>
                    <td>{{ lp.amonio }}</td>
                    <td>{{ lp.nitrito }}</td>
                    <td>{{ lp.nitrato }}</td>
                    <td>{{ lp.otros }}</td>
                  </tr>

                  <!-- PROMEDIOS -->
                  <tr class="bg-secondary text-white font-weight-bold text-right">
                    <th colspan="4" class="text-right">PROMEDIO:</th>
                    <td>{{ promedios.promedio_12_am | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_4_am | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_7_am | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_4_pm | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_8_pm | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_temperatura | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_ph | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_amonio | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_nitrito | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_nitrato | numeral("0,0.00") }}</td>
                    <td>{{ promedios.promedio_otros | numeral("0,0.00") }}</td>
                  </tr>
                </tbody>
              </table>

              <!-- PAGINACIÓN -->
              <nav class="mt-4 navigation">
                <ul class="pagination justify-content-center">
                  <li class="page-item" v-if="pagination.current_page > 1">
                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page - 1)">Ant</a>
                  </li>

                  <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == isActived }">
                    <a class="page-link" href="#" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                  </li>

                  <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                    <a class="page-link" href="#" @click.prevent="cambiarPagina(pagination.current_page + 1)">Sig</a>
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
import { Form } from "vform";

export default {
  data() {
    return {
      isLoading: false,

      json_fields: {
        "#": "id",
        Fecha: "fecha_parametro",
        Estanque: "contenedor",
        "12 am": "12_am",
        "4 am": "4_am",
        "7 am": "7_am",
        "4 pm": "4_pm",
        "8 pm": "8_pm",
        Temperatura: "temperatura",
        PH: "ph",
        Amonio: "amonio",
        Nitrito: "nitrito",
        Nitrato: "nitrato",
        Otros: "otros"
      },

      editando: 0,
      form: new Form({
        id: "",
        id_siembra: [],
        id_especie: "",
        fecha_parametro: "",
        "12_am": "",
        "4_am": "",
        "7_am": "",
        "4_pm": "",
        "8_pm": "",
        temperatura: "",
        ph: "",
        amonio: "",
        nitrito: "",
        nitrato: "",
        otros: ""
      }),

      listadoParametros: [],
      listadoEstanques: [],
      listadoSiembras: [],

      promedios: {
        promedio_12_am: 0,
        promedio_4_am: 0,
        promedio_7_am: 0,
        promedio_4_pm: 0,
        promedio_8_pm: 0,
        promedio_temperatura: 0,
        promedio_ph: 0,
        promedio_amonio: 0,
        promedio_nitrito: 0,
        promedio_nitrato: 0,
        promedio_otros: 0
      },

      f_contenedor: "-1",
      f_inicio_d: "",
      f_inicio_h: "",

      pagination: {
        total: 0,
        current_page: 1,
        per_page: 15,
        last_page: 1,
        from: 0,
        to: 0
      },

      offset: 3,
      search_nro_results: "15"
    };
  },

  components: { downloadexcel },

  computed: {
    isActived() {
      return this.pagination.current_page;
    },
    pagesNumber() {
      if (!this.pagination.to) return [];

      let from = this.pagination.current_page - this.offset;
      if (from < 1) from = 1;

      let to = from + this.offset * 2;
      if (to >= this.pagination.last_page) to = this.pagination.last_page;

      const pages = [];
      while (from <= to) pages.push(from++);

      return pages;
    },

    paginatedParametros() {
      const start = (this.pagination.current_page - 1) * this.pagination.per_page;
      const end = start + this.pagination.per_page;
      return this.listadoParametros.slice(start, end);
    }
  },

  methods: {

    /** ================================
     *  FORMATO CORTO DE FECHA (dd/mm/yyyy)
     *  =================================*/
    formatDate(value) {
      if (!value) return "";
      const d = new Date(value);
      if (isNaN(d)) return value;

      const day = String(d.getDate()).padStart(2, "0");
      const month = String(d.getMonth() + 1).padStart(2, "0");
      const year = d.getFullYear();

      return `${day}/${month}/${year}`;
    },

    async fetchData() {
      return this.listadoParametros;
    },

    setDefaultDateRangeLastMonth() {
      const today = new Date();
      const end = today.toISOString().slice(0, 10);

      const startDate = new Date();
      startDate.setMonth(startDate.getMonth() - 1);
      const start = startDate.toISOString().slice(0, 10);

      this.f_inicio_h = end;
      this.f_inicio_d = start;
    },

    buildPagination() {
      const total = this.listadoParametros.length;
      const perPage = Number(this.search_nro_results) || total || 1;
      const lastPage = Math.ceil(total / perPage) || 1;

      this.pagination.current_page = Math.min(
        Math.max(this.pagination.current_page, 1),
        lastPage
      );

      this.pagination.total = total;
      this.pagination.per_page = perPage;
      this.pagination.last_page = lastPage;

      if (total === 0) {
        this.pagination.from = 0;
        this.pagination.to = 0;
      } else {
        this.pagination.from =
          (this.pagination.current_page - 1) * this.pagination.per_page + 1;
        this.pagination.to = Math.min(
          this.pagination.current_page * this.pagination.per_page,
          total
        );
      }
    },

    filtrarParametros(page = 1) {
      this.isLoading = true;

      const data = {
        id_contenedor: this.f_contenedor === "-1" ? "-1" : this.f_contenedor,
        f_inicio_d: this.f_inicio_d || "-1",
        f_inicio_h: this.f_inicio_h || "-1"
      };

      axios
        .post("api/filtro-parametros", data)
        .then(r => {
          this.listadoParametros = r.data.calidad_agua || [];
          this.promedios = r.data.promedios || this.promedios;
          this.pagination.current_page = page;
          this.buildPagination();
        })
        .catch(() => {
          Swal.fire("Error", "No se pudieron cargar los parámetros", "error");
        })
        .finally(() => (this.isLoading = false));
    },

    listar() {
      this.setDefaultDateRangeLastMonth();
      this.listarEstanques();
      this.listarSiembras();
      this.filtrarParametros(1);
    },

    listarEstanques() {
      axios.get("api/contenedores").then(r => {
        this.listadoEstanques = r.data;
      });
    },

    listarSiembras() {
      axios.get("api/siembras/listado").then(r => {
        this.listadoSiembras = r.data;
      });
    },

    cambiarPagina(page) {
      this.pagination.current_page = page;
      this.buildPagination();
    }
  },

  mounted() {
    this.listar();
  }
};
</script>

<style scoped>
.filtro-bar .form-group { margin-bottom: 0.75rem; }

.filtro-control {
  border-radius: 999px;
  font-size: 0.9rem;
  height: 38px;
  padding: 0 0.85rem;
}

.filtro-btn-filtrar {
  border-radius: 999px;
  font-size: 0.9rem;
}

.table-container--wide {
  position: relative;
  overflow-x: auto;
  width: 100%;
}
</style>
