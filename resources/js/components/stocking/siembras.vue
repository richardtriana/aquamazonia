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
          <div class="card-header">Gestión de Siembras</div>

          <div class="card-body">
            <!-- BOTÓN NUEVA SIEMBRA -->
            <div class="row mb-1">
              <div class="col-12 text-right">
                <button class="btn btn-success" @click="anadirItem(), (id = 0)">
                  Nueva siembra
                </button>
              </div>
            </div>

            <!-- FILTROS -->
            <div class="row mb-3">
              <div class="col-md-12">
                <h2>Filtrar por:</h2>
                <form class="row align-items-end">
                  <!-- Estado de siembra -->
                  <div class="form-group col-md-2">
                    <label for="estado_siembra">Estado de siembra</label>
                    <select
                      name="estado_siembra"
                      id="estado_siembra"
                      class="custom-select"
                      v-model="estado_siembra"
                    >
                      <option value="-1">Todas</option>
                      <option value="1">Activas</option>
                      <option value="0">Inactivas</option>
                    </select>
                  </div>

                  <!-- Siembras activas / inactivas / todas -->
                  <div class="form-group col-md-3">
                    <label for="siembra_activa">
                      Siembras
                      {{
                        estado_siembra == "-1"
                          ? ""
                          : estado_siembra == 0
                          ? "inactivas"
                          : "activas"
                      }}
                      :
                    </label>
                    <template v-if="estado_siembra != '-1'">
                      <v-select
                        :options="filteredItems"
                        label="nombre_siembra"
                        :reduce="(siembra) => siembra.id"
                        v-model="f_siembra"
                        placeholder="Todas las siembras"
                      />
                    </template>
                    <template v-else>
                      <v-select
                        :options="listadoSiembras"
                        label="nombre_siembra"
                        :reduce="(siembra) => siembra.id"
                        v-model="f_siembra"
                        placeholder="Todas las siembras"
                      />
                    </template>
                  </div>

                  <!-- Fase -->
                  <div class="form-group col-md-2">
                    <label for="fase_select">Fase</label>
                    <select
                      id="fase_select"
                      class="custom-select"
                      v-model="search_phase"
                    >
                      <option value="">Todas</option>
                      <option
                        v-for="phase in listPhases"
                        :key="phase.id"
                        :value="phase.id"
                      >
                        {{ phase.phase }}
                      </option>
                    </select>
                  </div>

                  <!-- Tipo de siembra -->
                  <div class="form-group col-md-2">
                    <label for="search_type">Tipo de siembra</label>
                    <select
                      name="search_type"
                      id="search_type"
                      class="custom-select w-100"
                      v-model="search_type"
                    >
                      <option value="">Todas</option>
                      <option value="Monocultivo">Monocultivo</option>
                      <option value="Policultivo">Policultivo</option>
                    </select>
                  </div>

                  <!-- Contenedor -->
                  <div class="form-group col-md-2">
                    <label for="contenedor_select">Contenedor</label>
                    <v-select
                      id="contenedor_select"
                      :options="listadoContenedores"
                      label="contenedor"
                      :reduce="(contenedor) => contenedor.id"
                      v-model="contenedor_id"
                      placeholder="Todos"
                    />
                  </div>

                  <!-- ESPECIE (NUEVO FILTRO) -->
                  <div class="form-group col-md-2">
                    <label for="especie_select">Especie</label>
                    <v-select
                      id="especie_select"
                      :options="listadoEspecies"
                      label="especie"
                      :reduce="(especie) => especie.id"
                      v-model="especie_id"
                      placeholder="Todas"
                    />
                  </div>

                  <!-- Resultados por página -->
                  <div class="form-group col-md-2">
                    <label for="search_nro_results">
                      Mostrar {{ search_nro_results }} por página
                    </label>
                    <input
                      id="search_nro_results"
                      type="number"
                      min="1"
                      class="form-control"
                      v-model="search_nro_results"
                    />
                  </div>

                  <!-- Botón buscar -->
                  <div class="form-group col-md-1">
                    <button
                      type="button"
                      class="btn btn-primary rounded-circle mt-4"
                      @click="listar()"
                    >
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- SPINNER DE CARGA (AHORA USAMOS EL GLOBAL) -->
            <!-- Esta sección se elimina porque usamos el spinner global -->

            <!-- TABLA -->
            <div v-if="!isLoading">
              <table
                class="table table-bordered table-striped table-sm table-sticky"
              >
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th>
                      Nombre <br />
                      siembra
                    </th>
                    <th>Estanque</th>
                    <th class="text-center d-sm-none d-none d-md-block">
                      <h5>Especie</h5>

                      <div
                        class="py-3"
                        style="width: max-content; margin: auto"
                      >
                        <span style="width: 80px; display: inline-block">
                          Especie
                        </span>
                        <span style="width: 80px; display: inline-block">
                          Lote
                        </span>
                        <span style="width: 80px; display: inline-block">
                          Cantidad
                        </span>
                        <span style="width: 60px; display: inline-block">
                          Peso gr
                        </span>
                      </div>
                    </th>
                    <th>Inicio siembra</th>
                    <th>Fecha Alimentación</th>
                    <th>Ingreso</th>
                    <th>Config. alimentos</th>
                    <th>Config. aireación</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                    <th>Finalizar</th>
                    <th>
                      Inicio - fin de <br />
                      descanso estanque
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(siembra, index) in (listado.data || [])"
                    :key="siembra.id"
                  >
                    <!-- Número de fila calculado según la página actual -->
                    <th scope="row">
                      {{
                        (listado.current_page - 1) * listado.per_page + index + 1
                      }}
                    </th>
                    <td>
                      {{ siembra.nombre_siembra }} <br />
                      <small v-if="siembra.phase_id">
                        Fase: <b>{{ siembra.phase.phase }}</b>
                      </small>
                      <br />
                      <small v-if="siembra.tipo">
                        Tipo: <b>{{ siembra.tipo }}</b>
                      </small>
                    </td>
                    <td v-text="siembra.contenedor"></td>
                    <td class="d-sm-none d-none d-md-block">
                      <div
                        v-for="pez in siembra.peces"
                        :key="pez.id"
                        class="border-0"
                        style="width: max-content; margin: auto"
                      >
                        <span
                          class="nav-item border-bottom"
                          style="width: 80px; display: inline-block"
                        >
                          {{ pez.especie.especie }}
                        </span>
                        <span
                          class="nav-item border-bottom"
                          style="
                            width: 80px;
                            text-align: center;
                            display: inline-block;
                          "
                        >
                          {{ pez.lote }}
                        </span>
                        <span
                          class="nav-item border-bottom"
                          style="
                            width: 80px;
                            text-align: center;
                            display: inline-block;
                          "
                        >
                          {{ Math.floor(pez.cant_actual) }}
                        </span>
                        <span
                          class="nav-item border-bottom"
                          style="
                            width: 60px;
                            display: inline-block;
                            text-align: center;
                          "
                        >
                          {{ pez.peso_actual + "Gr" }}
                        </span>
                      </div>
                    </td>
                    <td v-text="siembra.fecha_inicio"></td>

                    <!-- FECHA ALIMENTACIÓN: VERDE = hoy, AMARILLO = ayer, ROJO = más antiguo -->
                    <td>
                      <span
                        v-if="siembra.fecha_alimento"
                        :class="[
                          'badge',
                          getFechaBadgeClass(siembra.fecha_alimento),
                        ]"
                      >
                        {{ siembra.fecha_alimento }}
                      </span>
                      <span v-else class="badge bg-danger">
                        Sin registro
                      </span>
                      <br />
                      <button
                        type="button"
                        class="btn btn-success btn-sm mt-1"
                        @click="abrirCrear(siembra)"
                      >
                        Añadir Alimentos
                      </button>
                    </td>

                    <!-- INGRESOS / HISTORIAL -->
                    <td>
                      <button
                        class="btn btn-primary"
                        @click="abrirIngreso(siembra)"
                      >
                        <i class="fas fa-list-ul"></i>
                      </button>
                    </td>

                    <!-- CONFIGURAR ALIMENTOS PERMITIDOS -->
                    <td>
                      <button
                        class="btn btn-info"
                        @click="abrirAsignarAlimentos(siembra)"
                      >
                        <i class="fas fa-seedling"></i> Asignar
                      </button>
                    </td>

                    <!-- CONFIGURAR RECURSOS (AIREACIÓN MECÁNICA) -->
                    <td>
                      <button
                        class="btn btn-warning"
                        @click="abrirAsignarRecursos(siembra)"
                      >
                        <i class="fas fa-wind"></i> Aireación
                      </button>
                    </td>

                    <!-- EDITAR / ELIMINAR / FINALIZAR -->
                    <td>
                      <button
                        class="btn btn-success"
                        @click="editarSiembra(siembra)"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                    </td>
                    <td>
                      <button
                        class="btn btn-danger"
                        @click="eliminarSiembra(siembra.id)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                    <td>
                      <button
                        v-if="siembra.estado == 1"
                        class="btn btn-danger text-white"
                        data-toggle="tooltip"
                        title="Finalizar siembra"
                        data-placement="top"
                        @click="finalizarSiembra(siembra.id)"
                      >
                        <i class="fas fa-power-off"></i> Desactivar
                      </button>
                    </td>
                    <td>
                      {{ siembra.ini_descanso }} - <br />
                      {{ siembra.fin_descanso }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <pagination
                :align="'center'"
                :data="listado"
                :limit="2"
                @pagination-change-page="listar"
              >
                <span slot="prev-nav"><i class="fas fa-arrow-left"></i></span>
                <span slot="next-nav"><i class="fas fa-arrow-right"></i></span>
              </pagination>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALES -->
    <create-edit-stocking
      :listado-contenedores="listadoContenedores"
      ref="createEditStocking"
      :listado-especies="listadoEspecies"
      :id="id"
    />
    <dialog-fish-food
      :siembra_id="id"
      :siembra-nombre="siembraNombreActual"
      ref="dialogFishFood"
    />
    <dialog-registers :siembra_id="id" ref="dialogRegister" />
    <finish-stocking
      ref="finishStocking"
      :id.sync="id"
      @list-stocking="listar()"
    />
    <!-- NUEVO MODAL: ASIGNAR ALIMENTOS PERMITIDOS -->
    <assign-foods
      :siembra_id="id"
      :siembra-nombre="siembraNombreActual"
      ref="assignFoods"
    />
    <!-- NUEVO MODAL: ASIGNAR RECURSOS (AIREACIÓN MECÁNICA) -->
    <assign-resources
      :siembra_id="id"
      :siembra-nombre="siembraNombreActual"
      ref="assignResources"
    />
  </div>
</template>

<script>
import finishStocking from "./finish-stocking.vue";
import createEditStocking from "./create-edit-stocking.vue";
import DialogRegisters from "./dialog-registers.vue";
import DialogFishFood from "./dialog-fish-food.vue";
import AssignFoods from "./assign-foods.vue";
import AssignResources from "./assign-resources.vue";
import { Form } from "vform";

export default {
  components: {
    finishStocking,
    createEditStocking,
    DialogRegisters,
    DialogFishFood,
    AssignFoods,
    AssignResources,
  },
  data() {
    return {
      id: 0,
      siembraNombreActual: "", // nombre/estanque que se está trabajando
      isLoading: false, // spinner de carga global

      form: new Form({
        nombre_siembra: "",
        id_siembra: "",
      }),
      listPhases: [],
      fechaActual: "", // 'YYYY-MM-DD'
      listadoEspecies: [],
      listadoContenedores: [],
      listado: {},
      listadoSiembras: [],
      listadoRN: [],
      lotes: [],
      estados: [],
      tipoRegistro: [],

      // Filtros siembras
      estado_siembra: "1", // por defecto Activas
      f_siembra: "",
      contenedor_id: "",
      search_phase: "",
      search_type: "",
      especie_id: "", // NUEVO: filtro por especie
      search_nro_results: "15",
    };
  },
  computed: {
    filteredItems() {
      return this.listadoSiembras.filter(
        (item) => String(item.estado) === String(this.estado_siembra)
      );
    },
  },
  methods: {
    // Calcula la clase del badge según la diferencia en días
    getFechaBadgeClass(fechaAlimento) {
      // Si falta alguna fecha, lo consideramos como antiguo => rojo
      if (!fechaAlimento || !this.fechaActual) {
        return "bg-danger";
      }

      // Parseo manual 'YYYY-MM-DD' para evitar problemas de zona horaria
      const [aA, mA, dA] = fechaAlimento.split("-").map(Number);
      const [aH, mH, dH] = this.fechaActual.split("-").map(Number);

      const dateAlim = new Date(Date.UTC(aA, mA - 1, dA));
      const dateHoy = new Date(Date.UTC(aH, mH - 1, dH));

      const diffMs = dateHoy - dateAlim;
      const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

      if (diffDays === 0) {
        // Hoy
        return "bg-success";
      } else if (diffDays === 1) {
        // Ayer
        return "bg-warning";
      } else if (diffDays > 1) {
        // Más antiguo
        return "bg-danger";
      }

      // Si por alguna razón la fecha es futura, color neutro
      return "bg-secondary";
    },

    listarEspecies() {
      let me = this;
      axios.get("api/especies").then(function (response) {
        me.listadoEspecies = response.data;
      });
    },
    listarContenedores() {
      let me = this;
      axios.get("api/contenedores").then(function (response) {
        me.listadoContenedores = response.data;
      });
    },
    listarPhases() {
      let me = this;
      axios.get("api/phases").then(function (response) {
        if (response.data && response.data.phases) {
          me.listPhases = response.data.phases.data;
        }
      });
    },

    anadirItem() {
      $("#modalSiembra").modal("show");
      this.listarEspecies();
      this.listarContenedores();
      this.listadoItems = [];
    },
    editarSiembra(siembra) {
      this.id = siembra.id;
      this.siembraNombreActual =
        siembra.nombre_siembra || siembra.contenedor || `ID ${siembra.id}`;

      $("#modalSiembra").modal("show");
      this.listarContenedores();
      this.$refs.createEditStocking.editarSiembra(siembra);
      this.listarEspecies();
    },

    abrirCrear(siembra) {
      this.id = siembra.id;
      this.siembraNombreActual =
        siembra.nombre_siembra || siembra.contenedor || `ID ${siembra.id}`;

      $("#modalRecursos").modal("show");
      this.$refs.dialogFishFood.listarRecursosAlimentos(siembra.id);
    },

    abrirIngreso(siembra) {
      this.id = siembra.id;
      this.siembraNombreActual =
        siembra.nombre_siembra || siembra.contenedor || `ID ${siembra.id}`;

      $("#modalIngreso").modal("show");
      this.$refs.dialogRegister.listarRegistros(siembra.id);
    },

    // abrir modal de asignación de alimentos permitidos
    abrirAsignarAlimentos(siembra) {
      this.id = siembra.id;
      this.siembraNombreActual =
        siembra.nombre_siembra || siembra.contenedor || `ID ${siembra.id}`;

      $("#modalAssignFoods").modal("show");
      this.$refs.assignFoods.loadData(siembra.id, this.siembraNombreActual);
    },

    // abrir modal de asignación de RECURSOS (Aireación Mecánica)
    abrirAsignarRecursos(siembra) {
      this.id = siembra.id;
      this.siembraNombreActual =
        siembra.nombre_siembra || siembra.contenedor || `ID ${siembra.id}`;

      $("#modalAssignResources").modal("show");
      if (this.$refs.assignResources && this.$refs.assignResources.loadData) {
        this.$refs.assignResources.loadData(
          siembra.id,
          this.siembraNombreActual
        );
      }
    },

    listar(page = 1) {
      let me = this;

      const data = {
        estado_siembra: me.estado_siembra,
        id_siembra: me.f_siembra === "" ? "-1" : me.f_siembra,
        contenedor_id: me.contenedor_id === "" ? "-1" : me.contenedor_id,
        especie_id: me.especie_id === "" ? "-1" : me.especie_id, // NUEVO: filtro por especie
        phase: this.search_phase === "" ? "-1" : this.search_phase,
        page: page,
        type: this.search_type,
        nro_results: this.search_nro_results,
      };

      me.isLoading = true;

      axios
        .get("api/siembras", { params: data })
        .then(function (response) {
          me.listado = response.data.siembra;
          me.fechaActual = response.data.fecha_actual;
        })
        .catch((error) => {
          console.error("Error al cargar siembras:", error);
          if (typeof Swal !== "undefined") {
            Swal.fire(
              "Error",
              "Ocurrió un problema cargando las siembras",
              "error"
            );
          }
        })
        .finally(() => {
          me.isLoading = false;
        });
    },

    listarSiembras() {
      let me = this;
      axios.get("api/siembras/listado").then(function (response) {
        me.listadoSiembras = response.data;
      });
    },

    listarLotes() {
      let me = this;
      axios.get("api/siembras/listado-lotes").then(function (response) {
        me.lotes = response.data.lotes;
      });
    },

    finalizarSiembra(id) {
      $("#modalFinalizar").modal("show");
      this.id = id;
    },

    activarSiembra(id) {
      const data = {
        id: this.id,
        estado: 1,
      };
      axios.post(`api/actualizarEstado/${id}`, data).then(({ response }) => {
        Swal.fire({
          title: "Exitoso",
          text: "El estado se ha cambiado correctamente",
          icon: "success",
        });
        this.listar();
      });
    },

    eliminarSiembra(index) {
      let me = this;
      Swal.fire({
        title: "Estás seguro?",
        text: "Una vez eliminado, no se puede recuperar este registro",
        icon: "warning",
        buttons: ["Cancelar", "Aceptar"],
        dangerMode: true,
      }).then((willDelete) => {
        if (willDelete) {
          axios.delete("api/siembras/" + index).then(() => {
            me.listar();
          });
        }
      });
    },
  },
  mounted() {
    this.listar(1, "");
    this.listarSiembras();
    this.listarContenedores();
    this.listarLotes();
    this.listarPhases();
    this.listarEspecies(); // Cargar especies para el filtro
    this.estados[0] = "Inactivo";
    this.estados[1] = "Activo";
    this.estados[2] = "Ocupado";
    this.estados[3] = "Descanso";
  },
};
</script>

<style scoped>
/* SPINNER GLOBAL */
.global-spinner {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.9);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
}

.global-spinner-inner {
  text-align: center;
}

.global-spinner-ring {
  display: inline-block;
  width: 80px;
  height: 80px;
  border: 6px solid #f3f3f3;
  border-top: 6px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.global-spinner-logo {
  font-size: 24px;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 10px;
}

.global-spinner-text {
  font-size: 16px;
  color: #7f8c8d;
}

/* Estilos para la tabla responsiva */
.table-sticky thead th {
  position: sticky;
  top: 0;
  background-color: #343a40;
  color: white;
  z-index: 10;
}

/* Ajustes para la tabla en dispositivos móviles */
@media (max-width: 768px) {
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  
  .table th, .table td {
    white-space: nowrap;
  }
}

/* Mejoras para los filtros */
.form-group {
  margin-bottom: 0.75rem;
}

.custom-select, .form-control {
  border-radius: 0.25rem;
  font-size: 0.9rem;
}

.btn-rounded {
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Estilos para los badges de fecha */
.badge {
  font-size: 0.8rem;
  padding: 0.4em 0.8em;
}

.bg-success {
  background-color: #28a745 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #212529;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-secondary {
  background-color: #6c757d !important;
}

/* Estilos para los botones de acción */
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn i {
  margin-right: 0.25rem;
}
</style>