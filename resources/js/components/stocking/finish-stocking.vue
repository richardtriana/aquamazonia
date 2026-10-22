<template>
  <div>
    <div class="modal fade" id="modalFinalizar" tabindex="-1" role="dialog" aria-labelledby="modalFinalizarLabel"
      aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Finalizar siembra</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <div class="row">
                <div class="col">
                  <h6>Inicio Descanso</h6>
                  <input type="date" class="form-control" placeholder="First name" v-model="ini_descanso" required>
                </div>
                <div class="col">
                  <h6>Fin descanso</h6>
                  <input type="date" class="form-control" placeholder="Last name" v-model="fin_descanso">
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-info mr-auto" @click="abrirModalRecursosCosecha">
              <i class="fas fa-tools"></i> Recursos utilizados
            </button>

            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" @click="fechaDescanso()">Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <assign-resources-ini
      modal-id="modalRecursosCosechaUnico"
      ref="modalRecursosCosecha"
      titulo="Registro de recursos cosecha"
      :recursos-iniciales="recursosCosecha"
      @update-list="actualizarRecursosCosecha"
    ></assign-resources-ini>

  </div>
</template>

<script>
// 1. Importamos el componente
import AssignResourcesIni from "./assign-resources_ini.vue";

export default {
  props: ['id'],
  components: {
    AssignResourcesIni // 2. Lo registramos
  },
  data() {
    return {
      // Finalización de siembra
      ini_descanso: '',
      fin_descanso: '',
      // 3. Variable para almacenar los recursos de la cosecha
      recursosCosecha: [] 
    }
  },
  methods: {
    // 4. Métodos para abrir y sincronizar el modal de recursos
    abrirModalRecursosCosecha() {
      let me = this;
      
      $('#modalFinalizar').one('hidden.bs.modal', function () {
         me.$refs.modalRecursosCosecha.abrir('Siembra ID: ' + me.id);
      });
      
      $("#modalFinalizar").modal("hide"); 

      $('#modalRecursosCosechaUnico').one('hidden.bs.modal', function () {
          $("#modalFinalizar").modal("show");
      });
    },

    actualizarRecursosCosecha(lista) {
      this.recursosCosecha = lista;
    },

    fechaDescanso() {
      let me = this;
      if (this.ini_descanso != '') {
          const data = {
            'id': this.id,
            'ini_descanso': this.ini_descanso,
            'fin_descanso': this.fin_descanso ?? '',
            estado: 0,
            // 5. Agregamos los recursos para que viajen al backend
            recursos_cosecha: this.recursosCosecha 
          }
          axios.post('api/actualizarEstado/' + this.id, data)
            .then(({ response }) => {
              this.ini_descanso = '';
              this.fin_descanso = '';
              this.recursosCosecha = []; // Limpiamos el array
              $('#modalFinalizar').modal('hide');
              this.$emit('list-stocking')
            });
        //
      } else {
        swal("Advertencia", "Por favor, diligencia los datos restantes", "warning");
      }
    },
  }
}
</script>