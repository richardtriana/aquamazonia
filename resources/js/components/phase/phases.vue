<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Fases</h3>

    <!-- CARD TOTAL + BOTÓN -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary p-3">
          <h6 class="text-muted mb-1">Total fases</h6>
          <h4 class="font-weight-bold">{{ phaseListing.total ?? 0 }}</h4>
        </div>
      </div>

      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button
          type="button"
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormPhase.ResetData()"
        >
          <i class="fas fa-plus"></i> Crear fase
        </button>
      </div>
    </div>

    <!-- TABLA -->
    <section v-if="!isLoading">
      <div class="card shadow-sm">
        <div class="card-body table-responsive-sm">

          <table class="table table-sm table-bordered table-hover">
            <thead class="thead-primary">
              <tr>
                <th>#</th>
                <th>Fase</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="phaseListing.data && phaseListing.data.length">
              <tr v-for="(phase, index) in phaseListing.data" :key="phase.id">
                <td>{{ index + 1 }}</td>
                <td>{{ phase.phase }}</td>

                <td>
                  <button
                    class="btn btn-sm btn-outline-success"
                    @click="ShowData(phase)"
                  >
                    <i class="fas fa-edit"></i> Editar
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="3" class="text-center text-muted py-3">
                  No se encontraron fases registradas
                </td>
              </tr>
            </tbody>

          </table>

        </div>
      </div>
    </section>

    <!-- MODAL -->
    <form-phase ref="FormPhase" @list-phases="listPhases" />

  </div>
</template>

<script>
import FormPhase from "./form-phase.vue";

export default {
  components: { FormPhase },

  data() {
    return {
      phaseListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listPhases(1);
  },

  methods: {
    listPhases(page = 1) {
      this.isLoading = true;

      axios
        .get(`api/phases?page=${page}`, this.$root.config)
        .then(res => (this.phaseListing = res.data.phases))
        .finally(() => (this.isLoading = false));
    },

    ShowData(phase) {
      this.$refs.FormPhase.OpenEditPhase(phase);
    },
  },
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
