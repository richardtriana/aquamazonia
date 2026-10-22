<template>
  <div class="col-12 container">
    <div v-if="isLoading" class="global-spinner">
      <div class="global-spinner-inner">
        <div class="global-spinner-ring"></div>
        <div class="global-spinner-logo">AQUAMAZONIA</div>
        <div class="global-spinner-text">Cargando alimentos...</div>
      </div>
    </div>

    <h3 class="page-header">Gestión de Alimentos</h3>

    <div class="row mb-3 align-items-center">
      <div class="col-md-3">
        <div class="card shadow-sm p-3 mb-0">
          <h6 class="mb-1 text-muted">Total alimentos</h6>
          <h3 class="mb-0">{{ listado.length }}</h3>
        </div>
      </div>

      <div class="col-md-9 text-right">
        <downloadexcel
          class="btn btn-success btn-sm px-4 mr-2"
          :fetch="fetchExcelData"
          :fields="json_fields"
          name="inventario-alimentos.xls"
          type="xls"
        >
          <i class="fas fa-file-excel"></i> Exportar Excel
        </downloadexcel>

        <button class="btn btn-primary btn-sm px-4" @click="$refs.FormFood.ResetData()">
          <i class="fas fa-plus"></i> Crear alimento
        </button>
      </div>
    </div>

    <div class="card shadow-sm p-3 mb-3 bg-light">
      <div class="row">
        <div class="col-md-4">
          <label class="small font-weight-bold">Buscar Alimento:</label>
          <input 
            type="text" 
            class="form-control form-control-sm" 
            v-model="filters.food" 
            placeholder="Nombre del alimento..."
            @keyup.enter="listFoods"
          >
        </div>
        <div class="col-md-3">
          <label class="small font-weight-bold">Categoría:</label>
          <v-select
            :options="optCategories"
            label="category"
            :reduce="c => c.id"
            v-model="filters.category_id"
            placeholder="Todas"
            class="bg-white"
          />
        </div>
        <div class="col-md-3">
          <label class="small font-weight-bold">Marca:</label>
          <v-select
            :options="optBrands"
            label="brand"
            :reduce="b => b.id"
            v-model="filters.brand_id"
            placeholder="Todas"
            class="bg-white"
          />
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-info btn-sm btn-block" @click="listFoods">
            <i class="fas fa-search"></i> Filtrar
          </button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm p-3">
      <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-primary text-center">
            <tr>
              <th>#</th>
              <th>Alimento</th>
              <th>Costo Kl</th>
              <th>Kg/Bulto</th>
              <th>Stock (Kg)</th>
              <th>Bultos Actuales</th>
              <th>Bodega</th>
              <th>Marca</th>
              <th>Categoría</th>
              <th>Opciones</th>
            </tr>
          </thead>

          <tbody v-if="listado.length > 0">
            <tr v-for="(food, index) in listado" :key="food.id">
              <td class="text-center">{{ index + 1 }}</td>
              <td class="font-weight-bold">{{ food.alimento }}</td>
              <td class="text-right">{{ food.costo_kg | numeral('$0,0.00') }}</td>
              <td class="text-center">{{ food.cantidad_bulto }} kg</td>
              <td class="text-right">{{ food.cantidad | numeral('0,0.00') }}</td>
              <td class="text-center font-weight-bold text-primary bg-light">
                {{ food.cantidad_bulto > 0 ? (food.cantidad / food.cantidad_bulto).toFixed(2) : '0.00' }}
              </td>
              <td>{{ food.warehouse?.warehouse || 'N/A' }}</td>
              <td>{{ food.brand?.brand || 'N/A' }}</td>
              <td>{{ food.category?.category || 'N/A' }}</td>

              <td class="text-center">
                <div class="btn-group">
                  <button class="btn btn-outline-success btn-sm" @click="ShowData(food)" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-outline-primary btn-sm" @click="ShowCosts(food.id)" title="Costos">
                    <i class="fas fa-history"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" @click="Delete(food.id)" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>

          <tbody v-else>
            <tr>
              <td colspan="10" class="text-center py-4 text-muted">No se encontraron alimentos con los filtros aplicados</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <form-food ref="FormFood" @list-foods="listFoods" />
    <food-history ref="FoodHistory" />

  </div>
</template>

<script>
import FormFood from "./form-food.vue";
import FoodHistory from "./food-history.vue";
import downloadexcel from "vue-json-excel";

export default {
  components: { FormFood, FoodHistory, downloadexcel },

  data() {
    return {
      listado: [],
      isLoading: false,
      // Objetos para filtros de búsqueda
      filters: {
        food: "",
        category_id: null,
        brand_id: null
      },
      optCategories: [],
      optBrands: [],
      // Configuración de campos para Exportación Excel
      json_fields: {
        "Nombre Alimento": "alimento",
        "Costo por Kg": {
          field: "costo_kg",
          callback: v => numeral(v).format("0,0.00")
        },
        "Peso por Bulto (Kg)": "cantidad_bulto",
        "Stock Total (Kg)": "cantidad",
        "Bultos en Inventario": {
          callback: v => v.cantidad_bulto > 0 ? (v.cantidad / v.cantidad_bulto).toFixed(2) : '0.00'
        },
        "Marca": "brand.brand",
        "Categoría": "category.category",
        "Bodega": "warehouse.warehouse",
        "Proveedor": "provider.provider"
      }
    };
  },

  created() {
    this.listFoods();
    this.loadFilterOptions();
  },

  methods: {
    // Carga de categorías y marcas para los select de filtros
    loadFilterOptions() {
      axios.get("api/food-categories?page=1").then(r => {
        this.optCategories = r.data.foodCategories.data;
      });
      axios.get("api/brands?brand=&page=1").then(r => {
        this.optBrands = r.data.brands.data;
      });
    },

    // Obtener listado de alimentos procesado por el controlador 'list'
    listFoods() {
      this.isLoading = true;
      
      const params = {
        food: this.filters.food,
        food_category_id: this.filters.category_id,
        brand_id: this.filters.brand_id,
        nro_results: 100 // Cantidad de registros por página para el inventario
      };

      axios.get("api/alimentos/list", { params })
        .then(res => {
          // Extraemos 'data' de la respuesta paginada del controlador
          this.listado = res.data.foods.data;
        })
        .catch(err => {
          console.error("Error al listar alimentos:", err);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    // Retorna los datos para el componente de Excel
    fetchExcelData() {
      return this.listado;
    },

    // Abrir modal de edición
    ShowData(food) {
      this.$refs.FormFood.OpenEditFood(food);
    },

    // Eliminar registro con confirmación
    Delete(id) {
      Swal.fire({
        title: "¿Eliminar alimento?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Eliminar"
      }).then(result => {
        if (result.isConfirmed) {
          axios.delete("api/alimentos/" + id)
            .then(() => {
              this.listFoods();
              toast.fire({ icon: 'success', title: 'Registro eliminado' });
            });
        }
      });
    },

    // Abrir historial de costos
    ShowCosts(idFood) {
      this.$refs.FoodHistory.ShowCosts(idFood);
    }
  }
};
</script>