<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Tipo de recurso</h3>

    <!-- CARD DE TOTAL -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary">
          <div class="card-body py-2">
            <h6 class="text-muted mb-1">Total categorías</h6>
            <h4 class="font-weight-bold">
              {{ resourceCategoryListing.total ? resourceCategoryListing.total : 0 }}
            </h4>
          </div>
        </div>
      </div>

      <!-- BOTÓN CREAR -->
      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button
          type="button"
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormResourceCategory.abrir()"
        >
          Crear Categoría
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
                <th>Tipo de recurso</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="resourceCategoryListing.data && resourceCategoryListing.data.length">
              <tr
                v-for="(resourceCategory, index) in resourceCategoryListing.data"
                :key="resourceCategory.id"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ resourceCategory.category }}</td>
                <td>{{ resourceCategory.state ? 'Activo' : 'Inactivo' }}</td>

                <td>
                  <button
                    class="btn btn-outline-success btn-sm"
                    @click="ShowData(resourceCategory)"
                  >
                    Editar
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="4" class="text-center text-muted py-3">
                  No hay categorías registradas
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
    </section>

    <!-- MODAL -->
    <form-resource-category
      ref="FormResourceCategory"
      @list-resource-categories="listResourceCategories(1)"
    />

  </div>
</template>

<script>
import FormResourceCategory from "./form-resource-category.vue";

export default {
  components: { FormResourceCategory },

  data() {
    return {
      resourceCategoryListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listResourceCategories(1);
  },

  methods: {
    listResourceCategories(page = 1) {
      this.isLoading = true;

      axios.get(`api/resource-categories?page=${page}`, this.$root.config)
        .then(res => this.resourceCategoryListing = res.data.resourceCategories)
        .finally(() => this.isLoading = false);
    },

    ShowData(resourceCategory) {
      this.$refs.FormResourceCategory.OpenEditResourceCategory(resourceCategory);
    },

    closeModal() {
      this.$refs.FormResourceCategory.ResetData();
      this.listResourceCategories(1);
    },
  }
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
