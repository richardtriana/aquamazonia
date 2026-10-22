<template>
  <div class="container">

    <!-- Título + Botón -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Recursos necesarios</h3>

      <button class="btn btn-success btn-sm" @click="abrirCrear">
        <i class="fa fa-plus"></i> Añadir
      </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
      <div class="card-body">
        <h5>Filtrar por:</h5>

        <form class="row g-2">

          <div class="col-md-2">
            <label>Estado</label>
            <select class="form-control" v-model="f_estado">
              <option value="-1">--Seleccionar--</option>
              <option value="0">Inactiva</option>
              <option value="1">Activa</option>
            </select>
          </div>

          <div class="col-md-3">
            <label>Siembras {{ f_estado == 0 ? "inactivas" : "activas" }}</label>

            <v-select
              :options="f_estado == '-1' ? listadoSiembras : filteredItems"
              label="nombre_siembra"
              :reduce="s => s.id"
              v-model="f_siembra"
            />
          </div>

          <div class="col-md-2">
            <label>Tipo de Actividad</label>
            <select class="form-control" v-model="t_actividad">
              <option value="-1">Seleccionar</option>
              <option v-for="a in listadoActividades" :key="a.id" :value="a.id"
                v-if="a.actividad != 'Alimentación'">
                {{ a.actividad }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Recurso</label>
            <select class="form-control" v-model="recurso_s">
              <option value="-1">Seleccionar</option>
              <option v-for="r in listadoRecursos" :key="r.id" :value="r.id">
                {{ r.recurso }}
              </option>
            </select>
          </div>

          <div class="col-md-2">
            <label>Desde</label>
            <input type="date" class="form-control" v-model="fecha_ra1" />
          </div>

          <div class="col-md-2">
            <label>Hasta</label>
            <input type="date" class="form-control" v-model="fecha_ra2" />
          </div>

          <!-- Resultados por página -->
          <div class="col-md-2 d-flex align-items-end">
            <div class="form-group w-100">
              <label for="search_nro_results" class="small mb-1">Mostrar por página</label>
              <input
                id="search_nro_results"
                type="number"
                min="1"
                class="form-control form-control-sm"
                v-model="search_nro_results"
                @change="listar(1)"
              />
            </div>
          </div>

          <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="button" @click="listar(1)">
              <i class="fa fa-search"></i> Filtrar
            </button>
          </div>

          <div class="col-md-2 d-flex align-items-end">
            <downloadexcel
              class="btn btn-success w-100"
              :fetch="fetchData"
              :fields="json_fields"
              name="recursos-necesarios.xls"
              type="xls">
              <i class="fa fa-download"></i> Exportar
            </downloadexcel>
          </div>

        </form>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card">
      <div class="card-body">

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-striped">
            <thead class="thead-dark">
              <tr>
                <th>#</th>
                <th>Tipo Actividad</th>
                <th>Siembra</th>
                <th>Fecha</th>
                <th>Min Hombre</th>
                <th>Costo Min</th>
                <th>Recurso</th>
                <th>Cantidad</th>
                <th>Costo</th>
                <th>Total</th>
                <th>Detalles</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(item, i) in listado.data" :key="i">
                <td>{{ listado.from ? listado.from + i : i + 1 }}</td>
                <td>{{ item.actividad }}</td>
                <td>{{ item.nombre_siembra }}</td>
                <td>{{ item.fecha_ra }}</td>
                <td>{{ item.minutos_hombre }}</td>
                <td>{{ item.total_minutos_hombre | numeral('$0,0.00') }}</td>
                <td>{{ item.recurso }}</td>
                <td>{{ item.cantidad_recurso }}</td>
                <td>{{ item.costo | numeral('$0,0.00') }}</td>
                <td>{{ item.costo_total_recurso | numeral('$0,0.00') }}</td>
                <td>{{ item.detalles }}</td>
                <td>
                  <button class="btn btn-danger btn-sm" @click="eliminar(item.id)">
                    <i class="fa fa-trash"></i>
                  </button>
                  <button class="btn btn-success btn-sm" @click="editar(item)">
                    <i class="fa fa-edit"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="showPagination && listado.data && listado.data.length > 0" class="mt-3">
          <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
              Mostrando {{ listado.from || 0 }} a {{ listado.to || 0 }} de {{ listado.total || 0 }} registros
            </div>
            
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: !listado.prev_page_url }">
                  <button class="page-link" @click="listar(listado.current_page - 1)" :disabled="!listado.prev_page_url">
                    <i class="fas fa-arrow-left"></i>
                  </button>
                </li>
                
                <li class="page-item" v-for="page in pagesNumber" :key="page" :class="{ active: page == listado.current_page }">
                  <button class="page-link" @click="listar(page)">{{ page }}</button>
                </li>
                
                <li class="page-item" :class="{ disabled: !listado.next_page_url }">
                  <button class="page-link" @click="listar(listado.current_page + 1)" :disabled="!listado.next_page_url">
                    <i class="fas fa-arrow-right"></i>
                  </button>
                </li>
              </ul>
            </nav>
            
            <div class="text-muted small">
              Página {{ listado.current_page || 1 }} de {{ listado.last_page || 1 }}
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalRecursos" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Recursos por siembra</h5>
            <button class="close" data-dismiss="modal">&times;</button>
          </div>

          <div class="modal-body">
            <form class="row g-3">

              <!-- Siembra -->
              <div class="col-md-6">
                <label>Seleccionar siembra</label>
                <v-select
                  :options="siembrasActivas"
                  label="nombre_siembra"
                  :reduce="s => s.id"
                  v-model="form.id_siembra"
                  @input="cargarDatosPreestablecidos"
                />
              </div>

              <!-- Actividad -->
              <div class="col-md-6">
                <label>Tipo de Actividad</label>
                <select class="form-control" v-model="form.tipo_actividad"
                  @change="cargarDatosPreestablecidos">
                  <option value="">--Seleccionar--</option>
                  <option v-for="a in listadoActividades" :value="a.id" :key="a.id">
                    {{ a.actividad }}
                  </option>
                </select>
              </div>

              <!-- Fecha -->
              <div class="col-md-4">
                <label>Fecha</label>
                <input type="date" class="form-control" v-model="form.fecha_ra" />
              </div>

              <!-- Minutos hombre -->
              <div class="col-md-4">
                <label>Minutos hombre</label>
                <input type="number" class="form-control" v-model="form.minutos_hombre" />
              </div>

              <!-- Recurso -->
              <div class="col-md-4">
                <label>Recurso</label>
                <select class="form-control" v-model="form.id_recurso">
                  <option value="">--Seleccionar--</option>
                  <option v-for="r in listadoRecursos" :value="r.id" :key="r.id">
                    {{ r.recurso }}
                  </option>
                </select>
              </div>

              <!-- Calcular tiempo SI actividad = 12 -->
              <div class="col-12 p-3 bg-light border rounded"
                  v-if="form.tipo_actividad == 12">

                <h5>Calcular tiempo</h5>

                <div class="row">
                  <div class="col-md-6">
                    <label>Fecha inicio</label>
                    <input type="date" class="form-control" v-model="fecha_inicio" />
                  </div>

                  <div class="col-md-6">
                    <label>Hora inicio</label>
                    <input type="time" class="form-control" v-model="hora_inicio" />
                  </div>

                  <div class="col-md-6">
                    <label>Fecha fin</label>
                    <input type="date" class="form-control" v-model="fecha_fin" />
                  </div>

                  <div class="col-md-6">
                    <label>Hora fin</label>
                    <input type="time" class="form-control" v-model="hora_fin" />
                  </div>

                  <div class="col-12 mt-2">
                    <button class="btn btn-primary btn-sm"
                      type="button"
                      @click="calcularTiempo">
                      Calcular tiempo
                    </button>
                  </div>
                </div>
              </div>

              <!-- Cantidad -->
              <div class="col-md-6">
                <label>Cantidad</label>
                <input type="number" class="form-control" v-model="form.cantidad_recurso" />
              </div>

              <!-- Detalles -->
              <div class="col-md-6">
                <label>Detalles</label>
                <textarea class="form-control" v-model="form.detalles"></textarea>
              </div>

            </form>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

            <button v-if="editando == 0" class="btn btn-primary"
              @click="guardar">
              Guardar
            </button>

            <button v-if="editando == 1" class="btn btn-primary"
              @click="actualizar">
              Editar
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { Form } from "vform";
import downloadexcel from "vue-json-excel";

