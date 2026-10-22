<template>
  <div
    class="modal fade"
    id="resourceCategoryModal"
    tabindex="-1"
    aria-labelledby="resourceCategoryModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ edit ? "Editar categoría de recurso" : "Crear categoría de recurso" }}
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <form>

            <div class="form-group">
              <label>Categoría</label>
              <input
                type="text"
                class="form-control"
                v-model="formResourceCategory.category"
              />
              <small class="text-danger">{{ formErrors.category }}</small>
            </div>

            <div class="form-group">
              <label>Estado</label>

              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  value="1"
                  v-model="formResourceCategory.state"
                />
                <label class="form-check-label">Activo</label>
              </div>

              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  value="0"
                  v-model="formResourceCategory.state"
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

          <button class="btn btn-outline-primary" @click="SaveResourceCategory()">
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

      formResourceCategory: {
        id: null,
        category: "",
        state: "1",
      },

      formErrors: {
        category: "",
        state: "",
      },
    };
  },

  methods: {
    abrir() {
      this.edit = false;

      this.formResourceCategory = {
        id: null,
        category: "",
        state: "1",
      };

      this.assignErrors(false);
      $("#resourceCategoryModal").modal("show");
    },

    CreateResourceCategory() {
      axios.post("api/resource-categories", this.formResourceCategory, this.$root.config)
        .then(() => {
          $("#resourceCategoryModal").modal("hide");
          this.$emit("list-resource-categories");
        })
        .catch(res => this.assignErrors(res));
    },

    SaveResourceCategory() {
      if (!this.edit) this.CreateResourceCategory();
      else this.EditResourceCategory();
    },

    OpenEditResourceCategory(category) {
      this.edit = true;

      this.formResourceCategory = {
        id: category.id,
        category: category.category,
        state: category.state ? "1" : "0",
      };

      this.assignErrors(false);
      $("#resourceCategoryModal").modal("show");
    },

    EditResourceCategory() {
      axios.put(`api/resource-categories/${this.formResourceCategory.id}`, this.formResourceCategory, this.$root.config)
        .then(() => {
          $("#resourceCategoryModal").modal("hide");
          this.$emit("list-resource-categories");
        })
        .catch(res => this.assignErrors(res));
    },

    ResetData() {
      this.edit = false;

      this.formResourceCategory = {
        id: null,
        category: "",
        state: "1",
      };

      this.assignErrors(false);
    },

    closeModal() {
      $("#resourceCategoryModal").modal("hide");
      this.ResetData();
      this.$emit("list-resource-categories");
    },

    assignErrors(res) {
      if (!res) {
        this.formErrors = { category: "", state: "" };
        return;
      }

      const e = res.response?.data?.errors || {};

      this.formErrors = {
        category: e.category ?? "",
        state: e.state ?? "",
      };
    }
  }
};
</script>
