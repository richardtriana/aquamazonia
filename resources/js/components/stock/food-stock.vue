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
                                <label for="warehouse" class="col-form-label">Bodega</label>
                                <v-select label="warehouse" class="w-100" v-model="filter.warehouse_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listWarehouses"
                                    @search="onSearchWarehouse">
                                    <template slot="no-options"> Escribe para iniciar la búsqueda </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center"> {{ option.warehouse }} </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center"> {{ option.warehouse }} </div>
                                    </template>
                                </v-select>
                            </div>

                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="brand" class="col-form-label">Marca</label>
                                <v-select label="brand" class="w-100" v-model="filter.brand_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listBrands"
                                    @search="onSearchBrand">
                                    <template slot="no-options"> Escribe para iniciar la búsqueda </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center"> {{ option.brand }} </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center"> {{ option.brand }} </div>
                                    </template>
                                </v-select>
                            </div>

                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="food_category" class="col-form-label">Categoría</label>
                                <v-select label="category" class="w-100" v-model="filter.food_category_id"
                                    :reduce="(option) => option.id" :filterable="false" :options="listFoodCategories"
                                    @search="onSearchFoodCategory">
                                    <template slot="no-options"> Escribe para iniciar la búsqueda </template>
                                    <template slot="option" slot-scope="option">
                                        <div class="d-center"> {{ option.category }} </div>
                                    </template>
                                    <template slot="selected-option" slot-scope="option">
                                        <div class="selected d-center"> {{ option.category }} </div>
                                    </template>
                                </v-select>
                            </div>

                            <div class="form-group col-3">
                                <label for="filter.quantity_sign">Cantidad:</label>
                                <select name="filter.quantity_sign" class="custom-select" v-model="filter.quantity_sign" id="filter.quantity_sign">
                                    <option value="" selected>--Seleccionar--</option>
                                    <option value=">">Mayor que</option>
                                    <option value="<">Menor que</option>
                                    <option value="=">Igual a</option>
                                </select>
                            </div>
                            <div class="form-group col-3">
                                <label for="quantity">Valor:</label>
                                <input type="number" step="any" class="form-control" placeholder="Cantidad" v-model="filter.quantity" />
                            </div>
                            <div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-3">
                                <label for="filter.nro_results">Mostrar {{ filter.nro_results }} por página</label>
                                <input type="number" v-model="filter.nro_results" class="form-control">
                            </div>
                            <div class="form-group col-md-3 col-12">
                                <button class="btn btn-primary w-100" @click="listFoods()"> Filtrar </button>
                            </div>
                            <div class="form-group offset-md-9 col-md-3 col-12">
                                <downloadexcel class="btn btn-success form-control w-100" :data="listado.data"
                                    :fields="json_fields" name="informe-stock-alimentos.xls" type="xls">
                                    <i class="fa fa-fw fa-download"></i> Generar Excel
                                </downloadexcel>
                            </div>
                        </div>

                        <div class="row">
                            <table class="table table-hover table-sm table-bordered text-center">
                                <thead class="thead-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Alimento</th>
                                        <th>Costo Kg</th>
                                        <th>Categoría</th>
                                        <th>Kg/Bulto</th>
                                        <th>Cant en (Kg)</th>
                                        <th >Cant en (Bultos)</th>
                                        <th>Editar Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(alimento, index) in listado.data" :key="index">
                                        <th scope="row" v-text="index + 1"></th>
                                        <td class="text-left" v-text="alimento.alimento"></td>
                                        <td>{{ alimento.costo_kg | numeral('$0,0.00') }}</td>
                                        <td>{{ alimento.category ? alimento.category.category : "" }}</td>
                                        <td>{{ alimento.cantidad_bulto }} kg</td>
                                        <td class="font-weight-bold">{{ alimento.cantidad | numeral('0,0.00') }}</td>
                                        <td class="text-primary font-weight-bold">
                                            {{ alimento.cantidad_bulto > 0 ? (alimento.cantidad / alimento.cantidad_bulto).toFixed(2) : '0.00' }}
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" class="form-control form-control-sm"
                                                    placeholder="Cant." v-model="alimento.qty" />
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="Motivo" v-model="alimento.subject" />
                                                <div class="input-group-append">
                                                    <button class="btn btn-sm btn-outline-success" type="button"
                                                        v-if="alimento.qty && alimento.qty != 0"
                                                        @click="updateStock(alimento.id, alimento.qty, alimento.subject)">
                                                        <i class="fa fa-check-circle"></i>
                                                    </button>
                                                    <button v-else class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fa fa-check-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <pagination :align="'center'" :data="listado" :limit="8" @pagination-change-page="listFoods">
                            <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                            <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
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
            totals: {},
            listWarehouses: [],
            listBrands: [],
            listFoodCategories: [],
            filter: {
                nro_results: 15,
                food: '',
                warehouse_id: '',
                brand_id: '',
                food_category_id: '',
                quantity_sign: '',
                quantity: ''
            },
            json_fields: {
                "ID": "id",
                "Alimento": "alimento",
                "Costo KG": {
                    field: "costo_kg",
                    callback: (value) => numeral(value).format('0.00')
                },
                "Categoría": "category.category",
                "KG por Bulto": "cantidad_bulto",
                "Stock (Kg)": "cantidad",
                "Stock (Bultos)": {
                    callback: (obj) => {
                        return obj.cantidad_bulto > 0 ? (obj.cantidad / obj.cantidad_bulto).toFixed(2) : '0.00';
                    }
                }
            }
        };
    },
    components: {
        downloadexcel,
    },
    methods: {
        listFoods(page = 1) {
            let me = this;
            // Podrías crear una variable 'loading: false' en data()
            // this.loading = true; 
            let data = { page, ...this.filter }
            
            axios.get("api/alimentos/list", { params: data }).then(function (response) {
                me.listado = response.data.foods;
                me.totals = response.data.totals;
                // this.loading = false;
            });
        },
        onSearchWarehouse(search, loading) {
            if (search.length) {
                loading(true);
                axios.get(`api/warehouses`, { params: { warehouse: search } })
                    .then((response) => {
                        this.listWarehouses = response.data.warehouses.data;
                        loading(false);
                    }).catch(e => console.log(e));
            }
        },
        onSearchBrand(search, loading) {
            if (search.length) {
                loading(true);
                axios.get(`api/brands`, { params: { brand: search } })
                    .then((response) => {
                        this.listBrands = response.data.brands.data;
                        loading(false);
                    }).catch(e => console.log(e));
            }
        },
        onSearchFoodCategory(search, loading) {
            if (search.length) {
                loading(true);
                axios.get(`api/food-categories`, { params: { category: search } })
                    .then((response) => {
                        this.listFoodCategories = response.data.foodCategories.data;
                        loading(false);
                    }).catch(e => console.log(e));
            }
        },
        updateStock(id, cantidad, descripcion) {
            let me = this;
            axios.post(`api/food/stock-update/${id}`, {
                cantidad: cantidad,
                descripcion: descripcion
            })
            .then(function (response) {
                // 1. Mostrar mensaje de éxito
                toast.fire({ icon: 'success', title: 'Stock actualizado correctamente' });

                // 2. BUSCAR EL ALIMENTO EN EL LISTADO LOCAL Y LIMPIAR INPUTS
                // Esto es vital para que la interfaz "reaccione"
                let index = me.listado.data.findIndex(item => item.id === id);
                if (index !== -1) {
                    me.listado.data[index].qty = '';     // Vacía el input de cantidad
                    me.listado.data[index].subject = ''; // Vacía el input de motivo
                }

                // 3. RECARGAR DATOS DEL SERVIDOR
                // Usamos la página actual para que el usuario no pierda donde estaba
                let page = me.listado.current_page ? me.listado.current_page : 1;
                me.listFoods(page);
            })
            .catch(e => {
                console.error(e);
                toast.fire({ icon: 'error', title: 'Error al actualizar stock' });
            });
        },
    },
    mounted() {
        this.listFoods();
    },
};
</script>