export default {
  components: { downloadexcel },

  data() {
    return {
      json_fields: {
        "Tipo actividad": "actividad",
        Siembra: "nombre_siembra",
        Fecha: "fecha_ra",
        "Minutos hombre": "minutos_hombre",
        "Costo minutos hombre": "total_minutos_hombre",
        Recurso: "recurso",
        Cantidad: "cantidad_recurso",
        Costo: "costo",
        "Costo total recurso": "costo_total_recurso",
        Detalles: "detalles",
      },

      listado: { data: [] },
      promedios: [],
      listadoSiembras: [],
      listadoActividades: [],
      listadoRecursos: [],

      f_siembra: "",
      f_estado: "1",
      t_actividad: "-1",
      recurso_s: "-1",
      fecha_ra1: "",
      fecha_ra2: "",
      see_all: false,
      search_nro_results: "30",

      editando: 0,

      form: new Form({
        id: "",
        id_siembra: "",
        tipo_actividad: "",
        fecha_ra: "",
        minutos_hombre: "",
        id_recurso: "",
        cantidad_recurso: "",
        detalles: "",
      }),

      // para calcular tiempo
      fecha_inicio: "",
      hora_inicio: "07:00",
      fecha_fin: "",
      hora_fin: "07:00",
    };
  },

  computed: {
    filteredItems() {
      return this.listadoSiembras.filter(s => s.estado == this.f_estado);
    },

    siembrasActivas() {
      return this.listadoSiembras.filter(s => s.estado == 1);
    },

    pagesNumber() {
      if (!this.listado.last_page) return [];

      let from = this.listado.current_page - 2;
      if (from < 1) from = 1;

      let to = from + 4;
      if (to >= this.listado.last_page) to = this.listado.last_page;

      const pages = [];
      while (from <= to) pages.push(from++);

      return pages;
    },

    showPagination() {
      return this.listado && this.listado.last_page > 1;
    },
  },

  methods: {

    /** ─────────────────────────────
     *    CARGAR DATOS PREESTABLECIDOS
     *    (solo actividad 12)
     * ───────────────────────────── */
    cargarDatosPreestablecidos() {
      if (this.form.tipo_actividad != 12) return;
      if (!this.form.id_siembra) return;

      axios
        .get("api/siembra-recurso-datos", {
          params: { siembra_id: this.form.id_siembra },
        })
        .then(({ data }) => {
          if (data.length === 0) return;

          const d = data[0];

          this.form.fecha_ra = d.fecha_ra ?? "";
          this.form.minutos_hombre = d.minutos_hombre ?? "";
          this.form.id_recurso = d.recurso_id ?? "";
          this.form.detalles = d.detalles ?? "";
        });
    },

    abrirCrear() {
      this.editando = 0;
      this.form.reset();
      $("#modalRecursos").modal("show");
    },

    editar(obj) {
      this.editando = 1;
      this.form.fill(obj);
      $("#modalRecursos").modal("show");
    },

    eliminar(id) {
      Swal.fire({
        title: "¿Eliminar?",
        icon: "warning",
        showCancelButton: true,
      }).then((r) => {
        if (r.isConfirmed) {
          axios.delete(`api/recursos-necesarios/${id}`).then(() => {
            this.listar();
          });
        }
      });
    },

    /** Guardar */
    guardar() {
      const data = {
        ...this.form,
        tipo: "RECURSOS NECESARIOS",
        descripcion: "RECURSOS NECESARIOS SIEMBRAS",
      };

      axios.post("api/recursos-necesarios", data).then(() => {
        this.listar();
        $("#modalRecursos").modal("hide");
      });
    },

    /** Actualizar */
    actualizar() {
      this.form.put(`api/recursos-necesarios/${this.form.id}`).then(() => {
        this.listar();
        $("#modalRecursos").modal("hide");
      });
    },

    /** Listar recursos */
    listar(page = 1) {
      const params = {
        page,
        f_siembra: this.f_siembra || "-1",
        f_estado: this.f_estado,
        tipo_actividad: this.t_actividad,
        recurso_s: this.recurso_s,
        fecha_ra1: this.fecha_ra1 || "-3",
        fecha_ra2: this.fecha_ra2 || "-1",
        see_all: this.see_all ? 1 : 0,
        nro_results: this.search_nro_results === "" ? 30 : this.search_nro_results,
      };

      axios.get("api/recursos-necesarios", { params }).then(({ data }) => {
        this.listado = data.recursosNecesarios || { data: [] };
        this.promedios = data.promedioRecursos || [];
      }).catch(error => {
        console.error("Error al cargar recursos:", error);
        this.listado = { data: [] };
        this.promedios = [];
      });
    },

    listarSiembras() {
      axios.get("api/siembras/listado").then(({ data }) => {
        this.listadoSiembras = data;
      });
    },

    listarActividades() {
      axios.get("api/actividades").then(({ data }) => {
        this.listadoActividades = data;
      });
    },

    listarRecursos() {
      axios.get("api/recursos").then(({ data }) => {
        this.listadoRecursos = data;
      });
    },

    calcularTiempo() {
      const inicio = new Date(`${this.fecha_inicio} ${this.hora_inicio}`);
      const fin = new Date(`${this.fecha_fin} ${this.hora_fin}`);

      const ms = fin - inicio;
      if (ms > 0) {
        this.form.cantidad_recurso = ms / 60000;
      }
    },

    async fetchData() {
      return this.listado.data || [];
    },
  },

  mounted() {
    this.listar();
    this.listarSiembras();
    this.listarActividades();
    this.listarRecursos();
  },
};
</script>

<style scoped>
.table-responsive {
  overflow-x: auto;
}

.pagination {
  margin-bottom: 0;
}

.page-link {
  cursor: pointer;
}

.page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}

.page-link:focus {
  box-shadow: none;
}

.form-group .form-control-sm {
  height: calc(1.8125rem + 2px);
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.small {
  font-size: 0.875rem;
}
</style>