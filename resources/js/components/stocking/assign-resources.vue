<template>
  <div
    class="modal fade"
    id="modalAssignResources"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalAssignResourcesLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <!-- HEADER -->
        <div class="modal-header">
          <h5 class="modal-title" id="modalAssignResourcesLabel">
            Configuración de recursos para aireación mecánica
            <small v-if="siembraNombreActual">
              — {{ siembraNombreActual }}
            </small>
          </h5>
          <button
            type="button"
            class="close"
            data-dismiss="modal"
            aria-label="Cerrar"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <!-- BODY -->
        <div class="modal-body">
          <!-- Aviso -->
          <div v-if="!localSiembraId" class="alert alert-warning">
            Debes seleccionar una siembra desde la tabla antes de configurar
            los recursos de aireación.
          </div>

          <div v-else>
            <p class="small text-muted mb-3">
              Estos datos se usarán como valores por defecto cuando registres
              <strong>Recursos necesarios</strong> con la actividad
              <strong>"Aireación Mecánica" (id: 12)</strong> para esta siembra.
            </p>

            <!-- FORMULARIO -->
            <form @submit.prevent="submit">
              <div class="row">
                <!-- RECURSO -->
                <div class="form-group col-md-6">
                  <label for="recurso_id">
                    Recurso de aireación
                    <span class="text-danger">*</span>
                  </label>

                  <v-select
                    id="recurso_id"
                    :options="listadoRecursos"
                    label="recurso"
                    :reduce="(recurso) => recurso.id"
                    v-model="form.recurso_id"
                    placeholder="Seleccione un recurso"
                  >
                    <template #option="{ recurso, unidad, costo }">
                      <div class="d-flex flex-column">
                        <span>{{ recurso }}</span>
                        <small class="text-muted">
                          Unidad: {{ unidad }} — Costo: {{ costo }}
                        </small>
                      </div>
                    </template>

                    <template #selected-option="{ recurso }">
                      <span>{{ recurso }}</span>
                    </template>
                  </v-select>

                  <small class="form-text text-muted">
                    Ejemplo: Splash, blower, aireadores, bombas, etc.
                  </small>
                </div>

                <!-- MINUTOS HOMBRE -->
                <div class="form-group col-md-6">
                  <label for="minutos_hombre">
                    Minutos hombre (opcional)
                  </label>
                  <input
                    type="number"
                    min="0"
                    step="any"
                    id="minutos_hombre"
                    class="form-control"
                    v-model.number="form.minutos_hombre"
                    placeholder="Ej: 15"
                  />
                  <small class="form-text text-muted">
                    Tiempo de personal requerido para esta operación.
                  </small>
                </div>
              </div>

              <!-- DETALLES -->
              <div class="form-group">
                <label for="detalles">Detalles / Nota</label>
                <textarea
                  id="detalles"
                  class="form-control"
                  rows="2"
                  v-model="form.detalles"
                  placeholder="Ej: Aireación nocturna, turno 1, de 8:00 pm a 10:00 pm."
                ></textarea>
              </div>

              <!-- BOTONES -->
              <div class="text-right">
                <button
                  type="button"
                  class="btn btn-secondary mr-2"
                  data-dismiss="modal"
                >
                  Cerrar
                </button>

                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="isSaving || !localSiembraId"
                >
                  <span v-if="isSaving">
                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                  </span>
                  <span v-else>
                    {{ editando === 0 ? "Agregar configuración" : "Actualizar configuración" }}
                  </span>
                </button>
              </div>
            </form>

            <!-- LISTADO -->
            <hr />
            <h6 class="mt-3">Configuraciones registradas para esta siembra</h6>

            <div v-if="isLoading" class="text-center my-3">
              <i class="fas fa-spinner fa-spin"></i>
              <p class="mb-0">Cargando configuraciones...</p>
            </div>

            <table
              v-if="!isLoading"
              class="table table-sm table-bordered table-hover mt-2"
            >
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Recurso</th>
                  <th>Minutos hombre</th>
                  <th>Detalles</th>
                  <th class="text-center">Opciones</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="listadoConfigs.length === 0">
                  <td colspan="5" class="text-center text-muted">
                    No hay configuraciones registradas aún.
                  </td>
                </tr>

                <tr v-for="(item, index) in listadoConfigs" :key="item.id">
                  <td>{{ index + 1 }}</td>

                  <td>
                    <strong>{{ getRecursoNombre(item.recurso_id) }}</strong><br />
                    <small class="text-muted">
                      ID recurso: {{ item.recurso_id }}
                    </small>
                  </td>

                  <td>{{ item.minutos_hombre }}</td>

                  <td>{{ item.detalles }}</td>

                  <td class="text-center">
                    <button
                      type="button"
                      class="btn btn-success btn-sm mr-1"
                      @click="seleccionarParaEditar(item)"
                    >
                      <i class="fas fa-edit"></i>
                    </button>

                    <button
                      type="button"
                      class="btn btn-danger btn-sm"
                      @click="eliminar(item)"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>

      </div>
    </div>
  </div>
