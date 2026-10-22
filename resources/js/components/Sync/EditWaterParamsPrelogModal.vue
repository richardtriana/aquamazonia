<template>
    <div class="modal" tabindex="-1" role="dialog" :class="{ 'd-block': isOpen }">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ isEditing ? 'Editar Preregistro' : 'Nuevo Preregistro' }} - Parámetros de Agua
                    </h5>
                    <button type="button" class="close" @click="close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="save">
                        <div class="row">
                            <!-- Fecha del parámetro -->
                            <div class="form-group col-md-6">
                                <label for="fecha_parametro">Fecha del parámetro *</label>
                                <input
                                    type="date"
                                    id="fecha_parametro"
                                    class="form-control"
                                    v-model="form.fecha_parametro"
                                    required
                                />
                            </div>

                            <!-- Siembra -->
                            <div class="form-group col-md-6">
                                <label for="id_siembra">Siembra *</label>
                                <v-select
                                    :options="siembras"
                                    label="nombre_siembra"
                                    :reduce="s => s.id"
                                    v-model="form.id_siembra"
                                    placeholder="Seleccionar siembra"
                                    required
                                />
                            </div>

                            <!-- Registrador -->
                            <div class="form-group col-md-6">
                                <label for="registered_user_id">Registrador *</label>
                                <select
                                    id="registered_user_id"
                                    class="form-control"
                                    v-model="form.registered_user_id"
                                    required
                                >
                                    <option value="">Seleccionar registrador</option>
                                    <option
                                        v-for="user in registeredUsers"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ formatRegisteredUser(user) }}
                                    </option>
                                </select>
                            </div>

                            <!-- pH -->
                            <div class="form-group col-md-6">
                                <label for="ph">pH (0-14)</label>
                                <input
                                    type="number"
                                    id="ph"
                                    class="form-control"
                                    v-model.number="form.ph"
                                    min="0"
                                    max="14"
                                    step="0.1"
                                    placeholder="Ej: 7.5"
                                />
                            </div>

                            <!-- Temperatura -->
                            <div class="form-group col-md-6">
                                <label for="temperatura">Temperatura (°C)</label>
                                <input
                                    type="number"
                                    id="temperatura"
                                    class="form-control"
                                    v-model.number="form.temperatura"
                                    min="0"
                                    step="0.1"
                                    placeholder="Ej: 25.5"
                                />
                            </div>

                            <!-- Amonio -->
                            <div class="form-group col-md-6">
                                <label for="amonio">Amonio (mg/L)</label>
                                <input
                                    type="number"
                                    id="amonio"
                                    class="form-control"
                                    v-model.number="form.amonio"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej: 0.25"
                                />
                            </div>

                            <!-- Nitrito -->
                            <div class="form-group col-md-6">
                                <label for="nitrito">Nitrito (mg/L)</label>
                                <input
                                    type="number"
                                    id="nitrito"
                                    class="form-control"
                                    v-model.number="form.nitrito"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej: 0.10"
                                />
                            </div>

                            <!-- Nitrato -->
                            <div class="form-group col-md-6">
                                <label for="nitrato">Nitrato (mg/L)</label>
                                <input
                                    type="number"
                                    id="nitrato"
                                    class="form-control"
                                    v-model.number="form.nitrato"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej: 5.00"
                                />
                            </div>

                            <!-- Oxígeno disuelto - 12 am -->
                            <div class="form-group col-md-4">
                                <label for="12_am">Oxígeno 12 am (mg/L)</label>
                                <input
                                    type="number"
                                    id="12_am"
                                    class="form-control"
                                    v-model.number="form['12_am']"
                                    min="0"
                                    step="0.1"
                                    placeholder="Ej: 5.5"
                                />
                            </div>

                            <!-- Oxígeno disuelto - 7 am -->
                            <div class="form-group col-md-4">
                                <label for="7_am">Oxígeno 7 am (mg/L)</label>
                                <input
                                    type="number"
                                    id="7_am"
                                    class="form-control"
                                    v-model.number="form['7_am']"
                                    min="0"
                                    step="0.1"
                                    placeholder="Ej: 6.0"
                                />
                            </div>

                            <!-- Oxígeno disuelto - 8 pm -->
                            <div class="form-group col-md-4">
                                <label for="8_pm">Oxígeno 8 pm (mg/L)</label>
                                <input
                                    type="number"
                                    id="8_pm"
                                    class="form-control"
                                    v-model.number="form['8_pm']"
                                    min="0"
                                    step="0.1"
                                    placeholder="Ej: 5.8"
                                />
                            </div>

                            <!-- Otros parámetros -->
                            <div class="form-group col-md-12">
                                <label for="otros">Otros parámetros</label>
                                <textarea
                                    id="otros"
                                    class="form-control"
                                    v-model="form.otros"
                                    rows="2"
                                    placeholder="Notas adicionales sobre otros parámetros medidos..."
                                ></textarea>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group col-md-12">
                                <label for="descripcion">Descripción / Observaciones</label>
                                <textarea
                                    id="descripcion"
                                    class="form-control"
                                    v-model="form.descripcion"
                                    rows="3"
                                    placeholder="Observaciones generales sobre los parámetros medidos..."
                                ></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="close">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving">
                            <i class="fas fa-spinner fa-spin"></i> Guardando...
                        </span>
                        <span v-else>
                            <i class="fas fa-save"></i> Guardar
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        siembras: {
            type: Array,
            default: () => []
        },
        registeredUsers: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            isOpen: false,
            isEditing: false,
            saving: false,
            form: {
                id: null,
                fecha_parametro: "",
                id_siembra: "",
                registered_user_id: "",
                ph: null,
                temperatura: null,
                amonio: null,
                nitrito: null,
                nitrato: null,
                '12_am': null,
                '7_am': null,
                '8_pm': null,
                otros: "",
                descripcion: ""
            }
        };
    },
    methods: {
        open(item = null) {
            if (item) {
                // Editar registro existente
                this.isEditing = true;
                this.form = { ...item };
            } else {
                // Nuevo registro
                this.isEditing = false;
                this.resetForm();
            }
            this.isOpen = true;
        },
        
        close() {
            this.isOpen = false;
            this.resetForm();
        },
        
        resetForm() {
            this.form = {
                id: null,
                fecha_parametro: "",
                id_siembra: "",
                registered_user_id: "",
                ph: null,
                temperatura: null,
                amonio: null,
                nitrito: null,
                nitrato: null,
                '12_am': null,
                '7_am': null,
                '8_pm': null,
                otros: "",
                descripcion: ""
            };
        },
        
        formatRegisteredUser(user) {
            if (!user) return "";
            if (user.name) return user.name;
            if (user.email) return user.email;
            return `ID ${user.id}`;
        },
        
        save() {
            this.saving = true;
            
            // Preparar datos para enviar (convertir null a "" para campos vacíos)
            const data = { ...this.form };
            
            // Enviar datos al backend
            const url = this.isEditing 
                ? `api/parametros-agua-avanzados-prelogs/${data.id}`
                : "api/parametros-agua-avanzados-prelogs";
                
            const method = this.isEditing ? "put" : "post";
            
            axios[method](url, data)
                .then(response => {
                    Swal.fire({
                        icon: "success",
                        title: "¡Guardado!",
                        text: response.data.message || "Registro guardado exitosamente.",
                        timer: 1200,
                        showConfirmButton: false
                    });
                    
                    this.close();
                    this.$emit("updated");
                })
                .catch(error => {
                    console.error(error);
                    
                    let errorMessage = "Ocurrió un error al guardar el registro.";
                    
                    if (error.response && error.response.data) {
                        if (error.response.data.errors) {
                            // Validación de Laravel
                            const errors = Object.values(error.response.data.errors).flat();
                            errorMessage = errors.join("<br>");
                        } else if (error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }
                    }
                    
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        html: errorMessage
                    });
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    }
};
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    border-radius: 10px;
}

.modal-header {
    background-color: #1d4ed8;
    color: white;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.close {
    color: white;
    opacity: 1;
}

.close:hover {
    color: #e2e8f0;
}
</style>
