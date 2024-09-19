<template>
  <div class="modal fade" id="addProviderModal" tabindex="-1" aria-labelledby="addProviderModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <div class="row w-100">
            <h5 class="modal-title col-8" id="addProviderModalLabel">Proveedores</h5>
            <div class="col-3">
              <button type="reset" class="btn btn-primary" data-toggle="modal" data-target="#providerModal"
                @click="edit = false" >
                Crear Proveedor
              </button>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Documento | Nombre de proveedor"
              aria-label=" with two button addons" aria-describedby="button-addon4" v-model="filters.provider"
              @keyup="searchProvider()" />
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button" @click="searchProvider()">
                Buscar Proveedor
              </button>
            </div>
          </div>
          <table class="table table-bordered table-sm table-responsive">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nombres</th>
                <th>NIT</th>
                <th>Direccion</th>
                <th>Contacto</th>
                <th>Opciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="provider in providerList.data" v-bind:key="provider.id">
                <th scope="row">{{ provider.id }}</th>
                <td>{{ provider.provider }}</td>
                <td>{{ provider.nit }}</td>
                <td>{{ provider.address }}</td>
                <td>{{ provider.tel }}</td>
                <td>
                  <button class="btn btn-outline-secondary" @click="$emit('add-provider', provider)"
                    data-dismiss="modal">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cerrar
          </button>
        </div>
      </div>
    </div>
     <create-edit-provider
      ref="CreateEditProvider"
      @list-providers="listProviders(1)"
    />
  </div>
</template>

<script>
import CreateEditProvider from "../../provider/form-provider.vue";

export default {
  components: { CreateEditProvider },
  name: "add-provider",
  data() {
    return {
      providerList: {},
      filters: {
        provider: "",
      },
    };
  },
  created() {
    this.listProviders();
  },
  methods: {
    listProviders() {
      let me = this;
      axios
        .get("api/providers", null, this.$root.config)
        .then(function (response) {
          me.providerList = response.data.providers;
          console.log(response.data.providers)
        });
    },
    searchProvider() {
      let me = this;
      if (me.filters.provider == "") {
        return false;
      }
      var url = "api/providers?provider=" + me.filters.provider;
      if (me.filters.provider.length >= 3) {
        axios
          .get(url, null, me.$root.config)
          .then(function (response) {
            me.providerList = response.data.providers;
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