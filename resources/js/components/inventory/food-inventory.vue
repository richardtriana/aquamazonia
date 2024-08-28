<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Gestión de alimentos</div>

                    <div class="card-body">
                        <div class="row">
                            
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="warehouse" class="col-form-label">Bodega
                                </label>
                                <v-select label="warehouse" class="w-100" v-model="filter.warehouse" :reduce="(option) => option.id"
                                    :filterable="false" :options="listWarehouses" @search="onSearchWarehouse">
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
                                <v-select label="brand" class="w-100" v-model="filter.brand" :reduce="(option) => option.id"
                                    :filterable="false" :options="listBrands" @search="onSearchBrand">
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
                                <v-select label="food_category" class="w-100" v-model="filter.food_category" :reduce="(option) => option.id"
                                    :filterable="false" :options="listFoodCategories" @search="onSearchFoodCategory">
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
                           
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.nro_results">Mostrar {{ filter.nro_results }} resultados por página</label>
                                <input type="number" v-model="filter.nro_results" class="form-control">
                            </div>
                            <div class="form-group col-md-2">
                                <button class="btn btn-primary" @click="listFoods()">
                                    Filtrar resultados
                                </button>
                            </div>
                            
                        </div>
                        <!-- <div class="row mb-1">
                            <div class="col-12 text-right">
                                <button class="btn btn-success" @click="$refs.FormFood.ResetData()" data-toggle="modal"
                                    data-target="#modalFoods">
                                    Añadir Alimento
                                </button>
                            </div>
                        </div> -->
                        <div class="row">
                            <table class="table table-hover table-sm table-bordered">
                                <thead class="thead-primary">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Alimento</th>
                                        <th scope="col">Costo Kl</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Cantidad minima</th>
                                        <th>Cantidad maxima</th>
                                        <th scope="col">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(alimento, index) in listado" :key="index">
                                        <th scope="row" v-text="index + 1"></th>
                                        <td v-text="alimento.alimento"></td>
                                        <td v-text="alimento.costo_kg"></td>
                                       
                                        <td>{{ alimento.category ? alimento.category.category : "" }}</td>
                                        <td>{{ alimento.cantidad  }}</td>
                                        <td>{{ alimento.cantidad_minima  }}</td>
                                        <td>{{ alimento.cantidad_maxima }}</td>
                                        <td>
                                         opciojnes
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
            listado: [],
            listWarehouses:[],
            listBrands:[],
            listFoodCategories:[],
            filter: {
                nro_results:15
            }
        };
    },

    methods: {
        listFoods() {
            let me = this;


            let data = {
                page:1,
                ...this.filter

            }
            axios.get("api/alimentos",{ params: data }).then(function (response) {
                me.listado = response.data;
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