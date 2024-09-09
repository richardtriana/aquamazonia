<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Kardex de alimentos</div>

                    <div class="card-body">

                        <div class="row mt-2">
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.product">Alimento</label>
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
                            <div class="form-group col-md-2">
                                <button class="btn btn-primary" @click="listFoods()">
                                    Filtrar resultados
                                </button>
                            </div>

                        </div>
                        <div class="row">
                            <table class="table table-sm table-bordered table-hover" v-if="productData">
                                <thead class=" thead-dark">
                                  <tr>
                                    <th>ID</th>
                                    <th>Alimento</th>
                                    <th>Cantidad actual</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td> {{ productData.id }} </td>
                                    <td> {{ productData.alimento }}</td>
                                    <td> {{ productData.cantidad }}</td>
                                  </tr>
                                </tbody>
                              </table>
                            <table class="table table-hover table-sm table-bordered">
                                <thead class="thead-dark text-white">
                                    <tr>
                                        <th scope="col"># ID alimento</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Afectación</th>
                                        <th>Fecha</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(alimento, index) in listado.data" :key="index">
                                        <th scope="row">{{ alimento.alimento_id }}</th>
                                        <th scope="row">{{ alimento.cantidad }}</th>
                                        <td>{{ alimento.tipo }}: {{ alimento.descripcion }}</td>
                                        <td>{{ alimento.fecha }}</td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <pagination :align="'center'" :data="listado" :limit="8" @pagination-change-page="listFoods">
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


export default {
    data() {
        return {
            listado: {},
            productData: {},
            listWarehouses: [],
            listBrands: [],
            listFoodCategories: [],
            filter: {
                nro_results: 15,
                type: "alimento"
            }
        };
    },

    methods: {
        listFoods(page = 1) {
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
        onSearchFoodCategory(search, loading) {
            if (search.length) {
                loading(true);
                let data = {
                    category: search
                };

                axios.get(`api/food-categories`, {
                    params: data
                })
                    .then((response) => {
                        this.listFoodCategories = (response.data.foodCategories.data);
                        loading(false)
                    })
                    .catch(e => console.log(e))
            }
        },
    },
    mounted() {
        this.listFoods();
    },
};
</script>