<template>
  <!-- Modal añadir alimentos a siembras -->
  <div
    class="modal fade"
    id="modalRecursos"
    tabindex="-1"
    role="dialog"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header flex-column align-items-start">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <h3 class="modal-title mb-0" id="exampleModalLabel">
              Alimentos por siembra
            </h3>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
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
          <form class="row" id="editarAlimentacion">
            <div class="form-group col-md-3">
              <label for="fecha_registro_alimentacion">Fecha (*)</label>
              <input
                type="date"
                class="form-control"
                id="fecha_registro_alimentacion"
                aria-describedby="fecha_registro_alimentacion"
                placeholder="fecha_registro_alimentacion"
                v-model="form.fecha_ra"
                required
              />
            </div>

            <div class="form-group col-md-3">
              <label for="alimento">Alimento (*)</label>
              <select
                class="form-control custom-select"
                id="alimento"
                v-model="form.id_alimento"
                required
                @change="onAlimentoChange"
              >
                <option value="">--Seleccionar--</option>
                <option
                  v-for="(alimento, index) in listadoAlimentos"
                  :key="index"
                  :value="alimento.id"
                >
                  {{ alimento.alimento }}
                </option>
              </select>
              <small v-if="usoAlimentosRestringidos" class="form-text text-muted">
                Mostrando sólo alimentos asignados a esta siembra.
              </small>
            </div>

            <div class="form-group col-md-6">
              <label for="detalles">Detalles</label>
              <textarea
                class="form-control"
                id="detalles"
                aria-describedby="detalles"
                placeholder="Detalles"
                v-model="form.detalles"
              ></textarea>
            </div>

            <div class="form-group col-md-3">
              <label for="minutos_hombre">Minutos hombre</label>
              <input
                type="number"
                class="form-control"
                step="any"
                id="minutos_hombre"
                aria-describedby="minutos_hombre"
                placeholder="Minutos hombre"
                v-model="form.minutos_hombre"
              />
            </div>

            <div class="form-group col-md-3">
              <label for="kg_manana">Kg Mañana</label>
              <input
                type="number"
                step="any"
                class="form-control"
                id="kg_manana"
                aria-describedby="cant_manana"
                placeholder="Kg Mañana"
                v-model="form.cant_manana"
              />
            </div>

            <div class="form-group col-md-3">
              <label for="kg_tarde">Kg tarde</label>
              <input
                type="number"
                step="any"
                class="form-control"
                id="kg_tarde"
                aria-describedby="cant_tarde"
                placeholder="Kg tarde"
                v-model="form.cant_tarde"
              />
            </div>

            <div class="form-group col-md-4">
              <label for="conv_alimenticia">
                Conversión alimenticia teórica
              </label>
              <input
                type="number"
                step="any"
                class="form-control"
                id="conv_alimenticia"
                placeholder="Conversión alimenticia teórica"
                v-model="form.conv_alimenticia"
              />
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-primary"
                @click="guardarRecursos"
              >
                <span>{{ editandoAlimento === 0 ? "Guardar" : "Actualizar" }}</span>
              </button>
            </div>
          </form>

          <div class="container">
            <table class="table table-sm table-hover table-responsive table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>
                    Tipo de <br />
                    Actividad
                  </th>
                  <th>Fecha</th>
                  <th><br />Alimento</th>
                  <th>Cantidad<br />Mañana</th>
                  <th>Cantidad<br />Tarde</th>
                  <th width="15%">Detalles</th>
                  <th>Editar</th>
                  <th>Eliminar</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in listadoAlimentosSiembra"
                  :key="index"
                >
                  <td>{{ index + 1 }}</td>
                  <td>{{ item.actividad }}</td>
                  <td>{{ item.fecha_ra }}</td>
                  <td>{{ item.alimento }}</td>
                  <td>
                    {{
                      item.cant_manana == null ? "-" : item.cant_manana + " kg"
                    }}
                  </td>
                  <td>
                    {{
                      item.cant_tarde == null ? "-" : item.cant_tarde + " kg"
                    }}
                  </td>
                  <td>{{ item.detalles }}</td>
                  <td>
                    <button
                      class="btn btn-success"
                      @click="editarAlimento(item)"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                  </td>
                  <td>
                    <button
                      class="btn btn-danger"
                      @click="eliminarAlimento(item.id_registro)"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="!listadoAlimentosSiembra.length">
                  <td colspan="9" class="text-center">
                    No hay registros de alimentación para esta siembra.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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
