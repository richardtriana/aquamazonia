<template>
  <div 
    class="modal fade" 
    id="providerModal" 
    tabindex="-1" 
    aria-labelledby="providerModalLabel" 
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ edit ? 'Editar proveedor' : 'Crear proveedor' }}
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <form>

            <div class="form-group">
              <label>Proveedor</label>
              <input type="text" class="form-control" v-model="formProvider.provider">
              <small class="text-danger">{{ formErrors.provider }}</small>
            </div>

            <div class="form-group">
              <label>NIT</label>
              <input type="text" class="form-control" v-model="formProvider.nit">
              <small class="text-danger">{{ formErrors.nit }}</small>
            </div>

            <div class="form-group">
              <label>Teléfono</label>
              <input type="text" class="form-control" v-model="formProvider.tel">
              <small class="text-danger">{{ formErrors.tel }}</small>
            </div>

            <div class="form-group">
              <label>Dirección</label>
              <input type="text" class="form-control" v-model="formProvider.address">
              <small class="text-danger">{{ formErrors.address }}</small>
            </div>

            <div class="form-group">
              <label>Estado</label>

              <div class="form-check">
                <input 
                  class="form-check-input" 
                  type="radio" 
                  v-model="formProvider.state"
                  value="1">
                <label class="form-check-label">Activo</label>
              </div>

              <div class="form-check">
                <input 
                  class="form-check-input" 
                  type="radio" 
                  v-model="formProvider.state"
                  value="0">
                <label class="form-check-label">Desactivado</label>
              </div>

              <small class="text-danger">{{ formErrors.state }}</small>
            </div>

          </form>

        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" @click="closeModal()">Cerrar</button>
          <button class="btn btn-outline-primary" @click="SaveProvider()">Guardar</button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      edit: false,

      formProvider: {
        id: null,
        provider: "",
        nit: "",
        tel: "",
        address: "",
        state: "1"
      },

      formErrors: {
        provider: "",
        nit: "",
        tel: "",
        address: "",
        state: ""
      }
    };
  },

  methods: {

    abrir() {
      this.edit = false;

      this.formProvider = {
        id: null,
        provider: "",
        nit: "",
        tel: "",
        address: "",
        state: "1"
      };

      this.assignErrors(false);

      $("#providerModal").modal("show");
    },

    CreateProvider() {
      axios.post("api/providers", this.formProvider, this.$root.config)
        .then(() => {
          $("#providerModal").modal("hide");
          this.$emit("list-providers");
        })
        .catch(res => this.assignErrors(res));
    },

    OpenEditProvider(provider) {
      this.edit = true;

      this.formProvider = {
        id: provider.id,
        provider: provider.provider,
        nit: provider.nit,
        tel: provider.tel,
        address: provider.address,
        state: provider.state ? "1" : "0"
      };

      this.assignErrors(false);

      $("#providerModal").modal("show");
    },

    EditProvider() {
      axios.put("api/providers/" + this.formProvider.id, this.formProvider, this.$root.config)
        .then(() => {
          $("#providerModal").modal("hide");
          this.$emit("list-providers");
        })
        .catch(res => this.assignErrors(res));
    },

    SaveProvider() {
      if (!this.edit) this.CreateProvider();
      else this.EditProvider();
    },

    ResetData() {
      this.edit = false;
      this.formProvider = {
        id: null,
        provider: "",
        nit: "",
        tel: "",
        address: "",
        state: "1"
      };
      this.assignErrors(false);
    },

    closeModal() {
      $("#providerModal").modal("hide");
      this.ResetData();
      this.$emit("list-providers");
    },

    assignErrors(response) {
      if (response) {
        const e = response.response.data.errors;
        this.formErrors = {
          provider: e?.provider ?? "",
          nit: e?.nit ?? "",
          tel: e?.tel ?? "",
          address: e?.address ?? "",
          state: e?.state ?? ""
        };
      } else {
        this.formErrors = {
          provider: "",
          nit: "",
          tel: "",
          address: "",
          state: ""
        };
      }
    }
  }
};
</script>
