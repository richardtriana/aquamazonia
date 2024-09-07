<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Stock de alimentos</div>

                    <div class="card-body">

                        <div class="row mt-2">
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.food">Alimento</label>
                                <input type="text" v-model="filter.food" class="form-control">
                            </div>
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="warehouse" class="col-form-label">Bodega
                                </label>
                                <v-select label="warehouse" class="w-100" v-model="filter.warehouse_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listWarehouses"
                                    @search="onSearchWarehouse">
                                    <template slot="no-options">
                                        Escribe para iniciar la búsqueda
                                    </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center">
                                            {{ option.warehouse }}
                                        </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center">
                                            {{ option.warehouse }}
                                        </div>
                                    </template>
                                </v-select>
                            </div>

                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="brand" class="col-form-label">Marca
                                </label>
                                <v-select label="brand" class="w-100" v-model="filter.brand_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listBrands"
                                    @search="onSearchBrand">
                                    <template slot="no-options">
                                        Escribe para iniciar la búsqueda
                                    </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center">
                                            {{ option.brand }}
                                        </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center">
                                            {{ option.brand }}
                                        </div>
                                    </template>
                                </v-select>
                            </div>

                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="food_category" class="col-form-label">Categoría
                                </label>
                                <v-select label="food_category" class="w-100" v-model="filter.food_category_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listFoodCategories"
                                    @search="onSearchFoodCategory">
                                    <template slot="no-options">
                                        Escribe para iniciar la búsqueda
                                    </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center">
                                            {{ option.category }}
                                        </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center">
                                            {{ option.category }}
                                        </div>
                                    </template>
                                </v-select>
                            </div>
                            <div class="form-group col-3">
                                <label for="filter.quantity_sign">Cantidad:</label>
                                <select name="filter.quantity_sign" class="custom-select" v-model="filter.quantity_sign"
                                    id="filter.quantity_sign">
                                    <option value="" selected>--Seleccionar--</option>
                                    <option value=">">Mayor que</option>
                                    <option value="<">Menor que</option>
                                    <option value="=">Igual a</option>
                                </select>
                            </div>
                            <div class="form-group col-3">
                                <label for="quantity">Cantidad:</label>
                                <input type="number" step="any" class="form-control" id="filter.quantity"
                                    placeholder="Cantidad" v-model="filter.quantity" />
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
                            <table class="table table-hover table-sm table-bordered">
                                <thead class="thead-primary">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Alimento</th>
                                        <th scope="col">Costo Kl</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Editar</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(alimento, index) in listado.data" :key="index">
                                        <th scope="row" v-text="index + 1"></th>
                                        <td v-text="alimento.alimento"></td>
                                        <td>{{ alimento.costo_kg | numeral('$0,0.00') }}</td>
                                        <td>{{ alimento.category ? alimento.category.category : "" }}</td>
                                        <td>{{ alimento.cantidad }}</td>
                                        <td>
                                            <div class="input-group mb-3">
                                                <input type="number" class="form-control"
                                                    placeholder="Cantidad de productos" aria-label="Product quantity"
                                                    aria-describedby="button-qty" value="0" v-model="alimento.qty" />
                                                <input type="text" class="form-control"
                                                    placeholder="Descripción de movimiento"
                                                    aria-label="Descripción de movimiento" aria-describedby="button-qty"
                                                    v-model="alimento.subject" />
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-success" type="button"
                                                        id="button-qty" v-if="alimento.qty && alimento.qty != 0"
                                                        @click="updateStock(alimento.id, alimento.qty, alimento.subject)">
                                                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                                                    </button>
                                                    <button v-else class="btn btn-outline-secondary" type="button"
                                                        disabled>
                                                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

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
            totals: {},
            listWarehouses: [],
            listBrands: [],
            listFoodCategories: [],
            filter: {
                nro_results: 15
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
            axios.get("api/food/list", { params: data }).then(function (response) {
                me.listado = response.data.foods;
                me.totals = response.data.totals;
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
        updateStock(id, cantidad, descripcion) {
            let me = this;
            axios
                .post(
                    `api/food/stock-update/${id}`,
                    {
                        cantidad: cantidad,
                        descripcion: descripcion
                    }
                )
                .then(function (response) {
                    me.listFoods(1);
                });
        },
    },
    mounted() {
        this.listFoods();
    },
};
</script>