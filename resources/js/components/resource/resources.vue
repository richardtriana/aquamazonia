<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Gestión de recursos</div>

          <div class="card-body">
            <div class="row mb-1">
              <div class="col-12 text-right">

                <!-- BOTÓN CORREGIDO -->
                <button class="btn btn-success" @click="$refs.FormResource.abrir()">
                  Añadir Recurso
                </button>

              </div>
            </div>

            <div class="row">
              <table class="table table-hover table-sm table-bordered">
                <thead class="thead-primary">
                  <tr>
                    <th>#</th>
                    <th>Recurso</th>
                    <th>Unidad</th>
                    <th>Costo</th>
                    <th>Bodega</th>
                    <th>Marca</th>
                    <th>Categoría</th>
                    <th>Proveedor</th>
                    <th>Opciones</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(recurso, index) in listado" :key="index">
                    <th>{{ index + 1 }}</th>
                    <td>{{ recurso.recurso }}</td>
                    <td>{{ recurso.unidad }}</td>
                    <td>{{ recurso.costo }}</td>
                    <td>{{ recurso.warehouse ? recurso.warehouse.warehouse : "" }}</td>
                    <td>{{ recurso.brand ? recurso.brand.brand : "" }}</td>
                    <td>{{ recurso.category ? recurso.category.category : "" }}</td>
                    <td>{{ recurso.provider ? recurso.provider.provider : "" }}</td>

                    <td>
                      <button @click="ShowData(recurso)" class="btn btn-success btn-sm" type="button">
                        <i class="fas fa-edit"></i>
                      </button>

                      <button @click="Delete(recurso.id)" class="btn btn-danger btn-sm" type="button">
                        <i class="fas fa-trash"></i>
                      </button>

                      <button class="btn btn-primary btn-sm" type="button" @click="ShowCosts(recurso.id)">
                        <i class="fas fa-dollar-sign"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>

              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODALES -->
    <form-resource ref="FormResource" @list-resources="listResources"></form-resource>
    <resource-history ref="ResourceHistory"></resource-history>

  </div>
</template>

<script>
import FormResource from "./form-resource.vue";
import ResourceHistory from "./resource-history.vue";

export default {
  data() {
    return {
      listado: [],
    };
  },

  components: {
    FormResource,
    ResourceHistory
  },

  methods: {
    listResources() {
      axios.get("api/recursos").then((response) => {
        this.listado = response.data;
      });
    },

    ShowData(resource) {
      this.$refs.FormResource.OpenEditResource(resource);
    },

    Delete(resourceId) {
      Swal.fire({
        title: "¿Estás seguro?",
        text: "Una vez eliminado, no se puede recuperar",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#c7120c",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Aceptar!",
        reverseButtons: true,
      }).then((result) => {
        if (result.isConfirmed) {
          axios.delete("api/recursos/" + resourceId).then(() => {
            this.listResources();
          });
        }
      });
    },

    ShowCosts(idRecurso) {
      this.$refs.ResourceHistory.ShowCosts(idRecurso);
    },

    closeModal() {
      this.$refs.FormResource.ResetData();
      this.listResources();
    },
  },

  mounted() {
    this.listResources();
  },
};
</script>
