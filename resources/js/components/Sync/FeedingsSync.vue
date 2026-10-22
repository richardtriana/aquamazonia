<template>
    <div class="container">
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

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center text-uppercase">
                        SINCRONIZACIÓN PREREGISTROS
                    </div>
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'alimentacion' }" 
                                   href="#" @click.prevent="switchTab('alimentacion')">
                                    <i class="fas fa-utensils mr-1"></i> Alimentación
                                    <span v-if="totalAlimentacion > 0" class="badge badge-primary ml-1">
                                        {{ totalAlimentacion }}
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'aireacion' }" 
                                   href="#" @click.prevent="switchTab('aireacion')">
                                    <i class="fas fa-wind mr-1"></i> Aireación
                                    <span v-if="totalAireacion > 0" class="badge badge-primary ml-1">
                                        {{ totalAireacion }}
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'parametros-agua' }" 
                                   href="#" @click.prevent="switchTab('parametros-agua')">
                                    <i class="fas fa-tint mr-1"></i> Parámetros de Agua
                                    <span v-if="totalParametrosAgua > 0" class="badge badge-primary ml-1">
                                        {{ totalParametrosAgua }}
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div v-if="activeTab === 'alimentacion'" class="card-body">
                        <div class="row text-left mb-2">
                            <h5>Filtrar por:</h5>
                        </div>

                        <div class="row align-items-end mb-3 filtro-bar">
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_fecha_ra">Fecha</label>
                                <input id="filter_fecha_ra" type="date" class="form-control filtro-control" v-model="filtersAlimentacion.fecha_ra">
                            </div>

                            <div class="form-group col-md-4 col-lg-3">
                                <label for="filter_siembra">Siembra</label>
                                <v-select :options="listadoSiembras" label="nombre_siembra" :reduce="s => s.id" v-model="filtersAlimentacion.id_siembra" placeholder="Todas las siembras" class="filtro-vselect" />
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_alimento">Alimento</label>
                                <select id="filter_alimento" class="form-control filtro-control" v-model="filtersAlimentacion.id_alimento">
                                    <option value="">Todos</option>
                                    <option v-for="(a, index) in listadoAlimentos" :key="index" :value="a.id">
                                        {{ a.alimento }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_registered_user">Registrador</label>
                                <select id="filter_registered_user" class="form-control filtro-control" v-model="filtersAlimentacion.registered_user_id">
                                    <option value="">Todos</option>
                                    <option v-for="(u, index) in listadoRegisteredUsers" :key="index" :value="u.id">
                                        {{ formatRegisteredUser(u) }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="per_page">Mostrar {{ filtersAlimentacion.per_page }} por página</label>
                                <input id="per_page" type="number" min="1" class="form-control filtro-control" v-model.number="filtersAlimentacion.per_page">
                            </div>

                            <div class="form-group col-md-2 col-lg-1 text-right">
                                <button class="btn btn-primary btn-block filtro-btn-filtrar" @click="listarAlimentacion()">
                                    Filtrar
                                </button>
                            </div>
                        </div>

                        <div class="row align-items-end mb-3 filtro-bar">
                            <div class="form-group col-md-6 col-lg-4">
                                <label>Filtro Kg Mañana</label>
                                <div class="d-flex">
                                    <select class="form-control filtro-control mr-2" v-model="filtersAlimentacion.kg_manana_op" style="flex: 0 0 130px;">
                                        <option value="">Operador</option>
                                        <option value=">">Mayor que (&gt;)</option>
                                        <option value="<">Menor que (&lt;)</option>
                                        <option value="=">Igual a (=)</option>
                                    </select>
                                    <input type="number" min="0" step="0.01" class="form-control filtro-control" v-model.number="filtersAlimentacion.kg_manana_val" placeholder="Cantidad">
                                </div>
                            </div>

                            <div class="form-group col-md-6 col-lg-4">
                                <label>Filtro Kg Tarde</label>
                                <div class="d-flex">
                                    <select class="form-control filtro-control mr-2" v-model="filtersAlimentacion.kg_tarde_op" style="flex: 0 0 130px;">
                                        <option value="">Operador</option>
                                        <option value=">">Mayor que (&gt;)</option>
                                        <option value="<">Menor que (&lt;)</option>
                                        <option value="=">Igual a (=)</option>
                                    </select>
                                    <input type="number" min="0" step="0.01" class="form-control filtro-control" v-model.number="filtersAlimentacion.kg_tarde_val" placeholder="Cantidad">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center justify-content-between">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <button type="button" class="btn btn-success" :disabled="!selectedPrelogsAlimentacion.length || syncingAlimentacion" @click="syncSelectedAlimentacion">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Sincronizar seleccionados
                                    <span v-if="selectedPrelogsAlimentacion.length">
                                        ({{ selectedPrelogsAlimentacion.length }})
                                    </span>
                                </button>
                            </div>
                            <div class="col-md-6 text-md-right text-left">
                                <small class="text-muted">
                                    Preregistros encontrados:
                                    <strong>
                                        {{ listadoPrelogsAlimentacion.total || (listadoPrelogsAlimentacion.data ? listadoPrelogsAlimentacion.data.length : 0) }}
                                    </strong>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-between align-items-center">
                            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                                <downloadexcel class="btn btn-success w-100" :fetch="fetchDataAlimentacion" :fields="json_fields_alimentacion" name="preregistros-alimentacion.xls" type="xls">
                                    <i class="fa fa-fw fa-download"></i> Generar Excel
                                </downloadexcel>
                            </div>

                            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                                <button type="button" class="btn btn-outline-secondary w-100" @click="openQrModal">
                                    <i class="fas fa-qrcode mr-1"></i>
                                    QR acceso registrador
                                </button>
                            </div>

                            <div class="col-md-4 col-lg-3 position-relative">
                                <button type="button" class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn" @click="toggleColumnPanelAlimentacion">
                                    <span>
                                        <i class="fas fa-columns mr-1"></i>
                                        Columnas
                                    </span>
                                    <i :class="['fas', showColumnPanelAlimentacion ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                </button>

                                <div v-if="showColumnPanelAlimentacion" class="column-panel-dropdown shadow-lg">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="column-panel-title">Marca / desmarca las columnas que deseas ver</div>
                                        <div class="column-panel-actions">
                                            <button type="button" class="btn btn-sm btn-outline-success mr-1" @click.stop="selectAllColumnsAlimentacion">Todas</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" @click.stop="clearAllColumnsAlimentacion">Ninguna</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="column-item" v-for="item in columnItemsAlimentacion" :key="item.key" @click.stop="toggleColumnAlimentacion(item.key)">
                                                <span class="column-checkbox" :class="{ 'is-checked': columnVisibilityAlimentacion[item.key] }">
                                                    <i v-if="columnVisibilityAlimentacion[item.key]" class="fas fa-check"></i>
                                                </span>
                                                <span class="column-label">{{ item.label }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-container table-container--wide">
                            <table class="table-sticky table table-sm table-hover table-bordered">
                                <thead class="thead-primary">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            Sel.
                                            <div class="tp-checkbox-wrapper">
                                                <span class="tp-checkbox" :class="{ 'tp-checkbox--checked': areAllCurrentPageSelectedAlimentacion }" @click.stop="toggleSelectAllCurrentAlimentacion"></span>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th v-if="columnVisibilityAlimentacion.tipo_actividad">Tipo Actividad</th>
                                        <th v-if="columnVisibilityAlimentacion.siembra">Siembra</th>
                                        <th v-if="columnVisibilityAlimentacion.fecha">Fecha</th>
                                        <th v-if="columnVisibilityAlimentacion.minutos_hombre">Minutos hombre</th>
                                        <th v-if="columnVisibilityAlimentacion.alimento">Alimento</th>
                                        <th v-if="columnVisibilityAlimentacion.cant_manana">Cantidad Mañana (kg)</th>
                                        <th v-if="columnVisibilityAlimentacion.cant_tarde">Cantidad Tarde (kg)</th>
                                        <th v-if="columnVisibilityAlimentacion.total_dia">Total día (kg)</th>
                                        <th v-if="columnVisibilityAlimentacion.costo_kg">Costo Kg</th>
                                        <th v-if="columnVisibilityAlimentacion.costo_total">Costo total</th>
                                        <th v-if="columnVisibilityAlimentacion.conv_teorica">Conversión alimenticia teórica</th>
                                        <th v-if="columnVisibilityAlimentacion.biomasa">Incremento biomasa</th>
                                        <th v-if="columnVisibilityAlimentacion.detalles">Detalles</th>
                                        <th v-if="columnVisibilityAlimentacion.registrador">Registrador</th>
                                        <th width="110">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in displayedPrelogsAlimentacion" :key="item.id">
                                        <td class="text-center">
                                            <div class="tp-checkbox-wrapper">
                                                <span class="tp-checkbox" :class="{ 'tp-checkbox--checked': isSelectedAlimentacion(item.id) }" @click.stop="toggleSeleccionAlimentacion(item.id)"></span>
                                            </div>
                                        </td>
                                        <td>{{ index + 1 + offsetIndexAlimentacion }}</td>
                                        <td v-if="columnVisibilityAlimentacion.tipo_actividad">{{ item.tipo_actividad ? `Tipo ${item.tipo_actividad}` : '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.siembra">{{ getSiembraNombre(item.id_siembra) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.fecha">{{ formatFecha(item.fecha_ra) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.minutos_hombre">{{ item.minutos_hombre || '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.alimento">{{ getAlimentoNombre(item.id_alimento) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.cant_manana">{{ item.cant_manana ? item.cant_manana + ' kg' : '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.cant_tarde">{{ item.cant_tarde ? item.cant_tarde + ' kg' : '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.total_dia">{{ calcularTotalDia(item) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.costo_kg">{{ calcularCostoKg(item) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.costo_total">{{ calcularCostoTotal(item) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.conv_teorica">{{ item.conv_alimenticia || '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.biomasa">{{ calcularBiomasa(item) }}</td>
                                        <td v-if="columnVisibilityAlimentacion.detalles">{{ item.detalles || '-' }}</td>
                                        <td v-if="columnVisibilityAlimentacion.registrador">{{ getRegisteredUserNombre(item.registered_user_id) }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-primary mr-1" @click="openEditAlimentacion(item)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-xs btn-danger" @click="eliminarPrelogAlimentacion(item)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!displayedPrelogsAlimentacion.length">
                                        <td :colspan="totalColumnasAlimentacion" class="text-center">
                                            No hay preregistros de alimentación que coincidan con los filtros.
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot v-if="displayedPrelogsAlimentacion.length">
                                    <tr class="table-totals-row">
                                        <td></td>
                                        <td></td>

                                        <td v-if="columnVisibilityAlimentacion.tipo_actividad"></td>
                                        <td v-if="columnVisibilityAlimentacion.siembra"></td>
                                        
                                        <td v-if="columnVisibilityAlimentacion.fecha" class="text-right font-weight-bold">
                                            Totales (página filtrada):
                                        </td>
                                        <td v-else-if="!columnVisibilityAlimentacion.siembra && !columnVisibilityAlimentacion.tipo_actividad" class="text-right font-weight-bold">
                                            Totales:
                                        </td>
                                        
                                        <td v-if="columnVisibilityAlimentacion.minutos_hombre">
                                            {{ pageTotalsAlimentacion.minutos_hombre | numeral("0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibilityAlimentacion.alimento"></td>
                                        
                                        <td v-if="columnVisibilityAlimentacion.cant_manana">
                                            {{ pageTotalsAlimentacion.kg_manana | numeral("0,0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibilityAlimentacion.cant_tarde">
                                            {{ pageTotalsAlimentacion.kg_tarde | numeral("0,0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibilityAlimentacion.total_dia">
                                            {{ pageTotalsAlimentacion.total_dia | numeral("0,0.00") }} kg
                                        </td>
                                        <td v-if="columnVisibilityAlimentacion.costo_kg"></td>
                                        <td v-if="columnVisibilityAlimentacion.costo_total"></td>
                                        
                                        <td v-if="columnVisibilityAlimentacion.conv_teorica">
                                            {{ pageTotalsAlimentacion.conv_teorica_promedio | numeral("0.000") }}
                                        </td>
                                        
                                        <td v-if="columnVisibilityAlimentacion.biomasa"></td>
                                        <td v-if="columnVisibilityAlimentacion.detalles"></td>
                                        <td v-if="columnVisibilityAlimentacion.registrador"></td>
                                        <td></td> </tr>
                                </tfoot>
                            </table>

                            <pagination
                                v-if="listadoPrelogsAlimentacion && listadoPrelogsAlimentacion.data && listadoPrelogsAlimentacion.total"
                                :align="'center'"
                                :data="listadoPrelogsAlimentacion"
                                :limit="2"
                                @pagination-change-page="listarAlimentacion"
                            >
                                <span slot="prev-nav">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span slot="next-nav">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </pagination>
                        </div>
                    </div>
                    
                    <div v-else-if="activeTab === 'aireacion'" class="card-body">
                        <div class="row text-left mb-2">
                            <h5>Filtrar por:</h5>
                        </div>

                        <div class="row align-items-end mb-3 filtro-bar">
                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_fecha_ra_aireacion">Fecha</label>
                                <input id="filter_fecha_ra_aireacion" type="date" class="form-control filtro-control" v-model="filtersAireacion.fecha_ra">
                            </div>

                            <div class="form-group col-md-4 col-lg-3">
                                <label for="filter_siembra_aireacion">Siembra</label>
                                <v-select :options="listadoSiembras" label="nombre_siembra" :reduce="s => s.id" v-model="filtersAireacion.id_siembra" placeholder="Todas las siembras" class="filtro-vselect" />
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_recurso_aireacion">Recurso</label>
                                <select id="filter_recurso_aireacion" class="form-control filtro-control" v-model="filtersAireacion.id_recurso">
                                    <option value="">Todos</option>
                                    <option v-for="(r, index) in listadoRecursos" :key="index" :value="r.id">
                                        {{ r.nombre }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_tipo_actividad_aireacion">Tipo Actividad</label>
                                <select id="filter_tipo_actividad_aireacion" class="form-control filtro-control" v-model="filtersAireacion.tipo_actividad">
                                    <option value="">Todos</option>
                                    <option value="12">Aireación Mecánica</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="filter_registered_user_aireacion">Registrador</label>
                                <select id="filter_registered_user_aireacion" class="form-control filtro-control" v-model="filtersAireacion.registered_user_id">
                                    <option value="">Todos</option>
                                    <option v-for="(u, index) in listadoRegisteredUsers" :key="index" :value="u.id">
                                        {{ formatRegisteredUser(u) }}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3 col-lg-2">
                                <label for="per_page_aireacion">Mostrar {{ filtersAireacion.per_page }} por página</label>
                                <input id="per_page_aireacion" type="number" min="1" class="form-control filtro-control" v-model.number="filtersAireacion.per_page">
                            </div>

                            <div class="form-group col-md-2 col-lg-1 text-right">
                                <button class="btn btn-primary btn-block filtro-btn-filtrar" @click="listarAireacion()">
                                    Filtrar
                                </button>
                            </div>
                        </div>

                        <div class="row align-items-end mb-3 filtro-bar">
                            <div class="form-group col-md-6 col-lg-4">
                                <label>Filtro Minutos Hombre</label>
                                <div class="d-flex">
                                    <select class="form-control filtro-control mr-2" v-model="filtersAireacion.minutos_hombre_op" style="flex: 0 0 130px;">
                                        <option value="">Operador</option>
                                        <option value=">">Mayor que (&gt;)</option>
                                        <option value="<">Menor que (&lt;)</option>
                                        <option value="=">Igual a (=)</option>
                                    </select>
                                    <input type="number" min="0" step="0.01" class="form-control filtro-control" v-model.number="filtersAireacion.minutos_hombre_val" placeholder="Minutos">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center justify-content-between">
                            <div class="col-md-6 mb-2 mb-md-0 d-flex align-items-center" style="gap:8px">
                                <button type="button" class="btn btn-success" :disabled="!selectedPrelogsAireacion.length || syncingAireacion" @click="syncSelectedAireacion">
                                    <i class="fas fa-sync-alt mr-1"></i>
                                    Sincronizar seleccionados
                                    <span v-if="selectedPrelogsAireacion.length">
                                        ({{ selectedPrelogsAireacion.length }})
                                    </span>
                                </button>
                                <button type="button" class="btn btn-warning" title="Reparar registros de aireación" @click="abrirRepairModal">
                                    <i class="fas fa-tools mr-1"></i> Reparar registros
                                </button>
                            </div>
                            <div class="col-md-6 text-md-right text-left">
                                <small class="text-muted">
                                    Preregistros encontrados:
                                    <strong>
                                        {{ listadoPrelogsAireacion.total || (listadoPrelogsAireacion.data ? listadoPrelogsAireacion.data.length : 0) }}
                                    </strong>
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-between align-items-center">
                            <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                                <downloadexcel class="btn btn-success w-100" :fetch="fetchDataAireacion" :fields="json_fields_aireacion" name="preregistros-aireacion.xls" type="xls">
                                    <i class="fa fa-fw fa-download"></i> Generar Excel
                                </downloadexcel>
                            </div>

                            <div class="col-md-4 col-lg-3 position-relative">
                                <button type="button" class="btn btn-light border w-100 d-flex justify-content-between align-items-center column-toggle-btn" @click="toggleColumnPanelAireacion">
                                    <span>
                                        <i class="fas fa-columns mr-1"></i>
                                        Columnas
                                    </span>
                                    <i :class="['fas', showColumnPanelAireacion ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                                </button>

                                <div v-if="showColumnPanelAireacion" class="column-panel-dropdown shadow-lg">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="column-panel-title">Marca / desmarca las columnas que deseas ver</div>
                                        <div class="column-panel-actions">
                                            <button type="button" class="btn btn-sm btn-outline-success mr-1" @click.stop="selectAllColumnsAireacion">Todas</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" @click.stop="clearAllColumnsAireacion">Ninguna</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="column-item" v-for="item in columnItemsAireacion" :key="item.key" @click.stop="toggleColumnAireacion(item.key)">
                                                <span class="column-checkbox" :class="{ 'is-checked': columnVisibilityAireacion[item.key] }">
                                                    <i v-if="columnVisibilityAireacion[item.key]" class="fas fa-check"></i>
                                                </span>
                                                <span class="column-label">{{ item.label }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-container table-container--wide">
                            <table class="table-sticky table table-sm table-hover table-bordered">
                                <thead class="thead-primary">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            Sel.
                                            <div class="tp-checkbox-wrapper">
                                                <span class="tp-checkbox" :class="{ 'tp-checkbox--checked': areAllCurrentPageSelectedAireacion }" @click.stop="toggleSelectAllCurrentAireacion"></span>
                                            </div>
                                        </th>
                                        <th>#</th>
                                        <th v-if="columnVisibilityAireacion.tipo_actividad">Tipo Actividad</th>
                                        <th v-if="columnVisibilityAireacion.siembra">Siembra</th>
                                        <th v-if="columnVisibilityAireacion.fecha">Fecha</th>
                                        <th v-if="columnVisibilityAireacion.min_hombre">Min Hombre</th>
                                        <th v-if="columnVisibilityAireacion.costo_min">Costo Min</th>
                                        <th v-if="columnVisibilityAireacion.recurso">Recurso</th>
                                        <th v-if="columnVisibilityAireacion.cantidad">Cantidad</th>
                                        <th v-if="columnVisibilityAireacion.costo">Costo</th>
                                        <th v-if="columnVisibilityAireacion.total">Total</th>
                                        <th v-if="columnVisibilityAireacion.descripcion">Descripción</th>
                                        <th v-if="columnVisibilityAireacion.detalles">Detalles</th>
                                        <th v-if="columnVisibilityAireacion.registrador">Registrador</th>
                                        <th width="110">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in displayedPrelogsAireacion" :key="item.id">
                                        <td class="text-center">
                                            <div class="tp-checkbox-wrapper">
                                                <span class="tp-checkbox" :class="{ 'tp-checkbox--checked': isSelectedAireacion(item.id) }" @click.stop="toggleSeleccionAireacion(item.id)"></span>
                                            </div>
                                        </td>
                                        <td>{{ index + 1 + offsetIndexAireacion }}</td>
                                        <td v-if="columnVisibilityAireacion.tipo_actividad">{{ getTipoActividad(item.tipo_actividad) }}</td>
                                        <td v-if="columnVisibilityAireacion.siembra">{{ getSiembraNombre(item.id_siembra) }}</td>
                                        <td v-if="columnVisibilityAireacion.fecha">{{ formatFecha(item.fecha_ra) }}</td>
                                        <td v-if="columnVisibilityAireacion.min_hombre">{{ item.minutos_hombre || '-' }}</td>
                                        <td v-if="columnVisibilityAireacion.costo_min">{{ calcularCostoMinuto(item) }}</td>
                                        <td v-if="columnVisibilityAireacion.recurso">{{ getRecursoNombre(item.id_recurso) }}</td>
                                        <td v-if="columnVisibilityAireacion.cantidad">{{ calcularCantidad(item) }}</td>
                                        <td v-if="columnVisibilityAireacion.costo">{{ calcularCosto(item) }}</td>
                                        <td v-if="columnVisibilityAireacion.total">{{ calcularTotal(item) }}</td>
                                        <td v-if="columnVisibilityAireacion.descripcion">{{ item.descripcion || '-' }}</td>
                                        <td v-if="columnVisibilityAireacion.detalles">{{ item.detalles || '-' }}</td>
                                        <td v-if="columnVisibilityAireacion.registrador">{{ getRegisteredUserNombre(item.registered_user_id) }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-primary mr-1" @click="openEditAireacion(item)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-xs btn-danger" @click="eliminarPrelogAireacion(item)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!displayedPrelogsAireacion.length">
                                        <td :colspan="totalColumnasAireacion" class="text-center">
                                            No hay preregistros de aireación que coincidan con los filtros.
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot v-if="displayedPrelogsAireacion.length">
                                    <tr class="table-totals-row">
                                        <td></td>
                                        <td></td>

                                        <td v-if="columnVisibilityAireacion.tipo_actividad"></td>
                                        <td v-if="columnVisibilityAireacion.siembra"></td>
                                        <td v-if="columnVisibilityAireacion.fecha" class="text-right font-weight-bold">
                                            Totales (página filtrada):
                                        </td>

                                        <td v-if="columnVisibilityAireacion.min_hombre">
                                            {{ pageTotalsAireacion.minutos_hombre | numeral("0,0.00") }}
                                        </td>
                                        <td v-if="columnVisibilityAireacion.costo_min"></td>
                                        <td v-if="columnVisibilityAireacion.recurso"></td>
                                        <td v-if="columnVisibilityAireacion.cantidad"></td>
                                        <td v-if="columnVisibilityAireacion.costo"></td>
                                        <td v-if="columnVisibilityAireacion.total"></td>
                                        <td v-if="columnVisibilityAireacion.descripcion"></td>
                                        <td v-if="columnVisibilityAireacion.detalles"></td>
                                        <td v-if="columnVisibilityAireacion.registrador"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <pagination
                                v-if="listadoPrelogsAireacion && listadoPrelogsAireacion.data && listadoPrelogsAireacion.total"
                                :align="'center'"
                                :data="listadoPrelogsAireacion"
                                :limit="2"
                                @pagination-change-page="listarAireacion"
                            >
                                <span slot="prev-nav">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span slot="next-nav">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </pagination>
                        </div>
                    </div>
                    
                    <div v-else-if="activeTab === 'parametros-agua'" class="card-body p-0">
                        <water-params-sync ref="waterParamsSync"></water-params-sync>
                    </div>
                </div>
            </div>
        </div>

        <edit-alimentacion-modal
            ref="editAlimentacionModal"
            :siembras="listadoSiembras"
            :alimentos="listadoAlimentos"
            :registered-users="listadoRegisteredUsers"
            @updated="onPrelogAlimentacionUpdated"
        />

        <edit-aireacion-modal
            ref="editAireacionModal"
            :siembras="listadoSiembras"
            :recursos="listadoRecursos"
            :registered-users="listadoRegisteredUsers"
            @updated="onPrelogAireacionUpdated"
        />

        <registrador-q-r-modal
            :visible="showQrModal"
            :link="registradorLink"
            @close="showQrModal = false"
        />

        <!-- MODAL REPARACIÓN AIREACIÓN -->
        <div v-if="repairModal.show" class="modal-repair-overlay" @click.self="repairModal.show = false">
            <div class="modal-repair-box">
                <div class="modal-repair-header">
                    <span><i class="fas fa-tools mr-2"></i> Reparación de Registros de Aireación</span>
                    <button class="modal-repair-close" @click="repairModal.show = false">&times;</button>
                </div>

                <div class="modal-repair-body">

                    <!-- FASE 0: inicio -->
                    <div v-if="repairModal.fase === 'inicio'" class="text-center py-4">
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <p class="mb-4">Este proceso busca registros de aireación sincronizados donde la <strong>duración real</strong> está en el campo Detalles pero <strong>cantidad_recurso = 0</strong>, y los corrige extrayendo los minutos de operación reales.</p>
                        <div class="alert alert-info text-left">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>¿Qué corrige?</strong><br>
                            • Extrae la duración real de "Duración: X min" del campo Detalles<br>
                            • Actualiza <code>cantidad_recurso</code> con esa duración<br>
                            • Limpia <code>conv_alimenticia</code> (no aplica a aireación)
                        </div>
                        <button class="btn btn-primary btn-lg" :disabled="repairModal.loading" @click="analizarRegistros">
                            <i class="fas fa-search mr-1"></i> Analizar registros
                        </button>
                    </div>

                    <!-- FASE 1: analizando -->
                    <div v-if="repairModal.fase === 'analizando'" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                        <p>Analizando registros...</p>
                    </div>

                    <!-- FASE 2: preview -->
                    <div v-if="repairModal.fase === 'preview'">
                        <div v-if="repairModal.registros.length === 0" class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-success font-weight-bold">No se encontraron registros que necesiten reparación.</p>
                            <button class="btn btn-secondary" @click="repairModal.show = false">Cerrar</button>
                        </div>
                        <div v-else>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Se encontraron <strong>{{ repairModal.registros.length }}</strong> registro(s) que necesitan reparación.
                            </div>
                            <div class="table-responsive" style="max-height:340px; overflow-y:auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-dark sticky-top">
                                        <tr>
                                            <th>#</th>
                                            <th>Siembra</th>
                                            <th>Fecha</th>
                                            <th>Recurso</th>
                                            <th class="text-center">Cant. actual</th>
                                            <th class="text-center text-success">Cant. nueva (min)</th>
                                            <th class="text-center text-success">Costo total nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(r, i) in repairModal.registros" :key="r.id">
                                            <td>{{ i + 1 }}</td>
                                            <td><small>{{ r.siembra }}</small></td>
                                            <td><small>{{ r.fecha_ra }}</small></td>
                                            <td><small>{{ r.recurso }}</small></td>
                                            <td class="text-center text-danger"><strong>{{ r.cantidad_recurso_actual }}</strong></td>
                                            <td class="text-center text-success"><strong>{{ r.cantidad_recurso_nueva }}</strong></td>
                                            <td class="text-center text-success"><small>$ {{ Number(r.costo_total_nuevo).toLocaleString('es-CO') }}</small></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button class="btn btn-secondary" @click="repairModal.fase = 'inicio'">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver
                                </button>
                                <button class="btn btn-danger" :disabled="repairModal.loading" @click="ejecutarReparacion">
                                    <i class="fas fa-wrench mr-1"></i> Reparar {{ repairModal.registros.length }} registro(s)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- FASE 3: reparando -->
                    <div v-if="repairModal.fase === 'reparando'" class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-warning mb-3"></i>
                        <p>Reparando registros...</p>
                    </div>

                    <!-- FASE 4: resultado -->
                    <div v-if="repairModal.fase === 'resultado'" class="text-center py-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success mb-3">{{ repairModal.resultado.message }}</h5>
                        <div class="table-responsive mb-3" style="max-height:300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-dark sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Recurso</th>
                                        <th class="text-center">Duración (min)</th>
                                        <th class="text-center">Costo total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(r, i) in repairModal.resultado.records" :key="r.id">
                                        <td>{{ i + 1 }}</td>
                                        <td><small>{{ r.recurso }}</small></td>
                                        <td class="text-center text-success"><strong>{{ r.cantidad_recurso }}</strong></td>
                                        <td class="text-center">$ {{ Number(r.costo_total).toLocaleString('es-CO') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-success" @click="repairModal.show = false; listarAireacion()">
                            <i class="fas fa-check mr-1"></i> Cerrar y actualizar lista
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
import EditAlimentacionModal from "./EditAlimentacionModal.vue";
import EditAireacionModal from "./EditAireacionModal.vue";
import RegistradorQRModal from "./RegistradorQRModal.vue";
import WaterParamsSync from "./WaterParamsSync.vue";
import downloadexcel from "vue-json-excel";

export default {
    components: {
        EditAlimentacionModal,
        EditAireacionModal,
        RegistradorQRModal,
        WaterParamsSync,
        downloadexcel
    },
    data() {
        return {
            // SPINNER
            isLoading: false,
            
            // Pestaña activa
            activeTab: 'alimentacion',

            // Contadores de registros por pestaña
            totalAlimentacion: 0,
            totalAireacion: 0,
            totalParametrosAgua: 0, 

            // DATOS COMPARTIDOS
            listadoSiembras: [],
            listadoAlimentos: [],
            listadoRecursos: [],
            listadoRegisteredUsers: [],

            // ============ ALIMENTACIÓN ============
            listadoPrelogsAlimentacion: {
                data: []
            },
            syncingAlimentacion: false,
            selectedPrelogsAlimentacion: [],
            showColumnPanelAlimentacion: false,
            columnVisibilityAlimentacion: {
                tipo_actividad: false,
                siembra: true,
                fecha: true,
                minutos_hombre: true,
                alimento: true,
                cant_manana: true,
                cant_tarde: true,
                total_dia: true,
                costo_kg: false,
                costo_total: false,
                conv_teorica: true,
                biomasa: false,
                detalles: true,
                registrador: true
            },
            filtersAlimentacion: {
                fecha_ra: "",
                id_siembra: "",
                id_alimento: "",
                registered_user_id: "",
                per_page: 20,
                kg_manana_op: "",
                kg_manana_val: null,
                kg_tarde_op: "",
                kg_tarde_val: null
            },
            json_fields_alimentacion: {
                "Tipo Actividad": "tipo_actividad",
                Siembra: "siembra",
                Fecha: "fecha",
                "Minutos hombre": "minutos_hombre",
                Alimento: "alimento",
                "Cantidad Mañana (kg)": "cant_manana",
                "Cantidad Tarde (kg)": "cant_tarde",
                "Total día (kg)": "total_dia",
                "Costo Kg": "costo_kg",
                "Costo total": "costo_total",
                "Conversión alimenticia teórica": "conv_teorica",
                "Incremento biomasa": "biomasa",
                Detalles: "detalles",
                Registrador: "registrador"
            },

            // ============ AIREACIÓN ============
            listadoPrelogsAireacion: {
                data: []
            },
            syncingAireacion: false,
            selectedPrelogsAireacion: [],
            showColumnPanelAireacion: false,
            columnVisibilityAireacion: {
                tipo_actividad: true,
                siembra: true,
                fecha: true,
                min_hombre: true,
                costo_min: false,
                recurso: true,
                cantidad: false,
                costo: false,
                total: false,
                descripcion: true,
                detalles: true,
                registrador: true
            },
            filtersAireacion: {
                fecha_ra: "",
                id_siembra: "",
                id_recurso: "",
                tipo_actividad: "",
                registered_user_id: "",
                per_page: 20,
                minutos_hombre_op: "",
                minutos_hombre_val: null
            },
            json_fields_aireacion: {
                "Tipo Actividad": "tipo_actividad",
                Siembra: "siembra",
                Fecha: "fecha",
                "Min Hombre": "min_hombre",
                "Costo Min": "costo_min",
                Recurso: "recurso",
                Cantidad: "cantidad",
                Costo: "costo",
                Total: "total",
                Descripción: "descripcion",
                Detalles: "detalles",
                Registrador: "registrador"
            },

            showQrModal: false,

            repairModal: {
                show:      false,
                fase:      'inicio',   // inicio | analizando | preview | reparando | resultado
                loading:   false,
                registros: [],
                resultado: { message: '', repaired: 0, records: [] },
            },
        };
    },
    computed: {
        // ============ ALIMENTACIÓN ============
        offsetIndexAlimentacion() {
            if (
                !this.listadoPrelogsAlimentacion ||
                !this.listadoPrelogsAlimentacion.current_page ||
                !this.listadoPrelogsAlimentacion.per_page
            ) {
                return 0;
            }
            return (
                (this.listadoPrelogsAlimentacion.current_page - 1) *
                this.listadoPrelogsAlimentacion.per_page
            );
        },
        
        displayedPrelogsAlimentacion() {
            const rows = (this.listadoPrelogsAlimentacion.data || []).slice();

            return rows.filter(item => {
                if (item.tipo === 'AIREACION') {
                    return false;
                }

                const manana =
                    item.cant_manana !== null && item.cant_manana !== undefined
                        ? parseFloat(item.cant_manana)
                        : null;
                const tarde =
                    item.cant_tarde !== null && item.cant_tarde !== undefined
                        ? parseFloat(item.cant_tarde)
                        : null;

                if (
                    !this.passesKgFilter(
                        manana,
                        this.filtersAlimentacion.kg_manana_op,
                        this.filtersAlimentacion.kg_manana_val
                    )
                ) {
                    return false;
                }
                if (
                    !this.passesKgFilter(
                        tarde,
                        this.filtersAlimentacion.kg_tarde_op,
                        this.filtersAlimentacion.kg_tarde_val
                    )
                ) {
                    return false;
                }
                return true;
            });
        },
        pageTotalsAlimentacion() {
            const sumField = field =>
                this.displayedPrelogsAlimentacion.reduce((acc, item) => {
                    const val =
                        item[field] !== null && item[field] !== undefined
                            ? parseFloat(item[field])
                            : 0;
                    return acc + (isNaN(val) ? 0 : val);
                }, 0);

            const totalDia = this.displayedPrelogsAlimentacion.reduce((acc, item) => {
                const manana = parseFloat(item.cant_manana) || 0;
                const tarde = parseFloat(item.cant_tarde) || 0;
                return acc + manana + tarde;
            }, 0);

            // NUEVO CÁLCULO DE PROMEDIO DE CONVERSIÓN TEÓRICA
            let sumaConvTeorica = 0;
            let countConvTeorica = 0;
            
            this.displayedPrelogsAlimentacion.forEach(item => {
                const conv = parseFloat(item.conv_alimenticia);
                if (!isNaN(conv) && conv > 0) {
                    sumaConvTeorica += conv;
                    countConvTeorica++;
                }
            });

            const promedioConvTeorica = countConvTeorica > 0 ? (sumaConvTeorica / countConvTeorica) : 0;

            return {
                kg_manana: sumField("cant_manana"),
                kg_tarde: sumField("cant_tarde"),
                minutos_hombre: sumField("minutos_hombre"),
                total_dia: totalDia,
                conv_teorica_promedio: promedioConvTeorica
            };
        },
        columnItemsAlimentacion() {
            return [
                { key: "tipo_actividad", label: "Tipo Actividad" },
                { key: "siembra", label: "Siembra" },
                { key: "fecha", label: "Fecha" },
                { key: "minutos_hombre", label: "Minutos hombre" },
                { key: "alimento", label: "Alimento" },
                { key: "cant_manana", label: "Cantidad Mañana" },
                { key: "cant_tarde", label: "Cantidad Tarde" },
                { key: "total_dia", label: "Total día" },
                { key: "costo_kg", label: "Costo Kg" },
                { key: "costo_total", label: "Costo total" },
                { key: "conv_teorica", label: "Conversión teórica" },
                { key: "biomasa", label: "Incremento biomasa" },
                { key: "detalles", label: "Detalles" },
                { key: "registrador", label: "Registrador" }
            ];
        },
        areAllCurrentPageSelectedAlimentacion() {
            if (!this.displayedPrelogsAlimentacion.length) {
                return false;
            }
            return this.displayedPrelogsAlimentacion.every(item =>
                this.selectedPrelogsAlimentacion.includes(item.id)
            );
        },
        totalColumnasAlimentacion() {
            let count = 3; // Sel., #, Acciones
            Object.values(this.columnVisibilityAlimentacion).forEach(visible => {
                if (visible) count++;
            });
            return count;
        },

        // ============ AIREACIÓN ============
        offsetIndexAireacion() {
            if (
                !this.listadoPrelogsAireacion ||
                !this.listadoPrelogsAireacion.current_page ||
                !this.listadoPrelogsAireacion.per_page
            ) {
                return 0;
            }
            return (
                (this.listadoPrelogsAireacion.current_page - 1) *
                this.listadoPrelogsAireacion.per_page
            );
        },
        displayedPrelogsAireacion() {
            const rows = (this.listadoPrelogsAireacion.data || []).slice();

            return rows.filter(item => {
                if (item.tipo !== 'AIREACION') {
                    return false;
                }

                const minutos = parseFloat(item.minutos_hombre) || null;

                if (
                    !this.passesFilter(
                        minutos,
                        this.filtersAireacion.minutos_hombre_op,
                        this.filtersAireacion.minutos_hombre_val
                    )
                ) {
                    return false;
                }
                return true;
            });
        },
        pageTotalsAireacion() {
            const sumField = field =>
                this.displayedPrelogsAireacion.reduce((acc, item) => {
                    const val =
                        item[field] !== null && item[field] !== undefined
                            ? parseFloat(item[field])
                            : 0;
                    return acc + (isNaN(val) ? 0 : val);
                }, 0);

            return {
                minutos_hombre: sumField("minutos_hombre")
            };
        },
        columnItemsAireacion() {
            return [
                { key: "tipo_actividad", label: "Tipo Actividad" },
                { key: "siembra", label: "Siembra" },
                { key: "fecha", label: "Fecha" },
                { key: "min_hombre", label: "Min Hombre" },
                { key: "costo_min", label: "Costo Min" },
                { key: "recurso", label: "Recurso" },
                { key: "cantidad", label: "Cantidad" },
                { key: "costo", label: "Costo" },
                { key: "total", label: "Total" },
                { key: "descripcion", label: "Descripción" },
                { key: "detalles", label: "Detalles" },
                { key: "registrador", label: "Registrador" }
            ];
        },
        areAllCurrentPageSelectedAireacion() {
            if (!this.displayedPrelogsAireacion.length) {
                return false;
            }
            return this.displayedPrelogsAireacion.every(item =>
                this.selectedPrelogsAireacion.includes(item.id)
            );
        },
        totalColumnasAireacion() {
            let count = 3; // Sel., #, Acciones
            Object.values(this.columnVisibilityAireacion).forEach(visible => {
                if (visible) count++;
            });
            return count;
        },

        registradorLink() {
            let base =
                window.APP_URL ||
                window.app_url ||
                `${window.location.origin}/aquamazonia/public/`;

            base = base.replace(/\/+$/, "");
            return `${base}/registrador#/`;
        }
    },
    methods: {
        // ============ MÉTODOS COMPARTIDOS ============
        switchTab(tabName) {
            this.activeTab = tabName;
            this.selectedPrelogsAlimentacion = [];
            this.selectedPrelogsAireacion = [];
            this.showColumnPanelAlimentacion = false;
            this.showColumnPanelAireacion = false;
            
            if (tabName === 'alimentacion') {
                this.listarAlimentacion();
            } else if (tabName === 'aireacion') {
                this.listarAireacion();
            } else if (tabName === 'parametros-agua') {
                this.obtenerTotalParametrosAgua();
            }
        },

        openQrModal() {
            this.showQrModal = true;
        },

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
        getAlimentoNombre(id) {
            const a = this.listadoAlimentos.find(x => x.id === id);
            return a ? a.alimento : `ID ${id}`;
        },
        getRecursoNombre(id) {
            if (!id) return "-";
            const r = this.listadoRecursos.find(x => x.id === id);
            return r ? (r.recurso || r.nombre || `Recurso #${id}`) : `Recurso #${id}`;
        },
        getRegisteredUserNombre(id) {
            const u = this.listadoRegisteredUsers.find(x => x.id === id);
            return u ? this.formatRegisteredUser(u) : id ? `ID ${id}` : "-";
        },
        getTipoActividad(id) {
            if (id === 12) return "Aireación Mecánica";
            return `Tipo ${id}`;
        },

        // ============ MÉTODOS ALIMENTACIÓN ============
        passesKgFilter(value, op, threshold) {
            if (!op || threshold === null || threshold === "" || isNaN(threshold)) {
                return true;
            }
            if (value === null || isNaN(value)) {
                return false;
            }
            const t = parseFloat(threshold);
            if (op === ">") return value > t;
            if (op === "<") return value < t;
            if (op === "=") return value === t;
            return true;
        },

        calcularTotalDia(item) {
            const manana = parseFloat(item.cant_manana) || 0;
            const tarde = parseFloat(item.cant_tarde) || 0;
            return (manana + tarde).toFixed(2) + ' kg';
        },

        calcularCostoKg(item) {
            const costo = item.alimento?.costo_kg;
            if (costo === null || costo === undefined) return '-';
            return '$ ' + parseFloat(costo).toLocaleString('es-CO');
        },

        calcularCostoTotal(item) {
            const costo = parseFloat(item.alimento?.costo_kg);
            if (!costo) return '-';
            const manana = parseFloat(item.cant_manana) || 0;
            const tarde  = parseFloat(item.cant_tarde)  || 0;
            const total  = manana + tarde;
            if (total === 0) return '-';
            return '$ ' + (costo * total).toLocaleString('es-CO');
        },

        calcularBiomasa(item) {
            const conv = parseFloat(item.conv_alimenticia);
            if (!conv || conv === 0) return '-';
            const manana = parseFloat(item.cant_manana) || 0;
            const tarde  = parseFloat(item.cant_tarde)  || 0;
            const total  = manana + tarde;
            if (total === 0) return '-';
            return (total / conv).toFixed(2) + ' kg';
        },

        toggleColumnPanelAlimentacion() {
            this.showColumnPanelAlimentacion = !this.showColumnPanelAlimentacion;
        },
        toggleColumnAlimentacion(key) {
            this.$set(this.columnVisibilityAlimentacion, key, !this.columnVisibilityAlimentacion[key]);
        },
        selectAllColumnsAlimentacion() {
            Object.keys(this.columnVisibilityAlimentacion).forEach(k => {
                this.columnVisibilityAlimentacion[k] = true;
            });
        },
        clearAllColumnsAlimentacion() {
            Object.keys(this.columnVisibilityAlimentacion).forEach(k => {
                this.columnVisibilityAlimentacion[k] = false;
            });
        },

        isSelectedAlimentacion(id) {
            return this.selectedPrelogsAlimentacion.includes(id);
        },
        toggleSeleccionAlimentacion(id) {
            const idx = this.selectedPrelogsAlimentacion.indexOf(id);
            if (idx === -1) {
                this.selectedPrelogsAlimentacion.push(id);
            } else {
                this.selectedPrelogsAlimentacion.splice(idx, 1);
            }
        },
        toggleSelectAllCurrentAlimentacion() {
            if (!this.displayedPrelogsAlimentacion.length) return;

            if (this.areAllCurrentPageSelectedAlimentacion) {
                const idsPagina = this.displayedPrelogsAlimentacion.map(i => i.id);
                this.selectedPrelogsAlimentacion = this.selectedPrelogsAlimentacion.filter(
                    id => !idsPagina.includes(id)
                );
            } else {
                this.displayedPrelogsAlimentacion.forEach(i => {
                    if (!this.selectedPrelogsAlimentacion.includes(i.id)) {
                        this.selectedPrelogsAlimentacion.push(i.id);
                    }
                });
            }
        },

        listarAlimentacion(page = 1) {
            this.isLoading = true;

            const params = {
                page,
                per_page: this.filtersAlimentacion.per_page || 20,
                tipo: 'alimentacion'
            };

            if (this.filtersAlimentacion.fecha_ra) {
                params.fecha_ra = this.filtersAlimentacion.fecha_ra;
            }
            if (this.filtersAlimentacion.id_siembra) {
                params.id_siembra = this.filtersAlimentacion.id_siembra;
            }
            if (this.filtersAlimentacion.id_alimento) {
                params.id_alimento = this.filtersAlimentacion.id_alimento;
            }
            if (this.filtersAlimentacion.registered_user_id) {
                params.registered_user_id = this.filtersAlimentacion.registered_user_id;
            }

            axios
                .get("api/feeding-prelogs", { params })
                .then(response => {
                    this.listadoPrelogsAlimentacion = response.data || { data: [] };
                    this.totalAlimentacion = this.listadoPrelogsAlimentacion.total || 0;
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "Ocurrió un problema cargando los preregistros.",
                        "error"
                    );
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        openEditAlimentacion(item) {
            if (this.$refs.editAlimentacionModal) {
                this.$refs.editAlimentacionModal.open(item);
            }
        },

        onPrelogAlimentacionUpdated() {
            const page = this.listadoPrelogsAlimentacion.current_page || 1;
            this.listarAlimentacion(page);
            if (this.$root) {
                this.$root.$emit("feeding-prelogs-updated");
            }
        },

        eliminarPrelogAlimentacion(item) {
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
                        .delete(`api/feeding-prelogs/${item.id}`)
                        .then(() => {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "El preregistro se eliminó correctamente.",
                                timer: 1200,
                                showConfirmButton: false
                            });
                            const page = this.listadoPrelogsAlimentacion.current_page || 1;
                            this.listarAlimentacion(page);
                            this.selectedPrelogsAlimentacion = this.selectedPrelogsAlimentacion.filter(
                                id => id !== item.id
                            );
                            if (this.$root) {
                                this.$root.$emit("feeding-prelogs-updated");
                            }
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

        syncSelectedAlimentacion() {
            if (!this.selectedPrelogsAlimentacion.length) return;

            this.syncingAlimentacion = true;
            this.isLoading = true;

            axios
                .post("api/feeding-prelogs/sync", {
                    ids: this.selectedPrelogsAlimentacion
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

                    this.selectedPrelogsAlimentacion = [];
                    this.listarAlimentacion(this.listadoPrelogsAlimentacion.current_page || 1);

                    if (this.$root) {
                        this.$root.$emit("feeding-prelogs-updated");
                    }
                })
                .catch(error => {
                    console.error(error);
                    let msg =
                        "Ocurrió un problema al sincronizar los preregistros.";
                    if (error.response && error.response.status === 404) {
                        msg =
                            "El endpoint de sincronización aún no está implementado en el backend (api/feeding-prelogs/sync).";
                    }
                    Swal.fire("Error", msg, "error");
                })
                .finally(() => {
                    this.syncingAlimentacion = false;
                    this.isLoading = false;
                });
        },

        async fetchDataAlimentacion() {
            const params = {
                no_paginate: 1,
                tipo: 'alimentacion'
            };

            if (this.filtersAlimentacion.fecha_ra) {
                params.fecha_ra = this.filtersAlimentacion.fecha_ra;
            }
            if (this.filtersAlimentacion.id_siembra) {
                params.id_siembra = this.filtersAlimentacion.id_siembra;
            }
            if (this.filtersAlimentacion.id_alimento) {
                params.id_alimento = this.filtersAlimentacion.id_alimento;
            }
            if (this.filtersAlimentacion.registered_user_id) {
                params.registered_user_id = this.filtersAlimentacion.registered_user_id;
            }

            const response = await axios.get("api/feeding-prelogs", { params });
            const rows = Array.isArray(response.data)
                ? response.data
                : response.data.data || [];

            const filtrados = rows.filter(item => {
                if (item.tipo === 'AIREACION') {
                    return false;
                }

                const manana =
                    item.cant_manana !== null && item.cant_manana !== undefined
                        ? parseFloat(item.cant_manana)
                        : null;
                const tarde =
                    item.cant_tarde !== null && item.cant_tarde !== undefined
                        ? parseFloat(item.cant_tarde)
                        : null;

                if (
                    !this.passesKgFilter(
                        manana,
                        this.filtersAlimentacion.kg_manana_op,
                        this.filtersAlimentacion.kg_manana_val
                    )
                ) {
                    return false;
                }
                if (
                    !this.passesKgFilter(
                        tarde,
                        this.filtersAlimentacion.kg_tarde_op,
                        this.filtersAlimentacion.kg_tarde_val
                    )
                ) {
                    return false;
                }
                return true;
            });

            return filtrados.map(item => ({
                tipo_actividad: item.tipo_actividad ? `Tipo ${item.tipo_actividad}` : '-',
                siembra: this.getSiembraNombre(item.id_siembra),
                fecha: this.formatFecha(item.fecha_ra),
                minutos_hombre: item.minutos_hombre,
                alimento: this.getAlimentoNombre(item.id_alimento),
                cant_manana: item.cant_manana,
                cant_tarde: item.cant_tarde,
                total_dia: (parseFloat(item.cant_manana) || 0) + (parseFloat(item.cant_tarde) || 0),
                costo_kg: this.calcularCostoKg(item),
                costo_total: this.calcularCostoTotal(item),
                conv_teorica: item.conv_alimenticia,
                biomasa: this.calcularBiomasa(item),
                detalles: item.detalles,
                registrador: this.getRegisteredUserNombre(item.registered_user_id)
            }));
        },

        // ============ MÉTODOS AIREACIÓN ============
        passesFilter(value, op, threshold) {
            if (!op || threshold === null || threshold === "" || isNaN(threshold)) {
                return true;
            }
            if (value === null || isNaN(value)) {
                return false;
            }
            const t = parseFloat(threshold);
            if (op === ">") return value > t;
            if (op === "<") return value < t;
            if (op === "=") return value === t;
            return true;
        },

        calcularCostoMinuto(item) {
            const costo = parseFloat(item.recurso?.costo);
            if (!costo) return '-';
            return '$ ' + costo.toLocaleString('es-CO') + '/min';
        },

        calcularCantidad(item) {
            const min = parseFloat(item.cant_manana) || 0;
            if (min <= 0) return '-';
            return min.toLocaleString('es-CO') + ' min';
        },

        calcularCosto(item) {
            const costo = parseFloat(item.recurso?.costo);
            if (!costo) return '-';
            return '$ ' + costo.toLocaleString('es-CO') + '/min';
        },

        calcularTotal(item) {
            const min   = parseFloat(item.cant_manana) || 0;
            const costo = parseFloat(item.recurso?.costo) || 0;
            if (!min || !costo) return '-';
            return '$ ' + (min * costo).toLocaleString('es-CO');
        },

        toggleColumnPanelAireacion() {
            this.showColumnPanelAireacion = !this.showColumnPanelAireacion;
        },
        toggleColumnAireacion(key) {
            this.$set(this.columnVisibilityAireacion, key, !this.columnVisibilityAireacion[key]);
        },
        selectAllColumnsAireacion() {
            Object.keys(this.columnVisibilityAireacion).forEach(k => {
                this.columnVisibilityAireacion[k] = true;
            });
        },
        clearAllColumnsAireacion() {
            Object.keys(this.columnVisibilityAireacion).forEach(k => {
                this.columnVisibilityAireacion[k] = false;
            });
        },

        isSelectedAireacion(id) {
            return this.selectedPrelogsAireacion.includes(id);
        },
        toggleSeleccionAireacion(id) {
            const idx = this.selectedPrelogsAireacion.indexOf(id);
            if (idx === -1) {
                this.selectedPrelogsAireacion.push(id);
            } else {
                this.selectedPrelogsAireacion.splice(idx, 1);
            }
        },
        toggleSelectAllCurrentAireacion() {
            if (!this.displayedPrelogsAireacion.length) return;

            if (this.areAllCurrentPageSelectedAireacion) {
                const idsPagina = this.displayedPrelogsAireacion.map(i => i.id);
                this.selectedPrelogsAireacion = this.selectedPrelogsAireacion.filter(
                    id => !idsPagina.includes(id)
                );
            } else {
                this.displayedPrelogsAireacion.forEach(i => {
                    if (!this.selectedPrelogsAireacion.includes(i.id)) {
                        this.selectedPrelogsAireacion.push(i.id);
                    }
                });
            }
        },

        listarAireacion(page = 1) {
            this.isLoading = true;

            const params = {
                page,
                per_page: this.filtersAireacion.per_page || 20,
                tipo: 'recursos'
            };

            if (this.filtersAireacion.fecha_ra) {
                params.fecha_ra = this.filtersAireacion.fecha_ra;
            }
            if (this.filtersAireacion.id_siembra) {
                params.id_siembra = this.filtersAireacion.id_siembra;
            }
            if (this.filtersAireacion.id_recurso) {
                params.id_recurso = this.filtersAireacion.id_recurso;
            }
            if (this.filtersAireacion.tipo_actividad) {
                params.tipo_actividad = this.filtersAireacion.tipo_actividad;
            }
            if (this.filtersAireacion.registered_user_id) {
                params.registered_user_id = this.filtersAireacion.registered_user_id;
            }

            axios
                .get("api/feeding-prelogs", { params })
                .then(response => {
                    this.listadoPrelogsAireacion = response.data || { data: [] };
                    this.totalAireacion = this.listadoPrelogsAireacion.total || 0;
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "Ocurrió un problema cargando los preregistros.",
                        "error"
                    );
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        openEditAireacion(item) {
            if (this.$refs.editAireacionModal) {
                this.$refs.editAireacionModal.open(item);
            }
        },

        onPrelogAireacionUpdated() {
            const page = this.listadoPrelogsAireacion.current_page || 1;
            this.listarAireacion(page);
            if (this.$root) {
                this.$root.$emit("feeding-prelogs-updated");
            }
        },

        eliminarPrelogAireacion(item) {
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
                        .delete(`api/feeding-prelogs/${item.id}`)
                        .then(() => {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: "El preregistro se eliminó correctamente.",
                                timer: 1200,
                                showConfirmButton: false
                            });
                            const page = this.listadoPrelogsAireacion.current_page || 1;
                            this.listarAireacion(page);
                            this.selectedPrelogsAireacion = this.selectedPrelogsAireacion.filter(
                                id => id !== item.id
                            );
                            if (this.$root) {
                                this.$root.$emit("feeding-prelogs-updated");
                            }
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

        syncSelectedAireacion() {
            if (!this.selectedPrelogsAireacion.length) return;

            this.syncingAireacion = true;
            this.isLoading = true;

            axios
                .post("api/feeding-prelogs/sync", {
                    ids: this.selectedPrelogsAireacion
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

                    this.selectedPrelogsAireacion = [];
                    this.listarAireacion(this.listadoPrelogsAireacion.current_page || 1);

                    if (this.$root) {
                        this.$root.$emit("feeding-prelogs-updated");
                    }
                })
                .catch(error => {
                    console.error(error);
                    let msg =
                        "Ocurrió un problema al sincronizar los preregistros.";
                    if (error.response && error.response.status === 404) {
                        msg =
                            "El endpoint de sincronización aún no está implementado en el backend (api/feeding-prelogs/sync).";
                    }
                    Swal.fire("Error", msg, "error");
                })
                .finally(() => {
                    this.syncingAireacion = false;
                    this.isLoading = false;
                });
        },

        async fetchDataAireacion() {
            const params = {
                no_paginate: 1,
                tipo: 'recursos'
            };

            if (this.filtersAireacion.fecha_ra) {
                params.fecha_ra = this.filtersAireacion.fecha_ra;
            }
            if (this.filtersAireacion.id_siembra) {
                params.id_siembra = this.filtersAireacion.id_siembra;
            }
            if (this.filtersAireacion.id_recurso) {
                params.id_recurso = this.filtersAireacion.id_recurso;
            }
            if (this.filtersAireacion.tipo_actividad) {
                params.tipo_actividad = this.filtersAireacion.tipo_actividad;
            }
            if (this.filtersAireacion.registered_user_id) {
                params.registered_user_id = this.filtersAireacion.registered_user_id;
            }

            const response = await axios.get("api/feeding-prelogs", { params });
            const rows = Array.isArray(response.data)
                ? response.data
                : response.data.data || [];

            const filtrados = rows.filter(item => {
                if (item.tipo !== 'AIREACION') {
                    return false;
                }

                const minutos = parseFloat(item.minutos_hombre) || null;

                if (
                    !this.passesFilter(
                        minutos,
                        this.filtersAireacion.minutos_hombre_op,
                        this.filtersAireacion.minutos_hombre_val
                    )
                ) {
                    return false;
                }
                return true;
            });

            return filtrados.map(item => ({
                tipo_actividad: this.getTipoActividad(item.tipo_actividad),
                siembra: this.getSiembraNombre(item.id_siembra),
                fecha: this.formatFecha(item.fecha_ra),
                min_hombre: item.minutos_hombre,
                costo_min: this.calcularCostoMinuto(item),
                recurso: this.getRecursoNombre(item.id_recurso),
                cantidad: this.calcularCantidad(item),
                costo: this.calcularCosto(item),
                total: this.calcularTotal(item),
                descripcion: item.descripcion,
                detalles: item.detalles,
                registrador: this.getRegisteredUserNombre(item.registered_user_id)
            }));
        },

        // ============ REPARACIÓN AIREACIÓN ============
        abrirRepairModal() {
            this.repairModal.show     = false;
            this.repairModal.fase     = 'inicio';
            this.repairModal.loading  = false;
            this.repairModal.registros = [];
            this.repairModal.resultado = { message: '', repaired: 0, records: [] };
            this.$nextTick(() => { this.repairModal.show = true; });
        },

        async analizarRegistros() {
            this.repairModal.fase    = 'analizando';
            this.repairModal.loading = true;
            try {
                const res = await axios.get('api/feeding-prelogs/repair-aireacion');
                this.repairModal.registros = res.data.records || [];
                this.repairModal.fase      = 'preview';
            } catch (e) {
                Swal.fire('Error', 'No se pudo analizar los registros.', 'error');
                this.repairModal.fase = 'inicio';
            } finally {
                this.repairModal.loading = false;
            }
        },

        async ejecutarReparacion() {
            const result = await Swal.fire({
                title: '¿Reparar ' + this.repairModal.registros.length + ' registro(s)?',
                text: 'Se actualizarán las cantidades con la duración real de cada aireador.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e67e22',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, reparar',
                cancelButtonText: 'Cancelar',
            });
            if (!result.isConfirmed) return;

            this.repairModal.fase    = 'reparando';
            this.repairModal.loading = true;
            try {
                const res = await axios.post('api/feeding-prelogs/repair-aireacion');
                this.repairModal.resultado = res.data;
                this.repairModal.fase      = 'resultado';
            } catch (e) {
                const msg = (e.response && e.response.data && e.response.data.error)
                    ? e.response.data.error : 'Error al reparar los registros.';
                Swal.fire('Error', msg, 'error');
                this.repairModal.fase = 'preview';
            } finally {
                this.repairModal.loading = false;
            }
        },

        // ============ MÉTODOS GENERALES ============
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

        listarAlimentos() {
            axios
                .get("api/alimentos")
                .then(response => {
                    this.listadoAlimentos = response.data || [];
                })
                .catch(() => {
                    this.listadoAlimentos = [];
                });
        },

        listarRecursos() {
            axios
                .get("api/recursos")
                .then(response => {
                    this.listadoRecursos = response.data || [];
                })
                .catch(() => {
                    this.listadoRecursos = [];
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

        async obtenerTotalParametrosAgua() {
            try {
                const response = await axios.get("api/parametros-agua-avanzados-prelogs/total-pendientes");
                this.totalParametrosAgua = response.data.total || 0;
            } catch (error) {
                console.error("Error obteniendo total de parámetros de agua:", error);
                this.totalParametrosAgua = 0;
            }
        }
    },
    mounted() {
        this.listarSiembras();
        this.listarAlimentos();
        this.listarRecursos();
        this.listarRegisteredUsers();
        this.listarAlimentacion();
        
        this.obtenerTotalParametrosAgua();
    }
};
</script>

<style scoped>
/* Estilos para las pestañas */
.nav-tabs .nav-link {
    color: #4b5563;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    color: #1d4ed8;
    border: none;
}

.nav-tabs .nav-link.active {
    color: #1d4ed8;
    background-color: #ffffff;
    border-bottom: 3px solid #1d4ed8;
    font-weight: 600;
}

.card-header-tabs {
    margin-bottom: -1px;
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

/* Spinner global */
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

/* Modal de reparación */
.modal-repair-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-repair-box {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 820px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
}
.modal-repair-header {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    padding: 14px 20px;
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-repair-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
}
.modal-repair-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}
.modal-repair-body .sticky-top { position: sticky; top: 0; z-index: 1; }
</style>