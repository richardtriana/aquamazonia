<template>
    <div class="water-params-container">
        <!-- SPINNER GLOBAL -->
        <div v-if="isLoading" class="global-spinner">
            <div class="global-spinner-inner">
                <div class="global-spinner-ring"></div>
                <div class="global-spinner-logo">
                    AQUAMAZONIA
                </div>
                <div class="global-spinner-text">
                    Cargando información, por favor espera...
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="row text-left mb-3">
            <div class="col-12">
                <h5 class="mb-3">Filtrar por:</h5>
            </div>
        </div>

        <!-- FILTROS PRINCIPALES -->
        <div class="row align-items-end mb-3 filtro-bar">
            <!-- Fecha -->
            <div class="form-group col-md-3 col-lg-2">
                <label for="filter_fecha_parametro">Fecha</label>
                <input
                    id="filter_fecha_parametro"
                    type="date"
                    class="form-control filtro-control"
                    v-model="filters.fecha_parametro"
                >
            </div>

            <!-- Siembra -->
            <div class="form-group col-md-4 col-lg-3">
                <label for="filter_siembra">Siembra</label>
                <v-select
                    :options="listadoSiembras"
                    label="nombre_siembra"
                    :reduce="s => s.id"
                    v-model="filters.id_siembra"
                    placeholder="Todas las siembras"
                    class="filtro-vselect"
                />
            </div>

            <!-- Registrador -->
            <div class="form-group col-md-3 col-lg-2">
                <label for="filter_registered_user">Registrador</label>
                <select
                    id="filter_registered_user"
                    class="form-control filtro-control"
                    v-model="filters.registered_user_id"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="(u, index) in listadoRegisteredUsers"
                        :key="index"
                        :value="u.id"
                    >
                        {{ formatRegisteredUser(u) }}
                    </option>
                </select>
            </div>

            <!-- Resultados por página -->
            <div class="form-group col-md-3 col-lg-2">
                <label for="per_page">
                    Mostrar {{ filters.per_page }} por página
                </label>
                <input
                    id="per_page"
                    type="number"
                    min="1"
                    class="form-control filtro-control"
                    v-model.number="filters.per_page"
                >
            </div>

            <!-- Botón filtrar -->
            <div class="form-group col-md-2 col-lg-1 text-right">
                <button
                    class="btn btn-primary btn-block filtro-btn-filtrar"
                    @click="listar()"
                >
                    Filtrar
                </button>
            </div>
        </div>

        <!-- FILTROS AVANZADOS -->
        <div class="row align-items-end mb-4 filtro-bar">
            <!-- Filtro pH -->
            <div class="form-group col-md-4 col-lg-3">
                <label>Filtro pH</label>
                <div class="d-flex">
                    <select
                        class="form-control filtro-control mr-2"
                        v-model="filters.ph_op"
                        style="flex: 0 0 130px;"
                    >
                        <option value="">Operador</option>
                        <option value=">">Mayor que (&gt;)</option>
                        <option value="<">Menor que (&lt;)</option>
                        <option value="=">Igual a (=)</option>
                        <option value="between">Entre</option>
                    </select>
                    <input
                        v-if="filters.ph_op !== 'between'"
                        type="number"
                        min="0"
                        max="14"
                        step="0.1"
                        class="form-control filtro-control"
                        v-model.number="filters.ph_val"
                        placeholder="Valor (0-14)"
                    >
                    <div v-if="filters.ph_op === 'between'" class="d-flex w-100">
                        <input
                            type="number"
                            min="0"
                            max="14"
                            step="0.1"
                            class="form-control filtro-control mr-1"
                            v-model.number="filters.ph_min"
                            placeholder="Mín"
                        >
                        <input
                            type="number"
                            min="0"
                            max="14"
                            step="0.1"
                            class="form-control filtro-control ml-1"
                            v-model.number="filters.ph_max"
                            placeholder="Máx"
                        >
                    </div>
                </div>
            </div>

            <!-- Filtro Temperatura -->
            <div class="form-group col-md-4 col-lg-3">
                <label>Filtro Temperatura (°C)</label>
                <div class="d-flex">
                    <select
                        class="form-control filtro-control mr-2"
                        v-model="filters.temp_op"
                        style="flex: 0 0 130px;"
                    >
                        <option value="">Operador</option>
                        <option value=">">Mayor que (&gt;)</option>
                        <option value="<">Menor que (&lt;)</option>
                        <option value="=">Igual a (=)</option>
                    </select>
                    <input
                        type="number"
                        min="0"
                        step="0.1"
                        class="form-control filtro-control"
                        v-model.number="filters.temp_val"
                        placeholder="Valor"
                    >
                </div>
            </div>

            <!-- Filtro Amonio -->
            <div class="form-group col-md-4 col-lg-3">
                <label>Filtro Amonio (mg/L)</label>
                <div class="d-flex">
                    <select
                        class="form-control filtro-control mr-2"
                        v-model="filters.amonio_op"
                        style="flex: 0 0 130px;"
                    >
                        <option value="">Operador</option>
                        <option value=">">Mayor que (&gt;)</option>
                        <option value="<">Menor que (&lt;)</option>
                        <option value="=">Igual a (=)</option>
                    </select>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        class="form-control filtro-control"
                        v-model.number="filters.amonio_val"
                        placeholder="Valor"
                    >
                </div>
            </div>
        </div>

        <!-- ACCIONES: SINCRONIZAR + CONTADOR -->
        <div class="row mb-3 align-items-center justify-content-between">
            <div class="col-md-6 mb-2 mb-md-0">
                <button
                    type="button"
                    class="btn btn-success"
                    :disabled="!selectedPrelogs.length || syncing"
                    @click="syncSelected"
                >
                    <i class="fas fa-sync-alt mr-1"></i>
                    Sincronizar seleccionados
                    <span v-if="selectedPrelogs.length">
                        ({{ selectedPrelogs.length }})
                    </span>
                </button>
            </div>
            <div class="col-md-6 text-md-right text-left">
                <small class="text-muted">
                    Preregistros encontrados:
                    <strong>
                        {{ listadoPrelogs.total || (listadoPrelogs.data ? listadoPrelogs.data.length : 0) }}
                    </strong>
                </small>
            </div>
        </div>

        <!-- ACCIONES: EXCEL + SELECTOR DE COLUMNAS -->
        <div class="row mb-3 justify-content-between align-items-center">
            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                <downloadexcel
                    class="btn btn-success w-100"
                    :fetch="fetchData"
                    :fields="json_fields"
                    name="preregistros-parametros-agua.xls"
                    type="xls"
                >
                    <i class="fa fa-fw fa-download"></i> Generar Excel
                </downloadexcel>
            </div>

            <!-- Botón columnas -->
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
                            showColumnPanel ? 'fa-chevron-up' : 'fa-chevron-down'
                        ]"
                    ></i>
                </button>

                <!-- PANEL DESPLEGABLE DE COLUMNAS -->
                <div
                    v-if="showColumnPanel"
                    class="column-panel-dropdown shadow-lg"
                >
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="column-panel-title">
                            Marca / desmarca las columnas que deseas ver
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
                                v-for="item in columnItems"
                                :key="item.key"
                                @click.stop="toggleColumn(item.key)"
                            >
                                <span
                                    class="column-checkbox"
                                    :class="{ 'is-checked': columnVisibility[item.key] }"
                                >
                                    <i
                                        v-if="columnVisibility[item.key]"
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

        <!-- TABLA DE PREREGISTROS -->
        <div class="table-container table-container--wide">
            <table class="table-sticky table table-sm table-hover table-bordered">
                <thead class="thead-primary">
                    <tr>
                        <!-- Columna de selección -->
                        <th class="text-center" style="width: 50px;">
                            Sel.
                            <div class="tp-checkbox-wrapper">
                                <span
                                    class="tp-checkbox"
                                    :class="{ 'tp-checkbox--checked': areAllCurrentPageSelected }"
                                    @click.stop="toggleSelectAllCurrent"
                                ></span>
                            </div>
                        </th>
                        <th>#</th>
                        <th v-if="columnVisibility.fecha">Fecha</th>
                        <th v-if="columnVisibility.siembra">Siembra</th>
                        <th v-if="columnVisibility.ph">pH</th>
                        <th v-if="columnVisibility.temperatura">Temp. (°C)</th>
                        <th v-if="columnVisibility.amonio">Amonio</th>
                        <th v-if="columnVisibility.nitrito">Nitrito</th>
                        <th v-if="columnVisibility.nitrato">Nitrato</th>
                        <th v-if="columnVisibility['12_am']">12 am</th>
                        <th v-if="columnVisibility['4_am']">4 am</th>
                        <th v-if="columnVisibility['7_am']">7 am</th>
                        <th v-if="columnVisibility['4_pm']">4 pm</th>
                        <th v-if="columnVisibility['8_pm']">8 pm</th>
                        <th v-if="columnVisibility.otros">Otros</th>
                        <th v-if="columnVisibility.registrador">Registrador</th>
                        <th v-if="columnVisibility.descripcion">Descripción</th>
                        <th width="110">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in displayedPrelogs"
                        :key="item.id"
                    >
                        <td class="text-center">
                            <div class="tp-checkbox-wrapper">
                                <span
                                    class="tp-checkbox"
                                    :class="{ 'tp-checkbox--checked': isSelected(item.id) }"
                                    @click.stop="toggleSeleccion(item.id)"
                                ></span>
                            </div>
                        </td>
                        <td>{{ index + 1 + offsetIndex }}</td>
                        <td v-if="columnVisibility.fecha">
                            {{ formatFecha(item.fecha_parametro) }}
                        </td>
                        <td v-if="columnVisibility.siembra">
                            {{ getSiembraNombre(item.id_siembra) }}
                        </td>
                        <td v-if="columnVisibility.ph">
                            {{ item.ph || '-' }}
                        </td>
                        <td v-if="columnVisibility.temperatura">
                            {{ item.temperatura || '-' }}
                        </td>
                        <td v-if="columnVisibility.amonio">
                            {{ item.amonio || '-' }}
                        </td>
                        <td v-if="columnVisibility.nitrito">
                            {{ item.nitrito || '-' }}
                        </td>
                        <td v-if="columnVisibility.nitrato">
                            {{ item.nitrato || '-' }}
                        </td>
                        <td v-if="columnVisibility['12_am']">
                            {{ item['12_am'] || '-' }}
                        </td>
                        <td v-if="columnVisibility['4_am']">
                            {{ item['4_am'] || '-' }}
                        </td>
                        <td v-if="columnVisibility['7_am']">
                            {{ item['7_am'] || '-' }}
                        </td>
                        <td v-if="columnVisibility['4_pm']">
                            {{ item['4_pm'] || '-' }}
                        </td>
                        <td v-if="columnVisibility['8_pm']">
                            {{ item['8_pm'] || '-' }}
                        </td>
                        <td v-if="columnVisibility.otros">
                            {{ item.otros ? (item.otros.length > 20 ? item.otros.substring(0, 20) + '...' : item.otros) : '-' }}
                        </td>
                        <td v-if="columnVisibility.registrador">
                            {{ getRegisteredUserNombre(item.registered_user_id) }}
                        </td>
                        <td v-if="columnVisibility.descripcion">
                            {{ item.descripcion ? (item.descripcion.length > 30 ? item.descripcion.substring(0, 30) + '...' : item.descripcion) : '-' }}
                        </td>
                        <td>
                            <button
                                class="btn btn-xs btn-primary mr-1"
                                @click="openEdit(item)"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                class="btn btn-xs btn-danger"
                                @click="eliminarPrelog(item)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!displayedPrelogs.length">
                        <td :colspan="calculateColspan()" class="text-center">
                            No hay preregistros de parámetros de agua que coincidan con los filtros.
                        </td>
                    </tr>
                </tbody>

                <!-- PROMEDIOS DE LA PÁGINA ACTUAL -->
                <tfoot v-if="displayedPrelogs.length">
                    <tr class="table-totals-row">
                        <td></td>
                        <td></td>
                        <td v-if="columnVisibility.fecha" colspan="2" class="text-right font-weight-bold">
                            Promedios (página):
                        </td>
                        <td v-if="columnVisibility.ph">
                            {{ pageAverages.ph | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility.temperatura">
                            {{ pageAverages.temperatura | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility.amonio">
                            {{ pageAverages.amonio | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility.nitrito">
                            {{ pageAverages.nitrito | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility.nitrato">
                            {{ pageAverages.nitrato | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility['12_am']">
                            {{ pageAverages['12_am'] | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility['4_am']">
                            {{ pageAverages['4_am'] | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility['7_am']">
                            {{ pageAverages['7_am'] | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility['4_pm']">
                            {{ pageAverages['4_pm'] | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility['8_pm']">
                            {{ pageAverages['8_pm'] | numeral("0.00") }}
                        </td>
                        <td v-if="columnVisibility.otros"></td>
                        <td v-if="columnVisibility.registrador"></td>
                        <td v-if="columnVisibility.descripcion"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <pagination
                v-if="listadoPrelogs && listadoPrelogs.data && listadoPrelogs.total"
                :align="'center'"
                :data="listadoPrelogs"
                :limit="2"
                @pagination-change-page="listar"
            >
                <span slot="prev-nav">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span slot="next-nav">
                    <i class="fas fa-arrow-right"></i>
                </span>
            </pagination>
        </div>

        <!-- MODAL EDITAR PREREGISTRO -->
        <edit-water-params-prelog-modal
            ref="editModal"
            :siembras="listadoSiembras"
            :registered-users="listadoRegisteredUsers"
            @updated="onPrelogUpdated"
        />
    </div>
</template>

<script>
import EditWaterParamsPrelogModal from "./EditWaterParamsPrelogModal.vue";
import downloadexcel from "vue-json-excel";

export default {
    components: {
        EditWaterParamsPrelogModal,
        downloadexcel
    },
    data() {
        return {
            // SPINNER
            isLoading: false,
            syncing: false,

            listadoPrelogs: {
                data: []
            },

            listadoSiembras: [],
            listadoRegisteredUsers: [],

            filters: {
                fecha_parametro: "",
                id_siembra: "",
                registered_user_id: "",
                per_page: 15,
                ph_op: "",
                ph_val: null,
                ph_min: null,
                ph_max: null,
                temp_op: "",
                temp_val: null,
                amonio_op: "",
                amonio_val: null
            },

            showColumnPanel: false,
            columnVisibility: {
                fecha: true,
                siembra: true,
                ph: true,
                temperatura: true,
                amonio: true,
                nitrito: true,
                nitrato: true,
                '12_am': true,
                '4_am': true,
                '7_am': true,
                '4_pm': true,
                '8_pm': true,
                otros: false,
                registrador: true,
                descripcion: false
            },

            selectedPrelogs: [],

            json_fields: {
                Fecha: "fecha",
                Siembra: "siembra",
                pH: "ph",
                "Temperatura (°C)": "temperatura",
                "Amonio (mg/L)": "amonio",
                "Nitrito (mg/L)": "nitrito",
                "Nitrato (mg/L)": "nitrato",
                "12 am": "12_am",
                "4 am": "4_am",
                "7 am": "7_am",
                "4 pm": "4_pm",
                "8 pm": "8_pm",
                Otros: "otros",
                Registrador: "registrador",
                Descripción: "descripcion"
            }
        };
    },
    computed: {
        offsetIndex() {
            if (
                !this.listadoPrelogs ||
                !this.listadoPrelogs.current_page ||
                !this.listadoPrelogs.per_page
            ) {
                return 0;
            }
            return (
                (this.listadoPrelogs.current_page - 1) *
                this.listadoPrelogs.per_page
            );
        },
        displayedPrelogs() {
            const rows = (this.listadoPrelogs.data || []).slice();

            return rows.filter(item => {
                // Filtro pH
                const ph = parseFloat(item.ph);
                if (!this.passesParamFilter(
                    ph,
                    this.filters.ph_op,
                    this.filters.ph_val,
                    this.filters.ph_min,
                    this.filters.ph_max
                )) {
                    return false;
                }

                // Filtro temperatura
                const temp = parseFloat(item.temperatura);
                if (!this.passesParamFilter(
                    temp,
                    this.filters.temp_op,
                    this.filters.temp_val
                )) {
                    return false;
                }

                // Filtro amonio
                const amonio = parseFloat(item.amonio);
                if (!this.passesParamFilter(
                    amonio,
                    this.filters.amonio_op,
                    this.filters.amonio_val
                )) {
                    return false;
                }

                return true;
            });
        },
        pageAverages() {
            const avgField = field => {
                const values = this.displayedPrelogs
                    .map(item => parseFloat(item[field]))
                    .filter(val => !isNaN(val));
                
                if (values.length === 0) return 0;
                
                const sum = values.reduce((acc, val) => acc + val, 0);
                return sum / values.length;
            };

            return {
                ph: avgField("ph"),
                temperatura: avgField("temperatura"),
                amonio: avgField("amonio"),
                nitrito: avgField("nitrito"),
                nitrato: avgField("nitrato"),
                '12_am': avgField("12_am"),
                '4_am': avgField("4_am"),
                '7_am': avgField("7_am"),
                '4_pm': avgField("4_pm"),
                '8_pm': avgField("8_pm")
            };
        },
        columnItems() {
            return [
                { key: "fecha", label: "Fecha" },
                { key: "siembra", label: "Siembra" },
                { key: "ph", label: "pH" },
                { key: "temperatura", label: "Temp. (°C)" },
                { key: "amonio", label: "Amonio" },
                { key: "nitrito", label: "Nitrito" },
                { key: "nitrato", label: "Nitrato" },
                { key: "12_am", label: "12 am" },
                { key: "4_am", label: "4 am" },
                { key: "7_am", label: "7 am" },
                { key: "4_pm", label: "4 pm" },
                { key: "8_pm", label: "8 pm" },
                { key: "otros", label: "Otros" },
                { key: "registrador", label: "Registrador" },
                { key: "descripcion", label: "Descripción" }
            ];
        },
        areAllCurrentPageSelected() {
            if (!this.displayedPrelogs.length) {
                return false;
            }
            return this.displayedPrelogs.every(item =>
                this.selectedPrelogs.includes(item.id)
            );
        }
    },
    methods: {
        // Formatea la fecha a YYYY-MM-DD
        formatFecha(fecha) {
            if (!fecha) return "";
            if (typeof fecha === "string" && fecha.length >= 10) {
                return fecha.substr(0, 10);
            }
            const d = new Date(fecha);
            if (isNaN(d.getTime())) return fecha;
            const yyyy = d.getFullYear();
            const mm = ("0" + (d.getMonth() + 1)).slice(-2);
            const dd = ("0" + d.getDate()).slice(-2);
            return `${yyyy}-${mm}-${dd}`;
        },

        passesParamFilter(value, op, threshold, min, max) {
            if (!op || (threshold === null && min === null && max === null)) {
                return true;
            }
            
            if (isNaN(value)) {
                return false;
            }

            if (op === "between") {
                if (min === null || max === null) return true;
                return value >= parseFloat(min) && value <= parseFloat(max);
            }

            const t = parseFloat(threshold);
            if (isNaN(t)) return true;
            
            if (op === ">") return value > t;
            if (op === "<") return value < t;
            if (op === "=") return value === t;
            
            return true;
        },

        calculateColspan() {
            // 2 columnas fijas (selección y #) + columnas visibles + columna acciones
            const visibleColumns = Object.values(this.columnVisibility).filter(v => v).length;
            return 2 + visibleColumns + 1; // +1 para la columna de acciones
        },

        toggleColumnPanel() {
            this.showColumnPanel = !this.showColumnPanel;
        },
        toggleColumn(key) {
            this.$set(this.columnVisibility, key, !this.columnVisibility[key]);
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

        formatRegisteredUser(user) {
            if (!user) return "";
            if (user.name) return user.name;
            if (user.email) return user.email;
            return `ID ${user.id}`;
        },
        getSiembraNombre(id) {
            const s = this.listadoSiembras.find(x => x.id === id);
            return s ? s.nombre_siembra : `Siembra #${id}`;
        },
        getRegisteredUserNombre(id) {
            const u = this.listadoRegisteredUsers.find(x => x.id === id);
            return u ? this.formatRegisteredUser(u) : id ? `ID ${id}` : "-";
        },

        isSelected(id) {
            return this.selectedPrelogs.includes(id);
        },
        toggleSeleccion(id) {
            const idx = this.selectedPrelogs.indexOf(id);
            if (idx === -1) {
                this.selectedPrelogs.push(id);
            } else {
                this.selectedPrelogs.splice(idx, 1);
            }
        },
        toggleSelectAllCurrent() {
            if (!this.displayedPrelogs.length) return;

            if (this.areAllCurrentPageSelected) {
                const idsPagina = this.displayedPrelogs.map(i => i.id);
                this.selectedPrelogs = this.selectedPrelogs.filter(
                    id => !idsPagina.includes(id)
                );
            } else {
                this.displayedPrelogs.forEach(i => {
                    if (!this.selectedPrelogs.includes(i.id)) {
                        this.selectedPrelogs.push(i.id);
                    }
                });
            }
        },

        listar(page = 1) {
            this.isLoading = true;

            const params = {
                page,
                nro_results: this.filters.per_page || 15
            };

            if (this.filters.fecha_parametro) {
                params.fecha_parametro = this.filters.fecha_parametro;
            }
            if (this.filters.id_siembra && this.filters.id_siembra !== -1) {
                params.id_siembra = this.filters.id_siembra;
            }
            if (this.filters.registered_user_id) {
                params.registered_user_id = this.filters.registered_user_id;
            }

            axios
                .get("api/parametros-agua-avanzados-prelogs", { params })
                .then(response => {
                    if (response.data && response.data.data) {
                        this.listadoPrelogs = {
                            data: response.data.data,
                            total: response.data.total,
                            current_page: response.data.current_page,
                            per_page: response.data.per_page
                        };
                    } else {
                        this.listadoPrelogs = { data: [] };
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "Ocurrió un problema cargando los preregistros de parámetros de agua.",
                        "error"
                    );
                    this.listadoPrelogs = { data: [] };
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        listarSiembras() {
            axios
                .get("api/siembras/listado")
                .then(response => {
                    this.listadoSiembras = response.data || [];
                })
                .catch(() => {
                    this.listadoSiembras = [];
                });
        },

        listarRegisteredUsers() {
            axios
                .get("api/registered-users")
                .then(response => {
                    this.listadoRegisteredUsers =
                        response.data.data || response.data || [];
                })
                .catch(() => {
                    this.listadoRegisteredUsers = [];
                });
        },

        openEdit(item) {
            if (this.$refs.editModal) {
                this.$refs.editModal.open(item);
            }
        },

        onPrelogUpdated() {
            const page = this.listadoPrelogs.current_page || 1;
            this.listar(page);
        },

        eliminarPrelog(item) {
            Swal.fire({
                title: "¿Eliminar preregistro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#c7120c",
                cancelButtonText: "Cancelar",
                confirmButtonText: "Sí, eliminar",
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    axios
                        .delete(`api/parametros-agua-avanzados-prelogs/${item.id}`)
                        .then(() => {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "El preregistro se eliminó correctamente.",
                                timer: 1200,
                                showConfirmButton: false
                            });
                            const page = this.listadoPrelogs.current_page || 1;
                            this.listar(page);
                            this.selectedPrelogs = this.selectedPrelogs.filter(
                                id => id !== item.id
                            );
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire(
                                "Error",
                                "No se pudo eliminar el preregistro.",
                                "error"
                            );
                        });
                }
            });
        },

        syncSelected() {
            if (!this.selectedPrelogs.length) return;

            this.syncing = true;
            this.isLoading = true;

            axios
                .post("api/parametros-agua-avanzados-prelogs/sincronizar", {
                    ids: this.selectedPrelogs
                })
                .then(response => {
                    Swal.fire({
                        icon: "success",
                        title: "Sincronización completa",
                        text:
                            (response.data && response.data.message) ||
                            "Los preregistros seleccionados se sincronizaron correctamente.",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    this.selectedPrelogs = [];
                    this.listar(this.listadoPrelogs.current_page || 1);
                })
                .catch(error => {
                    console.error(error);
                    let msg =
                        "Ocurrió un problema al sincronizar los preregistros.";
                    if (error.response && error.response.status === 404) {
                        msg =
                            "El endpoint de sincronización aún no está implementado en el backend.";
                    }
                    Swal.fire("Error", msg, "error");
                })
                .finally(() => {
                    this.syncing = false;
                    this.isLoading = false;
                });
        },

        async fetchData() {
            const params = {
                no_paginate: 1
            };

            if (this.filters.fecha_parametro) {
                params.fecha_parametro = this.filters.fecha_parametro;
            }
            if (this.filters.id_siembra && this.filters.id_siembra !== -1) {
                params.id_siembra = this.filters.id_siembra;
            }
            if (this.filters.registered_user_id) {
                params.registered_user_id = this.filters.registered_user_id;
            }

            const response = await axios.get("api/parametros-agua-avanzados-prelogs", { params });
            const rows = Array.isArray(response.data)
                ? response.data
                : response.data.data || [];

            const filtrados = rows.filter(item => {
                const ph = parseFloat(item.ph);
                const temp = parseFloat(item.temperatura);
                const amonio = parseFloat(item.amonio);

                if (!this.passesParamFilter(
                    ph,
                    this.filters.ph_op,
                    this.filters.ph_val,
                    this.filters.ph_min,
                    this.filters.ph_max
                )) {
                    return false;
                }

                if (!this.passesParamFilter(
                    temp,
                    this.filters.temp_op,
                    this.filters.temp_val
                )) {
                    return false;
                }

                if (!this.passesParamFilter(
                    amonio,
                    this.filters.amonio_op,
                    this.filters.amonio_val
                )) {
                    return false;
                }

                return true;
            });

            return filtrados.map(item => ({
                fecha: this.formatFecha(item.fecha_parametro),
                siembra: this.getSiembraNombre(item.id_siembra),
                ph: item.ph,
                temperatura: item.temperatura,
                amonio: item.amonio,
                nitrito: item.nitrito,
                nitrato: item.nitrato,
                '12_am': item['12_am'],
                '4_am': item['4_am'],
                '7_am': item['7_am'],
                '4_pm': item['4_pm'],
                '8_pm': item['8_pm'],
                otros: item.otros,
                registrador: this.getRegisteredUserNombre(item.registered_user_id),
                descripcion: item.descripcion
            }));
        },

        /**
         * Obtener el total de registros pendientes de parámetros de agua
         * Este método es llamado desde el componente padre FeedingsSync.vue
         * @returns {Promise<number>} Total de registros pendientes
         */
        async obtenerTotalPendientes() {
            try {
                const response = await axios.get("api/parametros-agua-avanzados-prelogs/total-pendientes");
                if (response.data && response.data.success) {
                    return response.data.total || 0;
                }
                return 0;
            } catch (error) {
                console.error("Error obteniendo total de parámetros de agua pendientes:", error);
                return 0;
            }
        },

        /**
         * Método público para que el componente padre pueda obtener el total
         * Se puede llamar desde el padre: this.$refs.waterParamsSync.getTotalPendientes()
         */
        getTotalPendientes() {
            return this.obtenerTotalPendientes();
        }
    },
    mounted() {
        this.listarSiembras();
        this.listarRegisteredUsers();
        this.listar();
    }
};
</script>

<style scoped>
.water-params-container {
    padding: 1.5rem;
}

/* Barra de filtros */
.filtro-bar .form-group {
    margin-bottom: 0.75rem;
}

.filtro-control {
    border-radius: 999px;
    font-size: 0.9rem;
    height: 38px;
    padding: 0 0.85rem;
}

.filtro-vselect >>> .vs__dropdown-toggle {
    border-radius: 999px;
    min-height: 38px;
    padding: 0 0.5rem;
}

.filtro-vselect >>> .vs__selected,
.filtro-vselect >>> .vs__search {
    margin-top: 2px;
    margin-bottom: 2px;
}

.filtro-btn-filtrar {
    border-radius: 999px;
    font-size: 0.9rem;
}

/* Tabla estilo informes */
.table-container--wide {
    position: relative;
}

.table-sticky thead th {
    position: sticky;
    top: 0;
    background-color: #1d4ed8;
    color: #ffffff;
    z-index: 5;
}

.thead-primary {
    background-color: #1d4ed8;
    color: #ffffff;
}

/* Fila de totales */
.table-totals-row {
    background-color: #f9fafb;
    font-weight: 600;
}

/* Botón de columnas */
.column-toggle-btn {
    border-radius: 999px;
    background-color: #ffffff;
    font-size: 0.85rem;
}

/* Dropdown de columnas */
.column-panel-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 0.35rem;
    z-index: 30;
    background: #ffffff;
    border-radius: 14px;
    padding: 0.75rem 1rem 0.85rem;
    min-width: 260px;
    max-width: 360px;
    max-height: 260px;
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

/* Ítems de columnas con "check" custom del panel */
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

/* Checkbox custom para selección de filas */
.tp-checkbox-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

.tp-checkbox {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 2px solid #4b5563;
    background-color: #ffffff;
    display: inline-block;
    position: relative;
    cursor: pointer;
    transition: all 0.12s ease;
}

.tp-checkbox--checked {
    background-color: #22c55e;
    border-color: #15803d;
}

.tp-checkbox--checked::after {
    content: "";
    position: absolute;
    left: 4px;
    top: 1px;
    width: 6px;
    height: 11px;
    border-right: 2px solid #ffffff;
    border-bottom: 2px solid #ffffff;
    transform: rotate(40deg);
}

/* Spinner */
.global-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.85);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.global-spinner-inner {
    text-align: center;
    max-width: 300px;
}

.global-spinner-ring {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #1d4ed8;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.global-spinner-logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1d4ed8;
    margin-bottom: 10px;
}

.global-spinner-text {
    color: #6b7280;
    font-size: 0.9rem;
}
</style>