<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Alimentación</div>

          <div class="card-body">
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

            <div class="row mb-1">
              <div class="col-md-12 text-right ">
                <div class="form-group col-md-2 ml-auto">
                  <downloadexcel
                    class="btn btn-success form-control"
                    :fetch="fetchData"
                    :fields="json_fields"
                    name="alimentacion.xls"
                    type="xls"
                  >
                    <i class="fa fa-fw fa-download"></i> Generar Excel
                  </downloadexcel>
                </div>
              </div>

              <div class="col-md-12">
                <h5>Filtrar por:</h5>
                <form class="row">
                  <input type="hidden" value="1" v-model="t_actividad">

                  <div class="form-group col-3">
                    <label for="estado_siembta">Estado de siembra</label>
                    <select
                      name="f_estado"
                      class="custom-select"
                      id="f_estado"
                      v-model="f_estado"
                    >
                      <option value="-1">Todas</option>
                      <option value="1">Activas</option>
                      <option value="0">Inactivas</option>
                    </select>
                  </div>

                  <div class="form-group col-3">
                    <label for="siembra_activa">
                      Siembras {{ f_estado == 0 ? "inactivas" : "activas" }} :
                    </label>

                    <!-- Cuando se filtra por estado -->
                    <template v-if="f_estado != '-1'">
                      <v-select
                        :options="filteredItems"
                        label="nombre_siembra"
                        :reduce="(siembra) => siembra.id"
                        v-model="f_siembra"
                      />
                    </template>

                    <!-- Cuando se muestran todas -->
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
                    <label for="alimento">Alimento: </label>
                    <select class="form-control" id="alimento" v-model="alimento_s">
                      <option selected> Seleccionar</option>
                      <option
                        v-for="(alimento, index) in listadoAlimentos"
                        :key="index"
                        :value="alimento.id"
                      >
                        {{ alimento.alimento }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group col-md-2">
                    <label for="search">Desde: </label>
                    <input
                      class="form-control"
                      type="date"
                      placeholder="Search"
                      aria-label="fecha_ra1"
                      v-model="fecha_ra1"
                    >
                  </div>

                  <div class="form-group col-md-2">
                    <label for="search">Hasta: </label>
                    <input
                      class="form-control"
                      type="date"
                      placeholder="Search"
                      aria-label="fecha_ra2"
                      v-model="fecha_ra2"
                    >
                  </div>

                  <div class="form-group col-md-2">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      value="1"
                      v-model="see_all"
                      id="see_all"
                    >
                    <label for="see_all" class="form-check-label">
                      <span></span>
                      Ver todos los registros
                    </label>
                  </div>

                  <div class="form-group col-md-2">
                    <button
                      class="btn btn-primary rounded-circle mt-4"
                      type="button"
                      @click="listar(1)"
                    >
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div>
              <table class="table table-sticky table-bordered table-striped table-sm table-sm-responsive">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th>Tipo de Actividad</th>
                    <th>Siembra</th>
                    <th>Fecha</th>
                    <th>Minutos hombre</th>
                    <th>Alimento</th>
                    <th>Cantidad Mañana</th>
                    <th>Cantidad Tarde</th>
                    <th>Total día</th>
                    <th>Costo Kg</th>
                    <th>Costo total</th>
                    <th>Conversión alimenticia teórica</th>
                    <th>Incremento biomasa acumulada por conversión</th>
                    <th>Detalles</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in listado" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td v-text="item.actividad"></td>
                    <td v-text="item.nombre_siembra" class="fixed-column"></td>
                    <td v-text="item.fecha_ra"></td>
                    <td>{{ item.minutos_hombre | numeral('0,0.00') }}</td>
                    <td v-text="item.alimento"></td>
                    <td v-text="item.cant_manana == null ? '-' : item.cant_manana + ' kg'"></td>
                    <td v-text="item.cant_tarde == null ? '-' : item.cant_tarde + ' kg'"></td>
                    <td v-text="item.alimento_dia == null ? '-' : item.alimento_dia + ' kg'"></td>
                    <td>
                      {{ item.costo_kg | numeral('$0,0.00') }}
                    </td>
                    <td>
                      {{ item.costo_total_alimento | numeral('$0,0.00') }}
                    </td>
                    <td>
                      {{ item.conv_alimenticia | numeral('0,0.00') }}
                    </td>
                    <td>
                      {{ item.incr_bio_acum_conver | numeral('0,0.00') }}
                    </td>
                    <td v-text="item.detalles"></td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr class="font-weight-bold">
                    <th colspan="4" class="text-right">PROMEDIO:</th>
                    <th>
                      {{ promedios.tmh | numeral('0,0.00') }}
                    </th>
                    <th></th>
                    <th>
                      {{ promedios.cman | numeral('0,0.00') }}
                    </th>
                    <th>
                      {{ promedios.ctar | numeral('0,0.00') }}
                    </th>
                    <th>
                      {{ promedios.alid | numeral('0,0.00') }}
                    </th>
                    <th>
                      {{ promedios.coskg | numeral('$0,0.00') }}
                    </th>
                    <th>
                      {{ promedios.cta | numeral('$0,0.00') }}
                    </th>
                    <th></th>
                    <th>
                      {{ promedios.icb | numeral('0,0.00') }}
                    </th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <nav v-show="showPagination" class="mt-5 navigation">
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

    <!-- Modal añadir recursos a siembras -->
    <div
      class="modal fade"
      id="modalRecursos"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title" id="exampleModalLabel">Alimentos por siembra</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form class="row">
              <div class="col-md-6">
                <div class="form-group row">
                  <label for="Alimento" class="col-md-4">Alimento</label>
                  <select
                    class="form-control col-md-7"
                    id="alimento"
                    v-model="form.id_alimento"
                  >
                    <option>--Seleccionar--</option>
                    <option
                      v-for="(alimento, index) in listadoAlimentos"
                      :key="index"
                      :value="alimento.id"
                    >
                      {{ alimento.alimento }}
                    </option>
                  </select>
                </div>

                <div class="form-group row ">
                  <label for="minutos hombre" class="col-md-4">Fecha</label>
                  <input
                    type="date"
                    class="form-control col-md-7"
                    id="fecha_ra"
                    aria-describedby="fecha_ra"
                    placeholder="Minutos hombre"
                    v-model="form.fecha_ra"
                  >
                </div>

                <div class="form-group row">
                  <label for="minutos hombre" class="col-md-4">Minutos hombre</label>
                  <input
                    type="number"
                    class="form-control col-md-7"
                    id="minutos_hombre"
                    step="any"
                    aria-describedby="minutos_hombre"
                    placeholder="Minutos hombre"
                    v-model="form.minutos_hombre"
                  >
                </div>
              </div>

              <!-- Segunda columna -->
              <div class="col-md-6">
                <div class="form-group row">
                  <label for="cant_manana" class="col-md-4">Kg Mañana</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control col-md-7"
                    id="kg_manana"
                    aria-describedby="cant_manana"
                    placeholder="Kg Mañana"
                    v-model="form.cant_manana"
                  >
                </div>

                <div class="form-group row">
                  <label for="cant_tarde" class="col-md-4">Kg tarde</label>
                  <input
                    type="number"
                    step="any"
                    class="form-control col-md-7"
                    id="cant_tarde"
                    aria-describedby="cant_tarde"
                    placeholder="Kg tarde"
                    v-model="form.cant_tarde"
                  >
                </div>

                <div class="form-group row">
                  <label for="detalles" class="col-md-4">Detalles</label>
                  <textarea
                    class="form-control col-md-7"
                    id="detalles"
                    aria-describedby="detalles"
                    placeholder="Detalles"
                    v-model="form.detalles"
                  ></textarea>
                </div>

                <fieldset class="form-group">
                  <div class="row">
                    <legend for="detalles" class="col-md-4 col-form-label">
                      Seleccionar siembras
                    </legend>
                    <div class="col-md-7">
                      <div
                        v-for="(item, index) in listadoSiembras"
                        :key="index"
                        class="form-check"
                      >
                        <input
                          class="form-check-input"
                          type="checkbox"
                          :value="item.id"
                          v-model="form.id_siembra"
                        >
                        <label class="form-check-label" for="siembra">
                          {{ item.nombre_siembra }}
                        </label>
                        <br>
                      </div>
                    </div>
                  </div>
                </fieldset>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="guardarRecursos()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Form } from 'vform'
import downloadexcel from "vue-json-excel"

export default {
  data() {
    return {
      json_fields: {
        'Tipo actividad': 'actividad',
        'Siembra': 'nombre_siembra',
        'Fecha': 'fecha_ra',
        'Minutos hombre': 'minutos_hombre',
        'Alimento': 'alimento',
        'Cantidad KG Mañana': 'cant_manana',
        'Cantidad KG tarde': 'cant_tarde',
        'Cantidad total día': 'alimento_dia',
        'Costo KG': 'costo_kg',
        'Costo total': 'costo_total_alimento',
        'Conversion alimenticia teorica': 'conv_alimenticia',
        'incremento biomasa acumulada por conversion': 'incr_bio_acum_conver',
        'Detalles': 'detalles'
      },
      form: new Form({
        id_siembra: [],
        id_recurso: '',
        id_alimento: '',
        tipo_actividad: '1',
        fecha_ra: '',
        minutos_hombre: '',
        cant_manana: '',
        cant_tarde: '',
        detalles: ''
      }),
      pagination: {
        total: 0,
        current_page: 0,
        per_page: 0,
        last_page: 0,
        from: 0,
        to: 0,
      },
      offset: 10,
      t_actividad: '',
      fecha_ra1: '',
      fecha_ra2: '',
      f_siembra: -1,
      f_estado: '1',
      see_all: 0,
      alimento_s: '',
      recurso_s: '',
      busqueda: '',
      addSiembras: [],
      listado: [],
      promedios: [],
      listadoRS: [],
      listadorxs: [],
      listadoSiembras: [],
      listadoAlimentos: [],
      listadoRecursos: [],
      nombresRecursos: [],
      nombresAlimentos: [],
      showPagination: 1,
      // spinner
      isLoading: false,
    }
  },
  components: {
    downloadexcel,
  },
  computed: {
    isActived() {
      return this.pagination.current_page;
    },
    //Calcula los elementos de la paginación
    pagesNumber() {
      if (!this.pagination.to) {
        return [];
      }

      let from = this.pagination.current_page - this.offset;
      if (from < 1) {
        from = 1;
      }

      let to = from + (this.offset * 2);
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
    // siembras según estado + opción "Todas las siembras"
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
    }
  },
  methods: {
    async fetchData() {
      // Excel exporta lo que está listado actualmente
      return this.listado;
    },
    abrirCrear() {
      $('#modalRecursos').modal('show');
    },

    listar(page = 1) {
      // filtros
      this.f_s = (this.f_siembra === '' || this.f_siembra === -1) ? '-1' : this.f_siembra;
      this.check = (this.see_all === '' ? 0 : this.see_all);
      this.actividad = (this.t_actividad === '' ? '1' : this.t_actividad);
      this.ali = (this.alimento_s === '' ? '-1' : this.alimento_s);
      this.fecha1 = (this.fecha_ra1 === '' ? '-3' : this.fecha_ra1);
      this.fecha2 = (this.fecha_ra2 === '' ? '-1' : this.fecha_ra2);
      this.rec = (this.recurso_s === '' ? '-1' : this.recurso_s);

      const data = {
        f_siembra: this.f_s,
        see_all: this.check,
        tipo_actividad: '1',
        alimento_s: this.ali,
        recurso_s: this.rec,
        fecha_ra1: this.fecha1,
        fecha_ra2: this.fecha2
      };

      let me = this;
      this.isLoading = true;

      axios
        .get("api/lista-alimentacion?page=" + page, { params: data })
        .then(function (response) {
          me.promedios = response.data.promedioRecursos;

          if (response.data.pagination) {
            me.showPagination = 1;
            me.listado = response.data.recursosNecesarios.data;
            me.pagination = response.data.pagination;
          } else {
            me.showPagination = 0;
            me.listado = response.data.recursosNecesarios;
            me.pagination = [];
          }
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

    listarAlimentos() {
      let me = this;
      axios.get("api/alimentos")
        .then(function (response) {
          me.listadoAlimentos = response.data;
          const auxAlimento = response.data;
          auxAlimento.forEach(element => {
            me.nombresAlimentos[element.id] = element.alimento;
          });
        })
    },

    listarRecursos() {
      let me = this;
      axios.get("api/recursos")
        .then(function (response) {
          me.listadoRecursos = response.data;
          const auxRecurso = response.data;
          auxRecurso.forEach(element => {
            me.nombresRecursos[element.id] = element.recurso;
          });
        })
    },

    guardarRecursos() {
      let me = this;
      const data = {
        ...this.form,
        tipo: 'ALIMENTACION',
        descripcion: 'ALIMENTACION SIEMBRAS'
      };
      axios.post("api/recursos-necesarios", data)
        .then(() => {
          me.listar(me.pagination.current_page || 1);
          $('#modalRecursos').modal('hide');
        })
    },

    cambiarPagina(page) {
      this.pagination.current_page = page;
      this.listar(page);
    },

    setDefaultLastMonthRange() {
      const today = new Date();
      const from = new Date();
      from.setMonth(from.getMonth() - 1);

      const pad = (n) => (n < 10 ? '0' + n : n);
      const toStr = (d) =>
        `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

      this.fecha_ra1 = toStr(from);
      this.fecha_ra2 = toStr(today);
    }
  },

  mounted() {
    this.setDefaultLastMonthRange();
    this.listar(1);
    this.listarSiembras();
    this.listarAlimentos();
    this.listarRecursos();
  }
}
</script>
