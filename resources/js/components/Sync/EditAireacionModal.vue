<template>
    <div>
        <div class="modal" :class="{ 'show d-block': visible }" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Preregistro de Aireación</h5>
                        <button type="button" class="close" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="guardarCambios">
                            <div class="row">
                                <!-- Fecha -->
                                <div class="form-group col-md-6">
                                    <label for="edit_fecha_ra_aireacion">Fecha *</label>
                                    <input
                                        id="edit_fecha_ra_aireacion"
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
                                    <label for="edit_id_siembra_aireacion">Siembra *</label>
                                    <select
                                        id="edit_id_siembra_aireacion"
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

                                <!-- Tipo Actividad -->
                                <div class="form-group col-md-6">
                                    <label for="edit_tipo_actividad_aireacion">Tipo Actividad *</label>
                                    <select
                                        id="edit_tipo_actividad_aireacion"
                                        class="form-control"
                                        v-model="formData.tipo_actividad"
                                        :class="{ 'is-invalid': errors.tipo_actividad }"
                                        required
                                    >
                                        <option value="">Seleccionar tipo</option>
                                        <option value="12">Aireación Mecánica</option>
                                    </select>
                                    <div v-if="errors.tipo_actividad" class="invalid-feedback">
                                        {{ errors.tipo_actividad[0] }}
                                    </div>
                                </div>

                                <!-- Recurso -->
                                <div class="form-group col-md-6">
                                    <label for="edit_id_recurso_aireacion">Recurso *</label>
                                    <select
                                        id="edit_id_recurso_aireacion"
                                        class="form-control"
                                        v-model="formData.id_recurso"
                                        :class="{ 'is-invalid': errors.id_recurso }"
                                        required
                                    >
                                        <option value="">Seleccionar recurso</option>
                                        <option
                                            v-for="recurso in recursos"
                                            :key="recurso.id"
                                            :value="recurso.id"
                                        >
                                            {{ recurso.nombre }}
                                        </option>
                                    </select>
                                    <div v-if="errors.id_recurso" class="invalid-feedback">
                                        {{ errors.id_recurso[0] }}
                                    </div>
                                </div>

                                <!-- Registrador -->
                                <div class="form-group col-md-6">
                                    <label for="edit_registered_user_id_aireacion">Registrador</label>
                                    <select
                                        id="edit_registered_user_id_aireacion"
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

                                <!-- Minutos Hombre -->
                                <div class="form-group col-md-6">
                                    <label for="edit_minutos_hombre_aireacion">Minutos Hombre *</label>
                                    <input
                                        id="edit_minutos_hombre_aireacion"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        v-model.number="formData.minutos_hombre"
                                        :class="{ 'is-invalid': errors.minutos_hombre }"
                                        required
                                    />
                                    <div v-if="errors.minutos_hombre" class="invalid-feedback">
                                        {{ errors.minutos_hombre[0] }}
                                    </div>
                                </div>

                                <!-- Descripción -->
                                <div class="form-group col-md-12">
                                    <label for="edit_descripcion_aireacion">Descripción</label>
                                    <input
                                        id="edit_descripcion_aireacion"
                                        type="text"
                                        class="form-control"
                                        v-model="formData.descripcion"
                                        :class="{ 'is-invalid': errors.descripcion }"
                                    />
                                    <div v-if="errors.descripcion" class="invalid-feedback">
                                        {{ errors.descripcion[0] }}
                                    </div>
                                </div>

                                <!-- Detalles -->
                                <div class="form-group col-12">
                                    <label for="edit_detalles_aireacion">Detalles</label>
                                    <textarea
                                        id="edit_detalles_aireacion"
                                        class="form-control"
                                        v-model="formData.detalles"
                                        :class="{ 'is-invalid': errors.detalles }"
                                        rows="3"
                                    ></textarea>
                                    <div v-if="errors.detalles" class="invalid-feedback">
                                        {{ errors.detalles[0] }}
                                    </div>
                                </div>

                                <!-- Campos para cálculos (pueden estar ocultos) -->
                                <div class="form-group col-md-6 d-none">
                                    <label for="edit_cantidad">Cantidad</label>
                                    <input
                                        id="edit_cantidad"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        v-model.number="formData.cantidad"
                                    />
                                </div>

                                <div class="form-group col-md-6 d-none">
                                    <label for="edit_costo">Costo</label>
                                    <input
                                        id="edit_costo"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        v-model.number="formData.costo"
                                    />
                                </div>

                                <div class="form-group col-md-6 d-none">
                                    <label for="edit_total">Total</label>
                                    <input
                                        id="edit_total"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        v-model.number="formData.total"
                                    />
                                </div>

                                <!-- Tipo (siempre AIREACION) -->
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
        recursos: {
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
                id_recurso: '',
                tipo_actividad: '12', // Por defecto Aireación Mecánica
                registered_user_id: '',
                minutos_hombre: null,
                descripcion: '',
                detalles: '',
                cantidad: null,
                costo: null,
                total: null,
                tipo: 'AIREACION'
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
            this.formData.id_recurso = item.id_recurso;
            this.formData.tipo_actividad = item.tipo_actividad || '12';
            this.formData.registered_user_id = item.registered_user_id || '';
            this.formData.minutos_hombre = item.minutos_hombre !== null ? parseFloat(item.minutos_hombre) : null;
            this.formData.descripcion = item.descripcion || '';
            this.formData.detalles = item.detalles || '';
            this.formData.cantidad = item.cantidad !== null ? parseFloat(item.cantidad) : null;
            this.formData.costo = item.costo !== null ? parseFloat(item.costo) : null;
            this.formData.total = item.total !== null ? parseFloat(item.total) : null;
            this.formData.tipo = item.tipo || 'AIREACION';
            
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
                id_recurso: '',
                tipo_actividad: '12',
                registered_user_id: '',
                minutos_hombre: null,
                descripcion: '',
                detalles: '',
                cantidad: null,
                costo: null,
                total: null,
                tipo: 'AIREACION'
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
            
            // Asegurar que el tipo sea AIREACION
            dataToSend.tipo = 'AIREACION';
            
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

.d-none {
    display: none !important;
}
</style>