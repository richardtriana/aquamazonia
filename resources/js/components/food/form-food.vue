<template>
  <div class="modal fade" id="modalFoods" tabindex="-1" data-backdrop="static" role="dialog"
    aria-labelledby="modalFoodsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" v-text="edit == 0 ? 'Crear alimento' : 'Actualizar alimento'"></h5>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="SaveFood">

            <div class="row">
              <div class="form-group col-md-6">
                <label>Nombre del Alimento</label>
                <input type="text" class="form-control" v-model="form.alimento" required />
              </div>

              <div class="form-group col-md-6">
                <label>Costo por Kg</label>
                <input type="number" step="any" class="form-control" v-model="form.costo_kg" required />
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label class="text-primary font-weight-bold">Kg por Bulto (Asignación)</label>
                <input type="number" step="0.01" class="form-control border-primary" v-model="form.cantidad_bulto" required />
                <small class="text-muted">Indique el peso estándar del bulto (ej: 25 o 40).</small>
              </div>

              <div class="form-group col-md-6">
                <label>Categoría</label>
                <v-select
                  label="category"
                  v-model="form.category_id"
                  :reduce="o => o.id"
                  :options="optCategories"
                  :filterable="false"
                  @search="onSearchCategory"
                ></v-select>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label>Bodega</label>
                <v-select
                  label="warehouse"
                  v-model="form.warehouse_id"
                  :reduce="o => o.id"
                  :options="optWarehouses"
                  :filterable="false"
                  @search="onSearchWarehouse"
                ></v-select>
              </div>

              <div class="form-group col-md-6">
                <label>Proveedor</label>
                <v-select
                  label="provider"
                  v-model="form.provider_id"
                  :reduce="o => o.id"
                  :options="optProviders"
                  :filterable="false"
                  @search="onSearchProvider"
                ></v-select>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-6">
                <label>Marca</label>
                <v-select
                  label="brand"
                  v-model="form.brand_id"
                  :reduce="o => o.id"
                  :options="optBrands"
                  :filterable="false"
                  @search="onSearchBrand"
                ></v-select>
              </div>

              <div class="form-group col-md-6">
                <label>Stock Actual (Kg)</label>
                <input type="number" class="form-control" v-model="form.cantidad" />
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-4">
                <label>Stock Mínimo</label>
                <input type="number" class="form-control" v-model="form.cantidad_minima" />
              </div>

              <div class="form-group col-md-4">
                <label>Stock Máximo</label>
                <input type="number" class="form-control" v-model="form.cantidad_maxima" />
              </div>

              <div class="form-group col-md-4">
                <label class="d-block">¿Maneja Inventario?</label>
                <div class="form-check form-check-inline mt-2">
                  <input class="form-check-input" type="radio" value="1" v-model="form.inventario">
                  <label class="form-check-label">Sí</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                  <input class="form-check-input" type="radio" value="0" v-model="form.inventario">
                  <label class="form-check-label">No</label>
                </div>
              </div>
            </div>

            <hr>

            <div class="form-group text-right">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="form.busy">
                <i class="fas fa-save"></i> {{ edit == 0 ? 'Crear Alimento' : 'Actualizar Alimento' }}
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import { Form } from "vform";

export default {
  data() {
    return {
      edit: 0,
      optWarehouses: [],
      optCategories: [],
      optProviders: [],
      optBrands: [],

      form: new Form({
        id: "",
        alimento: "",
        costo_kg: "",
        cantidad_bulto: 0, // Indispensable agregarlo aquí para que vform lo reconozca
        warehouse_id: "",
        category_id: "",
        provider_id: "",
        brand_id: "",
        cantidad: 0,
        cantidad_minima: 0,
        cantidad_maxima: 0,
        inventario: "1",
      }),
    };
  },

  methods: {
    cargarListasIniciales() {
      // Usamos Promise.all para cargar todo antes de mostrar el modal si fuera necesario, 
      // pero aquí lo dejamos fluido
      axios.get("api/food-categories?page=1").then(r => this.optCategories = r.data.foodCategories.data);
      axios.get("api/warehouses?page=1").then(r => this.optWarehouses = r.data.warehouses.data);
      axios.get("api/providers?page=1").then(r => this.optProviders = r.data.providers.data);
      axios.get("api/brands?page=1").then(r => this.optBrands = r.data.brands.data);
    },

    ResetData() {
      this.edit = 0;
      this.form.reset();
      this.form.clear();
      this.form.inventario = "1";
      this.cargarListasIniciales();
      $("#modalFoods").modal("show");
    },

    OpenEditFood(food) {
      this.edit = 1;
      this.form.reset();
      this.form.clear();
      this.form.fill(food);
      // Aseguramos que el radio button de inventario se lea como string si viene como número
      this.form.inventario = String(food.inventario);
      this.cargarListasIniciales();
      $("#modalFoods").modal("show");
    },

    SaveFood() {
      if (this.edit == 0) {
        this.CreateFood();
      } else {
        this.EditFood();
      }
    },

    CreateFood() {
      this.form.post("api/alimentos")
        .then(() => {
          $("#modalFoods").modal("hide");
          this.$emit("list-foods");
          toast.fire({ icon: 'success', title: 'Alimento creado con éxito' });
        })
        .catch(err => {
          console.error("Error al crear:", err);
        });
    },

    EditFood() {
      this.form.put(`api/alimentos/${this.form.id}`)
        .then(() => {
          $("#modalFoods").modal("hide");
          this.$emit("list-foods");
          toast.fire({ icon: 'success', title: 'Alimento actualizado con éxito' });
        })
        .catch(err => {
          console.error("Error al editar:", err);
        });
    },

    // Métodos de búsqueda para v-select
    onSearchWarehouse(search, loading) {
      if (!search.length) return;
      loading(true);
      axios.get(`api/warehouses?warehouse=${search}&page=1`)
        .then(r => {
          this.optWarehouses = r.data.warehouses.data;
          loading(false);
        });
    },
    onSearchBrand(search, loading) {
      if (!search.length) return;
      loading(true);
      axios.get(`api/brands?brand=${search}&page=1`)
        .then(r => {
          this.optBrands = r.data.brands.data;
          loading(false);
        });
    },
    onSearchCategory(search, loading) {
      if (!search.length) return;
      loading(true);
      axios.get(`api/food-categories?category=${search}&page=1`)
        .then(r => {
          this.optCategories = r.data.foodCategories.data;
          loading(false);
        });
    },
    onSearchProvider(search, loading) {
      if (!search.length) return;
      loading(true);
      axios.get(`api/providers?provider=${search}&page=1`)
        .then(r => {
          this.optProviders = r.data.providers.data;
          loading(false);
        });
    },
  }
};
</script>