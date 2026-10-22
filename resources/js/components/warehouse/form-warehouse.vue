<template>
  <div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel"
       aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">{{ edit ? 'Editar bodega' : 'Crear bodega' }}</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <form>
            <div class="form-group">
              <label>Bodega</label>
              <input type="text" class="form-control" v-model="formWarehouse.warehouse">
              <small class="text-danger">{{ formErrors.warehouse }}</small>
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <input type="text" class="form-control" v-model="formWarehouse.description">
              <small class="text-danger">{{ formErrors.description }}</small>
            </div>

            <div class="form-group">
              <label>Estado</label>

              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="formWarehouse.state"
                       value="1" id="estado-1">
                <label class="form-check-label" for="estado-1">Activo</label>
              </div>

              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="formWarehouse.state"
                       value="0" id="estado-0">
                <label class="form-check-label" for="estado-0">Desactivado</label>
              </div>

              <small class="text-danger">{{ formErrors.state }}</small>
            </div>
          </form>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" @click="closeModal()">Cerrar</button>
          <button type="button" class="btn btn-outline-primary" @click="SaveWarehouse()">Guardar</button>
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

      formWarehouse: {
        id: null,
        warehouse: "",
        description: "",
        state: "1"
      },

      formErrors: {
        warehouse: "",
        description: "",
        state: ""
      }
    };
  },

  methods: {

    /* ABRIR PARA CREACIÓN */
    abrir() {
      this.edit = false;

      this.formWarehouse = {
        id: null,
        warehouse: "",
        description: "",
        state: "1"
      };

      this.assignErrors(false);

      $("#warehouseModal").modal("show");
    },

    /* CREAR */
    CreateWarehouse() {
      this.assignErrors(false);

      axios.post("api/warehouses", this.formWarehouse, this.$root.config)
        .then(() => {
          $("#warehouseModal").modal("hide");
          this.$emit("list-warehouses");
        })
        .catch(res => this.assignErrors(res));
    },

    /* EDITAR */
    OpenEditWarehouse(warehouse) {
      this.edit = true;

      this.formWarehouse = {
        id: warehouse.id,
        warehouse: warehouse.warehouse,
        description: warehouse.description,
        state: warehouse.state ? "1" : "0"
      };

      this.assignErrors(false);

      $("#warehouseModal").modal("show");
    },

    EditWarehouse() {
      this.assignErrors(false);

      axios.put("api/warehouses/" + this.formWarehouse.id, this.formWarehouse, this.$root.config)
        .then(() => {
          $("#warehouseModal").modal("hide");
          this.$emit("list-warehouses");
        })
        .catch(res => this.assignErrors(res));
    },

    SaveWarehouse() {
      if (!this.edit) this.CreateWarehouse();
      else this.EditWarehouse();
    },

    ResetData() {
      this.edit = false;

      this.formWarehouse = {
        id: null,
        warehouse: "",
        description: "",
        state: "1"
      };

      this.assignErrors(false);

      $("#warehouseModal").modal("hide");
    },

    closeModal() {
      this.ResetData();
      this.$emit("list-warehouses");
    },

    assignErrors(response) {
      if (response) {
        let e = response.response.data.errors;
        this.formErrors = {
          warehouse: e?.warehouse ?? "",
          description: e?.description ?? "",
          state: e?.state ?? ""
        };
      } else {
        this.formErrors = { warehouse: "", description: "", state: "" };
      }
    }
  }
};
</script>
