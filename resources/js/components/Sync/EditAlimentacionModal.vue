<template>
    <div>
        <div class="modal" :class="{ 'show d-block': visible }" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Preregistro de Alimentación</h5>
                        <button type="button" class="close" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="guardarCambios">
                            <div class="row">
                                <!-- Fecha -->
                                <div class="form-group col-md-6">
                                    <label for="edit_fecha_ra">Fecha *</label>
                                    <input
                                        id="edit_fecha_ra"
                                        type="date"
                                        class="form-control"
                                        v-model="formData.fecha_ra"
                                        :class="{ 'is-invalid': errors.fecha_ra }"
                                        required
                                    />
                                    <div v-if="errors.fecha_ra" class="invalid-feedback">
                                        {{ errors.fecha_ra[0] }}
                                    </div>
                                </div>

                                <!-- Siembra -->
                                <div class="form-group col-md-6">
                                    <label for="edit_id_siembra">Siembra *</label>
                                    <select
                                        id="edit_id_siembra"
                                        class="form-control"
                                        v-model="formData.id_siembra"
                                        :class="{ 'is-invalid': errors.id_siembra }"
                                        required
                                    >
                                        <option value="">Seleccionar siembra</option>
                                        <option
                                            v-for="siembra in siembras"
                                            :key="siembra.id"
                                            :value="siembra.id"
                                        >
                                            {{ siembra.nombre_siembra }}
                                        </option>
                                    </select>
                                    <div v-if="errors.id_siembra" class="invalid-feedback">
                                        {{ errors.id_siembra[0] }}
                                    </div>
                                </div>

                                <!-- Alimento -->
                                <div class="form-group col-md-6">
                                    <label for="edit_id_alimento">Alimento *</label>
                                    <select
                                        id="edit_id_alimento"
                                        class="form-control"
                                        v-model="formData.id_alimento"
                                        :class="{ 'is-invalid': errors.id_alimento }"
                                        required
                                    >
                                        <option value="">Seleccionar alimento</option>
                                        <option
                                            v-for="alimento in alimentos"
                                            :key="alimento.id"
                                            :value="alimento.id"
                                        >
                                            {{ alimento.alimento }}
                                        </option>
                                    </select>
                                    <div v-if="errors.id_alimento" class="invalid-feedback">
                                        {{ errors.id_alimento[0] }}
                                    </div>
                                </div>

                                <!-- Registrador -->
                                <div class="form-group col-md-6">
                                    <label for="edit_registered_user_id">Registrador</label>
                                    <select
                                        id="edit_registered_user_id"
                                        class="form-control"
                                        v-model="formData.registered_user_id"
                                        :class="{ 'is-invalid': errors.registered_user_id }"
                                    >
                                        <option value="">Seleccionar registrador</option>
                                        <option
                                            v-for="user in registeredUsers"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ formatUser(user) }}
                                        </option>
                                    </select>
                                    <div v-if="errors.registered_user_id" class="invalid-feedback">
                                        {{ errors.registered_user_id[0] }}
                                    </div>
                                </div>

                                <!-- Cantidad Mañana -->
                                <div class="form-group col-md-4">
                                    <label for="edit_cant_manana">Cantidad Mañana (kg)</label>
                                    <input
                                        id="edit_cant_manana"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        v-model.number="formData.cant_manana"
                                        :class="{ 'is-invalid': errors.cant_manana }"
                                    />
                                    <div v-if="errors.cant_manana" class="invalid-feedback">
                                        {{ errors.cant_manana[0] }}
                                    </div>
                                </div>

                                <!-- Cantidad Tarde -->
                                <div class="form-group col-md-4">
                                    <label for="edit_cant_tarde">Cantidad Tarde (kg)</label>
                                    <input
                                        id="edit_cant_tarde"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        v-model.number="formData.cant_tarde"
                                        :class="{ 'is-invalid': errors.cant_tarde }"
                                    />
                                    <div v-if="errors.cant_tarde" class="invalid-feedback">
                                        {{ errors.cant_tarde[0] }}
                                    </div>
                                </div>

                                <!-- Minutos Hombre -->
                                <div class="form-group col-md-4">
                                    <label for="edit_minutos_hombre">Minutos Hombre</label>
                                    <input
                                        id="edit_minutos_hombre"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        v-model.number="formData.minutos_hombre"
                                        :class="{ 'is-invalid': errors.minutos_hombre }"
                                    />
                                    <div v-if="errors.minutos_hombre" class="invalid-feedback">
                                        {{ errors.minutos_hombre[0] }}
                                    </div>
                                </div>

                                <!-- Conversión Alimenticia -->
                                <div class="form-group col-md-6">
                                    <label for="edit_conv_alimenticia">Conversión Alimenticia Teórica</label>
                                    <input
                                        id="edit_conv_alimenticia"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        v-model.number="formData.conv_alimenticia"
                                        :class="{ 'is-invalid': errors.conv_alimenticia }"
                                    />
                                    <div v-if="errors.conv_alimenticia" class="invalid-feedback">
                                        {{ errors.conv_alimenticia[0] }}
                                    </div>
                                </div>

                                <!-- Tipo Actividad -->
                                <div class="form-group col-md-6">
                                    <label for="edit_tipo_actividad">Tipo Actividad</label>
                                    <input
                                        id="edit_tipo_actividad"
                                        type="text"
                                        class="form-control"
                                        v-model="formData.tipo_actividad"
                                        :class="{ 'is-invalid': errors.tipo_actividad }"
                                    />
                                    <div v-if="errors.tipo_actividad" class="invalid-feedback">
                                        {{ errors.tipo_actividad[0] }}
                                    </div>
                                </div>

                                <!-- Detalles -->
                                <div class="form-group col-12">
                                    <label for="edit_detalles">Detalles</label>
                                    <textarea
                                        id="edit_detalles"
                                        class="form-control"
                                        v-model="formData.detalles"
                                        :class="{ 'is-invalid': errors.detalles }"
                                        rows="3"
                                    ></textarea>
                                    <div v-if="errors.detalles" class="invalid-feedback">
                                        {{ errors.detalles[0] }}
                                    </div>
                                </div>

                                <!-- Tipo (hidden, para alimentación siempre es null o ALIMENTACION) -->
                                <input type="hidden" v-model="formData.tipo" />
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="guardarCambios"
                            :disabled="saving"
                        >
                            <span v-if="saving" class="spinner-border spinner-border-sm mr-1"></span>
                            {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="visible" class="modal-backdrop fade show"></div>
    </div>
</template>

<script>
export default {
    props: {
        siembras: {
            type: Array,
            default: () => []
        },
        alimentos: {
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
            visible: false,
            saving: false,
            formData: {
                id: null,
                fecha_ra: '',
                id_siembra: '',
                id_alimento: '',
                registered_user_id: '',
                cant_manana: null,
                cant_tarde: null,
                minutos_hombre: null,
                conv_alimenticia: null,
                tipo_actividad: '',
                detalles: '',
                tipo: null
            },
            errors: {}
        };
    },
    methods: {
        open(item) {
            this.resetForm();
            this.formData.id = item.id;
            this.formData.fecha_ra = this.formatDateForInput(item.fecha_ra);
            this.formData.id_siembra = item.id_siembra;
            this.formData.id_alimento = item.id_alimento;
            this.formData.registered_user_id = item.registered_user_id || '';
            this.formData.cant_manana = item.cant_manana !== null ? parseFloat(item.cant_manana) : null;
            this.formData.cant_tarde = item.cant_tarde !== null ? parseFloat(item.cant_tarde) : null;
            this.formData.minutos_hombre = item.minutos_hombre !== null ? parseFloat(item.minutos_hombre) : null;
            this.formData.conv_alimenticia = item.conv_alimenticia !== null ? parseFloat(item.conv_alimenticia) : null;
            this.formData.tipo_actividad = item.tipo_actividad || '';
            this.formData.detalles = item.detalles || '';
            this.formData.tipo = item.tipo || null;
            
            this.visible = true;
            this.errors = {};
        },
        
        closeModal() {
            this.visible = false;
            this.resetForm();
        },
        
        resetForm() {
            this.formData = {
                id: null,
                fecha_ra: '',
                id_siembra: '',
                id_alimento: '',
                registered_user_id: '',
                cant_manana: null,
                cant_tarde: null,
                minutos_hombre: null,
                conv_alimenticia: null,
                tipo_actividad: '',
                detalles: '',
                tipo: null
            };
            this.errors = {};
        },
        
        formatDateForInput(dateString) {
            if (!dateString) return '';
            // Si ya está en formato YYYY-MM-DD
            if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return dateString;
            }
            // Convertir de DD/MM/YYYY a YYYY-MM-DD
            if (dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                const parts = dateString.split('/');
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            // Convertir de timestamp o fecha completa
            const date = new Date(dateString);
            if (!isNaN(date.getTime())) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            return dateString;
        },
        
        formatUser(user) {
            if (!user) return '';
            if (user.name) return user.name;
            if (user.email) return user.email;
            return `Usuario #${user.id}`;
        },
        
        guardarCambios() {
            this.saving = true;
            this.errors = {};
            
            // Preparar datos para enviar
            const dataToSend = { ...this.formData };
            
            // Convertir valores vacíos a null
            Object.keys(dataToSend).forEach(key => {
                if (dataToSend[key] === '') {
                    dataToSend[key] = null;
                }
            });
            
            axios.put(`api/feeding-prelogs/${this.formData.id}`, dataToSend)
                .then(response => {
                    this.saving = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'El preregistro se actualizó correctamente.',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    
                    this.$emit('updated');
                    this.closeModal();
                })
                .catch(error => {
                    this.saving = false;
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: 'Por favor, corrige los errores en el formulario.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        console.error(error);
                        Swal.fire(
                            'Error',
                            'No se pudo actualizar el preregistro.',
                            'error'
                        );
                    }
                });
        }
    }
};
</script>

<style scoped>
.modal {
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
}

.modal-dialog {
    margin-top: 100px;
}

.modal-backdrop {
    z-index: 1040;
}

.form-group label {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.form-control {
    border-radius: 8px;
    font-size: 0.9rem;
}

.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.invalid-feedback {
    display: block;
    font-size: 0.8rem;
}

.modal-title {
    color: #1d4ed8;
    font-weight: 600;
}

.modal-header {
    border-bottom: 2px solid #1d4ed8;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}
</style>