<template>
  <div
    class="modal fade"
    id="addFoodModal"
    tabindex="-1"
    role="dialog"
    data-backdrop="static"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">

        <!-- HEADER -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Buscar alimento</h5>
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
              placeholder="Buscar alimento..."
              v-model="search"
              @keyup="loadFoods"
            />
          </div>

          <!-- TABLA -->
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover">
              <thead class="thead-dark">
                <tr>
                  <th>ID</th>
                  <th>Alimento</th>
                  <th>Costo Kg</th>
                  <th>Bodega</th>
                  <th>Seleccionar</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="f in foods" :key="f.id">
                  <td>{{ f.id }}</td>
                  <td>{{ f.alimento }}</td>
                  <td>{{ f.costo_kg }}</td>
                  <td>{{ f.warehouse?.warehouse || '' }}</td>

                  <td>
                    <button class="btn btn-success btn-sm" @click="selectFood(f)">
                      <i class="fa fa-plus"></i> Añadir
                    </button>
                  </td>
                </tr>

                <tr v-if="foods.length === 0">
                  <td colspan="5" class="text-center text-muted">Sin resultados</td>
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
      foods: [],
    };
  },

  methods: {
    // Abre el modal desde el padre
    open() {
      this.search = "";
      this.foods = [];
      $("#addFoodModal").modal("show");
    },

    // Cargar alimentos
    loadFoods() {
      axios
        .get("api/alimentos", { params: { alimento: this.search } })
        .then((res) => {
          this.foods = res.data;
        });
    },

    // Enviar alimento al padre
    selectFood(f) {
      this.$emit("add-food", f);
      $("#addFoodModal").modal("hide");
    },
  },
};
</script>
