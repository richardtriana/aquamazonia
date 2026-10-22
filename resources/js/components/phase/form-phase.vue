<template>
  <div
    class="modal fade"
    id="phaseModal"
    tabindex="-1"
    aria-labelledby="phaseModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ edit ? "Editar fase" : "Crear fase" }}
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="SavePhase()">

            <div class="form-group">
              <label>Fase</label>
              <input
                type="text"
                class="form-control"
                v-model="formPhase.phase"
              />
              <small class="form-text text-danger">{{ formErrors.phase }}</small>
            </div>

          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>

          <button type="button" class="btn btn-primary" @click="SavePhase">
            {{ edit ? "Actualizar" : "Guardar" }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      edit: false,

      formPhase: {
        id: "",
        phase: "",
      },

      formErrors: {
        phase: "",
      },
    };
  },

  methods: {
    // NUEVO: limpiar modal + errores
    ResetData() {
      this.edit = false;
      this.formPhase = { id: "", phase: "" };
      this.formErrors = { phase: "" };

      $("#phaseModal").modal("show");
    },

    OpenEditPhase(phase) {
      this.edit = true;

      this.formPhase = {
        id: phase.id,
        phase: phase.phase,
      };

      this.formErrors = { phase: "" };

      $("#phaseModal").modal("show");
    },

    SavePhase() {
      if (!this.edit) this.CreatePhase();
      else this.EditPhase();
    },

    CreatePhase() {
      axios
        .post("api/phases", this.formPhase, this.$root.config)
        .then(() => {
          $("#phaseModal").modal("hide");
          this.$emit("list-phases");
        })
        .catch(err => this.assignErrors(err));
    },

    EditPhase() {
      axios
        .put(`api/phases/${this.formPhase.id}`, this.formPhase, this.$root.config)
        .then(() => {
          $("#phaseModal").modal("hide");
          this.$emit("list-phases");
        })
        .catch(err => this.assignErrors(err));
    },

    assignErrors(error) {
      if (!error.response) return;

      let errs = error.response.data.errors || {};

      this.formErrors.phase = errs.phase ? errs.phase[0] : "";
    },
  },
};
</script>