import { Form } from "vform";

export default {
  props: {
    siembra_id: {
      type: [Number, String],
      default: null,
    },
    siembraNombre: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      editandoAlimento: 0,
      form: new Form({
        tipo_actividad: 1,
        fecha_ra: "",
        id_alimento: "",
        id_registro: "",
        detalles: "",
        minutos_hombre: "",
        cant_manana: "",
        cant_tarde: "",
        conv_alimenticia: "",
        id_siembra: "",
      }),
      // catálogo completo
      allAlimentos: [],
      // catálogo filtrado por asignación
      listadoAlimentos: [],
      usoAlimentosRestringidos: false,
      // histórico alimentación
      listadoAlimentosSiembra: [],
    };
  },
  computed: {
    nombreSiembraMostrado() {
      if (this.siembraNombre && this.siembraNombre.trim() !== "") {
        return this.siembraNombre;
      }
      if (this.siembra_id) {
        return `ID ${this.siembra_id}`;
      }
      return "";
    },
  },
  watch: {
    // Cada vez que cambie la siembra en el padre, recargamos datos
    siembra_id: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          // empezamos en modo "nuevo"
          this.resetForm();
          this.form.id_siembra = newVal;
          this.listarRecursosAlimentos(newVal);
          this.cargarAlimentosPorSiembra(newVal);
        }
      },
    },
  },
  methods: {
    eliminarAlimento(idRegistro) {
      let me = this;
      Swal.fire({
        title: "Estás seguro?",
        text: "Una vez eliminado, no se puede recuperar este registro",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#c7120c",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Aceptar!",
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .delete("api/recursos-necesarios/" + idRegistro)
            .then(() => {
              me.listarRecursosAlimentos(me.siembra_id);
            });
        }
      });
    },

    guardarRecursos() {
      this.form.id_siembra = this.siembra_id;

      const payload = {
        tipo_actividad: this.form.tipo_actividad,
        fecha_ra: this.form.fecha_ra,
        id_alimento: this.form.id_alimento,
        id_registro: this.form.id_registro,
        detalles: this.form.detalles,
        minutos_hombre: this.form.minutos_hombre,
        cant_manana: this.form.cant_manana,
        cant_tarde: this.form.cant_tarde,
        conv_alimenticia: this.form.conv_alimenticia,
        id_siembra: this.form.id_siembra,
        tipo: "ALIMENTACION",
        descripcion: "ALIMENTACION SIEMBRAS",
      };

      if (this.editandoAlimento === 0) {
        axios.post("api/recursos-necesarios", payload).then(() => {
          Swal.fire(
            "Excelente!",
            "Los datos se guardaron correctamente!",
            "success"
          );
          this.listarRecursosAlimentos(this.siembra_id);
          this.resetForm();
          // después de resetear, volvemos a aplicar lógica de alimentos
          this.cargarAlimentosPorSiembra(this.siembra_id);
        });
      } else {
        axios
          .put("api/recursos-necesarios/" + this.form.id_registro, payload)
          .then(() => {
            Swal.fire(
              "Excelente!",
              "Los datos se actualizaron correctamente!",
              "success"
            );
            this.listarRecursosAlimentos(this.siembra_id);
            this.editandoAlimento = 0;
            this.resetForm();
            this.cargarAlimentosPorSiembra(this.siembra_id);
          });
      }
    },

    listarRecursosAlimentos(id) {
      axios.post(`api/siembras-alimentacion/${id}`).then((response) => {
        this.listadoAlimentosSiembra = response.data.recursosNecesarios || [];
      });
    },

    editarAlimento(objeto) {
      this.editandoAlimento = 1;
      this.form.fill(objeto);
      this.form.id_siembra = this.siembra_id;
      // en edición NO se tocan defaults de siembra_alimento_datos
    },

    // Carga TODO el catálogo
    listarAlimentos() {
      axios.get("api/alimentos").then((response) => {
        this.allAlimentos = response.data || [];

        if (this.siembra_id) {
          // si ya hay siembra seleccionada, filtramos por ella
          this.cargarAlimentosPorSiembra(this.siembra_id);
        } else {
          // sin siembra, mostramos catálogo completo
          this.listadoAlimentos = this.allAlimentos;
          this.aplicarDefaultsIniciales();
        }
      });
    },

    // Filtra alimentos según asignación de /api/recursos-siembras
    cargarAlimentosPorSiembra(id) {
      if (!id) {
        this.listadoAlimentos = this.allAlimentos;
        this.usoAlimentosRestringidos = false;
        this.aplicarDefaultsIniciales();
        return;
      }

      axios
        .get("api/recursos-siembras", {
          params: {
            id_siembra: id,
            tipo: "ALIMENTACION",
          },
        })
        .then((response) => {
          const data = response.data || {};
          const ids = data.alimentos || [];

          if (!ids.length) {
            // si la siembra NO tiene alimentos asignados,
            // mostramos todo el catálogo
            this.listadoAlimentos = this.allAlimentos;
            this.usoAlimentosRestringidos = false;
          } else {
            // filtramos sólo los alimentos asignados a la siembra
            this.listadoAlimentos = this.allAlimentos.filter((a) =>
              ids.includes(a.id)
            );
            this.usoAlimentosRestringidos = true;
          }

          // 👉 aquí ya tenemos listadoAlimentos correcto,
          // aplicamos fecha actual y PRIMER alimento por defecto
          this.aplicarDefaultsIniciales();
        })
        .catch(() => {
          this.listadoAlimentos = this.allAlimentos;
          this.usoAlimentosRestringidos = false;
          this.aplicarDefaultsIniciales();
        });
    },

    aplicarDefaultsIniciales() {
      // si estamos editando un registro, NO tocar nada
      if (this.editandoAlimento === 1) return;

      // Fecha actual por defecto si está vacía
      if (!this.form.fecha_ra) {
        const hoy = new Date();
        const mes = String(hoy.getMonth() + 1).padStart(2, "0");
        const dia = String(hoy.getDate()).padStart(2, "0");
        this.form.fecha_ra = `${hoy.getFullYear()}-${mes}-${dia}`;
      }

      // Primer alimento de la lista si no hay uno ya seleccionado
      if (!this.form.id_alimento && this.listadoAlimentos.length > 0) {
        this.form.id_alimento = this.listadoAlimentos[0].id;
      }

      // Si ya tenemos siembra y alimento, traemos los datos por defecto
      if (this.form.id_siembra && this.form.id_alimento) {
        this.onAlimentoChange();
      }
    },

    onAlimentoChange() {
      // siembra o alimento no definidos → no hacemos nada
      if (!this.form.id_siembra || !this.form.id_alimento) {
        return;
      }

      // si estamos editando un registro existente, respetamos lo que ya tiene
      if (this.editandoAlimento === 1) {
        return;
      }

      axios
        .get("api/siembra-alimento-datos/buscar", {
          params: {
            siembra_id: this.form.id_siembra,
            alimento_id: this.form.id_alimento,
          },
        })
        .then((response) => {
          const data = response.data || {};

          this.form.minutos_hombre =
            data.minutos_hombre !== null && data.minutos_hombre !== undefined
              ? data.minutos_hombre
              : "";

          this.form.conv_alimenticia =
            data.conv_alimenticia !== null && data.conv_alimenticia !== undefined
              ? data.conv_alimenticia
              : "";

          this.form.detalles =
            data.detalles !== null && data.detalles !== undefined
              ? data.detalles
              : "";
        })
        .catch(() => {
          this.form.minutos_hombre = this.form.minutos_hombre || "";
          this.form.conv_alimenticia = this.form.conv_alimenticia || "";
          this.form.detalles = this.form.detalles || "";
        });
    },

    resetForm() {
      this.form.reset();
      this.editandoAlimento = 0;
      this.form.tipo_actividad = 1;

      // volver a setear la siembra actual
      this.form.id_siembra = this.siembra_id || "";

      // fecha actual por defecto
      const hoy = new Date();
      const mes = String(hoy.getMonth() + 1).padStart(2, "0");
      const dia = String(hoy.getDate()).padStart(2, "0");
      this.form.fecha_ra = `${hoy.getFullYear()}-${mes}-${dia}`;
    },
  },
  mounted() {
    // cargar catálogo una vez
    this.listarAlimentos();
    // historial de la siembra actual (si ya viene seteada)
    if (this.siembra_id) {
      this.listarRecursosAlimentos(this.siembra_id);
    }
  },
};
</script>
