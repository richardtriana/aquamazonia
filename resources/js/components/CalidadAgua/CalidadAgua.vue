<!-- resources\js\components\CalidadAgua\CalidadAgua.vue -->
<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Parámetros de calidad del agua
                    </div>

                    <div class="card-body">
                        <!-- Botón añadir parámetros SOLO cuando ya hay estanque seleccionado -->
                        <div
                            class="row text-right mb-3"
                            v-if="mostrar === 1"
                        >
                            <div class="col-md-12">
                                <!-- Button trigger modal -->
                                <button
                                    type="button"
                                    class="btn btn-success"
                                    @click="crearParametros()"
                                >
                                    <i class="fas fa-plus"></i> Añadir parámetros
                                </button>
                            </div>
                        </div>

                        <!-- VISTA DETALLE POR ESTANQUE -->
                        <div v-if="mostrar == 1">
                            <div class="col-md-12">
                                <h5>Filtrar por:</h5>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="Fecha desde">
                                            Fecha inicio desde:
                                        </label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="f_inicio_d"
                                            v-model="f_inicio_d"
                                        />
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="fecha hasta">
                                            Fecha inicio hasta:
                                        </label>
                                        <input
                                            type="date"
                                            class="form-control"
                                            id="f_inicio_h"
                                            v-model="f_inicio_h"
                                        />
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label for="fecha hasta">
                                            Buscar:
                                        </label>
                                        <button
                                            class="btn btn-primary form-control"
                                            @click="filtrarParametros()"
                                        >
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="form-group  col-md-3">
                                        <label for="Generar excel">
                                            Generar Excel:
                                        </label>
                                        <downloadexcel
                                            class="btn btn-success form-control"
                                            :fetch="fetchData"
                                            :fields="json_fields"
                                            name="informe-parametros-calidad-agua.xls"
                                            type="xls"
                                        >
                                            <i class="fa fa-fw fa-download"></i>
                                            Exportar Excel
                                        </downloadexcel>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÓN / PANEL DE COLUMNAS -->
                            <div class="row mb-3 justify-content-end">
                                <div class="col-md-4 col-lg-3 position-relative">
                                    <button
                                        type="button"
                                        class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn"
                                        @click="toggleColumnPanel"
                                    >
                                        <span>
                                            <i class="fas fa-columns mr-1"></i>
                                            Columnas
                                        </span>
                                        <i
                                            :class="[
                                                'fas',
                                                showColumnPanel
                                                    ? 'fa-chevron-up'
                                                    : 'fa-chevron-down'
                                            ]"
                                        ></i>
                                    </button>

                                    <div
                                        v-if="showColumnPanel"
                                        class="column-panel-dropdown shadow-lg"
                                    >
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-2"
                                        >
                                            <div class="column-panel-title">
                                                Marca / desmarca las columnas
                                            </div>
                                            <div class="column-panel-actions">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success mr-1"
                                                    @click.stop="selectAllColumns"
                                                >
                                                    Todas
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    @click.stop="clearAllColumns"
                                                >
                                                    Ninguna
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div
                                                    class="column-item"
                                                    v-for="item in columnList"
                                                    :key="item.key"
                                                    @click.stop="
                                                        toggleColumn(item.key)
                                                    "
                                                >
                                                    <span
                                                        class="column-checkbox"
                                                        :class="{
                                                            'is-checked':
                                                                columnVisibility[
                                                                    item.key
                                                                ]
                                                        }"
                                                    >
                                                        <i
                                                            v-if="
                                                                columnVisibility[
                                                                    item.key
                                                                ]
                                                            "
                                                            class="fas fa-check"
                                                        ></i>
                                                    </span>
                                                    <span class="column-label">
                                                        {{ item.label }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 text-right">
                                <button
                                    class="btn btn-primary"
                                    @click="ocultarParametros()"
                                >
                                    <i class="fas fa-arrow-left"></i> Regresar
                                </button>
                            </div>

                            <div
                                class="table-container table-container--wide"
                                id="table-container2"
                            >
                                <h2>
                                    Registros de parámetros de agua
                                    {{
                                        listadoParametros[0]
                                            ? listadoParametros[0].contenedor
                                            : ""
                                    }}
                                    <span v-if="siembraActiva" class="text-success">
                                        <i class="fas fa-seedling"></i> 
                                        Siembra: {{ siembraActiva.nombre_siembra }}
                                    </span>
                                </h2>
                                <table class="table-sticky table table-sm table-hover table-bordered">
                                    <thead class="thead-primary">
                                        <tr>
                                            <th rowspan="2" data-field="id">
                                                #
                                            </th>
                                            <th rowspan="2">ID registro</th>
                                            <th rowspan="2" data-field="id">
                                                Fecha
                                            </th>
                                            <th colspan="3" class="text-center">
                                                % Saturación de oxígeno
                                            </th>
                                            <th rowspan="2" data-field="id">
                                                Temperatura
                                            </th>
                                            <th
                                                rowspan="2"
                                                data-field="id"
                                                v-if="columnVisibility.ph"
                                            >
                                                PH
                                            </th>
                                            <th
                                                rowspan="2"
                                                data-field="id"
                                                v-if="columnVisibility.amonio"
                                            >
                                                Amonio
                                            </th>
                                            <th
                                                rowspan="2"
                                                data-field="id"
                                                v-if="columnVisibility.nitrito"
                                            >
                                                Nitrito
                                            </th>
                                            <th
                                                rowspan="2"
                                                data-field="id"
                                                v-if="columnVisibility.nitrato"
                                            >
                                                Nitrato
                                            </th>
                                            <th
                                                rowspan="2"
                                                data-field="id"
                                                v-if="columnVisibility.otros"
                                            >
                                                Otros
                                            </th>
                                            <th rowspan="2" data-field="id">
                                                Editar/Eliminar
                                            </th>
                                        </tr>
                                        <tr>
                                            <th data-field="">8:00 pm</th>
                                            <th data-field="">12:00 am</th>
                                            <th data-field="">4:00 am</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(lp, index) in parametrosConFechaFormateada"
                                            :key="index"
                                            v-if="lp.id != null"
                                        >
                                            <th v-text="index + 1"></th>
                                            <th v-text="lp.id"></th>
                                            <td v-text="lp.fecha_corta || lp.fecha_parametro"></td>
                                            <td v-text="lp['8_pm']"></td>
                                            <td v-text="lp['12_am']"></td>
                                            <td v-text="lp['4_am']"></td>
                                            <td v-text="lp.temperatura"></td>
                                            <td
                                                v-if="columnVisibility.ph"
                                                v-text="lp.ph"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.amonio"
                                                v-text="lp.amonio"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.nitrito"
                                                v-text="lp.nitrito"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.nitrato"
                                                v-text="lp.nitrato"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.otros"
                                                v-text="lp.otros"
                                            ></td>
                                            <td>
                                                <button
                                                    class="btn btn-success"
                                                    type="button"
                                                    @click="editarParametros(lp)"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-danger"
                                                    type="button"
                                                    @click="
                                                        eliminarParametros(lp.id)
                                                    "
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="bg-secondary text-white">
                                            <th colspan="3">PROMEDIO:</th>
                                            <td
                                                v-text="promedios.promedio_8_pm"
                                            ></td>
                                            <td
                                                v-text="promedios.promedio_12_am"
                                            ></td>
                                            <td
                                                v-text="promedios.promedio_4_am"
                                            ></td>
                                            <td
                                                v-text="promedios.promedio_temperatura"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.ph"
                                                v-text="promedios.promedio_ph"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.amonio"
                                                v-text="promedios.promedio_amonio"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.nitrito"
                                                v-text="promedios.promedio_nitrito"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.nitrato"
                                                v-text="promedios.promedio_nitrato"
                                            ></td>
                                            <td
                                                v-if="columnVisibility.otros"
                                                v-text="promedios.promedio_otros"
                                            ></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- LISTADO DE ESTANQUES -->
                        <div
                            class="table-container"
                            id="table-container2"
                            v-if="mostrar == 0"
                        >
                            <!-- Filtro por estanque -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="buscar_estanque">
                                        Buscar estanque:
                                    </label>
                                    <div class="input-group">
                                        <input
                                            id="buscar_estanque"
                                            type="text"
                                            class="form-control"
                                            v-model="busquedaEstanque"
                                            placeholder="Escriba el código o nombre del estanque"
                                        />
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <table class="table-sticky table table-sm table-hover table-bordered">
                                <thead class="thead-primary">
                                    <tr>
                                        <th scope="col">Estanque</th>
                                        <th scope="col">Capacidad</th>
                                        <th scope="col">Estado contenedor</th>
                                        <th scope="col">
                                            Ver parámetros de calidad
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(contenedor, index) in filteredParametrosEstanques"
                                        :key="index"
                                    >
                                        <td v-text="contenedor.contenedor"></td>
                                        <td v-text="contenedor.capacidad"></td>
                                        <td
                                            v-text="estados[contenedor.estado]"
                                        ></td>
                                        <td>
                                            <button
                                                class="btn btn-primary"
                                                @click="
                                                    mostrarParametros(
                                                        contenedor.id
                                                    )
                                                "
                                            >
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal separado -->
        <calidad-agua-modal
            ref="calidadAguaModal"
            :form="form"
            :id-estanque="idEstanque"
            :listado-parametros="listadoParametros"
            :editando="editando"
            :mostrarMasDatos.sync="mostrarMasDatos"
            :registrarAireacion.sync="registrarAireacion"
            :aireacion.sync="aireacion"
            :siembra-activa="siembraActiva"
            :recurso-aireacion.sync="recursoAireacion"
            @guardar="guardar"
            @editar="editar"
            @calcular-tiempo="calcularTiempoAireacion"
        />
    </div>
</template>

<script>
import downloadexcel from "vue-json-excel";
import { Form } from "vform";
import CalidadAguaModal from "./CalidadAguaModal.vue";

export default {
    components: {
        downloadexcel,
        CalidadAguaModal
    },
    data() {
        return {
            json_fields: {
                "#": "id",
                "Fecha ": "fecha_parametro",
                "8:00 p.m": "8_pm",
                "12:00 a.m": "12_am",
                "4:00 a.m": "4_am",
                Temperatura: "temperatura",
                Ph: "ph",
                Amonio: "amonio",
                Nitrito: "nitrito",
                Nitrato: "nitrato",
                Otros: "otros"
            },
            editando: 0,
            mostrar: 0,
            idEstanque: "",
            nombreEstanque: "",
            mostrarMasDatos: false,

            // panel columnas
            showColumnPanel: false,
            columnVisibility: {
                ph: true,
                amonio: true,
                nitrito: true,
                nitrato: true,
                otros: true
            },

            // filtro búsqueda estanque
            busquedaEstanque: "",

            // aireación
            registrarAireacion: false,
            aireacion: {
                fecha_inicio: "",
                hora_inicio: "07:00",
                fecha_fin: "",
                hora_fin: "07:00",
                minutos_calculados: null
            },

            // siembra activa en el estanque
            siembraActiva: null,

            // recurso de aireación
            recursoAireacion: {
                recurso_id: null,
                minutos: null,
                minutos_hombre: null,
                detalles: "",
                tipo_actividad_id: 12
            },

            form: new Form({
                id: "",
                id_contenedor: "",
                id_especie: "",
                fecha_parametro: "",
                "12_am": "",
                "4_am": "",
                "4_pm": "",
                "8_pm": "",
                temperatura: "",
                ph: "",
                amonio: "",
                nitrito: "",
                nitrato: "",
                otros: ""
            }),
            estados: [],
            listadoExistencias: [],
            listadoEspecies: [],
            listadoSiembras: [],
            listadoParametros: [],
            listadoParametrosExcel: [],
            listadoParametrosEstanques: [],
            listadoEstanques: [],
            promedios: [],
            f_inicio_d: "",
            f_inicio_h: "",

            // Estado de carga
            cargandoGuardado: false
        };
    },
    computed: {
        columnList() {
            return [
                { key: "ph", label: "PH" },
                { key: "amonio", label: "Amonio" },
                { key: "nitrito", label: "Nitrito" },
                { key: "nitrato", label: "Nitrato" },
                { key: "otros", label: "Otros" }
            ];
        },
        
        // Agregar este computed para formatear fechas
        parametrosConFechaFormateada() {
            return this.listadoParametros.map(parametro => {
                if (!parametro.fecha_parametro) return parametro;
                
                let fecha = new Date(parametro.fecha_parametro);
                
                // Verificar si la fecha es válida
                if (isNaN(fecha.getTime())) {
                    // Si no es una fecha válida, intentar parsear de otro formato
                    const fechaStr = parametro.fecha_parametro.toString();
                    // Extraer solo la parte de la fecha (antes de la T)
                    const fechaParte = fechaStr.split('T')[0];
                    fecha = new Date(fechaParte);
                    
                    if (isNaN(fecha.getTime())) {
                        // Si aún no es válida, devolver la fecha original
                        return {
                            ...parametro,
                            fecha_corta: parametro.fecha_parametro
                        };
                    }
                }
                
                // Formatear como YYYY-MM-DD
                const año = fecha.getFullYear();
                const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                const dia = String(fecha.getDate()).padStart(2, '0');
                
                return {
                    ...parametro,
                    fecha_corta: `${año}-${mes}-${dia}`
                };
            });
        },
        
        // listado de estanques filtrado por texto
        filteredParametrosEstanques() {
            if (!this.busquedaEstanque) {
                return this.listadoParametrosEstanques;
            }
            const term = this.busquedaEstanque.toLowerCase();
            return this.listadoParametrosEstanques.filter(item => {
                const nombre =
                    (item.contenedor ? item.contenedor.toString() : "").toLowerCase();
                const capacidad =
                    (item.capacidad ? item.capacidad.toString() : "").toLowerCase();
                const estadoTexto =
                    this.estados[item.estado] &&
                    this.estados[item.estado].toString().toLowerCase();

                return (
                    nombre.includes(term) ||
                    capacidad.includes(term) ||
                    (estadoTexto && estadoTexto.includes(term))
                );
            });
        }
    },
    methods: {
        // panel columnas
        toggleColumnPanel() {
            this.showColumnPanel = !this.showColumnPanel;
        },
        toggleColumn(key) {
            this.$set(
                this.columnVisibility,
                key,
                !this.columnVisibility[key]
            );
        },
        selectAllColumns() {
            Object.keys(this.columnVisibility).forEach(k => {
                this.columnVisibility[k] = true;
            });
        },
        clearAllColumns() {
            Object.keys(this.columnVisibility).forEach(k => {
                this.columnVisibility[k] = false;
            });
        },

        // cálculo de tiempo de aireación
        calcularTiempoAireacion() {
            if (
                !this.aireacion.fecha_inicio ||
                !this.aireacion.hora_inicio ||
                !this.aireacion.fecha_fin ||
                !this.aireacion.hora_fin
            ) {
                Swal.fire({
                    icon: "warning",
                    title: "Datos incompletos",
                    text:
                        "Debes seleccionar fecha y hora de inicio y fin para calcular el tiempo."
                });
                return;
            }

            const inicio = new Date(
                `${this.aireacion.fecha_inicio}T${this.aireacion.hora_inicio}:00`
            );
            const fin = new Date(
                `${this.aireacion.fecha_fin}T${this.aireacion.hora_fin}:00`
            );

            if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                Swal.fire({
                    icon: "error",
                    title: "Error en las fechas/horas",
                    text:
                        "No se pudo interpretar la fecha u hora. Verifica los datos ingresados."
                });
                return;
            }

            if (fin <= inicio) {
                Swal.fire({
                    icon: "warning",
                    title: "Rango inválido",
                    text:
                        "La fecha/hora de fin debe ser mayor que la de inicio."
                });
                return;
            }

            const diffMs = fin.getTime() - inicio.getTime();
            const minutos = Math.round(diffMs / (1000 * 60));

            this.aireacion.minutos_calculados = minutos;

            // Actualizar también en recursoAireacion
            this.recursoAireacion.minutos = minutos;

            Swal.fire({
                icon: "success",
                title: "Tiempo calculado",
                text: `Tiempo total de aireación: ${minutos} minutos.`
            });
        },

        async fetchData() {
            return this.listadoParametrosExcel;
        },
        filtrarParametros() {
            if (this.f_inicio_d == "") {
                this.f_d = "-1";
            } else {
                this.f_d = this.f_inicio_d;
            }
            if (this.f_inicio_h == "") {
                this.f_h = "-1";
            } else {
                this.f_h = this.f_inicio_h;
            }

            const data = {
                id_contenedor: this.idEstanque,
                f_inicio_d: this.f_d,
                f_inicio_h: this.f_h
            };

            axios.post("api/filtro-parametros", data).then(response => {
                this.listadoParametros = response.data.calidad_agua;
                this.promedios = response.data.promedios;
            });
            axios.post("api/filtro-parametros-excel", data).then(response => {
                this.listadoParametrosExcel = response.data.calidad_agua;
            });
        },
        listar() {
            this.listarSiembras();
            this.listarParametrosEstanques();
            this.listarEstanques();
        },
        listarSiembras() {
            axios.get("api/siembras/listado").then(response => {
                this.listadoSiembras = response.data;
            });
        },
        listarParametrosEstanques() {
            axios.get("api/parametros-contenedores").then(response => {
                this.listadoParametrosEstanques = response.data;
            });
        },
        listarEstanques() {
            axios.get("api/listadoContenedores").then(response => {
                this.listadoEstanques = response.data;
            });
        },
        mostrarParametros(idContenedor) {
            this.idEstanque = idContenedor;
            this.siembraActiva = null; // Resetear siembra activa

            // Primero obtener los parámetros del estanque
            axios
                .post("api/parametro-x-contenedor/" + idContenedor)
                .then(response => {
                    this.mostrar = 1;
                    this.listadoParametros = response.data.calidad_agua;
                    this.promedios = response.data.promedios;
                    
                    // Buscar siembra activa en este estanque
                    this.buscarSiembraActiva(idContenedor);
                });

            axios
                .post("api/parametro-x-contenedor-excel/" + idContenedor)
                .then(response => {
                    this.listadoParametrosExcel = response.data.calidad_agua;
                });
        },

        // Método para buscar siembra activa en el estanque
        buscarSiembraActiva(idContenedor) {
            console.log("Buscando siembra activa para contenedor:", idContenedor);
            
            // Usar el endpoint correcto con parámetros de filtro
            axios.get("api/siembras", {
                params: {
                    estado_siembra: 1, // Solo activas
                    contenedor_id: idContenedor,
                    page: 1,
                    nro_results: 15,
                    type: "" // Agregar type vacío si es necesario
                }
            })
            .then(response => {
                console.log("Respuesta de búsqueda de siembra:", response.data);
                
                if (response.data.siembra && 
                    response.data.siembra.data && 
                    response.data.siembra.data.length > 0) {
                    
                    // Tomar la primera siembra activa encontrada
                    this.siembraActiva = response.data.siembra.data[0];
                    console.log("Siembra activa encontrada:", this.siembraActiva);
                    
                    // Mostrar notificación
                    Swal.fire({
                        icon: "success",
                        title: "Siembra activa encontrada",
                        text: `Se encontró la siembra: ${this.siembraActiva.nombre_siembra}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    this.siembraActiva = null;
                    console.log("No hay siembra activa en este estanque");
                    
                    // Mostrar advertencia
                    Swal.fire({
                        icon: "warning",
                        title: "Sin siembra activa",
                        text: "No se encontró una siembra activa en este estanque. No se podrá cargar configuración de aireación predefinida.",
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            })
            .catch(error => {
                console.error("Error buscando siembra activa:", error);
                this.siembraActiva = null;
            });
        },

        ocultarParametros() {
            this.editando = 0;
            this.mostrarMasDatos = false;
            this.mostrar = 0;
            this.idEstanque = "";
            this.siembraActiva = null;
            this.registrarAireacion = false;
            this.aireacion = {
                fecha_inicio: "",
                hora_inicio: "07:00",
                fecha_fin: "",
                hora_fin: "07:00",
                minutos_calculados: null
            };
            this.recursoAireacion = {
                recurso_id: null,
                minutos: null,
                minutos_hombre: null,
                detalles: "",
                tipo_actividad_id: 12
            };
            this.listar();
        },
        crearParametros() {
            if (!this.idEstanque) {
                Swal.fire({
                    icon: "warning",
                    title: "Selecciona un estanque",
                    text:
                        "Debes seleccionar un estanque en la lista antes de registrar parámetros."
                });
                return;
            }

            this.editando = 0;
            this.mostrarMasDatos = false;
            this.registrarAireacion = false;
            this.aireacion = {
                fecha_inicio: "",
                hora_inicio: "07:00",
                fecha_fin: "",
                hora_fin: "07:00",
                minutos_calculados: null
            };
            // Resetear recursoAireacion
            this.recursoAireacion = {
                recurso_id: null,
                minutos: null,
                minutos_hombre: null,
                detalles: "",
                tipo_actividad_id: 12
            };
            
            this.form.reset();
            this.form.id_contenedor = this.idEstanque;

            $("#modalParametros").modal("show");
        },
        
        async guardar() {
            try {
                this.cargandoGuardado = true;
                
                // 1. Preparar datos de parámetros de calidad
                this.form.id_contenedor = this.idEstanque;

                // Convertir valores vacíos a null para campos numéricos
                const camposNumericos = ['8_pm', '12_am', '4_am', '4_pm', 'temperatura', 'ph', 'amonio', 'nitrito', 'nitrato', 'otros'];
                camposNumericos.forEach(campo => {
                    if (this.form[campo] === '' || this.form[campo] === null) {
                        this.form[campo] = null;
                    } else {
                        // Permitir decimales
                        this.form[campo] = parseFloat(this.form[campo]);
                    }
                });

                // 2. Guardar parámetros de calidad del agua
                const responseParametros = await this.form.post("api/parametros-calidad");
                
                console.log("Parámetros guardados:", responseParametros.data);

                // 3. Si hay aireación activa, guardar el recurso también
                if (this.registrarAireacion && this.siembraActiva && this.siembraActiva.id) {
                    await this.guardarRecursoAireacion(responseParametros.data.fecha_parametro);
                }

                this.editando = 0;
                this.filtrarParametros();
                $("#modalParametros").modal("hide");
                
                Swal.fire({
                    icon: "success",
                    title: "¡Registro completado!",
                    text: "Parámetros de calidad del agua y aireación guardados correctamente.",
                    timer: 3000,
                    showConfirmButton: false
                });

                // Resetear formulario
                this.form.reset();
                this.mostrarMasDatos = false;
                this.registrarAireacion = false;
                this.aireacion = {
                    fecha_inicio: "",
                    hora_inicio: "07:00",
                    fecha_fin: "",
                    hora_fin: "07:00",
                    minutos_calculados: null
                };
                this.recursoAireacion = {
                    recurso_id: null,
                    minutos: null,
                    minutos_hombre: null,
                    detalles: "",
                    tipo_actividad_id: 12
                };

            } catch (error) {
                console.error("Error al guardar:", error);
                
                let mensajeError = "Error al guardar los datos.";
                if (error.response && error.response.data) {
                    if (error.response.data.message) {
                        mensajeError = error.response.data.message;
                    } else if (error.response.data.errors) {
                        // Mostrar el primer error
                        const firstError = Object.values(error.response.data.errors)[0][0];
                        mensajeError = firstError;
                    }
                }
                
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: mensajeError
                });
            } finally {
                this.cargandoGuardado = false;
            }
        },
        
        async guardarRecursoAireacion(fechaParametros = null) {
            try {
                // Validar datos requeridos para recurso de aireación
                if (!this.recursoAireacion.recurso_id) {
                    throw new Error("Debe seleccionar un recurso para la aireación");
                }
                
                if (!this.aireacion.minutos_calculados && this.recursoAireacion.minutos === null) {
                    throw new Error("Debe calcular el tiempo de aireación primero");
                }

                // Preparar datos para recursos-necesarios
                const datosRecurso = {
                    id_siembra: this.siembraActiva.id,
                    tipo_actividad: 12, // Aireación mecánica
                    fecha_ra: fechaParametros || this.form.fecha_parametro, // Misma fecha que parámetros
                    minutos_hombre: this.recursoAireacion.minutos_hombre || "0",
                    id_recurso: this.recursoAireacion.recurso_id,
                    cantidad_recurso: this.aireacion.minutos_calculados || this.recursoAireacion.minutos || 0,
                    detalles: this.recursoAireacion.detalles || "Aireación Mecánica",
                    tipo: "RECURSOS NECESARIOS",
                    descripcion: "RECURSOS NECESARIOS SIEMBRAS"
                };

                console.log("Enviando recurso aireación:", datosRecurso);

                const responseRecurso = await axios.post("api/recursos-necesarios", datosRecurso);
                
                console.log("Recurso de aireación guardado:", responseRecurso.data);
                
                return responseRecurso.data;
            } catch (error) {
                console.error("Error al guardar recurso de aireación:", error);
                throw error;
            }
        },
        
        editarParametros(objeto) {
            this.form.reset();
            this.form.fill(objeto);
            this.form.id_contenedor = this.idEstanque;
            this.editando = 1;
            this.mostrarMasDatos = true;

            // al editar, por defecto no mostramos aireación
            this.registrarAireacion = false;
            this.aireacion = {
                fecha_inicio: "",
                hora_inicio: "07:00",
                fecha_fin: "",
                hora_fin: "07:00",
                minutos_calculados: null
            };
            this.recursoAireacion = {
                recurso_id: null,
                minutos: null,
                minutos_hombre: null,
                detalles: "",
                tipo_actividad_id: 12
            };

            $("#modalParametros").modal("show");
        },
        
        async editar() {
            try {
                this.cargandoGuardado = true;
                
                // 1. Preparar datos de parámetros de calidad
                this.form.id_contenedor = this.idEstanque;

                // Convertir valores vacíos a null para campos numéricos
                const camposNumericos = ['8_pm', '12_am', '4_am', '4_pm', 'temperatura', 'ph', 'amonio', 'nitrito', 'nitrato', 'otros'];
                camposNumericos.forEach(campo => {
                    if (this.form[campo] === '' || this.form[campo] === null) {
                        this.form[campo] = null;
                    } else {
                        // Permitir decimales
                        this.form[campo] = parseFloat(this.form[campo]);
                    }
                });

                // 2. Actualizar parámetros de calidad del agua
                const responseParametros = await this.form.put("api/parametros-calidad/" + this.form.id);
                
                console.log("Parámetros actualizados:", responseParametros.data);

                // 3. Si hay aireación activa, guardar/actualizar el recurso
                if (this.registrarAireacion && this.siembraActiva && this.siembraActiva.id) {
                    await this.guardarRecursoAireacion(this.form.fecha_parametro);
                }

                $("#modalParametros").modal("hide");
                this.mostrarParametros(this.idEstanque);
                this.listar();
                this.form.reset();
                this.mostrarMasDatos = false;
                this.registrarAireacion = false;
                this.aireacion = {
                    fecha_inicio: "",
                    hora_inicio: "07:00",
                    fecha_fin: "",
                    hora_fin: "07:00",
                    minutos_calculados: null
                };
                this.recursoAireacion = {
                    recurso_id: null,
                    minutos: null,
                    minutos_hombre: null,
                    detalles: "",
                    tipo_actividad_id: 12
                };
                
                Swal.fire({
                    icon: "success",
                    title: "¡Actualización completada!",
                    text: "Parámetros de calidad del agua actualizados correctamente.",
                    timer: 3000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error("Error al actualizar:", error);
                
                let mensajeError = "Error al actualizar los datos.";
                if (error.response && error.response.data) {
                    if (error.response.data.message) {
                        mensajeError = error.response.data.message;
                    } else if (error.response.data.errors) {
                        // Mostrar el primer error
                        const firstError = Object.values(error.response.data.errors)[0][0];
                        mensajeError = firstError;
                    }
                }
                
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: mensajeError
                });
            } finally {
                this.cargandoGuardado = false;
            }
        },
        
        eliminarParametros(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text:
                    "Una vez eliminado, no se puede recuperar los registros asociados a este ID",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#c7120c",
                cancelButtonText: "Cancelar",
                confirmButtonText: "Aceptar!",
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete("api/parametros-calidad/" + id)
                        .then(({ data }) => {
                            this.listar();
                            if (this.idEstanque) {
                                this.mostrarParametros(this.idEstanque);
                            }
                            
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "Registro eliminado correctamente.",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "No se pudo eliminar el registro."
                            });
                        });
                }
            });
        }
    },
    mounted() {
        this.listar();
        this.estados[0] = "Inactivo";
        this.estados[1] = "Activo";
        this.estados[2] = "Ocupado";
        this.estados[3] = "Descanso";
    }
};
</script>

<style scoped>
.column-toggle-btn {
  border-radius: 999px;
  background-color: #ffffff;
  font-size: 0.85rem;
}

.column-panel-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.35rem;
  z-index: 30;
  background: #ffffff;
  border-radius: 14px;
  padding: 0.75rem 1rem 0.85rem;
  min-width: 220px;
  max-width: 320px;
  max-height: 220px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
}

.column-panel-title {
  font-size: 0.8rem;
  color: #6b7280;
}

.column-panel-actions .btn {
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
}

.column-item {
  display: flex;
  align-items: center;
  font-size: 0.82rem;
  margin-bottom: 0.25rem;
  cursor: pointer;
  user-select: none;
  padding: 0.12rem 0;
}

.column-checkbox {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1px solid #cbd5e1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.45rem;
  background-color: #ffffff;
  transition: all 0.12s ease;
  flex-shrink: 0;
}

.column-checkbox.is-checked {
  background-color: #2563eb;
  border-color: #2563eb;
  color: #ffffff;
}

.column-checkbox i {
  font-size: 0.7rem;
}

.column-label {
  line-height: 1.1;
}

.table-container--wide {
  position: relative;
  overflow-x: auto;
  width: 100%;
}
</style>