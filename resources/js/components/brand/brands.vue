<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Marcas</h3>

    <!-- CARD DE TOTAL -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary">
          <div class="card-body py-2">
            <h6 class="text-muted mb-1">Total marcas</h6>
            <h4 class="font-weight-bold">
              {{ brandListing.total ? brandListing.total : 0 }}
            </h4>
          </div>
        </div>
      </div>

      <!-- BOTÓN CREAR -->
      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button
          type="button"
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormBrand.abrir()"
        >
          <i class="fas fa-plus"></i> Crear Marca
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
                <th>Marca</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="brandListing.data && brandListing.data.length">
              <tr v-for="(brand, index) in brandListing.data" :key="brand.id">
                <td>{{ index + 1 }}</td>
                <td>{{ brand.brand }}</td>
                <td>{{ brand.state ? 'Activo' : 'Inactivo' }}</td>

                <td>
                  <button
                    class="btn btn-sm btn-outline-success"
                    @click="ShowData(brand)"
                  >
                    <i class="fas fa-edit"></i> Editar
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="4" class="text-center text-muted py-3">
                  No se encontraron marcas registradas
                </td>
              </tr>
            </tbody>

          </table>

        </div>
      </div>
    </section>

    <!-- MODAL -->
    <form-brand
      ref="FormBrand"
      @list-brands="listBrands(1)"
    />
  </div>
</template>

<script>
import FormBrand from "./form-brand.vue";

export default {
  components: { FormBrand },

  data() {
    return {
      brandListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listBrands(1);
  },

  methods: {
    listBrands(page = 1) {
      this.isLoading = true;

      axios.get(`api/brands?page=${page}`, this.$root.config)
        .then(res => this.brandListing = res.data.brands)
        .finally(() => this.isLoading = false);
    },

    ShowData(brand) {
      this.$refs.FormBrand.OpenEditBrand(brand);
    },

    closeModal() {
      this.$refs.FormBrand.ResetData();
      this.listBrands(1);
    }
  }
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
