<template>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-12">

				<!-- NAV TABS -->
				<ul class="nav nav-tabs mb-3 users-tabs">
					<li class="nav-item">
						<a
							href="#"
							class="nav-link"
							:class="{ active: activeTab === 'usuarios' }"
							@click.prevent="activeTab = 'usuarios'"
						>
							GESTIÓN DE USUARIOS
						</a>
					</li>
					<li class="nav-item">
						<a
							href="#"
							class="nav-link"
							:class="{ active: activeTab === 'registered' }"
							@click.prevent="activeTab = 'registered'"
						>
							USUARIOS DE REGISTRO
						</a>
					</li>
				</ul>

				<!-- TAB: USUARIOS NORMALES -->
				<div class="card users-card" v-if="activeTab === 'usuarios'">
					<div class="card-header users-card-header">
						<span>GESTIÓN DE USUARIOS</span>

						<button
							class="btn btn-success btn-sm"
							type="button"
							@click="openCreateUser"
						>
							<i class="fas fa-plus mr-1"></i> Agregar
						</button>
					</div>

					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-sm mb-0 users-table">
								<thead>
									<tr>
										<th scope="col" class="col-min">#</th>
										<th scope="col">Nombre</th>
										<th scope="col">Correo</th>
										<th scope="col" class="col-estado">Estado</th>
										<th scope="col" class="col-opciones">Opciones</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="usuario in listado" :key="usuario.id">
										<td>{{ usuario.id }}</td>
										<td>{{ usuario.name }}</td>
										<td>{{ usuario.email }}</td>
										<td>
											<span
												class="pill-estado"
												:class="usuario.estado == 1 ? 'pill-activo' : 'pill-inactivo'"
											>
												{{ usuario.estado == 1 ? 'Activo' : 'Inactivo' }}
											</span>
										</td>
										<td>
											<button
												class="btn btn-success btn-sm mr-1"
												type="button"
												@click="openEditUser(usuario)"
											>
												<i class="fas fa-edit"></i>
											</button>
											<button
												class="btn btn-danger btn-sm"
												type="button"
												@click="eliminar(usuario.id)"
											>
												<i class="fas fa-trash"></i>
											</button>
										</td>
									</tr>
									<tr v-if="!listado.length">
										<td colspan="5" class="text-center text-muted py-3">
											No hay usuarios registrados.
										</td>
									</tr>
								</tbody>
							</table>
						</div>

					</div>
				</div>

				<!-- TAB: USUARIOS DE REGISTRO -->
				<div class="card users-card" v-if="activeTab === 'registered'">
					<div class="card-header users-card-header">
						<span>USUARIOS DE REGISTRO</span>

						<button
							class="btn btn-success btn-sm"
							type="button"
							@click="openCreateRegistered"
						>
							<i class="fas fa-plus mr-1"></i> Agregar
						</button>
					</div>

					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-sm mb-0 users-table">
								<thead>
									<tr>
										<th scope="col" class="col-min">#</th>
										<th scope="col">Nombre</th>
										<th scope="col">Teléfono</th>
										<th scope="col" class="col-pin">PIN</th>
										<th scope="col" class="col-estado">Estado</th>
										<th scope="col" class="col-rol">Rol</th>
										<th scope="col" class="col-opciones">Opciones</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="u in listadoRegistered" :key="u.id">
										<td>{{ u.id }}</td>
										<td>{{ u.name }}</td>
										<td>{{ u.phone }}</td>
										<td>{{ u.pin }}</td>
										<td>
											<span
												class="pill-estado"
												:class="u.estado == 1 ? 'pill-activo' : 'pill-inactivo'"
											>
												{{ u.estado == 1 ? 'Activo' : 'Inactivo' }}
											</span>
										</td>
										<td>
											<span class="pill-rol">
												{{ (u.rol || 'alimentador').toUpperCase() }}
											</span>
										</td>
										<td>
											<button
												class="btn btn-success btn-sm mr-1"
												type="button"
												@click="openEditRegistered(u)"
											>
												<i class="fas fa-edit"></i>
											</button>
											<button
												class="btn btn-danger btn-sm"
												type="button"
												@click="eliminarRegistered(u.id)"
											>
												<i class="fas fa-trash"></i>
											</button>
										</td>
									</tr>
									<tr v-if="!listadoRegistered.length">
										<td colspan="7" class="text-center text-muted py-3">
											No hay usuarios de registro.
										</td>
									</tr>
								</tbody>
							</table>
						</div>

					</div>
				</div>

			</div>
		</div>

		<!-- MODAL: USUARIO NORMAL -->
		<form-users
			ref="modalUsers"
			@list-users="listar"
		/>

		<!-- MODAL: USUARIO DE REGISTRO -->
		<form-registered-users
			ref="modalRegistered"
			@list-registered="listarRegistered"
		/>
	</div>
