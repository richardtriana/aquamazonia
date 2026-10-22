<template>
  <div
    class="modal fade"
    id="addResourceModal"
    tabindex="-1"
    role="dialog"
    data-backdrop="static"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

        <!-- HEADER -->
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Seleccionar recurso</h5>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <!-- BODY -->
        <div class="modal-body">

          <!-- BUSCADOR -->
          <div class="form-group">
            <input
              type="text"
              class="form-control"
              placeholder="Buscar recurso..."
              v-model="search"
              @keyup="loadResources"
            />
          </div>

          <!-- TABLA -->
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover">
              <thead class="bg-success text-white">
                <tr>
                  <th>ID</th>
                  <th>Recurso</th>
                  <th>Costo</th>
                  <th>Seleccionar</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="r in resources" :key="r.id">
                  <td>{{ r.id }}</td>
                  <td>{{ r.recurso }}</td>
                  <td>{{ r.costo }}</td>

                  <td>
                    <button class="btn btn-success btn-sm" @click="selectResource(r)">
                      <i class="fa fa-plus"></i> Elegir
                    </button>
                  </td>
                </tr>

                <tr v-if="resources.length === 0">
                  <td colspan="4" class="text-center text-muted">
                    Sin resultados
                  </td>
                </tr>
              </tbody>

            </table>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      search: "",
      resources: [],
    };
  },

  methods: {
    open() {
      this.search = "";
      this.resources = [];
      $("#addResourceModal").modal("show");
      this.loadResources();
    },

    // 🔥 URL corregida según tu backend:
    loadResources(page = 1) {
      axios
        .get("api/resource/list", {
          params: {
            page,
            nro_results: 15,
            recurso: this.search
          }
        })
        .then((res) => {
          this.resources = res.data.resources.data || [];
        })
        .catch(err => {
          console.error("Error cargando recursos:", err);
        });
    },

    selectResource(resource) {
      this.$emit("add-resource", resource);
      $("#addResourceModal").modal("hide");
    },
  },
};
</script>
