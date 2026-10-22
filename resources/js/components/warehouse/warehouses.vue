<template>
  <div class="col-12 container">

    <h3 class="page-header mb-4">Bodega</h3>

    <!-- CARD DE INFORMACIÓN -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="card shadow-sm border-left-primary">
          <div class="card-body py-2">
            <h6 class="text-muted mb-1">Total bodegas</h6>
            <h4 class="font-weight-bold">
              {{ warehouseListing.total ? warehouseListing.total : 0 }}
            </h4>
          </div>
        </div>
      </div>

      <!-- BOTÓN CREAR -->
      <div class="col-md-9 d-flex justify-content-end align-items-center">
        <button 
          type="button" 
          class="btn btn-primary btn-sm px-4"
          @click="$refs.FormWarehouse.abrir()">
          <i class="fas fa-plus"></i> Crear Bodega
        </button>
      </div>
    </div>

    <!-- LOADING -->
    <section v-if="isLoading">Cargando...</section>

    <!-- LISTADO -->
    <section v-if="!isLoading">
      <div class="card shadow-sm">
        <div class="card-body table-responsive">
          <table class="table table-bordered table-hover table-sm">

            <thead class="thead-primary">
              <tr>
                <th>#</th>
                <th>Bodega</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Opciones</th>
              </tr>
            </thead>

            <tbody v-if="warehouseListing.data && warehouseListing.data.length">
              <tr v-for="(warehouse, index) in warehouseListing.data" :key="warehouse.id">
                <td>{{ index + 1 }}</td>
                <td>{{ warehouse.warehouse }}</td>
                <td>{{ warehouse.description }}</td>
                <td>{{ warehouse.state ? 'Activo' : 'Inactivo' }}</td>

                <td>
                  <button 
                    class="btn btn-sm btn-outline-success"
                    @click="ShowData(warehouse)">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                </td>
              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="6" class="text-center text-muted py-3">
                  No se encontraron bodegas registradas
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
    </section>

    <!-- MODAL -->
    <form-warehouse 
      ref="FormWarehouse" 
      @list-warehouses="listWarehouse(1)" 
    />

  </div>
</template>

<script>
import FormWarehouse from "./form-warehouse.vue";

export default {
  components: { FormWarehouse },

  data() {
    return {
      warehouseListing: {},
      isLoading: false,
    };
  },

  created() {
    this.listWarehouse(1);
  },

  methods: {
    listWarehouse(page = 1) {
      this.isLoading = true;
      axios
        .get("api/warehouses?page=" + page, this.$root.config)
        .then(res => {
          this.warehouseListing = res.data.warehouses;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    ShowData(warehouse) {
      this.$refs.FormWarehouse.OpenEditWarehouse(warehouse);
    },

    closeModal() {
      this.$refs.FormWarehouse.ResetData();
      this.listWarehouse(1);
    }
  }
};
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #007bff !important;
}
</style>
