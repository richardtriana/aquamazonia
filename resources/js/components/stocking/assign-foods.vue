<template>
  <div
    class="modal fade"
    id="modalAssignFoods"
    tabindex="-1"
    role="dialog"
    aria-labelledby="assignFoodsLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header flex-column align-items-start">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <h3 class="modal-title mb-0" id="assignFoodsLabel">
              Alimentos permitidos por siembra
            </h3>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Cerrar"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <p class="mb-0 mt-1 small text-muted">
            Siembra:
            <strong>{{ nombreSiembraMostrado }}</strong>
          </p>
        </div>

        <div class="modal-body">
          <!-- FORM: ASIGNAR ALIMENTOS PERMITIDOS -->
          <form class="row mb-3" @submit.prevent="guardarAsignacion">
            <div class="form-group col-md-12">
              <label>Seleccionar alimentos permitidos</label>
              <v-select
                :options="listadoAlimentos"
                label="alimento"
                :reduce="(a) => a.id"
                v-model="alimentosSeleccionados"
                multiple
                placeholder="Elige uno o varios alimentos"
              />
              <small class="form-text text-muted">
                Estos serán los alimentos que podrás usar al registrar
                alimentación para esta siembra.
              </small>
            </div>

            <div class="form-group col-md-12 text-right">
              <button type="submit" class="btn btn-primary" :disabled="cargando">
                <span v-if="!cargando">Guardar asignación</span>
                <span v-else>Guardando...</span>
              </button>
            </div>
          </form>

          <hr />

          <!-- TABLA: ALIMENTOS ASIGNADOS + VALORES POR DEFECTO -->
          <h5>Alimentos actualmente asignados</h5>
          <table class="table table-sm table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Alimento</th>
                <th style="width: 120px;">Minutos hombre<br><small>(defecto)</small></th>
                <th style="width: 140px;">Conv. teórica<br><small>(defecto)</small></th>
                <th style="width: 220px;">Detalles<br><small>(defecto)</small></th>
                <th style="width: 130px;">Guardar datos</th>
                <th style="width: 90px;">Quitar</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="listadoAsignados.length === 0">
                <td colspan="7" class="text-center">
                  No hay alimentos asignados a esta siembra.
                </td>
              </tr>
              <tr
                v-for="(item, index) in listadoAsignados"
                :key="item.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ item.alimento }}</td>

                <!-- Minutos hombre -->
                <td>
                  <input
                    type="number"
                    step="any"
                    class="form-control form-control-sm"
                    v-model.number="defaultDatos[item.id].minutos_hombre"
                  >
                </td>

                <!-- Conversión alimenticia teórica -->
                <td>
                  <input
                    type="number"
                    step="any"
                    class="form-control form-control-sm"
                    v-model.number="defaultDatos[item.id].conv_alimenticia"
                  >
                </td>

                <!-- Detalles -->
                <td>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    v-model="defaultDatos[item.id].detalles"
                  >
                </td>

                <!-- Guardar datos por defecto -->
                <td class="text-center">
                  <button
                    class="btn btn-primary btn-sm"
                    @click="guardarDatosAlimento(item)"
                    :disabled="cargandoDatos[item.id] === true"
                  >
                    <span v-if="!cargandoDatos[item.id]">
                      <i class="fas fa-save"></i> Guardar
                    </span>
                    <span v-else>
                      <i class="fas fa-spinner fa-spin"></i>
                    </span>
                  </button>
                </td>

                <!-- Quitar asignación -->
                <td class="text-center">
                  <button
                    class="btn btn-danger btn-sm"
                    @click="eliminarAsignacion(item.id)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            data-dismiss="modal"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    siembra_id: {
      type: [Number, String],
      default: null,
    },
    // nombre que le mandamos desde siembras.vue
    siembraNombre: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      siembraIdLocal: null,
      siembraNombreLocal: "",      // por si lo recibimos en loadData()
      listadoAlimentos: [],        // catálogo completo (api/alimentos)
      alimentosSeleccionados: [],  // ids seleccionados en el v-select
      listadoAsignados: [],        // objetos alimento ya asignados (para la tabla)
      cargando: false,

      // mapa alimento_id -> { minutos_hombre, conv_alimenticia, detalles }
      defaultDatos: {},
      // para mostrar loading por alimento al guardar datos
      cargandoDatos: {},
    };
  },
  computed: {
    nombreSiembraMostrado() {
      // prioridad: prop -> local -> fallback ID
      if (this.siembraNombre && this.siembraNombre.trim() !== "") {
        return this.siembraNombre;
      }
      if (this.siembraNombreLocal && this.siembraNombreLocal.trim() !== "") {
        return this.siembraNombreLocal;
      }
      if (this.siembraIdLocal || this.siembra_id) {
        return `ID ${this.siembraIdLocal || this.siembra_id}`;
      }
      return "";
    },
  },
  methods: {
    // se llama desde siembras.vue al abrir el modal
    loadData(id, nombre = null) {
      this.siembraIdLocal = id;
      this.siembraNombreLocal = nombre || "";

      // primero cargo alimentos, luego asignación, luego datos por defecto
      this.obtenerAlimentos().then(() => {
        this.obtenerAsignados().then(() => {
          this.obtenerDatosPorDefecto();
        });
      });
    },

    obtenerAlimentos() {
      // DEVUELVO la promesa para poder encadenar
      return axios.get("api/alimentos").then((response) => {
        this.listadoAlimentos = response.data || [];
      });
    },

    obtenerAsignados() {
      if (!this.siembraIdLocal) return Promise.resolve();

      return axios
        .get("api/recursos-siembras", {
          params: {
            id_siembra: this.siembraIdLocal,
            tipo: "ALIMENTACION",
          },
        })
        .then((response) => {
          const data = response.data || {};
          // aquí vienen los IDs: [2,3,4]
          const ids = data.alimentos || [];

          // rellenamos el v-select con esos ids
          this.alimentosSeleccionados = ids;

          // y armamos la tabla mapeando contra el catálogo completo
          this.listadoAsignados = this.listadoAlimentos.filter((al) =>
            ids.includes(al.id)
          );

          // inicializamos defaultDatos para cada alimento asignado
          this.listadoAsignados.forEach((al) => {
            if (!this.defaultDatos[al.id]) {
              this.$set(this.defaultDatos, al.id, {
                minutos_hombre: "",
                conv_alimenticia: "",
                detalles: "",
              });
            }
          });
        });
    },

    // Carga los valores por defecto desde backend
    obtenerDatosPorDefecto() {
      if (!this.siembraIdLocal) return;

      axios
        .get(`api/siembra-alimento-datos/por-siembra/${this.siembraIdLocal}`)
        .then((response) => {
          const registros = response.data || [];

          registros.forEach((r) => {
            // si el alimento está asignado, rellenamos
            if (!this.defaultDatos[r.alimento_id]) {
              this.$set(this.defaultDatos, r.alimento_id, {
                minutos_hombre: "",
                conv_alimenticia: "",
                detalles: "",
              });
            }

            this.defaultDatos[r.alimento_id].minutos_hombre =
              r.minutos_hombre || "";
            this.defaultDatos[r.alimento_id].conv_alimenticia =
              r.conv_alimenticia || "";
            this.defaultDatos[r.alimento_id].detalles = r.detalles || "";
          });
        })
        .catch(() => {
          // si falla, simplemente dejamos los valores vacíos
        });
    },

    guardarAsignacion() {
      if (!this.siembraIdLocal) return;
      this.cargando = true;

      const data = {
        id_siembra: this.siembraIdLocal,
        alimentos: this.alimentosSeleccionados, // array de IDs
        tipo: "ALIMENTACION",
      };

      axios
        .post("api/recursos-siembras", data)
        .then(() => {
          Swal.fire(
            "Excelente!",
            "Los alimentos permitidos se actualizaron correctamente.",
            "success"
          );
          // volver a montar la tabla con los nombres correctos
          this.listadoAsignados = this.listadoAlimentos.filter((al) =>
            this.alimentosSeleccionados.includes(al.id)
          );

          // asegurar que todos los asignados tengan estructura en defaultDatos
          this.listadoAsignados.forEach((al) => {
            if (!this.defaultDatos[al.id]) {
              this.$set(this.defaultDatos, al.id, {
                minutos_hombre: "",
                conv_alimenticia: "",
                detalles: "",
              });
            }
          });
        })
        .finally(() => {
          this.cargando = false;
        });
    },

    // Guardar/actualizar los valores por defecto para un alimento específico
    guardarDatosAlimento(item) {
      if (!this.siembraIdLocal || !item || !item.id) return;

      const datos = this.defaultDatos[item.id] || {
        minutos_hombre: "",
        conv_alimenticia: "",
        detalles: "",
      };

      this.$set(this.cargandoDatos, item.id, true);

      axios
        .post("api/siembra-alimento-datos/guardar", {
          siembra_id: this.siembraIdLocal,
          alimento_id: item.id,
          minutos_hombre: datos.minutos_hombre,
          conv_alimenticia: datos.conv_alimenticia,
          detalles: datos.detalles,
        })
        .then(() => {
          Swal.fire(
            "Excelente!",
            "Valores por defecto guardados correctamente.",
            "success"
          );
        })
        .catch((error) => {
          console.log(error);
          Swal.fire(
            "Error",
            "No se pudieron guardar los valores por defecto.",
            "error"
          );
        })
        .finally(() => {
          this.$set(this.cargandoDatos, item.id, false);
        });
    },

    // no hay DELETE en backend específico para siembra-alimento-datos,
    // así que al quitar un alimento, simplemente dejamos de usarlo
    eliminarAsignacion(idAlimento) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "Este alimento dejará de estar disponible para esta siembra.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#c7120c",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Aceptar",
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          this.alimentosSeleccionados = this.alimentosSeleccionados.filter(
            (id) => id !== idAlimento
          );
          // actualizamos asignación en backend
          this.guardarAsignacion();
          // opcional: podemos limpiar sus valores por defecto localmente
          // delete this.defaultDatos[idAlimento]
        }
      });
    },
  },
};
</script>