</template>


<script>
import { Form } from "vform";

export default {
  name: "AssignResources",

  props: {
    siembra_id: {
      type: [Number, String],
      default: null
    },
    siembraNombre: {
      type: String,
      default: ""
    }
  },

  data() {
    return {
      localSiembraId: null,
      siembraNombreActual: "",
      isLoading: false,
      isSaving: false,
      editando: 0,

      listadoRecursos: [],
      listadoConfigs: [],

      form: new Form({
        id: "",
        siembra_id: "",
        recurso_id: "",
        minutos_hombre: "",
        detalles: ""
      })
    };
  },

  methods: {
    loadData(siembraId, siembraNombre = "") {
      this.localSiembraId = siembraId;
      this.siembraNombreActual = siembraNombre;
      this.editando = 0;

      this.form.reset();
      this.form.siembra_id = this.localSiembraId;

      this.cargarRecursos();
      this.cargarConfiguraciones();
    },

    cargarRecursos() {
      axios
        .get("api/recursos")
        .then(({ data }) => {
          this.listadoRecursos = data || [];
        })
        .catch(() => {
          this.listadoRecursos = [];
        });
    },

    cargarConfiguraciones() {
      if (!this.localSiembraId) return;

      this.isLoading = true;

      axios
        .get("api/siembra-recurso-datos", {
          params: { siembra_id: this.localSiembraId }
        })
        .then(({ data }) => {
          this.listadoConfigs = Array.isArray(data)
            ? data
            : data.data || [];
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    getRecursoNombre(id) {
      const r = this.listadoRecursos.find((x) => Number(x.id) === Number(id));
      return r ? r.recurso : `ID ${id}`;
    },

    seleccionarParaEditar(item) {
      this.editando = 1;

      this.form.reset();
      this.form.fill({
        id: item.id,
        siembra_id: this.localSiembraId,
        recurso_id: item.recurso_id,
        minutos_hombre: item.minutos_hombre,
        detalles: item.detalles
      });
    },

    submit() {
      if (!this.localSiembraId) return;

      if (!this.form.recurso_id) {
        Swal.fire({
          icon: "warning",
          title: "Recurso requerido",
          text: "Debes seleccionar un recurso de aireación."
        });
        return;
      }

      this.isSaving = true;
      this.form.siembra_id = this.localSiembraId;

      if (this.editando === 0) {
        this.form
          .post("api/siembra-recurso-datos")
          .then(() => {
            Swal.fire({
              icon: "success",
              title: "Guardado",
              text: "La configuración ha sido registrada correctamente."
            });

            this.form.reset();
            this.form.siembra_id = this.localSiembraId;

            this.cargarConfiguraciones();
          })
          .finally(() => {
            this.isSaving = false;
          });
      } else {
        this.form
          .put(`api/siembra-recurso-datos/${this.form.id}`)
          .then(() => {
            Swal.fire({
              icon: "success",
              title: "Actualizado",
              text: "La configuración ha sido actualizada correctamente."
            });

            this.editando = 0;
            this.form.reset();
            this.form.siembra_id = this.localSiembraId;

            this.cargarConfiguraciones();
          })
          .finally(() => {
            this.isSaving = false;
          });
      }
    },

    eliminar(item) {
      Swal.fire({
        title: "¿Eliminar configuración?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#c7120c",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Eliminar",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .delete(`api/siembra-recurso-datos/${item.id}`)
            .then(() => {
              Swal.fire({
                icon: "success",
                title: "Eliminado",
                text: "La configuración ha sido eliminada correctamente."
              });

              this.cargarConfiguraciones();
            });
        }
      });
    }
  }
};
</script>

<style scoped>
.table-sm td,
.table-sm th {
  vertical-align: middle;
}

.modal-title small {
  font-weight: normal;
  font-size: 0.85rem;
  color: #6c757d;
}
</style>
