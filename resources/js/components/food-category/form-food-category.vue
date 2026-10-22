<template>
  <div
    class="modal fade"
    id="foodCategoryModal"
    tabindex="-1"
    aria-labelledby="foodCategoryModalLabel"
    aria-hidden="true"
    data-backdrop="static"
  >
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ edit ? "Editar Categoría" : "Crear Categoría" }}
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
                v-model="formFoodCategory.category"
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
                  v-model="formFoodCategory.state"
                />
                <label class="form-check-label">Activo</label>
              </div>

              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  value="0"
                  v-model="formFoodCategory.state"
                />
                <label class="form-check-label">Inactivo</label>
              </div>

              <small class="text-danger">{{ formErrors.state }}</small>
            </div>

          </form>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-secondary" @click="closeModal()">
            Cerrar
          </button>

          <button class="btn btn-outline-primary" @click="SaveFoodCategory()">
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

      formFoodCategory: {
        id: null,
        category: "",
        state: "1",
      },

      formErrors: {
        category: "",
        state: "",
      }
    };
  },

  methods: {
    abrir() {
      this.edit = false;

      this.formFoodCategory = {
        id: null,
        category: "",
        state: "1",
      };

      this.assignErrors(false);
      $("#foodCategoryModal").modal("show");
    },

    CreateFoodCategory() {
      axios.post("api/food-categories", this.formFoodCategory, this.$root.config)
        .then(() => {
          $("#foodCategoryModal").modal("hide");
          this.$emit("list-food-categories");
        })
        .catch(res => this.assignErrors(res));
    },

    SaveFoodCategory() {
      if (!this.edit) this.CreateFoodCategory();
      else this.EditFoodCategory();
    },

    OpenEditFoodCategory(category) {
      this.edit = true;

      this.formFoodCategory = {
        id: category.id,
        category: category.category,
        state: category.state ? "1" : "0",
      };

      this.assignErrors(false);
      $("#foodCategoryModal").modal("show");
    },

    EditFoodCategory() {
      axios.put(`api/food-categories/${this.formFoodCategory.id}`, this.formFoodCategory, this.$root.config)
        .then(() => {
          $("#foodCategoryModal").modal("hide");
          this.$emit("list-food-categories");
        })
        .catch(res => this.assignErrors(res));
    },

    ResetData() {
      this.edit = false;

      this.formFoodCategory = {
        id: null,
        category: "",
        state: "1",
      };

      this.assignErrors(false);
    },

    closeModal() {
      $("#foodCategoryModal").modal("hide");
      this.ResetData();
      this.$emit("list-food-categories");
    },

    assignErrors(res) {
      if (!res) {
        this.formErrors = { category: "", state: "" };
        return;
      }

      const e = res.response.data.errors;

      this.formErrors = {
        category: e?.category ?? "",
        state: e?.state ?? ""
      };
    }
  }
};
</script>
