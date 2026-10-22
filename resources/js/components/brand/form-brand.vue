<template>
  <div
    class="modal fade"
    id="brandModal"
    tabindex="-1"
    aria-labelledby="brandModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ edit ? "Editar Marca" : "Crear Marca" }}
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form>

            <div class="form-group">
              <label>Marca</label>
              <input
                type="text"
                class="form-control"
                v-model="formBrand.brand"
              />
              <small class="text-danger">{{ formErrors.brand }}</small>
            </div>

            <div class="form-group">
              <label>Estado</label>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  value="1"
                  v-model="formBrand.state"
                />
                <label class="form-check-label">Activo</label>
              </div>

              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  value="0"
                  v-model="formBrand.state"
                />
                <label class="form-check-label">Desactivado</label>
              </div>

              <small class="text-danger">{{ formErrors.state }}</small>
            </div>

          </form>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" @click="closeModal()">
            Cerrar
          </button>
          <button class="btn btn-outline-primary" @click="SaveBrand()">
            Guardar
          </button>
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

      formBrand: {
        id: null,
        brand: "",
        state: "1",
      },

      formErrors: {
        brand: "",
        state: ""
      }
    };
  },

  methods: {
    abrir() {
      this.edit = false;

      this.formBrand = {
        id: null,
        brand: "",
        state: "1",
      };

      this.assignErrors(false);
      $("#brandModal").modal("show");
    },

    CreateBrand() {
      axios.post("api/brands", this.formBrand, this.$root.config)
        .then(() => {
          $("#brandModal").modal("hide");
          this.$emit("list-brands");
        })
        .catch(res => this.assignErrors(res));
    },

    OpenEditBrand(brand) {
      this.edit = true;

      this.formBrand = {
        id: brand.id,
        brand: brand.brand,
        state: brand.state ? "1" : "0"
      };

      this.assignErrors(false);
      $("#brandModal").modal("show");
    },

    EditBrand() {
      axios.put(`api/brands/${this.formBrand.id}`, this.formBrand, this.$root.config)
        .then(() => {
          $("#brandModal").modal("hide");
          this.$emit("list-brands");
        })
        .catch(res => this.assignErrors(res));
    },

    SaveBrand() {
      if (!this.edit) this.CreateBrand();
      else this.EditBrand();
    },

    ResetData() {
      this.edit = false;
      this.formBrand = {
        id: null,
        brand: "",
        state: "1",
      };
      this.assignErrors(false);
    },

    closeModal() {
      $("#brandModal").modal("hide");
      this.ResetData();
      this.$emit("list-brands");
    },

    assignErrors(response) {
      if (response) {
        const e = response.response.data.errors;
        this.formErrors = {
          brand: e?.brand ?? "",
          state: e?.state ?? ""
        };
      } else {
        this.formErrors = {
          brand: "",
          state: ""
        };
      }
    }
  }
};
</script>
