<template>
  <div
    class="modal fade"
    id="addProviderModal"
    tabindex="-1"
    role="dialog"
    data-backdrop="static"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

        <!-- HEADER -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Seleccionar proveedor</h5>
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
              placeholder="Buscar proveedor..."
              v-model="search"
              @keyup="loadProviders"
            />
          </div>

          <!-- TABLA -->
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover">
              <thead class="thead-dark">
                <tr>
                  <th>ID</th>
                  <th>Proveedor</th>
                  <th>Teléfono</th>
                  <th>Seleccionar</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="p in providers" :key="p.id">
                  <td>{{ p.id }}</td>
                  <td>{{ p.provider }}</td>
                  <td>{{ p.telefono || '-' }}</td>

                  <td>
                    <button class="btn btn-success btn-sm" @click="selectProvider(p)">
                      <i class="fa fa-plus"></i> Elegir
                    </button>
                  </td>
                </tr>

                <tr v-if="providers.length === 0">
                  <td colspan="4" class="text-center text-muted">Sin resultados</td>
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
      providers: [],
    };
  },

  methods: {
    // Abre modal
    open() {
      this.search = "";
      this.providers = [];
      $("#addProviderModal").modal("show");
    },

    // Cargar proveedores
    loadProviders() {
      axios
        .get("api/providers", { params: { provider: this.search } })
        .then((res) => {
          this.providers = res.data.providers.data || [];
        });
    },

    // Enviar proveedor al componente padre
    selectProvider(provider) {
      this.$emit("add-provider", provider);
      $("#addProviderModal").modal("hide");
    },
  },
};
</script>
