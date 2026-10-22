<template>
    <div class="modal fade" ref="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        {{ isAireacion ? 'Editar Preregistro de Aireación' : 'Editar Preregistro de Alimentación' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="guardar">
                        <!-- Fecha -->
                        <div class="form-group">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input
                                type="date"
                                class="form-control"
                                v-model="form.fecha_ra"
                                required
                            />
                        </div>

                        <!-- Siembra -->
                        <div class="form-group">
                            <label>Siembra <span class="text-danger">*</span></label>
                            <v-select
                                :options="siembras"
                                label="nombre_siembra"
                                :reduce="s => s.id"
                                v-model="form.id_siembra"
                                placeholder="Seleccione siembra"
                                required
                            />
                        </div>

                        <!-- Registrador -->
                        <div class="form-group">
                            <label>Registrador <span class="text-danger">*</span></label>
                            <select
                                class="form-control"
                                v-model="form.registered_user_id"
                                required
                            >
                                <option value="">Seleccione registrador</option>
                                <option
                                    v-for="user in registeredUsers"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name || user.email }}
                                </option>
                            </select>
                        </div>

                        <!-- Tipo de Actividad -->
                        <div class="form-group">
                            <label>Tipo de Actividad</label>
                            <select
                                class="form-control"
                                v-model="form.tipo_actividad"
                                @change="onTipoActividadChange"
                            >
                                <option value="">Seleccione tipo</option>
                                <option value="12">Aireación Mecánica</option>
                                <!-- Agrega otros tipos si es necesario -->
                            </select>
                        </div>

                        <!-- PARA ALIMENTACIÓN -->
                        <div v-if="!isAireacion">
                            <!-- Alimento -->
                            <div class="form-group">
                                <label>Alimento <span class="text-danger">*</span></label>
                                <select
                                    class="form-control"
                                    v-model="form.id_alimento"
                                    required
                                >
                                    <option value="">Seleccione alimento</option>
                                    <option
                                        v-for="alimento in alimentos"
                                        :key="alimento.id"
                                        :value="alimento.id"
                                    >
                                        {{ alimento.alimento }}
                                    </option>
                                </select>
                            </div>

                            <!-- Cantidad Mañana -->
                            <div class="form-group">
                                <label>Cantidad Mañana (kg)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    v-model="form.cant_manana"
                                />
                            </div>

                            <!-- Cantidad Tarde -->
                            <div class="form-group">
                                <label>Cantidad Tarde (kg)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    v-model="form.cant_tarde"
                                />
                            </div>

                            <!-- Conversión Alimenticia -->
                            <div class="form-group">
                                <label>Conversión Alimenticia</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    v-model="form.conv_alimenticia"
                                />
                            </div>
                        </div>

                        <!-- PARA AIREACIÓN -->
                        <div v-if="isAireacion">
                            <!-- Recurso -->
                            <div class="form-group">
                                <label>Recurso <span class="text-danger">*</span></label>
                                <select
                                    class="form-control"
                                    v-model="form.id_recurso"
                                    required
                                >
                                    <option value="">Seleccione recurso</option>
                                    <option
                                        v-for="recurso in recursos"
                                        :key="recurso.id"
                                        :value="recurso.id"
                                    >
                                        {{ recurso.nombre }}
                                    </option>
                                </select>
                            </div>

                            <!-- Minutos Hombre -->
                            <div class="form-group">
                                <label>Minutos Hombre</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control"
                                    v-model="form.minutos_hombre"
                                />
                            </div>

                            <!-- Tipo -->
                            <div class="form-group">
                                <label>Tipo</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form.tipo"
                                    placeholder="Ej: AIREACION"
                                />
                            </div>
                        </div>

                        <!-- Minutos Hombre (común para ambos) -->
                        <div v-if="!isAireacion" class="form-group">
                            <label>Minutos Hombre</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                v-model="form.minutos_hombre"
                            />
                        </div>

                        <!-- Detalles -->
                        <div class="form-group">
                            <label>Detalles</label>
                            <textarea
                                class="form-control"
                                v-model="form.detalles"
                                rows="3"
                            ></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" @click="guardar">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import $ from 'jquery';

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
        },
        recursos: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            form: {
                id: null,
                fecha_ra: '',
                id_siembra: null,
                id_alimento: null,
                id_recurso: null,
                registered_user_id: null,
                tipo_actividad: null,
                cant_manana: null,
                cant_tarde: null,
                minutos_hombre: null,
                conv_alimenticia: null,
                detalles: '',
                tipo: ''
            }
        };
    },
    computed: {
        isAireacion() {
            return this.form.tipo === 'AIREACION' || this.form.id_recurso !== null;
        }
    },
    methods: {
        open(item) {
            // Limpiar formulario
            Object.keys(this.form).forEach(key => {
                this.form[key] = item[key] || null;
            });
            
            // Asegurar que el id esté presente
            this.form.id = item.id;
            
            $(this.$refs.editModal).modal('show');
        },
        
        onTipoActividadChange() {
            // Si se selecciona aireación, establecer tipo
            if (this.form.tipo_actividad === '12') {
                this.form.tipo = 'AIREACION';
            }
        },
        
        guardar() {
            // Validación básica
            if (!this.form.fecha_ra || !this.form.id_siembra || !this.form.registered_user_id) {
                Swal.fire('Error', 'Por favor complete los campos requeridos', 'error');
                return;
            }
            
            // Para alimentación: validar alimento
            if (!this.isAireacion && !this.form.id_alimento) {
                Swal.fire('Error', 'Por favor seleccione un alimento', 'error');
                return;
            }
            
            // Para aireación: validar recurso
            if (this.isAireacion && !this.form.id_recurso) {
                Swal.fire('Error', 'Por favor seleccione un recurso', 'error');
                return;
            }
            
            axios.put(`api/feeding-prelogs/${this.form.id}`, this.form)
                .then(response => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: 'Preregistro actualizado correctamente',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    
                    $(this.$refs.editModal).modal('hide');
                    this.$emit('updated');
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo actualizar el preregistro', 'error');
                });
        }
    },
    mounted() {
        // Inicializar modal
        $(this.$refs.editModal).modal({ backdrop: 'static', keyboard: false });
    }
};
</script>