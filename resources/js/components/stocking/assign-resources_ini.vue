<template>
  <div
    class="modal fade"
    :id="modalId"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ titulo }} — {{ siembraNombre }}
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <div class="row">

            <div class="form-group col-md-6">
              <label>Tipo de Actividad</label>
              <select class="form-control" v-model="nuevo.tipo_actividad_id">
                <option value="">--Seleccionar--</option>
                <option
                  v-for="a in listadoActividades"
                  :key="a.id"
                  :value="a.id"
                >
                  {{ a.actividad }}
                </option>
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Recurso</label>
              <v-select
                :options="listadoRecursos"
                label="recurso"
                :reduce="r => r.id"
                v-model="nuevo.recurso_id"
                placeholder="Seleccione un recurso"
              ></v-select>
            </div>

            <div class="form-group col-md-3">
              <label>Minutos hombre</label>
              <input
                type="number"
                class="form-control"
                min="0"
                step="any"
                v-model.number="nuevo.minutos_hombre"
                placeholder="Ej: 15"
              />
            </div>

            <div class="form-group col-md-3">
              <label>Cantidad Recurso</label>
              <input
                type="number"
                class="form-control"
                min="0"
                step="any"
                v-model.number="nuevo.cantidad_recurso"
                placeholder="Ej: 1"
              />
            </div>

            <div class="form-group col-md-12">
              <label>Detalles</label>
              <input
                type="text"
                class="form-control"
                v-model="nuevo.detalles"
                placeholder="Observaciones opcionales"
              />
            </div>

            <div class="col-md-12 text-right">
              <button type="button" class="btn btn-success" @click="agregar">
                <i class="fas fa-plus"></i> Añadir
              </button>
            </div>

          </div>

          <hr />
          <h6>Lista de recursos añadidos</h6>

          <table class="table table-sm table-bordered mt-2">
            <thead>
              <tr>
                <th>#</th>
                <th>Actividad</th>
                <th>Recurso</th>
                <th>Min. Hombre</th>
                <th>Cantidad</th>
                <th>Detalles</th>
                <th></th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="lista.length === 0">
                <td colspan="7" class="text-center text-muted">
                  No hay recursos registrados.
                </td>
              </tr>

              <tr v-for="(item, index) in lista" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ getActividadNombre(item.tipo_actividad_id) }}</td>
                <td>{{ getRecursoNombre(item.recurso_id) }}</td>
                <td>{{ item.minutos_hombre }}</td>
                <td>{{ item.cantidad_recurso }}</td>
                <td>{{ item.detalles }}</td>
                <td class="text-center">
                  <button
                    class="btn btn-danger btn-sm"
                    @click="eliminar(index)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>

          <button class="btn btn-primary" @click="guardarLista">
            Guardar lista
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AssignResourcesIni",

  props: {
    recursosIniciales: {
      type: Array,
      default: () => []
    },
    titulo: {
      type: String,
      default: "Registrar recursos iniciales" 
    },
    // NUEVA PROPIEDAD PARA EL ID
    modalId: {
      type: String,
      default: "modalRecursosIniciales"
    }
  },

  data() {
    return {
      siembraNombre: "",
      lista: [],
      listadoActividades: [],
      listadoRecursos: [],

      nuevo: {
        tipo_actividad_id: "",
        recurso_id: "",
        minutos_hombre: "",
        cantidad_recurso: "",
        detalles: ""
      }
    };
  },

  methods: {
    abrir(nombre) {
      this.siembraNombre = nombre;
      this.lista = JSON.parse(JSON.stringify(this.recursosIniciales));

      // Usamos el ID dinámico
      $(`#${this.modalId}`).modal("show");

      this.cargarActividades();
      this.cargarRecursos();
    },

    cargarActividades() {
      axios.get("api/actividades").then(({ data }) => {
        this.listadoActividades = data;
      });
    },

    cargarRecursos() {
      axios.get("api/recursos").then(({ data }) => {
        this.listadoRecursos = data;
      });
    },

    getActividadNombre(id) {
      const a = this.listadoActividades.find(x => x.id == id);
      return a ? a.actividad : "Actividad " + id;
    },

    getRecursoNombre(id) {
      const r = this.listadoRecursos.find(x => x.id == id);
      return r ? r.recurso : "Recurso " + id;
    },

    agregar() {
      if (!this.nuevo.tipo_actividad_id || !this.nuevo.recurso_id) {
        Swal.fire("Campos incompletos", "Seleccione actividad y recurso", "warning");
        return;
      }

      this.lista.push({
        tipo_actividad_id: this.nuevo.tipo_actividad_id,
        recurso_id: this.nuevo.recurso_id,
        minutos_hombre: this.nuevo.minutos_hombre,
        cantidad_recurso: this.nuevo.cantidad_recurso,
        detalles: this.nuevo.detalles
      });

      this.nuevo = {
        tipo_actividad_id: "",
        recurso_id: "",
        minutos_hombre: "",
        cantidad_recurso: "",
        detalles: ""
      };
    },

    eliminar(i) {
      this.lista.splice(i, 1);
    },

    guardarLista() {
      this.$emit("update-list", this.lista);
      // Usamos el ID dinámico
      $(`#${this.modalId}`).modal("hide");
    }
  }
};
</script>

<style scoped>
.modal-title {
  font-weight: bold;
}
</style>