</template>

<script>
import formUsers from './form-users.vue'
import formRegisteredUsers from './form-registered-users.vue'

export default {
	components: {
		formUsers,
		formRegisteredUsers
	},

	data() {
		return {
			activeTab: 'usuarios',

			listado: [],
			listadoRegistered: []
		}
	},

	methods: {
		/* ========= USUARIOS NORMALES ========= */

		listar() {
			let me = this
			axios.get('api/usuarios')
				.then(function (response) {
					me.listado = response.data
				})
				.catch(function (error) {
					console.log(error)
				})
		},

		eliminar(id_elim) {
			let me = this
			if (confirm('¿Está seguro de inactivar este usuario?')) {
				axios.delete('api/usuarios/' + id_elim)
					.then(function () {
						me.listar()
					})
					.catch(function (error) {
						console.log(error)
					})
			}
		},

		openCreateUser() {
			if (this.$refs.modalUsers && this.$refs.modalUsers.open) {
				this.$refs.modalUsers.open(null) // crear
			}
		},

		openEditUser(usuario) {
			if (this.$refs.modalUsers && this.$refs.modalUsers.open) {
				this.$refs.modalUsers.open(usuario) // editar
			}
		},

		/* ======== USUARIOS DE REGISTRO ======== */

		listarRegistered() {
			let me = this
			axios.get('api/registered-users')
				.then(function (response) {
					me.listadoRegistered = response.data
				})
				.catch(function (error) {
					console.log(error)
				})
		},

		eliminarRegistered(id) {
			let me = this
			if (confirm('¿Está seguro de eliminar este usuario de registro?')) {
				axios.delete('api/registered-users/' + id)
					.then(function () {
						me.listarRegistered()
					})
					.catch(function (error) {
						console.log(error)
					})
			}
		},

		openCreateRegistered() {
			if (this.$refs.modalRegistered && this.$refs.modalRegistered.open) {
				this.$refs.modalRegistered.open(null) // crear
			}
		},

		openEditRegistered(u) {
			if (this.$refs.modalRegistered && this.$refs.modalRegistered.open) {
				this.$refs.modalRegistered.open(u) // editar
			}
		}
	},

	mounted() {
		this.listar()
		this.listarRegistered()
	}
}
</script>

<style scoped>
/* Tabs un poco más limpios */
.users-tabs .nav-link {
	font-weight: 600;
	text-transform: uppercase;
	font-size: 0.85rem;
}
.users-tabs .nav-link.active {
	border-top: 3px solid #1e7a3a;
}

/* Card */
.users-card {
	border-radius: 0.75rem;
	overflow: hidden;
}
.users-card-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	font-weight: 600;
	font-size: 0.95rem;
}

/* Tabla */
.users-table th {
	font-size: 0.8rem;
	text-transform: uppercase;
	color: #6c757d;
}
.users-table td {
	vertical-align: middle;
	font-size: 0.9rem;
}
.col-min {
	width: 50px;
}
.col-estado {
	width: 130px;
}
.col-rol {
	width: 160px;
}
.col-pin {
	width: 90px;
}
.col-opciones {
	width: 130px;
}

/* Pills de estado y rol */
.pill-estado,
.pill-rol {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0.1rem 0.55rem;
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 600;
	letter-spacing: 0.03em;
}

.pill-activo {
	background-color: #e6f4ea;
	color: #1e7a3a;
	border: 1px solid #c5e6cf;
}

.pill-inactivo {
	background-color: #f2f2f2;
	color: #6c757d;
	border: 1px solid #dddddd;
}

.pill-rol {
	background-color: #e7f1fb;
	color: #0b4f8a;
	border: 1px solid #c1daf8;
}
</style>
