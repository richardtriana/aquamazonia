<template>
  <div class="row px-2">

    <!-- ======================== COLUMNA IZQUIERDA ======================== -->
    <div class="col-9 justify-content-center p-2">

      <!-- TOTAL EN LA PARTE SUPERIOR -->
      <div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
        <table class="table table-borderless">
          <tr class="h1 text-white bg-success">
            <td class="text-right">Total</td>
            <td>
              $ {{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}
            </td>
          </tr>
        </table>
      </div>

      <!-- NOTIFICACIÓN DE ERROR -->
      <div class="position-fixed top-0 right-0 w-50" style="z-index: 3000">
        <div
          class="toast fade hide border border-danger w-100 m-3"
          style="max-width: 90%"
          role="alert"
          id="no-results"
          aria-live="assertive"
          aria-atomic="true"
          data-delay="3000"
        >
          <div class="toast-header">
            <strong class="mr-auto h3 text-danger">Advertencia</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="toast-body text-dark h4">
            No se ha encontrado coincidencias
          </div>
        </div>
      </div>

      <!-- ======================== BARRA DE BUSQUEDA ======================== -->
      <div class="row position-sticky sticky-top mb-2 bg-light p-1" style="top: 0.5rem">

        <!-- BUSCAR RECURSO -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            placeholder="Código o nombre del recurso"
            v-model="filter.resource"
            autofocus
            @keypress.enter="searchResource"
          />

          <div class="input-group-append">

            <button class="btn btn-outline-success" type="button" @click="searchResource">
              Añadir Recurso
            </button>

            <!-- ABRIR MODAL -->
            <button class="btn btn-outline-success" type="button" @click="$refs.addResource.open()">
              <i class="fa fa-search"></i>
            </button>

          </div>
        </div>

        <!-- BUSCAR PROVEEDOR -->
        <div class="input-group col-6">
          <input
            type="text"
            class="form-control"
            :placeholder="purchase.provider"
            v-model="filter.provider"
            @keypress.enter="searchProvider"
          />

          <div class="input-group-append">

            <button class="btn btn-outline-success" type="button" @click="searchProvider">
              Añadir Proveedor
            </button>

            <!-- ABRIR MODAL -->
            <button class="btn btn-outline-success" type="button" @click="$refs.addProvider.open()">
              <i class="fa fa-user"></i>
            </button>

          </div>
        </div>

      </div>

      <!-- ======================== TABLA DE RECURSOS ======================== -->
      <section>
        <div>
          <table class="table table-sm table-responsive-sm table-bordered table-hover">
            <thead class="bg-success text-white position-sticky sticky-top" style="top: 4rem">
              <tr>
                <th></th>
                <th>ID</th>
                <th>Recurso</th>
                <th>Cantidad</th>
                <th>Descuento %</th>
                <th>Descuento $</th>
                <th>Precio <br><small>IVA inc</small></th>
                <th>Total</th>
              </tr>
            </thead>

            <tbody v-if="itemsPurchaseList.length > 0">
              <tr v-for="(p, index) in itemsPurchaseList" :key="p.id">

                <td>
                  <button class="btn text-danger" @click="removeResource(index, p.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>

                <td>{{ p.id }}</td>
                <td>{{ p.item }}</td>

                <td>
                  <input
                    type="number"
                    name="quantity"
                    step="any"
                    placeholder="Cantidad"
                    class="form-control form-control-sm"
                    v-model="p.quantity"
                    style="max-width: 60px"
                  />
                </td>

                <td>
                  <input
                    type="number"
                    name="discount_percentage"
                    step="any"
                    placeholder="Descuento"
                    class="form-control form-control-sm"
                    v-model="p.discount_percentage"
                    style="max-width: 60px"
                  />
                </td>

                <td>
                  <input
                    v-if="p.discount_percentage != 0"
                    type="number"
                    class="form-control form-control-sm"
                    step="2"
                    :value="(p.discount_value = (p.quantity * p.price * (p.discount_percentage / 100)).toFixed(0))"
                    style="max-width: 100px"
                  />
                  <input
                    v-else
                    type="number"
                    class="form-control form-control-sm"
                    step="2"
                    v-model="p.discount_value"
                    style="max-width: 100px"
                  />
                </td>

                <td>
                  <input
                    type="number"
                    name="price"
                    step="any"
                    placeholder="Precio"
                    v-model="p.price"
                    class="form-control form-control-sm"
                    style="max-width: 100px"
                  />
                </td>

                <td>
                  <span v-if="p.discount_percentage != 0">
                    $ {{
                      (p.total_cost =
                        p.quantity * p.price -
                        p.quantity * p.price * (p.discount_percentage / 100))
                    }}
                  </span>

                  <span v-else>
                    $ {{
                      (p.total_cost =
                        p.quantity * p.price - p.discount_value).toFixed(2)
                    }}
                  </span>
                </td>

              </tr>
            </tbody>

            <tbody v-else>
              <tr>
                <td colspan="8" class="text-center text-muted">
                  No se han añadido recursos
                </td>
              </tr>
            </tbody>

          </table>
        </div>
      </section>

    </div>

    <!-- ======================== COLUMNA DERECHA ======================== -->
    <div class="col-md-3">
      <div class="">

        <section class="card">
          <div>
            <table class="table table-sm table-success text-right">

              <tr>
                <th colspan="7">Total:</th>
                <th>$ {{ (purchase.total_cost = total_cost).toFixed(0) }}</th>
              </tr>

              <tr>
                <th colspan="7">Descuento:</th>
                <th>$ {{ (purchase.total_discount = total_discount).toFixed(0) }}</th>
              </tr>

              <tr class="bg-success h5 text-white">
                <th colspan="7">Pagar:</th>
                <th>$ {{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}</th>
              </tr>

              <tr>
                <th colspan="7">Efectivo:</th>
                <th><input type="number" value="0" step="any" v-model="purchase.cash" /></th>
              </tr>

              <tr>
                <th colspan="7">Cambio:</th>
                <th><input type="text" :value="payment_return" readonly disabled /></th>
              </tr>

            </table>
          </div>
        </section>

        <div class="mt-2">

          <!-- GUARDAR -->
          <button type="button" class="btn btn-outline-success btn-block" @click="createOrUpdatePurchase(2)">
            <i class="bi bi-receipt"></i> <b>F1</b> Guardar
          </button>

          <!-- VOLVER A LISTADO -->
          <router-link
            to="/purchases/resources"
            type="button"
            class="btn btn-outline-secondary btn-block"
            v-if="purchase_id != 0"
          >
            <i class="bi bi-arrow-left"></i> Volver
          </router-link>

        </div>
      </div>
    </div>

    <!-- ======================== MODALES ======================== -->
    <add-resource ref="addResource" @add-resource="addResource($event)" />
    <add-provider ref="addProvider" @add-provider="addProvider($event)" />

  </div>
</template>

<script>
import AddResource from "../components/add-resource.vue";
import AddProvider from "../components/add-provider.vue";

export default {
  components: { AddResource, AddProvider },
  props: ["purchase_id"],

  data() {
    return {
      filter: {
        resource: "",
        provider: "",
        is_order: 1,
      },

      itemsPurchaseList: [],

      purchase: {
        id_provider: 1,
        provider: "Sin proveedor",
        state: 1,
        total_tax_inc: 0.0,
        total_tax_exc: 0.0,
        total_discount: 0.0,
        itemsPurchase: [],
        cash: 0,
        change: 0,
      },
    };
  },

  computed: {
    total_cost() {
      let total = 0.0;
      this.itemsPurchaseList.forEach(resource => {
        total += parseFloat(resource.price * resource.quantity);
      });
      return total;
    },

    total_discount() {
      let total = 0.0;
      this.itemsPurchaseList.forEach(resource => {
        total += parseFloat(resource.discount_value);
      });
      return total;
    },

    total_tax_inc() {
      let total = 0.0;
      this.itemsPurchaseList.forEach(resource => {
        total += parseFloat(
          resource.quantity * resource.price -
            resource.quantity * resource.price * (resource.discount_percentage / 100)
        );
      });
      return total;
    },

    payment_return() {
      let value = 0.0;
      if (this.purchase.cash > 0) {
        value = (this.purchase.cash - this.total_tax_inc).toFixed(0);
      }
      return value;
    },
  },

  methods: {
    listItemsPurchase() {
      if (this.purchase_id == 0) return;

      axios.get(`api/purchases/${this.purchase_id}`, this.$root.config).then(res => {
        this.purchase.id_provider = res.data.purchase_information.provider_id;
        this.purchase.provider = res.data.purchase_information.provider.name;
        this.itemsPurchaseList = res.data.purchase_details;
      });
    },

    searchResource() {
      if (this.filter.resource === "") return;

      axios
        .get("api/resource/search-item", { params: { ...this.filter } })
        .then(res => {
          const new_resource = res.data.resource;

          if (!new_resource) {
            $("#no-results").toast("show");
          } else {
            this.addResource(new_resource);
          }
        });

      this.filter.resource = "";
    },

    addResource(new_resource) {
      let exists = false;

      this.itemsPurchaseList.forEach(prod => {
        if (new_resource.id === prod.id) {
          exists = true;
          prod.quantity += 1;
          prod.price = prod.costo * prod.quantity;
        }
      });

      if (!exists) {
        this.itemsPurchaseList.push({
          resource_id: new_resource.id,
          id: new_resource.id,
          discount_percentage: 0,
          discount_value: 0,
          quantity: 1,
          item: new_resource.recurso,
          price: new_resource.costo,
        });
      }
    },

    removeResource(index, detail_id = null) {
      this.itemsPurchaseList.splice(index, 1);

      if (detail_id != null && detail_id != 0) {
        axios.delete(`api/purchase-details/${detail_id}`, this.$root.config);
      }
    },

    searchProvider() {
      if (this.filter.provider === "") return;

      axios
        .get("api/providers", { params: { provider: this.filter.provider } })
        .then(res => {
          const provider = res.data;
          if (!provider) {
            $("#no-results").toast("show");
          } else {
            this.addProvider(provider);
          }
        });
    },

    addProvider(provider) {
      this.purchase.id_provider = provider.id;
      this.purchase.provider = provider.provider;
      this.filter.provider = provider.provider;
    },

    createOrUpdatePurchase(state_purchase) {
      this.purchase.state = state_purchase;

      if (this.itemsPurchaseList.length === 0) {
        alert("No hay recursos en la orden");
        return;
      }

      this.purchase.itemsPurchase = this.itemsPurchaseList;

      // EDITAR
      if (this.purchase_id != 0 && this.purchase_id != null) {
        axios
          .put(`api/purchases/${this.purchase_id}`, this.purchase, this.$root.config)
          .then(() => {
            Swal.fire({
              icon: "success",
              title: "Excelente",
              text: "Los datos se han guardado correctamente",
            });

            this.$router.push("/purchases/resources");
          })
          .catch(() => {
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: "Hubo un error al guardar los datos",
            });
          });

        return;
      }

      // CREAR
      axios
        .post(`api/purchases`, this.purchase, this.$root.config)
        .then(() => {
          Swal.fire({
            icon: "success",
            title: "Excelente",
            text: "Los datos se han guardado correctamente",
          });

          this.$router.push("/purchases/resources");
        })
        .catch(() => {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Hubo un error al guardar los datos",
          });
        });
    },

    commands() {
      shortcut.add("F1", () => this.createOrUpdatePurchase(2));
      shortcut.add("F10", () => this.$refs.addResource.open());
    },
  },

  mounted() {
    $("#no-results").toast("hide");
    if (this.purchase_id != null && this.purchase_id != 0) {
      this.listItemsPurchase();
    }
    this.commands();
  },
};
</script>
