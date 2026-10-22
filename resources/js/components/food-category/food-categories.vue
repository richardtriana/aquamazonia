<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Tipo de Alimento</h3>

    <!-- CARD DE TOTAL -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary">
          <div class="card-body py-2">
            <h6 class="text-muted mb-1">Total categorías</h6>
            <h4 class="font-weight-bold">
              {{ foodCategoryListing.total ? foodCategoryListing.total : 0 }}
            </h4>
          </div>
        </div>
      </div>

      <!-- BOTÓN CREAR -->
      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button
          type="button"
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormFoodCategory.abrir()"
        >
          <i class="fas fa-plus"></i> Crear Categoría
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
                <th>Tipo de Alimento</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="foodCategoryListing.data && foodCategoryListing.data.length">
              <tr v-for="(foodCategory, index) in foodCategoryListing.data" :key="foodCategory.id">
                <td>{{ index + 1 }}</td>
                <td>{{ foodCategory.category }}</td>
                <td>{{ foodCategory.state ? 'Activo' : 'Inactivo' }}</td>

                <td>
                  <button
                    class="btn btn-outline-success btn-sm"
                    @click="ShowData(foodCategory)"
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
    <form-food-category
      ref="FormFoodCategory"
      @list-food-categories="listFoodCategories(1)"
    />

  </div>
</template>

<script>
import FormFoodCategory from "./form-food-category.vue";

export default {
  components: { FormFoodCategory },

  data() {
    return {
      foodCategoryListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listFoodCategories(1);
  },

  methods: {
    listFoodCategories(page = 1) {
      this.isLoading = true;
      axios.get(`api/food-categories?page=${page}`, this.$root.config)
        .then(res => this.foodCategoryListing = res.data.foodCategories)
        .finally(() => this.isLoading = false);
    },

    ShowData(foodCategory) {
      this.$refs.FormFoodCategory.OpenEditFoodCategory(foodCategory);
    },

    closeModal() {
      this.$refs.FormFoodCategory.ResetData();
      this.listFoodCategories(1);
    }
  }
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
