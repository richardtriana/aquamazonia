<template>
	<div class="row px-2">

		<!-- 🔙 BOTÓN VOLVER -->
        <div class="col-12 mb-3">
            <router-link class="btn btn-secondary btn-sm" to="/purchases/foods">
                <i class="fa fa-arrow-left"></i> Volver a compras de alimentos
            </router-link>
        </div>

		<!-- ============================
		    COLUMNA IZQUIERDA (ITEMS)
		============================= -->
		<div class="col-9 justify-content-center p-2">

			<!-- TOTAL SUPERIOR -->
			<div class="sticky-top mb-2 text-uppercase w-50" style="z-index:1022; left:100%">
				<table class="table table-borderless">
					<tr class="h1 text-white bg-primary">
						<td class="text-right">Total</td>
						<td>$ {{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}</td>
					</tr>
				</table>
			</div>

			<!-- ALERTA SIN RESULTADOS -->
			<div class="position-fixed top-0 right-0 w-50" style="z-index:3000">
				<div class="toast fade hide border border-danger w-100 m-3"
					role="alert" id="no-results" aria-live="assertive" 
					aria-atomic="true" data-delay="3000">
					<div class="toast-header">
						<strong class="mr-auto h4 text-danger">Advertencia</strong>
						<button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
							<span>&times;</span>
						</button>
					</div>
					<div class="toast-body text-dark h4">
						No se ha encontrado coincidencias
					</div>
				</div>
			</div>

			<!-- ============================
			    BARRA DE BÚSQUEDA 
			============================= -->
			<div class="row position-sticky sticky-top mb-2 bg-light p-2">

				<!-- BUSCAR ALIMENTO -->
				<div class="input-group col-6">
					<input type="text" class="form-control" placeholder="Código / nombre alimento"
						v-model="filter.food" @keypress.enter="searchFood()">

					<div class="input-group-append">
						<button class="btn btn-outline-secondary" @click="searchFood()">
							Añadir alimento
						</button>

						<button class="btn btn-outline-secondary" @click="openFoodModal()">
							<i class="fa fa-search"></i>
						</button>
					</div>
				</div>

				<!-- BUSCAR PROVEEDOR -->
				<div class="input-group col-6">
					<input type="text" class="form-control" placeholder="Proveedor"
						v-model="filter.provider" @keypress.enter="searchProvider()">

					<div class="input-group-append">
						<button class="btn btn-outline-secondary" @click="searchProvider()">
							Añadir proveedor
						</button>

						<button class="btn btn-outline-secondary" @click="openProviderModal()">
							<i class="fa fa-user"></i>
						</button>
					</div>
				</div>
			</div>

			<!-- ============================
			    TABLA DE ITEMS
			============================= -->
			<section>
				<table class="table table-sm table-bordered table-hover">
					<thead class="bg-secondary text-white position-sticky sticky-top" style="top:4rem">
						<tr>
							<th></th>
							<th>ID</th>
							<th>Alimento</th>
							<th>Cantidad</th>
							<th>Desc %</th>
							<th>Desc $</th>
							<th>Precio</th>
							<th>Total</th>
						</tr>
					</thead>

					<tbody v-if="itemsPurchaseList.length">
						<tr v-for="(p,index) in itemsPurchaseList" :key="p.id">

							<td>
								<button class="btn text-danger" @click="removeFood(index, p.detail_id)">
									<i class="fa fa-trash"></i>
								</button>
							</td>

							<td>{{ p.id }}</td>
							<td>{{ p.item }}</td>

							<td>
								<input type="number" class="form-control form-control-sm" 
									v-model="p.quantity" style="max-width:70px">
							</td>

							<td>
								<input type="number" class="form-control form-control-sm"
									v-model="p.discount_percentage" style="max-width:70px">
							</td>

							<td>
								<input type="number" class="form-control form-control-sm"
									v-model="p.discount_value" style="max-width:90px">
							</td>

							<td>
								<input type="number" class="form-control form-control-sm"
									v-model="p.price" style="max-width:100px">
							</td>

							<td>
								$
								{{
									(p.total_cost =
										p.quantity * p.price - p.discount_value
									).toFixed(2)
								}}
							</td>

						</tr>
					</tbody>

					<tbody v-else>
						<tr>
							<td colspan="8" class="text-center">No se han añadido alimentos</td>
						</tr>
					</tbody>

				</table>
			</section>
		</div>

		<!-- ============================
		    COLUMNA DERECHA (TOTALES)
		============================= -->
		<div class="col-md-3">

			<section class="card">
				<table class="table table-sm table-primary text-right">

					<tr>
						<th>Total:</th>
						<td>$ {{ (purchase.total_cost = total_cost).toFixed(0) }}</td>
					</tr>

					<tr>
						<th>Descuento:</th>
						<td>$ {{ (purchase.total_discount = total_discount).toFixed(0) }}</td>
					</tr>

					<tr class="bg-primary text-white">
						<th>Pagar:</th>
						<td>$ {{ (purchase.total_tax_inc = total_tax_inc).toFixed(0) }}</td>
					</tr>

					<tr>
						<th>Efectivo:</th>
						<td><input type="number" v-model="purchase.cash"></td>
					</tr>

					<tr>
						<th>Cambio:</th>
						<td><input type="text" :value="payment_return" disabled></td>
					</tr>

				</table>
			</section>

			<button class="btn btn-outline-primary btn-block mt-2"
				@click="createOrUpdatePurchase(2)">
				<i class="fa fa-save"></i> Guardar
			</button>

			<router-link v-if="purchase_id != 0" class="btn btn-outline-secondary btn-block"
				:to="{ name:'purchases-foods' }">
				Cancelar
			</router-link>
		</div>

		<!-- MODALES -->
		<add-food @add-food="addFood"></add-food>
		<add-provider @add-provider="addProvider"></add-provider>

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
			filter: { food: "", provider: "", is_order:1 },

			itemsPurchaseList: [],

			purchase: {
				id_provider: 1,
				provider: "Sin proveedor",
				state: 1,
				total_tax_inc: 0,
				total_cost: 0,
				total_discount: 0,
				cash: 0,
				change: 0,
				itemsPurchase:[]
			}
		};
	},

	computed: {
		total_cost() {
			return this.itemsPurchaseList.reduce((t,p) =>
				t + (p.quantity * p.price), 0);
		},

		total_discount() {
			return this.itemsPurchaseList.reduce((t,p)=>
				t + Number(p.discount_value), 0);
		},

		total_tax_inc() {
			return this.total_cost - this.total_discount;
		},

		payment_return() {
			return this.purchase.cash > 0
				? (this.purchase.cash - this.total_tax_inc).toFixed(0)
				: 0;
		}
	},

	methods: {

		/* ================================
		   LISTAR ITEMS SI ES EDICIÓN
		================================*/
		listItemsPurchase() {
			if (!this.purchase_id || this.purchase_id == 0) return;

			axios.get(`api/purchases/${this.purchase_id}`, this.$root.config)
				.then(res => {
					this.purchase.id_provider = res.data.purchase_information.provider_id;
					this.purchase.provider = res.data.purchase_information.provider.name;
					this.itemsPurchaseList = res.data.purchase_details;
				});
		},

		/* ================================
		   BUSCAR ALIMENTO
		================================*/
		searchFood() {
			if (!this.filter.food) return;

			axios.get(`api/food/search-item`, { params: this.filter })
				.then(res => {
					if (!res.data.food) $("#no-results").toast("show");
					else this.addFood(res.data.food);
				});

			this.filter.food = "";
		},

		addFood(food) {
			let exists = this.itemsPurchaseList.find(p => p.id === food.id);

			if (exists) {
				exists.quantity++;
				return;
			}

			this.itemsPurchaseList.push({
				id: food.id,
				item: food.alimento,
				price: food.costo_kg,
				quantity: 1,
				discount_percentage: 0,
				discount_value: 0,
				total_cost: 0
			});
		},

		/* ================================
		   BUSCAR PROVEEDOR
		================================*/
		searchProvider() {
			if (!this.filter.provider) return;

			axios.get("api/providers", { params:{ provider:this.filter.provider } })
				.then(res => {
					if (!res.data.providers?.data?.length)
						$("#no-results").toast("show");
					else
						this.addProvider(res.data.providers.data[0]);
				});
		},

		addProvider(provider) {
			this.purchase.id_provider = provider.id;
			this.purchase.provider = provider.provider;
			this.filter.provider = provider.provider;
		},

		/* ================================
		   MODALES
		================================*/
		openFoodModal() {
			$("#addFoodModal").modal("show");
		},

		openProviderModal() {
			$("#addProviderModal").modal("show");
		},

		removeFood(index, detail_id=null) {
			this.itemsPurchaseList.splice(index,1);

			if (detail_id)
				axios.delete(`api/purchase-details/${detail_id}`, this.$root.config);
		},

		/* ================================
		GUARDAR / ACTUALIZAR COMPRA
		================================*/
		createOrUpdatePurchase(state_purchase) {
			this.purchase.state = state_purchase;

			if (this.itemsPurchaseList.length === 0) {
				Swal.fire("No hay alimentos en la compra");
				return;
			}

			this.purchase.itemsPurchase = this.itemsPurchaseList;

			// --- BLOQUE DE AUTENTICACIÓN ---
			const token = localStorage.getItem('user_token'); 
			const authConfig = {
				headers: { 
					'Authorization': `Bearer ${token}`,
					'Accept': 'application/json'
				}
			};

			// NUEVA COMPRA
			if (!this.purchase_id || this.purchase_id == 0) {
				axios.post("api/purchases", this.purchase, authConfig)
					.then(() => {
						Swal.fire("Excelente", "Compra guardada correctamente", "success")
						.then(() => {
							// REDIRECCIÓN POR PATH (Sin usar 'name')
							this.$router.push("/purchases/foods"); 
						});
					})
					.catch((error) => {
						console.error(error);
						if (error.response && error.response.status === 401) {
							Swal.fire("Sesión Expirada", "Por favor, vuelve a iniciar sesión", "warning");
						} else {
							Swal.fire("Error", "No se pudo guardar la compra", "error");
						}
					});
				return;
			}

			// EDITAR COMPRA
			axios.put(`api/purchases/${this.purchase_id}`, this.purchase, authConfig)
				.then(() => {
					Swal.fire("Actualizado", "Compra actualizada correctamente", "success")
					.then(() => {
						// REDIRECCIÓN POR PATH (Sin usar 'name')
						this.$router.push("/purchases/foods"); 
					});
				})
				.catch((error) => {
					if (error.response && error.response.status === 401) {
						Swal.fire("Sesión Expirada", "Vuelve a loguearte", "warning");
					} else {
						Swal.fire("Error", "No se pudo actualizar", "error");
					}
				});
		},

		commands() {
			shortcut.add("F1", () => this.createOrUpdatePurchase(2));
			shortcut.add("F10", () => this.openFoodModal());
		}
	},

	mounted() {
		$("#no-results").toast("hide");
		this.listItemsPurchase();
		this.commands();
	}
};
</script>
