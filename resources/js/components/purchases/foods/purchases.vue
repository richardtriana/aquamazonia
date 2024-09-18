<template>
	<div class="w-100">
		<header class="page-header justify-content-between row px-4">
			<h3>Compras</h3>
			<router-link class="btn btn-primary" :to="{
				name: 'purchase-food',
				params: { purchase_id: 0 }
			}">
				Nueva compra
			</router-link>
		</header>
		<section>
			<div class="card-body">
				<div class="form-row">
					<h6 class="w-100">Buscar...</h6>
					<div class="form-group col-3">
						<label for="nro_factura">Nro Compra</label>
						<input type="text" name="nro_factura" id="nro_factura" class="form-control"
							placeholder="Nro Compra" v-model="filter.id" />
					</div>
					<div class="form-group col-3">
						<label for="name_provider">Proveedor</label>
						<input type="text" name="name_provider" id="name_provider" class="form-control"
							placeholder="Proveedor" v-model="filter.provider" />
					</div>
					<div class="form-group col-md-3">
						<label for="from_date">Desde</label>
						<input type="date" class="form-control" id="from_date" v-model="filter.from" />
					</div>
					<div class="form-group col-md-3">
						<label for="to_date">Hasta</label>
						<input type="date" class="form-control" id="to_date" v-model="filter.to" />
					</div>
					<div class="form-group offset-9 col-md-3">
						<button class="btn btn-success btn-block" @click="getPurchases(1)">
							Buscar
						</button>
					</div>
				</div>
				<table class="table table-sm table-bordered table-responsive-sm">
					<thead>
						<tr>
							<th>#</th>
							<th>Total Pago</th>
							<th>Total Descuento</th>
							<th>Proveedor</th>
							<th>Estado</th>
							<!-- <th>Ver</th> -->
							<!-- <th>Imprimir</th> -->
							<th>Editar</th>
							<!-- <th>Eliminar</th> -->
						</tr>
					</thead>
					<tbody>
						<tr v-for="o in PurchaseList.data" :key="o.id">
							<th scope="row">{{ o.id }}</th>
							<td>{{ o.total_cost | numeral('$0,0.00') }}</td>
							<td>{{ o.total_discount | numeral('$0,0.00') }}</td>
							<td>{{ o.provider.provider }}</td>
							<td>
								<span v-if="o.state == 0">Desechada</span>
								<span v-if="o.state == 1">Abierta</span>
								<span v-if="o.state == 2">Registrada</span>
								<span v-if="o.state == 3">Cotización</span>
							</td>
							<!-- <td>
								<router-link class="btn" :to="{
									name: 'details-purchase',
									params: { purchase_id: o.id }
								}">
									<i class="bi bi-eye"></i>
								</router-link>
							</td> -->
							<!-- <td>
								<button class="btn text-danger" @click="generatePdf(o.id)">
									<i class="bi bi-file-earmark-pdf-fill"></i>
								</button>
							</td> -->
							<td>
								<router-link class="btn btn" :to="{
									name: 'purchase-food',
									params: { purchase_id: o.id }
								}">
								<i class="fa fa-pen" aria-hidden="true"></i>
								</router-link>
							</td>
							<!-- <td>
								<button class="btn" @click="deletePurchase(o.id)">
									<i class="bi bi-trash"></i>
								</button>
							</td> -->
						</tr>
					</tbody>
				</table>
			</div>
			<pagination :align="'center'" :data="PurchaseList" :limit="8" @pagination-change-page="getPurchases">
				<span slot="prev-nav"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
				<span slot="next-nav"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
			</pagination>
		</section>
	</div>
</template>
<script>
export default {
	data() {
		return {
			load_pdf: false,
			PurchaseList: {},
			filter: {
				provider: "",
				from: "",
				to: "",
				type: "alimentos"
			}
		};
	},
	created() {
		this.getPurchases(1);
	},
	methods: {
		getPurchases(page = 1) {
			let me = this;
			let data = {
				page,
                ...this.filter
            }
			axios
				.get(
					`api/purchases`,
					{ params: data }
				)
				.then(function (response) {
					me.PurchaseList = response.data.purchases;
				});
		},
		deletePurchase(purchase_id) {
			axios
				.delete(`api/purchases/${purchase_id}`)
				.then(() => this.getpurchases(1));
		},
		generatePdf(id) {
			this.load_pdf = true;
			console.log(this.load_pdf);
			axios
				.get("api/purchases/generatePdf/" + id)
				.then(response => {
					const pdf = response.data.pdf;
					var a = document.createElement("a");
					a.href = "data:application/pdf;base64," + pdf;
					a.download = `Purchase-${id}.pdf`;
					a.click();
				})
				.finally(() => {
					this.load_pdf = false
				});
		}
	}
};
</script>
