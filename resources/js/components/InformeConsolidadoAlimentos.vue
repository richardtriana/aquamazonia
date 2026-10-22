<template>
  <div class="container">
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
            Informes consolidado de alimentos
          </div>
          <div class="card-body">
            <div class="row text-left mb-2">
              <h5>Filtrar por:</h5>
            </div>

            <div class="row align-items-end mb-3 filtro-bar">
              <div class="form-group col-md-3">
                <label for="contenedor">Estanque</label>
                <select
                  class="form-control filtro-control"
                  id="contenedor"
                  v-model="id_contenedor"
                >
                  <option value="-1">Seleccionar</option>
                  <option
                    :value="cont.id"
                    v-for="(cont, index) in listadoEstanques"
                    :key="index"
                  >
                    {{ cont.contenedor }}
                  </option>
                </select>
              </div>

              <div class="form-group col-md-3">
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

              <div class="form-group col-md-3">
                <label for="alimento">Alimento</label>
                <select
                  class="form-control filtro-control"
                  id="alimento"
                  v-model="id_alimento"
                >
                  <option value="-1">Seleccionar</option>
                  <option
                    v-for="(alimento, index) in listadoAlimentos"
                    :key="index"
                    :value="alimento.id"
                  >
                    {{ alimento.alimento }}
                  </option>
                </select>
              </div>

              <div class="form-group col-md-3">
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

              <div class="form-group col-md-2 d-flex">
                <button
                  class="btn btn-primary btn-block filtro-btn-filtrar"
                  @click="listar(1)"
                  type="button"
                >
                  Filtrar
                </button>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-3 col-lg-2">
                <downloadexcel
                  class="btn btn-success w-100"
                  :fetch="fetchData"
                  :fields="json_fields"
                  name="informe-consolidado-alimentos.xls"
                  type="xls"
                >
                  <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
              </div>
            </div>

            <div class="table-container" id="table-container2">
              <table class="table-sticky table table-sm table-hover table-bordered text-center">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th>Siembra</th>
                    <th>Estado</th>
                    <th>Tipo actividad</th>
                    <th>Alimento</th>
                    <th>Costo Kg</th>
                    <th>Kg por Bulto</th>
                    <th>Alimento Mañana (kg)</th>
                    <th>Alimento Tarde (kg)</th>
                    <th>Cantidad Total Alimento (kg)</th>
                    <th>Cantidad Bultos</th>
                    <th>% Cantidad Alimento</th>
                    <th>Costo Alimento</th>
                  </tr>
                </thead>
                <tbody v-if="listado.length > 0">
                  <tr v-for="(lrn, index) in listado" :key="index">
                    <td>{{ pagination.from ? pagination.from + index : index + 1 }}</td>
                    <td class="text-left">{{ lrn.nombre_siembra }}</td>
                    <td>{{ lrn.estado == 1 ? "Activa" : "Inactiva" }}</td>
                    <td>
                      {{ lrn.tipo_actividad == 1 ? "Alimentación" : "" }}
                    </td>
                    <td class="text-left">{{ lrn.alimento }}</td>
                    <td class="text-right">
                      {{ lrn.costoUnitarioAlimento | numeral("$0,0.00") }}
                    </td>
                    <td class="text-info">{{ lrn.cantidad_bulto }} kg</td>
                    <td class="text-right">
                      {{ lrn.c_manana | numeral("0,0.00") }}
                    </td>
                    <td class="text-right">
                      {{ lrn.c_tarde | numeral("0,0.00") }}
                    </td>
                    <td class="text-right font-weight-bold">
                      {{ lrn.cantidadTotalAlimento | numeral("0,0.00") }}
                    </td>
                    <td class="text-right font-weight-bold text-primary">
                      {{ lrn.cantidad_bulto > 0 ? (lrn.cantidadTotalAlimento / lrn.cantidad_bulto).toFixed(2) : '0.00' }}
                    </td>
                    <td class="text-right">
                      {{ lrn.porcCantidadAlimento | numeral("0,0.00") }} %
                    </td>
                    <td class="text-right">
                      {{ lrn.costoAlimento | numeral("$0,0.00") }}
                    </td>
                  </tr>

                  <tr class="font-weight-bold text-right bg-light">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="2" class="text-right">Totales:</td>
                    <td></td>
                    <td class="text-right">
                      <strong>{{ totalizadoAlimentos.c_manana | numeral("0,0.00") }}</strong>
                    </td>
                    <td class="text-right">
                      <strong>{{ totalizadoAlimentos.c_tarde | numeral("0,0.00") }}</strong>
                    </td>
                    <td class="text-right">
                      <strong>{{ totalizadoAlimentos.cantidadTotalAlimento | numeral("0,0.00") }}</strong>
                    </td>
                    <td class="text-right text-primary">
                      <strong>{{ totalizadoAlimentos.totalBultos | numeral("0,0.00") }}</strong>
                    </td>
                    <td class="text-right">
                      <strong>{{ totalizadoAlimentos.porcCantidadAlimento | numeral("0,0.00") }} %</strong>
                    </td>
                    <td class="text-right text-dark">
                      <strong>{{ totalizadoAlimentos.costoAlimento | numeral("$0,0.00") }}</strong>
                    </td>
                  </tr>
                </tbody>
              </table>

              <nav class="mt-4 navigation">
                <ul class="pagination justify-content-center">
                  <li class="page-item" v-if="pagination.current_page > 1">
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="cambiarPagina(pagination.current_page - 1)"
                    >
                      Ant
                    </a>
                  </li>
                  <li
                    class="page-item"
                    v-for="page in pagesNumber"
                    :key="page"
                    :class="[page == isActived ? 'active' : '']"
                  >
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="cambiarPagina(page)"
                      v-text="page"
                    ></a>
                  </li>
                  <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                    <a
                      class="page-link"
                      href="#"
                      @click.prevent="cambiarPagina(pagination.current_page + 1)"
                    >
                      Sig
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
      // SPINNER
      isLoading: false,

      json_fields: {
        Siembra: {
          field: "nombre_siembra",
          callback: value => value
        },
        Estado: {
          field: "estado",
          callback: value => (value == 1 ? "Activa" : "Inactiva")
        },
        "Tipo actividad": {
          field: "actividad",
          callback: value => value
        },
        Alimento: {
          field: "alimento",
          callback: value => value
        },
        "Costo Kg": {
          field: "costoUnitarioAlimento",
          callback: value => numeral(value).format("0,0.00")
        },
        "Kg/Bulto": {
          field: "cantidad_bulto",
          callback: value => value
        },
        "Alimento Mañana (Kg)": {
          field: "c_manana",
          callback: value => numeral(value).format("0,0.00")
        },
        "Alimento Tarde (Kg)": {
          field: "c_tarde",
          callback: value => numeral(value).format("0,0.00")
        },
        "Cantidad total alimento (Kg)": {
          field: "cantidadTotalAlimento",
          callback: value => numeral(value).format("0,0.00")
        },
        "Cantidad en Bultos": {
          callback: obj => {
            return obj.cantidad_bulto > 0 
              ? (obj.cantidadTotalAlimento / obj.cantidad_bulto).toFixed(2) 
              : '0.00'
          }
        },
        "% Cantidad Alimento": {
          field: "porcCantidadAlimento",
          callback: value => numeral(value).format("0,0.00")
        },
        "Costo total alimento": {
          field: "costoAlimento",
          callback: value => numeral(value).format("0,0.00")
        }
      },

      tipoActividad: "",
      id_alimento: "-1",
      f_siembra: "",          
      f_estado: "1",
      id_contenedor: "-1",
      search_nro_results: "15",

      listado: [],

      totalizadoAlimentos: {
        c_manana: 0,
        c_tarde: 0,
        cantidadTotalAlimento: 0,
        porcCantidadAlimento: 0,
        costoAlimento: 0,
        totalBultos: 0 // Nuevo totalizador
      },

      // paginación
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0
      },
      offset: 10,

      listadoSiembras: [],
      listadoAlimentos: [],
      listadoEstanques: []
    };
  },
  components: {
    downloadexcel
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
        item => String(item.estado) === String(this.f_estado)
      );
    }
  },
  methods: {
    async fetchData() {
      return this.listado;
    },
    calcularTotales() {
      const tot = {
        c_manana: 0,
        c_tarde: 0,
        cantidadTotalAlimento: 0,
        porcCantidadAlimento: 0,
        costoAlimento: 0,
        totalBultos: 0
      };

      this.listado.forEach(lrn => {
        tot.c_manana += Number(lrn.c_manana || 0);
        tot.c_tarde += Number(lrn.c_tarde || 0);
        tot.cantidadTotalAlimento += Number(lrn.cantidadTotalAlimento || 0);
        tot.porcCantidadAlimento += Number(lrn.porcCantidadAlimento || 0);
        tot.costoAlimento += Number(lrn.costoAlimento || 0);
        
        // Sumamos los bultos calculados por cada fila
        if(lrn.cantidad_bulto > 0) {
          tot.totalBultos += (Number(lrn.cantidadTotalAlimento) / Number(lrn.cantidad_bulto));
        }
      });

      this.totalizadoAlimentos = tot;
    },
    listar(page = 1) {
      this.isLoading = true;
      const data = {
        id_siembra: this.f_siembra === "" ? "-1" : this.f_siembra,
        id_contenedor:
          this.id_contenedor === "-1" ? "-1" : this.id_contenedor,
        estado_siembra: this.f_estado === "-1" ? "-1" : this.f_estado,
        id_alimento: this.id_alimento === "-1" ? "-1" : this.id_alimento,
        page: page,
        nro_results:
          this.search_nro_results === "" ? 15 : this.search_nro_results
      };

      axios
        .get("api/informes-alimentos", { params: data })
        .then(response => {
          this.listado = response.data.recursosNecesarios.data || [];
          this.pagination = response.data.pagination || this.pagination;
          this.calcularTotales();
        })
        .catch(error => {
          console.error(error);
          if (typeof Swal !== "undefined") {
            Swal.fire(
              "Error",
              "Ocurrió un problema cargando el informe de alimentos",
              "error"
            );
          }
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
    listarAlimentos() {
      axios.get("api/alimentos").then(response => {
        this.listadoAlimentos = response.data;
      });
    },
    listarEstanques() {
      axios.get("api/contenedores").then(response => {
        this.listadoEstanques = response.data;
      });
    },
    cambiarPagina(page) {
      this.pagination.current_page = page;
      this.listar(page);
    }
  },
  mounted() {
    this.listar(1);
    this.listarSiembras();
    this.listarAlimentos();
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
</style>