<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Kardex de recursos</div>

                    <div class="card-body">

                        <div class="row mt-2">
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.product">Recurso</label>
                                <input type="text" v-model="filter.product" class="form-control">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="from_date">Desde</label>
                                <input type="datetime-local" class="form-control" id="from_date"
                                    v-model="filter.from" />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="to_date">Hasta</label>
                                <input type="datetime-local" class="form-control" id="to_date" v-model="filter.to" />
                            </div>
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.nro_results">Mostrar {{ filter.nro_results }} resultados por
                                    página</label>
                                <input type="number" v-model="filter.nro_results" class="form-control">
                            </div>
                            <div class="form-group col-md-3 col-12">
                                <button class="btn btn-primary" @click="listResources()">
                                    Filtrar resultados
                                </button>
                            </div>
                            <div class="form-group col-md-3 col-12">
                                <downloadexcel class="btn btn-success form-control w-100" :data="listado.data"
                                    :fields="json_fields" name="informe-kardex-recursos.xls" type="xls">
                                    <i class="fa fa-fw fa-download"></i> Generar Excel
                                </downloadexcel>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-sm table-bordered table-hover" v-if="productData">
                                <thead class=" thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Recurso</th>
                                        <th>Cantidad actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td> {{ productData.id }} </td>
                                        <td> {{ productData.recurso }}</td>
                                        <td> {{ productData.cantidad }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-hover table-sm table-bordered">
                                <thead class="thead-dark text-white">
                                    <tr>
                                        <th scope="col"># ID recurso</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Afectación</th>
                                        <th>Fecha</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(recurso, index) in listado.data" :key="index">
                                        <th scope="row">{{ recurso.recurso_id }}</th>
                                        <th scope="row">{{ recurso.cantidad }}</th>
                                        <td>{{ recurso.tipo }}: {{ recurso.descripcion }}</td>
                                        <td>{{ recurso.fecha }}</td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <pagination :align="'center'" :data="listado" :limit="8"
                            @pagination-change-page="listResources">
                            <span slot="prev-nav"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                            <span slot="next-nav"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                        </pagination>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>

import downloadexcel from "vue-json-excel";

export default {
    data() {
        return {
            listado: {},
            productData: {},
            listWarehouses: [],
            listBrands: [],
            listResourceCategories: [],
            filter: {
                nro_results: 15,
                type: "recurso"
            },
            json_fields: {

                "ID recurso": {
                    field: "recurso_id",
                    callback: (value) => {
                        return value;
                    }
                },

                "Cantidad ": {
                    field: "cantidad",
                    callback: (value) => {
                        return numeral(value).format('0.00');
                    }
                },

                "Tipo Afectacion ": {
                    field: "tipo",
                    callback: (value) => {
                        return value;
                    }
                },
                "Descripcion Afectacion": {
                    field: "descripcion",
                    callback: (value) => {
                        return value;
                    }
                },
                "Fecha": {
                    field: "fecha",
                    callback: (value) => {
                        return value;
                    }
                },
            }
        };
    },
    components: {
        downloadexcel,
    },
    methods: {
        listResources(page = 1) {
            let me = this;
            let data = {
                page,
                ...this.filter
            }
            axios.get("api/kardex", { params: data }).then(function (response) {
                me.listado = response.data.kardexes;
                me.productData = response.data.product_data
            });
        },
        onSearchWarehouse(search, loading) {
            if (search.length) {
                loading(true);
                let data = {
                    warehouse: search
                };

                axios.get(`api/warehouses`, {
                    params: data
                })
                    .then((response) => {
                        this.listWarehouses = (response.data.warehouses.data);
                        loading(false)
                    })
                    .catch(e => console.log(e))
            }
        },
        onSearchBrand(search, loading) {
            if (search.length) {
                loading(true);
                let data = {
                    brand: search
                };

                axios.get(`api/brands`, {
                    params: data
                })
                    .then((response) => {
                        this.listBrands = (response.data.brands.data);
                        loading(false)
                    })
                    .catch(e => console.log(e))
            }
        },
        onSearchResourceCategory(search, loading) {
            if (search.length) {
                loading(true);
                let data = {
                    category: search
                };

                axios.get(`api/resource-categories`, {
                    params: data
                })
                    .then((response) => {
                        this.listResourceCategories = (response.data.resourceCategories.data);
                        loading(false)
                    })
                    .catch(e => console.log(e))
            }
        },
    },
    mounted() {
        this.listResources();
    },
};
</script>