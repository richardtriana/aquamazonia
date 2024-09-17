<template>
	<div class="row px-2">
		<div class="col-9 justify-content-center p-2">
			<div class="sticky-top mb-2 text-uppercase w-50" style="z-index: 1022; left: 100%">
				<table class="table table-borderless">
					<tr class="h1 text-white bg-primary">
						<td class="text-right">Total</td>
						<td>
							$
							{{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}
						</td>
					</tr>
				</table>

				<!-- </div> -->
			</div>
			<div class="position-fixed top-0 right-0 w-50" style="z-index: 3000">
				<div class="toast fade hide border border-danger w-100 m-3" style="max-width: 90%" role="alert"
					id="no-results" aria-live="assertive" aria-atomic="true" data-delay="3000">
					<div class="toast-header">
						<strong class="mr-auto h3 text-danger">Advertencia</strong>
						<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="toast-body text-dark h4">
						No se ha encontrado coincidencias
					</div>
				</div>
			</div>
			<div class="row position-sticky sticky-top mb-2 bg-light p-1" style="top: 0.5rem">
				<div class="input-group col-6">
					<input type="text" class="form-control" placeholder="Código de barras"
						aria-label=" with two button addons" aria-describedby="button-add-food" v-model="filter.food"
						autofocus @keypress.enter="searchFood()" />
					<div class="input-group-append" id="button-add-food">
						<button class="btn btn-outline-secondary" type="button" @click="searchFood()">
							Añadir Alimento
						</button>
						<button class="btn btn-outline-secondary" type="button" data-toggle="modal"
							data-target="#addFoodModal">
							<i class="fa fa-search" aria-hidden="true"></i>

						</button>
					</div>
				</div>
				<div class="input-group col-6">
					<input type="text" class="form-control" :placeholder="purchase.provider"
						aria-label=" with two button addons" aria-describedby="button-addon4" v-model="filter.provider"
						@keypress.enter="searchProvider()" />
					<div class="input-group-append" id="button-addon4">
						<button class="btn btn-outline-secondary" type="button" @click="searchProvider()">
							Añadir Proveedor
						</button>
						<button class="btn btn-outline-secondary" type="button" data-toggle="modal"
							data-target="#addProviderModal">
							<i class="fa fa-user" aria-hidden="true"></i>
						</button>
					</div>
				</div>
			</div>

			<section>
				<div>
					<table class="
		              table table-sm table-responsive-sm table-bordered table-hover
		            ">
						<thead class="bg-secondary text-white position-sticky sticky-top" style="top: 4rem">
							<tr>
								<th></th>
								<th>ID</th>
								<th>Alimento</th>
								<th>Cantidad</th>
								<th>Descuento %</th>
								<th>Descuento $</th>
								<th>Precio de venta <br> <small>IVA inc</small>
								</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody v-if="itemsPurchaseList.length > 0">
							<tr v-for="(p, index) in itemsPurchaseList" :key="p.id">
								<td>
									<button class="btn text-danger" @click="removeFood(index, p.id)">
										<i class="bi bi-trash"></i>
									</button>
								</td>

								<td>{{ p.id }}</td>
								<td>{{ p.item }}</td>
								<td>
									<input type="number" name="quantity" id="quantity" step="any" placeholder="Cantidad"
										class="form-control form-control-sm" v-model="p.quantity" style="max-width: 60px" />
								</td>
								
								<td>
									<input type="number" name="discount_percentage" id="discount_percentage" step="any"
										placeholder="Descuento" class="form-control form-control-sm"
										v-model="p.discount_percentage" style="max-width: 60px" />
								</td>
								<td>
									<input v-if="p.discount_percentage != 0" type="number"
										class="form-control form-control-sm" name="discount_price" id="discount_price"
										step="2" placeholder="Descuento" :value="
											(p.discount_price = (
												p.quantity *
												p.price *
												(p.discount_percentage / 100)
											).toFixed(0))
										" style="max-width: 100px" />
									<input v-else type="number" class="form-control form-control-sm" name="discount_price"
										id="discount_price" step="2" v-model="p.discount_price" style="max-width: 100px" />
								</td>
								<td>
									<input type="number" name="price" id="price" step="any"
										placeholder="Precio de venta" v-model="p.price"
										class="form-control form-control-sm" style="max-width: 100px" />
								</td>
								<td>
									<span v-if="p.discount_percentage != 0">
										$
										{{
											(p.total_cost =
												p.quantity * p.price -
												p.quantity *
												p.price *
												(p.discount_percentage / 100))
										}}
									</span>
									<span v-else>
										$
										{{
											(p.total_cost =
												p.quantity * p.price - p.discount_price).toFixed(2)
										}}
									</span>
								</td>
							</tr>
						</tbody>
						<tbody v-else>
							<tr>
								<td colspan="8">No se han añadido alimentos</td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</div>
		<div class="col-md-3">
			<div class="">
				<section class="card">
					<div>
						<table class="table table-sm table-primary text-right">
							<tr>
								<th colspan="7">Total:</th>
								<th>
									$
									{{ (purchase.total_cost = total_cost).toFixed(0) }}
								</th>
							</tr>
							
							<tr>
								<th colspan="7">Descuento:</th>
								<th>
									$
									{{ (purchase.total_discount = total_discount).toFixed(0) }}
								</th>
							</tr>
							<tr class="bg-primary h5 text-white">
								<th colspan="7">Pagar:</th>
								<th>
									$
									{{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}
								</th>
							</tr>
							<tr class="">
								<th colspan="7">Efectivo:</th>
								<th>
									<input type="number" value="0" step="any" v-model="purchase.cash" />
								</th>
							</tr>
							<tr class="">
								<th colspan="7">Cambio:</th>
								<th>
									<input type="text" :value="payment_return" readonly disabled />
								</th>
							</tr>
						</table>
					</div>
				</section>
				<div class="">
					<button type="button" class="btn btn-outline-primary btn-block" @click="createOrUpdatePurchase(2)">
						<!-- Facturar -->

						<i class="bi bi-receipt"></i> <b>F1</b> Guardar
					</button>
					<router-link to="/purchases" type="button" class="btn btn-outline-secondary btn-block"
						v-if="purchase_id != 0">
						<i class="bi bi-receipt"></i> Cancelar
					</router-link>
				</div>
			</div>
		</div>

		<add-food @add-food="addFood($event)" />
		<add-provider @add-provider="addProvider($event)" />
	</div>
</template>

<script>
import AddFood from "../components/add-food.vue";
import AddProvider from "../components/add-provider.vue";

export default {
	components: { AddFood, AddProvider },
	props: ["purchase_id"],

	data() {
		return {
			// add food or provider with keyup
			filter: {
				food: "",
				provider: "",
				is_order:1
			},
			itemsPurchaseList: [],

			purchase: {
				id_provider: 1,
				provider: "Sin proveedor",
				state: 1,
				total_tax_inc: 0.0,
				total_tax_exc: 0.0,
				total_discount: 0.0,
				itemsPurchase: [],
				cash: 0,
				change: 0
			}
		};
	},
	computed: {
		total_cost: function () {
			var total = 0.0;
			this.itemsPurchaseList.forEach(
				food =>
					(total += parseFloat(food.price * food.quantity))
			);
			return total;
		},
		total_discount: function () {
			var total = 0.0;
			this.itemsPurchaseList.forEach(food => {
				total += parseFloat(food.discount_price);
			});
			return total;
		},
		total_tax_inc: function () {
			var total = 0.0;
			this.itemsPurchaseList.forEach(food => {
				total += parseFloat(
					food.quantity * food.price -
					food.quantity *
					food.price *
					(food.discount_percentage / 100)
				);
			});
			return total;
		},
		payment_return: function () {
			var value = 0.0;
			if (this.purchase.cash > 0) {
				value = (this.purchase.cash - this.total_tax_inc).toFixed(0);
			}
			return value;
		}
	},
	methods: {
		listItemsPurchase() {
			if (this.purchase_id == 0) {
				return false;
			}

			let me = this;

			axios
				.get(`api/purchases/${me.purchase_id}`, this.$root.config)
				.then(function (response) {
					me.purchase.id_provider = response.data.purchase_information.provider_id;
					me.purchase.provider = response.data.purchase_information.provider.name;
					me.itemsPurchaseList = response.data.purchase_details;
				});
		},
		searchFood() {
			let me = this;
			if (me.filter.food == "") {
				return false;
			}
            let data = {
                ...this.filter
            }
			var url = "api/food/search-item" ;
			axios
				.get(url,  { params: data }, this.$root.config)
				.then(function (response) {
					var new_food = response.data.food;
					if (!new_food) {
						$("#no-results").toast("show");
					} else {
						me.addFood(new_food);
					}
				})
				.catch(function (error) {
					console.log(error);
				});
			me.filter.food = "";
		},
		addFood(new_food) {
			console.log("....::: FOOD", new_food)
			let me = this;
			let result = false;
			// Verifica si el alimento existe en la lista
			me.itemsPurchaseList.filter(prod => {
				if (new_food.id == prod.id) {
					result = true;
					if (result) {
						// Añade cantidad
						prod.quantity += 1;
						prod.price = prod.costo_kg * prod.quantity;
					}
				}
			});

			if (!result) {
				// Sino, lo añade al array
				me.itemsPurchaseList.push({
					food_id: new_food.id,
					id: new_food.id,
					discount_percentage: 0,
					discount_price: 0,
					quantity: 1,				
					item: new_food.alimento,
					price: new_food.costo_kg
				});
			}
		},
		removeFood(index, detail_id = null) {
			this.itemsPurchaseList.splice(index, 1);
			if (detail_id != null || detail_id != 0) {
				axios.delete(`api/purchase-details/${detail_id}`, this.$root.config);
			}
		},
		searchProvider() {
			let me = this;
			if (me.filter.provider == "") {
				return false;
			}
			var url = "api/providers?provider=" + me.filter.provider;
			axios
				.get(url, null, me.$root.config)
				.then(function (response) {
					var new_client = response.data;
					if (!new_client) {
						$("#no-results").toast("show");
					} else {
						me.addProvider(new_client);
					}
				})
				.catch(function (error) {
					console.log(error);
				});
		},
		addProvider(provider) {
			let me = this;
			me.purchase.id_provider = provider.id;
			me.purchase.provider = provider.provider;
			me.filter.provider = provider.provider;
		},

		createOrUpdatePurchase(state_purchase) {
			this.purchase.state = state_purchase;
			if (this.itemsPurchaseList.length > 0) {
				this.purchase.itemsPurchase = this.itemsPurchaseList;
				if (this.purchase_id != 0 && this.purchase_id != null) {
					axios
						.put(
							`api/purchases/${this.purchase_id}`,
							this.purchase,
							this.$root.config
						)
						.then(
							() => (
								Swal.fire({
									icon: 'success',
									title: 'Excelente',
									text: 'Los datos se han guardado correctamente',
								})
							)
						)
						.catch(function (error) {
							// handle error
							console.log('error', error)
							if (error) {
								Swal.fire({
									icon: 'error',
									title: 'Oops...',
									text: 'Hubo un error al guardar los datos',
								})
							}
						})
						.finally(
							// this.$router.push("/purchases")
						);
				} else {
					axios
						.post(`api/purchases`, this.purchase, this.$root.config)
						.then((response) => {
							Swal.fire({
								icon: "success",
								title: "Excelente",
								text: "Los datos se han guardado correctamente",
							});
						})
						.catch(function (error) {
							// handle error
							if (error) {
								Swal.fire({
									icon: "error",
									title: "Oops...",
									text: "Hubo un error al guardar los datos",
								});
							}
						})
						.finally(() =>
							setTimeout(() => {
								// this.$router.go(0)
							}, 3000)
						);
				}
			} else {
				alert("No hay alimentos en la orden");
			}
		},
		commands() {
			let me = this;
			shortcut.add("F1", function () {
				me.createOrUpdatePurchase(2);
			});

			shortcut.add("F2", function () {
				me.createOrUpdatePurchase(4);
			});

			shortcut.add("F10", function () {
				$("#addFoodModal").modal("show");
			});
		}
	},
	mounted() {
		$("#no-results").toast("hide");
		if (this.purchase_id != null || this.purchase_id != 0) {
			this.listItemsPurchase();
		}
		this.commands();
	}
};
</script>
