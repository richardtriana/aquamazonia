<template>
  <div class="modal fade" id="modalResources" tabindex="-1" data-backdrop="static" role="dialog"
    aria-labelledby="modalResourcesLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" v-text="edit == 0 ? 'Crear recurso' : 'Actualizar recurso'"></h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="SaveResource()">

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Nombre recurso</label>
              <div class="col-md-8">
                <input type="text" class="form-control" v-model="form.recurso">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Unidad</label>
              <div class="col-md-8">
                <input type="text" class="form-control" placeholder="Kg, KL, Minuto, Lt" v-model="form.unidad">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Costo Unidad</label>
              <div class="col-md-8">
                <input type="text" class="form-control" v-model="form.costo">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Fecha de vencimiento</label>
              <div class="col-md-8">
                <input type="date" class="form-control" v-model="form.expiration_date">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Cantidad</label>
              <div class="col-md-8">
                <input type="number" step="any" class="form-control" v-model="form.cantidad">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Cantidad mínima</label>
              <div class="col-md-8">
                <input type="number" step="any" class="form-control" v-model="form.cantidad_minima">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label">Cantidad máxima</label>
              <div class="col-md-8">
                <input type="number" step="any" class="form-control" v-model="form.cantidad_maxima">
              </div>
            </div>

            <!-- INVENTARIO -->
            <div class="form-group row">
              <label class="col-md-4 col-form-label">Inventario</label>
              <div class="col-md-8">

                <label class="mr-3">
                  <input type="radio" value="1" v-model="form.inventario"> Sí
                </label>

                <label>
                  <input type="radio" value="0" v-model="form.inventario"> No
                </label>

              </div>
            </div>

            <!-- CATEGORÍA -->
            <div class="form-group row">
              <label class="col-md-4 col-form-label">Categoría</label>
              <v-select class="col-md-8"
                v-model="form.category_id"
                label="category"
                :options="optCategories"
                :reduce="option => option.id"
                :filterable="false"
                @search="onSearchCategory">
              </v-select>
            </div>

            <!-- BODEGA -->
            <div class="form-group row">
              <label class="col-md-4 col-form-label">Bodega</label>
              <v-select class="col-md-8"
                v-model="form.warehouse_id"
                label="warehouse"
                :options="optWarehouses"
                :reduce="option => option.id"
                :filterable="false"
                @search="onSearchWarehouse">
              </v-select>
            </div>

            <!-- PROVEEDOR -->
            <div class="form-group row">
              <label class="col-md-4 col-form-label">Proveedor</label>
              <v-select class="col-md-8"
                v-model="form.provider_id"
                label="provider"
                :options="optProviders"
                :reduce="option => option.id"
                :filterable="false"
                @search="onSearchProvider">
              </v-select>
            </div>

            <!-- MARCA -->
            <div class="form-group row">
              <label class="col-md-4 col-form-label">Marca</label>
              <v-select class="col-md-8"
                v-model="form.brand_id"
                label="brand"
                :options="optBrands"
                :reduce="option => option.id"
                :filterable="false"
                @search="onSearchBrand">
              </v-select>
            </div>

            <!-- BOTONES -->
            <div class="form-group row">
              <div class="col-md-12 text-right">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">{{ edit == 0 ? 'Crear' : 'Actualizar' }}</button>
              </div>
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
      optWarehouses: [],
      optBrands: [],
      optProviders: [],
      optCategories: [],
      edit: 0,

      form: new Form({
        id: "",
        recurso: "",
        unidad: "",
        costo: "",
        warehouse_id: "",
        category_id: "",
        provider_id: "",
        brand_id: "",
        expiration_date: "",
        cantidad: "",
        cantidad_maxima: "",
        cantidad_minima: "",
        inventario: "1"
      }),
    };
  },

  methods: {

    /* ------------------------- */
    /* ABRIR MODAL - NUEVO       */
    /* ------------------------- */
    abrir() {
      this.edit = 0;
      this.form.reset();

      this.loadWarehouses();
      this.loadBrands();
      this.loadCategories();
      this.loadProviders();

      $("#modalResources").modal("show");
    },

    /* ------------------------- */
    /* CARGAR CATÁLOGOS          */
    /* ------------------------- */

    loadWarehouses() {
      axios.get("api/warehouses?page=1")
        .then(res => this.optWarehouses = res.data.warehouses.data);
    },

    loadBrands() {
      axios.get("api/brands?page=1")
        .then(res => this.optBrands = res.data.brands.data);
    },

    loadCategories() {
      axios.get("api/resource-categories?page=1")
        .then(res => this.optCategories = res.data.resourceCategories.data);
    },

    loadProviders() {
      axios.get("api/providers?page=1")
        .then(res => this.optProviders = res.data.providers.data);
    },

    /* ------------------------- */
    /* EDITAR                    */
    /* ------------------------- */
    OpenEditResource(resource) {
      this.edit = 1;

      this.loadWarehouses();
      this.loadBrands();
      this.loadCategories();
      this.loadProviders();

      this.form = new Form(resource);

      $("#modalResources").modal("show");
    },

    /* ------------------------- */
    /* GUARDAR                   */
    /* ------------------------- */
    SaveResource() {
      if (this.edit == 0) this.CreateResource();
      else this.EditResource();
    },

    CreateResource() {
      axios.post("api/recursos", this.form)
        .then(() => {
          $("#modalResources").modal("hide");
          this.$emit("list-resources");
        });
    },

    EditResource() {
      axios.put(`api/recursos/${this.form.id}`, this.form)
        .then(() => {
          $("#modalResources").modal("hide");
          this.$emit("list-resources");
        });
    },

    ResetData() {
      $("#modalResources").modal("hide");
      this.form.reset();
    },

    onSearchWarehouse(search, loading) {
      if (search.length) {
        loading(true);
        axios.get(`api/warehouses?warehouse=${search}&page=1`)
          .then(res => {
            this.optWarehouses = res.data.warehouses.data;
            loading(false);
          });
      }
    },

    onSearchBrand(search, loading) {
      if (search.length) {
        loading(true);
        axios.get(`api/brands?brand=${search}&page=1`)
          .then(res => {
            this.optBrands = res.data.brands.data;
            loading(false);
          });
      }
    },

    onSearchCategory(search, loading) {
      if (search.length) {
        loading(true);
        axios.get(`api/resource-categories?category=${search}&page=1`)
          .then(res => {
            this.optCategories = res.data.resourceCategories.data;
            loading(false);
          });
      }
    },

    onSearchProvider(search, loading) {
      if (search.length) {
        loading(true);
        axios.get(`api/providers?provider=${search}&page=1`)
          .then(res => {
            this.optProviders = res.data.providers.data;
            loading(false);
          });
      }
    },

  }
};
</script>
