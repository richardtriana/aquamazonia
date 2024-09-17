<template>
  <div class="modal fade" id="addFoodModal" tabindex="-1" aria-labelledby="addFoodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addFoodModalLabel">Foods</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">

            <input type="text" class="form-control" placeholder="Código de barras | Nombre de foodo"
              id="inputSearchFood" aria-label=" with two button addons" aria-describedby="button-addon4"
              v-model="filters.food" @keyup="searchFood()" />
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button" @click="searchFood()">
                Buscar Alimento
              </button>
            </div>
          </div>
          <section class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="thead-primary">
                <tr>
                  <th scope="col">#</th>
                  <th>ID</th>
                  <th scope="col">Alimento</th>
                  <th>Categoria</th>
                  <th scope="col">Precio</th>
                  <th scope="col">Cantidad</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(food, index) in FoodList.data" v-bind:key="food.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ food.id }}</td>
                  <td>{{ food.alimento }}</td>
                  <td>
                    {{ food.category ? food.category : "" }}
                  </td>
                  <td class="text-right">$ {{ food.costo_kg }}</td>
                  <td>{{ food.cantidad }}</td>

                  <td>
                    <button class="btn btn-success" @click="$emit('add-food', food)">
                      <i class="fa fa-plus-square" aria-hidden="true"></i>

                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "add-food",
  props: { 'is_order': 0 },
  data() {
    return {
      // Filter modal
      filters: {
        food: "",
        is_order: 1,
        nro_results: 5
      },
      FoodList: {},
    };
  },

  created() {
    this.listFoods();
  },
  methods: {
    listFoods() {
      let me = this;
      let data = {
        ...this.filters
      }
      axios
        .get(
          `api/food/list`, { params: data },
          this.$root.config
        )
        .then(function (response) {
          me.FoodList = response.data.foods;
        });
    },
    searchFood() {
      let me = this;
      if (me.filters.food == "") {
        return false;
      }
      let data = {
        ...this.filters
      }
      var url = `api/food/list`;
      if (me.filters.food.length >= 3) {
        axios
          .get(url, { params: data }, me.$root.config)
          .then(function (response) {
            me.FoodList = response.data.foods;
            console.log(me.FoodList)
          })
          .catch(function (error) {
            $("#no-results").toast("show");

            console.log(error);
          });
      }
    },
  },
};
</script>