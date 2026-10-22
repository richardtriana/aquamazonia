<template>
  <div
    class="modal fade"
    id="modalSiembra"
    tabindex="-1"
    data-backdrop="static"
    role="dialog"
    aria-labelledby="modalSiembraLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Crear siembra</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <div class="container row">

            <div class="form-group col-md-4">
              <label>Contenedor</label>
              <select
                v-if="!id"
                v-model="form.id_contenedor"
                class="form-control"
              >
                <option
                  v-for="c in listadoContenedores"
                  :key="c.id"
                  v-if="c.estado == 1"
                  :value="c.id"
                >
                  {{ c.contenedor }}
                </option>
              </select>

              <input
                v-else
                type="text"
                class="form-control"
                disabled
                :value="form.contenedor"
              />
            </div>

            <div class="form-group col-md-4">
              <label>Nombre de Siembra</label>
              <input
                class="form-control"
                v-model="form.nombre_siembra"
              />
            </div>

            <div class="form-group col-md-4">
              <label>Fecha Inicio</label>
              <input
                type="date"
                class="form-control"
                v-model="form.fecha_inicio"
                :disabled="id ? true : false"
              />
            </div>

            <div class="form-group col-md-4">
              <label>Fase</label>
              <select v-model="form.phase_id" class="form-control">
                <option
                  v-for="p in phaseListing.data"
                  :key="p.id"
                  :value="p.id"
                >
                  {{ p.phase }}
                </option>
              </select>
            </div>

            <div class="form-group col-md-4">
              <label>Tipo de cultivo</label>
              <select v-model="form.tipo" class="form-control">
                <option value="Monocultivo">Monocultivo</option>
                <option value="Policultivo">Policultivo</option>
              </select>
            </div>

          </div>

          <!-- MODAL HIJO -->
          <assign-resources-ini
            ref="modalRecursosIni"
            :recursos-iniciales="recursosIniciales"
            @update-list="actualizarRecursosIniciales"
          ></assign-resources-ini>

          <!-- TABLA DE ESPECIES -->
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Especie</th>
                <th>Lote</th>
                <th>Cantidad</th>
                <th>Peso (gr)</th>
                <th></th>
              </tr>
            </thead>

            <tbody>

              <tr>
                <td></td>

                <td>
                  <select v-model="newEspecie" class="form-control">
                    <option
                      v-for="e in listadoEspecies"
                      :key="e.id"
                      :value="e.id"
                    >
                      {{ e.especie }}
                    </option>
                  </select>
                </td>

                <td><input class="form-control" v-model="newLote" /></td>

                <td><input type="number" class="form-control" v-model="newCantidad" /></td>

                <td>
                  <input type="number" class="form-control" v-model="newPeso" />
                  <span style="position:absolute;margin-left:-25px;margin-top:-28px;color:#bbb">
                    Gr
                  </span>
                </td>

                <td>
                  <button class="btn btn-success" @click="anadirEspecie">
                    <i class="fas fa-plus"></i>
                  </button>
                </td>
              </tr>

              <tr v-for="(item, index) in listadoItems" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ nombresEspecies[item.id_especie] }}</td>
                <td>{{ item.lote }}</td>
                <td>{{ item.cantidad }}</td>
                <td>{{ item.peso_inicial }}</td>

                <td>
                  <button class="btn btn-danger" @click="removeItem(item.id_especie)">X</button>
                </td>
              </tr>

            </tbody>
          </table>

        </div>

        <div class="modal-footer">

          <!-- BOTÓN REGISTRAR RECURSOS INICIALES ABAJO -->
          <button
            type="button"
            class="btn btn-info"
            @click="abrirModalRecursosIniciales"
          >
            <i class="fas fa-tools"></i> Registrar recursos iniciales
          </button>

          <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>

          <button
            v-if="id == ''"
            class="btn btn-primary"
            :disabled="recursosIniciales.length === 0"
            @click="guardar"
          >
            Crear
          </button>

          <button
            v-else
            class="btn btn-primary"
            :disabled="recursosIniciales.length === 0"
            @click="guardarEdita(form.id_contenedor)"
          >
            Actualizar
          </button>

        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { Form } from "vform";
import AssignResourcesIni from "./assign-resources_ini.vue";

export default {
  name: "create-edit-stocking",

  components: { AssignResourcesIni },

  props: ["id", "listado-contenedores", "listado-especies"],

  data() {
    return {
      form: new Form({
        id: "",
        phase_id: 0,
      }),

      listadoItems: [],
      nombresEspecies: [],

      newEspecie: "",
      newLote: "",
      newCantidad: "",
      newPeso: "",

      recursosIniciales: [],

      phaseListing: {},
    };
  },

  methods: {
    abrirModalRecursosIniciales() {
      this.$refs.modalRecursosIni.abrir(this.form.nombre_siembra);
    },

    actualizarRecursosIniciales(lista) {
      this.recursosIniciales = lista;
    },

    nombreEspecie() {
      axios.get("api/especies").then(({ data }) => {
        data.forEach(e => {
          this.nombresEspecies[e.id] = e.especie;
        });
      });
    },

    anadirEspecie() {
      if (
        !this.newEspecie ||
        !this.newCantidad ||
        !this.newPeso
      ) {
        alert("Debe diligenciar todos los campos");
        return;
      }

      this.listadoItems.push({
        id_especie: this.newEspecie,
        lote: this.newLote,
        cantidad: this.newCantidad,
        peso_inicial: this.newPeso,
      });

      this.newEspecie = "";
      this.newLote = "";
      this.newCantidad = "";
      this.newPeso = "";
    },

    removeItem(id_especie) {
      this.listadoItems = this.listadoItems.filter(
        i => i.id_especie !== id_especie
      );
    },

    guardar() {
      if (
        !this.form.id_contenedor ||
        !this.form.nombre_siembra ||
        !this.form.fecha_inicio ||
        this.listadoItems.length === 0
      ) {
        alert("Complete todos los campos");
        return;
      }

      const data = {
        siembra: this.form,
        especies: this.listadoItems,
        recursos_iniciales: this.recursosIniciales,
      };

      axios.post("api/siembras", data).then(() => {
        this.form.reset();
        this.listadoItems = [];
        this.recursosIniciales = [];

        $("#modalSiembra").modal("hide");
      });
    },

    listPhases(page = 1) {
      axios.get("api/phases", { params: { page } }).then(({ data }) => {
        this.phaseListing = data.phases;
      });
    },
  },

  mounted() {
    this.nombreEspecie();
    this.listPhases(1);
  },
};
</script>

<style></style>
