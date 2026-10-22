<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Proveedores</h3>

    <!-- CARD DE TOTAL -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary">
          <div class="card-body py-2">
            <h6 class="text-muted mb-1">Total proveedores</h6>
            <h4 class="font-weight-bold">
              {{ providerListing.total ? providerListing.total : 0 }}
            </h4>
          </div>
        </div>
      </div>

      <!-- BOTÓN CREAR -->
      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button 
          type="button" 
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormProvider.abrir()">
          <i class="fas fa-plus"></i> Crear proveedor
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
                <th>Proveedor</th>
                <th>NIT</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="providerListing.data && providerListing.data.length">
              <tr v-for="(provider, index) in providerListing.data" :key="provider.id">
                <td>{{ index + 1 }}</td>
                <td>{{ provider.provider }}</td>
                <td>{{ provider.nit }}</td>
                <td>{{ provider.tel }}</td>
                <td>{{ provider.address }}</td>
                <td>{{ provider.state ? 'Activo' : 'Inactivo' }}</td>

                <td>
                  <button 
                    class="btn btn-sm btn-outline-success"
                    @click="ShowData(provider)">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="7" class="text-center text-muted py-3">
                  No se encontraron proveedores registrados
                </td>
              </tr>
            </tbody>

          </table>

        </div>
      </div>
    </section>

    <!-- MODAL -->
    <form-provider 
      ref="FormProvider" 
      @list-providers="listProviders(1)"
    />

  </div>
</template>

<script>
import FormProvider from "./form-provider.vue";

export default {
  components: { FormProvider },

  data() {
    return {
      providerListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listProviders(1);
  },

  methods: {
    listProviders(page = 1) {
      this.isLoading = true;
      axios.get("api/providers?page=" + page, this.$root.config)
        .then(res => this.providerListing = res.data.providers)
        .finally(() => this.isLoading = false);
    },

    ShowData(provider) {
      this.$refs.FormProvider.OpenEditProvider(provider);
    },

    closeModal() {
      this.$refs.FormProvider.ResetData();
      this.listProviders(1);
    }
  }
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
