<!-- resources\js\components\CalidadAgua\CalidadAguaModal.vue -->
<template>
    <!-- Modal añadir Parametros a siembras -->
    <div
        class="modal fade"
        id="modalParametros"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- HEADER -->
                <div class="modal-header">
                    <h3 class="modal-title">
                        Parámetros de calidad del agua
                        <small v-if="siembraActiva.nombre_siembra" class="text-muted">
                            — Siembra: {{ siembraActiva.nombre_siembra }}
                        </small>
                    </h3>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <!-- Formulario principal de parámetros -->
                    <form
                        class="container"
                        @submit.prevent="editando === 0 ? $emit('guardar') : $emit('editar')"
                    >
                        <!-- SECCIÓN 1: PARÁMETROS DE CALIDAD DEL AGUA -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Parámetros de calidad del agua</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- COLUMNA 1 -->
                                    <div class="col-md-6">
                                        <!-- FECHA -->
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-form-label">
                                                <i class="far fa-calendar-alt"></i> Fecha registro:
                                            </label>
                                            <div class="col-sm-6">
                                                <input
                                                    type="date"
                                                    class="form-control"
                                                    v-model="form.fecha_parametro"
                                                    required
                                                    @change="actualizarFechaConfiguracion"
                                                />
                                            </div>
                                        </div>

                                        <!-- SATURACIÓN -->
                                        <div class="col-sm-12 text-center mb-2">
                                            <h6 class="text-primary">% Saturación oxígeno</h6>
                                        </div>

                                        <div class="border rounded p-3 mb-3">
                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">
                                                    <i class="far fa-clock"></i> 8:00 pm:
                                                </label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            v-model.number="form['8_pm']"
                                                            step="0.01"
                                                            min="0"
                                                            max="200"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">
                                                    <i class="far fa-clock"></i> 12:00 am:
                                                </label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            v-model.number="form['12_am']"
                                                            step="0.01"
                                                            min="0"
                                                            max="200"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">
                                                    <i class="far fa-clock"></i> 4:00 am:
                                                </label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            v-model.number="form['4_am']"
                                                            step="0.01"
                                                            min="0"
                                                            max="200"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- COLUMNA 2 -->
                                    <div class="col-md-6">
                                        <!-- TEMPERATURA -->
                                        <div class="form-group row">
                                            <label class="col-sm-6 col-form-label">Temperatura:</label>
                                            <div class="col-sm-6">
                                                <div class="input-group">
                                                    <input
                                                        type="number"
                                                        class="form-control"
                                                        v-model.number="form.temperatura"
                                                        step="0.01"
                                                        min="-10"
                                                        max="50"
                                                        placeholder="0.00"
                                                    />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">°C</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Hasta 2 decimales</small>
                                            </div>
                                        </div>

                                        <!-- Botón para más datos -->
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <button
                                                    type="button"
                                                    class="btn btn-link"
                                                    @click="$emit('update:mostrarMasDatos', !mostrarMasDatos)"
                                                >
                                                    {{ mostrarMasDatos ? 'Ocultar más datos' : 'Más datos' }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- MÁS DATOS (PH, Amonio, etc.) -->
                                        <div v-if="mostrarMasDatos">
                                            <!-- PH con validación -->
                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">PH:</label>
                                                <div class="col-sm-6">
                                                    <input
                                                        type="number"
                                                        class="form-control"
                                                        v-model.number="form.ph"
                                                        step="0.01"
                                                        min="0"
                                                        max="14"
                                                        placeholder="0.00"
                                                        :class="{ 'is-invalid': phError }"
                                                        @blur="validarPH"
                                                    />
                                                    <div v-if="phError" class="invalid-feedback d-block">
                                                        {{ phError }}
                                                    </div>
                                                    <div v-if="phSugerencias.length > 0" class="mt-1">
                                                        <small class="text-muted">Valores válidos aproximados:</small>
                                                        <div>
                                                            <button 
                                                                v-for="sug in phSugerencias" 
                                                                :key="sug"
                                                                type="button" 
                                                                class="btn btn-sm btn-outline-primary me-1 mt-1"
                                                                @click="form.ph = sug"
                                                            >
                                                                {{ sug }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales (0-14)</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">Amonio (mg/L):</label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input 
                                                            type="number" 
                                                            class="form-control" 
                                                            v-model.number="form.amonio"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">mg/L</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">Nitrito (mg/L):</label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input 
                                                            type="number" 
                                                            class="form-control" 
                                                            v-model.number="form.nitrito"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">mg/L</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">Nitrato (mg/L):</label>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <input 
                                                            type="number" 
                                                            class="form-control" 
                                                            v-model.number="form.nitrato"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">mg/L</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Hasta 2 decimales</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-6 col-form-label">Otros:</label>
                                                <div class="col-sm-6">
                                                    <input 
                                                        type="text" 
                                                        class="form-control" 
                                                        v-model="form.otros"
                                                        placeholder="Notas adicionales..."
                                                    />
                                                    <small class="form-text text-muted">Texto libre</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: AIREACIÓN MECÁNICA -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-fan mr-2"></i>Registrar aireación mecánica para este evento
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Checkbox para activar/desactivar aireación -->
                                <div class="form-check mb-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="chkAireacion"
                                        :checked="registrarAireacion"
                                        @change="toggleAireacion($event.target.checked)"
                                    />
                                    <label class="form-check-label" for="chkAireacion">
                                        <strong>Habilitar registro de aireación mecánica</strong>
                                    </label>
                                </div>

                                <!-- CONTENIDO DE AIREACIÓN (solo si está activado) -->
                                <div v-if="registrarAireacion">
                                    <!-- INFO SIEMBRA PREVIA -->
                                    <div class="alert alert-success mb-3" v-if="siembraActiva.id">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Seleccionar siembra</strong><br>
                                                {{ siembraActiva.nombre_siembra }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Minutos hombre</strong><br>
                                                <span v-if="configuracionActual && configuracionActual.minutos_hombre">
                                                    {{ formatearDecimal(configuracionActual.minutos_hombre) }}
                                                </span>
                                                <span v-else class="text-muted">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Tipo de Actividad</strong><br>
                                                Aireación Mecánica
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DATOS PREEXISTENTES DE CONFIGURACIÓN -->
                                    <div v-if="configuracionesAireacion.length > 0" class="mb-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <strong>Configuración predefinida:</strong>
                                                <select 
                                                    class="form-control form-control-sm mt-1"
                                                    v-model="configuracionSeleccionadaId"
                                                    @change="cargarConfiguracionSeleccionada"
                                                >
                                                    <option value="">Seleccionar configuración...</option>
                                                    <option 
                                                        v-for="config in configuracionesAireacion" 
                                                        :key="config.id" 
                                                        :value="config.id"
                                                    >
                                                        {{ config.recurso.recurso }} - {{ formatearDecimal(config.minutos_hombre || 0) }} min hombre
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="small">Fecha registro</label>
                                                        <div class="form-control form-control-sm bg-light">
                                                            <strong>{{ form.fecha_parametro ? formatDateLocal(form.fecha_parametro) : 'Seleccione fecha' }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small">Recurso</label>
                                                        <div class="form-control form-control-sm bg-light">
                                                            <span v-if="configuracionActual">
                                                                {{ configuracionActual.recurso.recurso }}
                                                            </span>
                                                            <span v-else class="text-muted">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CALCULADORA DE TIEMPO -->
                                    <div class="card border-primary mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Calcular tiempo de aireación</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Fecha inicio -->
                                                <div class="form-group col-md-3">
                                                    <label class="small">Fecha inicio:</label>
                                                    <input
                                                        type="date"
                                                        class="form-control form-control-sm"
                                                        :value="aireacion.fecha_inicio"
                                                        @input="updateAireacion('fecha_inicio',$event.target.value)"
                                                    />
                                                </div>

                                                <!-- Hora inicio -->
                                                <div class="form-group col-md-2">
                                                    <label class="small">Horario:</label>
                                                    <input
                                                        type="time"
                                                        class="form-control form-control-sm"
                                                        :value="aireacion.hora_inicio"
                                                        @input="updateAireacion('hora_inicio',$event.target.value)"
                                                    />
                                                </div>

                                                <!-- Fecha fin -->
                                                <div class="form-group col-md-3">
                                                    <label class="small">Fecha fin:</label>
                                                    <input
                                                        type="date"
                                                        class="form-control form-control-sm"
                                                        :value="aireacion.fecha_fin"
                                                        @input="updateAireacion('fecha_fin',$event.target.value)"
                                                    />
                                                </div>

                                                <!-- Hora fin -->
                                                <div class="form-group col-md-2">
                                                    <label class="small">Horario:</label>
                                                    <input
                                                        type="time"
                                                        class="form-control form-control-sm"
                                                        :value="aireacion.hora_fin"
                                                        @input="updateAireacion('hora_fin',$event.target.value)"
                                                    />
                                                </div>

                                                <!-- Botón calcular -->
                                                <div class="form-group col-md-2">
                                                    <label class="small">&nbsp;</label>
                                                    <button
                                                        type="button"
                                                        class="btn btn-primary btn-sm w-100"
                                                        @click="calcularTiempo"
                                                    >
                                                        Calcular tiempo
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Resultado del cálculo -->
                                            <div v-if="aireacion.minutos_calculados !== null" 
                                                 class="alert alert-secondary mt-2 mb-0 py-2">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <strong>Minutos calculados:</strong> {{ aireacion.minutos_calculados }} min
                                                        <small class="d-block text-muted">
                                                            Se calcula con la diferencia entre la fecha/hora de inicio y fin.
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4 text-right">
                                                        <small>
                                                            Cantidad: <strong>{{ aireacion.minutos_calculados }} min</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RECURSO UTILIZADO EN LA AIREACIÓN -->
                                    <div class="card border-success">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Recurso utilizado en la aireación</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Recurso de aireación -->
                                                <div class="form-group col-md-6">
                                                    <label class="small">Recurso de aireación:</label>
                                                    <div class="input-group input-group-sm">
                                                        <v-select
                                                            label="recurso"
                                                            class="w-100"
                                                            v-model="selectedRecurso"
                                                            :reduce="option => option.id"
                                                            :filterable="false"
                                                            :options="optResources"
                                                            placeholder="Escribe para iniciar la búsqueda..."
                                                            @search="onSearchResource"
                                                        >
                                                            <template slot="no-options">
                                                                Escribe para iniciar la búsqueda
                                                            </template>
                                                            <template slot="option" slot-scope="option">
                                                                <div class="d-center">
                                                                    {{ option.recurso }} ({{ option.unidad }})
                                                                </div>
                                                            </template>
                                                            <template slot="selected-option" slot-scope="option">
                                                                <div class="selected d-center">
                                                                    {{ option.recurso }} ({{ option.unidad }})
                                                                </div>
                                                            </template>
                                                        </v-select>
                                                    </div>
                                                    <small v-if="configuracionActual" class="form-text text-success">
                                                        <i class="fas fa-check-circle"></i> 
                                                        Configuración cargada: {{ configuracionActual.recurso.recurso }}
                                                    </small>
                                                </div>

                                                <!-- Cantidad (Minutos calculados) -->
                                                <div class="form-group col-md-3">
                                                    <label class="small">Cantidad:</label>
                                                    <div class="input-group input-group-sm">
                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            :value="formatearDecimal(aireacion.minutos_calculados)"
                                                            readonly
                                                            placeholder="0.00"
                                                        />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">min</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Minutos de aireación (hasta 2 decimales)
                                                    </small>
                                                </div>

                                                <!-- Actividad -->
                                                <div class="form-group col-md-3">
                                                    <label class="small">Actividad:</label>
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm"
                                                        value="Aireación Mecánica"
                                                        readonly
                                                    />
                                                </div>
                                            </div>

                                            <!-- Detalles -->
                                            <div class="form-group mt-2">
                                                <label class="small">Detalles / observaciones:</label>
                                                <textarea
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    :value="recursoAireacion.detalles"
                                                    @input="updateRecursoAireacion('detalles',$event.target.value)"
                                                    placeholder="Ingrese detalles adicionales..."
                                                ></textarea>
                                            </div>

                                            <!-- Información adicional -->
                                            <div class="row mt-2">
                                                <div class="col-md-4">
                                                    <div class="alert alert-light py-1 mb-0">
                                                        <small class="text-muted">Minutos hombre predefinidos:</small><br>
                                                        <span v-if="configuracionActual && configuracionActual.minutos_hombre" 
                                                              class="font-weight-bold">
                                                            {{ formatearDecimal(configuracionActual.minutos_hombre) }} min
                                                        </span>
                                                        <span v-else class="text-muted">No definido</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="alert alert-light py-1 mb-0">
                                                        <small class="text-muted">Minutos totales:</small><br>
                                                        <span v-if="aireacion.minutos_calculados !== null" 
                                                              class="font-weight-bold text-primary">
                                                            {{ formatearDecimal(aireacion.minutos_calculados) }} minutos de aireación
                                                        </span>
                                                        <span v-else class="text-muted">
                                                            Calcule el tiempo primero
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INFORMACIÓN DEL ESTANQUE -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Información del estanque</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">ID contenedor:</small><br>
                                        <strong>{{ idEstanque || '-' }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Estanque:</small><br>
                                        <strong v-if="listadoParametros && listadoParametros[0]">
                                            {{ listadoParametros[0].contenedor }}
                                        </strong>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Siembra activa:</small><br>
                                        <span v-if="siembraActiva && siembraActiva.id" class="text-success">
                                            <i class="fas fa-seedling"></i> 
                                            {{ siembraActiva.nombre_siembra }} (ID: {{ siembraActiva.id }})
                                        </span>
                                        <span v-else class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay siembra activa
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- BOTONES -->
                        <div class="form-group row mt-3">
                            <div class="col-sm-12 text-right">
                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-dismiss="modal"
                                >
                                    Cerrar
                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    v-if="idEstanque"
                                    :disabled="cargando"
                                    @click="validarYGuardar"
                                >
                                    <span v-if="cargando">
                                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                                    </span>
                                    <span v-else>
                                        {{ editando === 0 ? 'Guardar' : 'Actualizar' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "CalidadAguaModal",
    props: {
        form: { type: Object, required: true },
        idEstanque: { type: [String, Number], default: "" },
        listadoParametros: { type: Array, default: () => [] },
        editando: { type: Number, default: 0 },
        mostrarMasDatos: { type: Boolean, default: false },
        registrarAireacion: { type: Boolean, default: false },
        aireacion: {
            type: Object,
            default: () => ({
                fecha_inicio: "",
                hora_inicio: "07:00",
                fecha_fin: "",
                hora_fin: "07:00",
                minutos_calculados: null
            })
        },
        siembraActiva: {
            type: Object,
            default: () => ({})
        },
        recursoAireacion: {
            type: Object,
            default: () => ({
                recurso_id: null,
                minutos: null,
                minutos_hombre: null,
                detalles: "",
                tipo_actividad_id: 12
            })
        }
    },
    data() {
        return {
            // Para recursos y configuraciones
            optResources: [],
            configuracionesAireacion: [],
            configuracionActual: null,
            configuracionSeleccionadaId: "",
            selectedRecurso: null,
            cargando: false,
            
            // Para validación de PH
            phError: '',
            phSugerencias: []
        };
    },
    watch: {
        'siembraActiva.id': {
            immediate: true,
            handler(newVal) {
                if (newVal && this.registrarAireacion) {
                    this.cargarConfiguracionesAireacion();
                }
            }
        },
        registrarAireacion(newVal) {
            if (newVal && this.siembraActiva.id) {
                this.cargarConfiguracionesAireacion();
            } else {
                // Resetear si se desactiva
                this.resetearAireacion();
            }
        },
        // Sincronizar selectedRecurso con recurso_id
        'recursoAireacion.recurso_id': {
            handler(newVal) {
                if (newVal && this.optResources.length > 0) {
                    const recurso = this.optResources.find(r => r.id === newVal);
                    if (recurso) {
                        this.selectedRecurso = recurso;
                    }
                }
            }
        },
        // Cuando cambia selectedRecurso
        selectedRecurso(newVal) {
            if (newVal && newVal.id) {
                this.updateRecursoAireacion("recurso_id", newVal.id);
            } else if (typeof newVal === 'number') {
                this.updateRecursoAireacion("recurso_id", newVal);
            }
        },
        // Cuando se calcula tiempo, actualizar el campo minutos del recurso
        'aireacion.minutos_calculados'(newVal) {
            if (newVal !== null) {
                this.updateRecursoAireacion('minutos', this.formatearDecimal(newVal));
            }
        },
        // Validar PH cuando cambia
        'form.ph': {
            handler(nuevoValor) {
                if (nuevoValor !== null && nuevoValor !== '') {
                    this.validarPH();
                }
            },
            deep: true
        }
    },
    mounted() {
        this.cargarRecursosIniciales();
        
        // Establecer fecha de hoy por defecto usando fecha LOCAL
        this.establecerFechaHoyLocal();
        
        // Escuchar apertura del modal
        $('#modalParametros').on('shown.bs.modal', () => {
            // Establecer fecha de hoy por defecto si está vacía
            if (!this.form.fecha_parametro || this.form.fecha_parametro === '') {
                this.establecerFechaHoyLocal();
            }
            
            if (this.registrarAireacion && this.siembraActiva.id) {
                this.cargarConfiguracionesAireacion();
            }
        });
    },
    methods: {
        // Formatear decimal a 2 posiciones
        formatearDecimal(valor) {
            if (valor === null || valor === '' || isNaN(valor)) return null;
            const num = parseFloat(valor);
            return parseFloat(num.toFixed(2));
        },
        
        // Validar PH
        validarPH() {
            this.phError = '';
            this.phSugerencias = [];
            
            if (this.form.ph === null || this.form.ph === '') {
                return;
            }
            
            const ph = parseFloat(this.form.ph);
            
            // Validar rango
            if (ph < 0 || ph > 14) {
                this.phError = 'El pH debe estar entre 0 y 14';
                return;
            }
            
            // Validar valores específicos (ejemplo: si está cerca de 12 o 13)
            if (ph >= 11.5 && ph <= 13.5) {
                const dif12 = Math.abs(ph - 12);
                const dif13 = Math.abs(ph - 13);
                
                if (dif12 < 0.5 || dif13 < 0.5) {
                    this.phSugerencias = [12, 13];
                    this.phError = 'Ingresa un valor válido. Los dos valores válidos más aproximados son 12 y 13.';
                }
            }
        },
        
        // Preparar datos para guardar con decimales formateados
        prepararDatosFormateados() {
            // Crear copia del form para formatear decimales
            const datosFormateados = { ...this.form };
            
            // Lista de campos que aceptan decimales (ahora 2 decimales)
            const camposDecimales = [
                'temperatura',
                '12_am',
                '4_am',
                '8_pm',
                'ph',
                'amonio',
                'nitrito',
                'nitrato'
            ];
            
            // Formatear cada campo decimal
            camposDecimales.forEach(campo => {
                if (datosFormateados[campo] !== null && datosFormateados[campo] !== '') {
                    datosFormateados[campo] = this.formatearDecimal(datosFormateados[campo]);
                }
            });
            
            return datosFormateados;
        },
        
        // Establecer fecha de hoy usando fecha LOCAL (evita problemas UTC)
        establecerFechaHoyLocal() {
            const hoy = new Date();
            
            // Crear fecha en formato YYYY-MM-DD sin conversión UTC
            const year = hoy.getFullYear();
            const month = String(hoy.getMonth() + 1).padStart(2, '0');
            const day = String(hoy.getDate()).padStart(2, '0');
            const fechaHoy = `${year}-${month}-${day}`;
            
            console.log('Fecha hoy establecida (LOCAL):', fechaHoy);
            
            // Solo establecer en fecha de registro
            this.form.fecha_parametro = fechaHoy;
            
            // Asegurar que fecha_inicio y fecha_fin estén VACÍAS
            this.updateAireacion('fecha_inicio', '');
            this.updateAireacion('fecha_fin', '');
        },
        
        // Actualizar fecha en configuración cuando cambia la fecha de registro
        actualizarFechaConfiguracion() {
            console.log('Fecha registro actualizada:', this.form.fecha_parametro);
            // La fecha de configuración ahora mostrará automáticamente esta fecha
        },
        
        // Formatear fecha para mostrar (LOCAL - sin problemas UTC)
        formatDateLocal(dateStr) {
            if (!dateStr) return 'dd/mm/aaaa';
            
            try {
                // Si la fecha ya está en formato YYYY-MM-DD
                if (dateStr.includes('-')) {
                    const [year, month, day] = dateStr.split('-');
                    const date = new Date(year, month - 1, day);
                    
                    // Verificar que sea una fecha válida
                    if (isNaN(date.getTime())) {
                        return 'Fecha inválida';
                    }
                    
                    return date.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }
                
                // Si viene en otro formato
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) {
                    return 'dd/mm/aaaa';
                }
                
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (error) {
                console.error('Error formateando fecha:', error, dateStr);
                return 'dd/mm/aaaa';
            }
        },
        
        // Calcular tiempo
        calcularTiempo() {
            if (!this.aireacion.fecha_inicio || !this.aireacion.hora_inicio || 
                !this.aireacion.fecha_fin || !this.aireacion.hora_fin) {
                this.$swal({
                    icon: "warning",
                    title: "Datos incompletos",
                    text: "Debes completar fecha y hora de inicio y fin para calcular el tiempo."
                });
                return;
            }

            // IMPORTANTE: Usar fechas LOCALES para el cálculo
            const inicio = new Date(`${this.aireacion.fecha_inicio}T${this.aireacion.hora_inicio}:00`);
            const fin = new Date(`${this.aireacion.fecha_fin}T${this.aireacion.hora_fin}:00`);
            
            if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                this.$swal({
                    icon: "error",
                    title: "Error en las fechas",
                    text: "No se pudo interpretar la fecha u hora. Verifica los formatos."
                });
                return;
            }

            if (fin <= inicio) {
                this.$swal({
                    icon: "warning",
                    title: "Rango inválido",
                    text: "La fecha/hora de fin debe ser mayor que la de inicio."
                });
                return;
            }

            const diffMs = fin.getTime() - inicio.getTime();
            const minutos = Math.round(diffMs / (1000 * 60));

            // Formatear minutos a 2 decimales
            const minutosFormateados = this.formatearDecimal(minutos);
            this.updateAireacion('minutos_calculados', minutosFormateados);
            
            this.$swal({
                icon: "success",
                title: "Tiempo calculado",
                text: `Tiempo total de aireación: ${minutosFormateados} minutos.`,
                timer: 2000,
                showConfirmButton: false
            });
        },
        
        // Validar y guardar
        validarYGuardar() {
            // Validar PH si tiene valor
            if (this.form.ph !== null && this.form.ph !== '') {
                this.validarPH();
                if (this.phError && this.phError.includes('Ingresa un valor válido')) {
                    this.$swal({
                        icon: "warning",
                        title: "Valor de pH no válido",
                        html: `
                            <div class="text-left">
                                <p>El valor de pH debe ser uno de los siguientes:</p>
                                <ul>
                                    <li><strong>12</strong></li>
                                    <li><strong>13</strong></li>
                                </ul>
                                <p>Valor ingresado: ${this.form.ph}</p>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Usar valor sugerido',
                        cancelButtonText: 'Corregir manualmente'
                    }).then((result) => {
                        if (result.value && this.phSugerencias.length > 0) {
                            // Usar el primer valor sugerido
                            this.form.ph = this.phSugerencias[0];
                            this.validarYGuardar(); // Reintentar guardar
                        }
                    });
                    return;
                }
            }
            
            // Validar que si hay aireación activa, tenga recurso seleccionado
            if (this.registrarAireacion && !this.recursoAireacion.recurso_id) {
                this.$swal({
                    icon: "warning",
                    title: "Recurso requerido",
                    text: "Debes seleccionar un recurso para la aireación mecánica."
                });
                return;
            }
            
            // Validar fecha de registro
            if (!this.form.fecha_parametro) {
                this.$swal({
                    icon: "warning",
                    title: "Fecha requerida",
                    text: "Debes seleccionar una fecha de registro."
                });
                return;
            }
            
            // Preparar datos para guardar
            this.prepararDatosGuardado();
        },
        
        // Preparar datos para guardar
        prepararDatosGuardado() {
            // 1. Preparar datos de parámetros de calidad de agua CON DECIMALES FORMATEADOS
            const datosParametros = {
                ...this.prepararDatosFormateados(),
                fecha_parametro: this.form.fecha_parametro, // Fecha EXACTA
                id_estanque: this.idEstanque,
                siembra_id: this.siembraActiva.id
            };
            
            console.log('Datos parámetros a guardar (formateados):', datosParametros);
            
            // 2. Preparar datos de aireación si está activa
            let datosAireacion = null;
            if (this.registrarAireacion && this.recursoAireacion.recurso_id) {
                datosAireacion = {
                    siembra_id: this.siembraActiva.id,
                    recurso_id: this.recursoAireacion.recurso_id,
                    // USAR LA MISMA FECHA EXACTA de registro - CORREGIDO
                    fecha: this.form.fecha_parametro, // <--- ¡ESTO ES CLAVE!
                    minutos: this.formatearDecimal(this.recursoAireacion.minutos) || 0,
                    minutos_hombre: this.formatearDecimal(this.recursoAireacion.minutos_hombre) || 0,
                    detalles: this.recursoAireacion.detalles || '',
                    tipo_actividad_id: this.recursoAireacion.tipo_actividad_id || 12,
                    // Campos para el cálculo si existen
                    fecha_inicio: this.aireacion.fecha_inicio || null,
                    hora_inicio: this.aireacion.hora_inicio || null,
                    fecha_fin: this.aireacion.fecha_fin || null,
                    hora_fin: this.aireacion.hora_fin || null,
                    minutos_calculados: this.formatearDecimal(this.aireacion.minutos_calculados) || null
                };
                
                console.log('Datos aireación a guardar:', datosAireacion);
                console.log('CONFIRMACIÓN - Misma fecha:', {
                    fechaParametros: this.form.fecha_parametro,
                    fechaAireacion: datosAireacion.fecha,
                    sonIguales: this.form.fecha_parametro === datosAireacion.fecha
                });
            }
            
            // Emitir evento al componente padre con los datos CORREGIDOS
            this.$emit('guardar-datos', {
                parametros: datosParametros,
                aireacion: datosAireacion,
                editando: this.editando
            });
        },
        
        // Toggle aireación
        toggleAireacion(checked) {
            this.$emit("update:registrarAireacion", checked);
        },
        
        // Resetear aireación
        resetearAireacion() {
            this.configuracionesAireacion = [];
            this.configuracionActual = null;
            this.configuracionSeleccionadaId = "";
            this.selectedRecurso = null;
            
            this.updateAireacion('fecha_inicio', '');
            this.updateAireacion('fecha_fin', '');
            this.updateAireacion('minutos_calculados', null);
        },
        
        // Actualizar aireación
        updateAireacion(field, value) {
            const updated = { ...this.aireacion, [field]: value };
            this.$emit("update:aireacion", updated);
        },
        
        // Actualizar recurso aireación
        updateRecursoAireacion(field, value) {
            const updated = { ...this.recursoAireacion, [field]: value };
            this.$emit("update:recursoAireacion", updated);
        },
        
        // Cargar recursos iniciales
        async cargarRecursosIniciales() {
            try {
                const response = await axios.get(`api/recursos`, {
                    params: {
                        page: 1,
                        per_page: 10,
                        activo: 1,
                        tipo: 'aireacion'
                    }
                });
                
                this.optResources = response.data.recursos 
                    ? response.data.recursos.data 
                    : response.data.data || [];
            } catch (error) {
                console.error("Error cargando recursos:", error);
            }
        },
        
        // Buscar recursos
        onSearchResource(search, loading) {
            if (!search || search.length < 2) return;
            loading(true);
            axios
                .get(`api/recursos`, {
                    params: {
                        recurso: search,
                        page: 1,
                        activo: 1
                    }
                })
                .then(response => {
                    this.optResources = response.data.recursos
                        ? response.data.recursos.data
                        : response.data.data || [];
                    loading(false);
                })
                .catch(e => {
                    console.log(e);
                    loading(false);
                });
        },
        
        // Cargar configuraciones de aireación
        async cargarConfiguracionesAireacion() {
            if (!this.siembraActiva.id) {
                this.$swal({
                    icon: "warning",
                    title: "Sin siembra activa",
                    text: "No hay siembra activa seleccionada para cargar configuraciones.",
                    timer: 3000
                });
                return;
            }
            
            this.cargando = true;
            try {
                const { data } = await axios.get("api/siembra-recurso-datos", {
                    params: { siembra_id: this.siembraActiva.id }
                });
                
                this.configuracionesAireacion = Array.isArray(data) ? data : [];
                
                if (this.configuracionesAireacion.length > 0) {
                    // Cargar la primera configuración por defecto
                    this.seleccionarConfiguracion(this.configuracionesAireacion[0]);
                    this.configuracionSeleccionadaId = this.configuracionesAireacion[0].id;
                    
                    this.$swal({
                        icon: "success",
                        title: "Configuración cargada",
                        html: `
                            <div class="text-left">
                                <p><strong>Configuración predefinida cargada:</strong></p>
                                <ul class="mb-0">
                                    <li>Recurso: ${this.configuracionesAireacion[0].recurso.recurso}</li>
                                    <li>Minutos hombre: ${this.formatearDecimal(this.configuracionesAireacion[0].minutos_hombre) || '0'}</li>
                                    <li><strong>Fecha registro:</strong> ${this.formatDateLocal(this.form.fecha_parametro)}</li>
                                    <li>Cantidad: Se calculará con el tiempo de aireación</li>
                                    ${this.configuracionesAireacion[0].detalles ? 
                                        `<li>Detalles: ${this.configuracionesAireacion[0].detalles}</li>` : ''}
                                </ul>
                            </div>
                        `,
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    this.$swal({
                        icon: "info",
                        title: "Sin configuraciones",
                        text: "Esta siembra no tiene configuraciones predefinidas de aireación.",
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error("Error cargando configuraciones:", error);
                this.$swal({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo cargar las configuraciones de aireación.",
                    timer: 3000
                });
            } finally {
                this.cargando = false;
            }
        },
        
        // Cargar configuración seleccionada
        cargarConfiguracionSeleccionada() {
            if (!this.configuracionSeleccionadaId) return;
            const config = this.configuracionesAireacion.find(c => c.id == this.configuracionSeleccionadaId);
            if (config) {
                this.seleccionarConfiguracion(config);
            }
        },
        
        // Seleccionar configuración
        seleccionarConfiguracion(config) {
            this.configuracionActual = config;
            this.configuracionSeleccionadaId = config.id;
            
            // Actualizar recurso de aireación con los datos de la configuración
            this.updateRecursoAireacion('recurso_id', config.recurso_id);
            this.updateRecursoAireacion('minutos_hombre', this.formatearDecimal(config.minutos_hombre) || 0);
            this.updateRecursoAireacion('detalles', config.detalles || '');
            
            // Si la configuración tiene minutos, usarlos como cantidad inicial
            if (config.minutos && this.aireacion.minutos_calculados === null) {
                this.updateAireacion('minutos_calculados', this.formatearDecimal(config.minutos));
            }
            
            // NO establecer fecha_inicio automáticamente
            // El usuario debe ingresar manualmente las fechas de cálculo
            
            // Actualizar v-select
            if (config.recurso) {
                this.selectedRecurso = config.recurso;
                // Agregar a opciones si no existe
                if (!this.optResources.some(r => r.id === config.recurso_id)) {
                    this.optResources.push(config.recurso);
                }
            }
            
            this.$swal({
                icon: "success",
                title: "Configuración aplicada",
                text: `${config.recurso.recurso} - ${this.formatearDecimal(config.minutos_hombre) || 0} min hombre`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    }
};
</script>

<style scoped>
/* Estilos anteriores se mantienen igual */
.card-header.bg-info {
    background-color: #17a2b8 !important;
}

.card-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}

.v-select {
    font-size: 0.875rem;
}

.v-select .vs__dropdown-toggle {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
}

.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.form-group label.small {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.2rem;
}

.alert-secondary {
    background-color: #e9ecef;
    border-color: #d6d8db;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.border-primary {
    border-color: #007bff !important;
}

.border-success {
    border-color: #28a745 !important;
}

.card {
    border: 1px solid rgba(0,0,0,.125);
    border-radius: 0.25rem;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

.form-control.bg-light {
    background-color: #f8f9fa !important;
    border-color: #ced4da;
}

/* Estilo para inputs numéricos */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    opacity: 1;
    height: auto;
}

.input-group-append .input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.invalid-feedback {
    font-size: 0.875em;
}
</style>