<template>
  <div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addResourceModalLabel">Recursos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="input-group">

            <input type="text" class="form-control" placeholder="Código de barras | Nombre de resourceo"
              id="inputSearchResource" aria-label=" with two button addons" aria-describedby="button-addon4"
              v-model="filters.resource" @keyup="searchResource()" />
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button" @click="searchResource()">
                Buscar Recurso
              </button>
            </div>
          </div>
          <section class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="thead-primary">
                <tr>
                  <th scope="col">#</th>
                  <th>ID</th>
                  <th scope="col">Recurso</th>
                  <th>Categoria</th>
                  <th scope="col">Precio</th>
                  <th scope="col">Cantidad</th>
                  <th>Añadir</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(resource, index) in ResourceList.data" v-bind:key="resource.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ resource.id }}</td>
                  <td>{{ resource.recurso }}</td>
                  <td>
                    {{ resource.category ? resource.category.category : "" }}
                  </td>
                  <td class="text-right">$ {{ resource.costo }}</td>
                  <td>{{ resource.cantidad }}</td>

                  <td>
                    <button class="btn btn-success" @click="$emit('add-resource', resource)">
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
  name: "add-resource",
  props: { 'is_order': 0 },
  data() {
    return {
      // Filter modal
      filters: {
        resource: "",
        is_order: 1,
        nro_results: 5
      },
      ResourceList: {},
    };
  },

  created() {
    this.listResources();
  },
  methods: {
    listResources() {
      let me = this;
      let data = {
        ...this.filters
      }
      axios
        .get(
          `api/resource/list`, { params: data },
          this.$root.config
        )
        .then(function (response) {
          me.ResourceList = response.data.resources;
        });
    },
    searchResource() {
      let me = this;
      if (me.filters.resource == "") {
        return false;
      }
      let data = {
        ...this.filters
      }
      var url = `api/resource/list`;
      if (me.filters.resource.length >= 3) {
        axios
          .get(url, { params: data }, me.$root.config)
          .then(function (response) {
            me.ResourceList = response.data.resources;
            console.log(me.ResourceList)
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