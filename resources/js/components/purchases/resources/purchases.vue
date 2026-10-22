<template>
  <div class="col-12 container">

    <!-- ============================
         TÍTULO + BOTÓN
    ============================= -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="page-header">Compras de recursos</h3>

      <router-link
        class="btn btn-primary btn-sm px-4"
        :to="{ name: 'purchase-resource', params: { purchase_id: 0 } }"
      >
        <i class="fas fa-plus"></i> Nueva compra
      </router-link>
    </div>

    <!-- ============================
         FILTROS
    ============================= -->
    <div class="card shadow-sm p-3 mb-3">
      <div class="row">

        <div class="col-md-3">
          <label>Nro Compra</label>
          <input
            type="text"
            class="form-control"
            placeholder="Nro Compra"
            v-model="filter.id"
          />
        </div>

        <div class="col-md-3">
          <label>Proveedor</label>
          <input
            type="text"
            class="form-control"
            placeholder="Proveedor"
            v-model="filter.provider"
          />
        </div>

        <div class="col-md-3">
          <label>Desde</label>
          <input type="date" class="form-control" v-model="filter.from" />
        </div>

        <div class="col-md-3">
          <label>Hasta</label>
          <input type="date" class="form-control" v-model="filter.to" />
        </div>

        <div class="col-md-12 text-right mt-3">
          <button
            class="btn btn-success btn-sm px-4"
            @click="getPurchases(1)"
          >
            <i class="fas fa-search"></i> Buscar
          </button>
        </div>

      </div>
    </div>

    <!-- ============================
         TABLA
    ============================= -->
    <div class="card shadow-sm">
      <div class="card-body table-responsive-sm">

        <table class="table table-sm table-bordered table-hover">
          <thead class="thead-primary">
            <tr>
              <th>#</th>
              <th>Total Pago</th>
              <th>Total Descuento</th>
              <th>Proveedor</th>
              <th>Estado</th>
              <th>Editar</th>
            </tr>
          </thead>

          <tbody v-if="PurchaseList.data && PurchaseList.data.length">
            <tr v-for="o in PurchaseList.data" :key="o.id">
              <td>{{ o.id }}</td>
              <td>{{ o.total_cost | numeral('$0,0.00') }}</td>
              <td>{{ o.total_discount | numeral('$0,0.00') }}</td>
              <td>{{ o.provider.provider }}</td>

              <td>
                <span v-if="o.state == 0">Desechada</span>
                <span v-if="o.state == 1">Abierta</span>
                <span v-if="o.state == 2">Registrada</span>
                <span v-if="o.state == 3">Cotización</span>
              </td>

              <td>
                <router-link
                  class="btn btn-outline-primary btn-sm"
                  :to="{ name: 'purchase-resource', params: { purchase_id: o.id } }"
                >
                  <i class="fas fa-edit"></i>
                </router-link>
              </td>
            </tr>
          </tbody>

          <tbody v-else>
            <tr>
              <td colspan="6" class="text-center text-muted py-3">
                No se encontraron compras registradas
              </td>
            </tr>
          </tbody>

        </table>

      </div>
    </div>

    <!-- ============================
         PAGINACIÓN
    ============================= -->
    <pagination
      :align="'center'"
      :data="PurchaseList"
      :limit="8"
      @pagination-change-page="getPurchases"
    >
      <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
      <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
    </pagination>

  </div>
</template>

<script>
export default {
  data() {
    return {
      PurchaseList: {},

      filter: {
        id: "",
        provider: "",
        from: "",
        to: "",
        type: "recursos", // ← CLAVE para filtrar solo compras de recursos
      },
    };
  },

  created() {
    this.getPurchases(1);
  },

  methods: {
    getPurchases(page = 1) {
      const params = {
        page,
        ...this.filter,
      };

      axios
        .get("api/purchases", { params })
        .then((res) => {
          this.PurchaseList = res.data.purchases;
        });
    },
  },
};
</script>

<style scoped>
.thead-primary {
  background: #0c3d5e;
  color: white;
}
</style>
