<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <!-- NAV TABS -->
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="#"
                            :class="{ active: activeTab === 'usuarios' }"
                            @click.prevent="activeTab = 'usuarios'"
                        >
                            Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="#"
                            :class="{ active: activeTab === 'registered' }"
                            @click.prevent="activeTab = 'registered'"
                        >
                            Usuarios de registro
                        </a>
                    </li>
                </ul>

                <!-- TAB: USUARIOS (ORIGINAL) -->
                <div class="card" v-if="activeTab === 'usuarios'">
                    <div class="card-header">Gestión de Usuarios</div>

                    <div class="card-body">
                        <div class="row mb-1">
                            <div class="col-12 ">
                                <button
                                    class="btn btn-success float-right"
                                    @click="limpiar()"
                                    type="button"
                                    data-toggle="modal"
                                    data-target="#agregar"
                                >
                                    Agregar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Correo</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="usuario in listado" :key="usuario.id">
                                        <td></td>
                                        <td v-text="usuario.name"></td>
                                        <td v-text="usuario.email"></td>
                                        <td v-text="usuario.estado == 1 ? 'Activo' : 'Inacitvo'"></td>
                                        <td>
                                            <!-- <span style="font-size: 1.5em; color:#FFC107;"><i class="fas fa-user"></i></span>-->
                                            <button
                                                @click="editar(usuario)"
                                                class="btn btn-success"
                                                type="button"
                                                data-toggle="modal"
                                                data-target="#agregar"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" @click="eliminar(usuario.id)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr></tr>
                                    <tr></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB: USUARIOS DE REGISTRO -->
                <div class="card" v-if="activeTab === 'registered'">
                    <div class="card-header">Usuarios de registro</div>

                    <div class="card-body">
                        <div class="row mb-1">
                            <div class="col-12 ">
                                <button
                                    class="btn btn-success float-right"
                                    @click="limpiarRegistered()"
                                    type="button"
                                    data-toggle="modal"
                                    data-target="#agregarRegistered"
                                >
                                    Agregar
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Teléfono</th>
                                        <th scope="col">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="u in listadoRegistered" :key="u.id">
                                        <td>{{ u.id }}</td>
                                        <td v-text="u.name"></td>
                                        <td v-text="u.phone"></td>
                                        <td>
                                            <button
                                                @click="editarRegistered(u)"
                                                class="btn btn-success"
                                                type="button"
                                                data-toggle="modal"
                                                data-target="#agregarRegistered"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger" @click="eliminarRegistered(u.id)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!listadoRegistered.length">
                                        <td colspan="4" class="text-center text-muted">
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

        <!-- Modal USUARIOS (ORIGINAL) -->
        <div
            class="modal fade"
            id="agregar"
            tabindex="-1"
            role="dialog"
            data-backdrop="static"
            aria-labelledby="Agregar/Editar"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Datos Usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="/vuejs/form" @submit.prevent="guardar">

                        <div class="modal-body">

                            <div
                                :class="['form-group row', form.errors().has('name') ? 'has-error' : '']"
                            >
                                <label for="name" class="col-sm-12 col-md-4 col-form-label">Nombre</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="name"
                                        name="name"
                                        value=""
                                        autofocus="autofocus"
                                        class="form-control"
                                        type="text"
                                        v-model="form.name"
                                        required
                                    >
                                    <span
                                        v-if="form.errors().has('name')"
                                        class="label label-danger"
                                        v-text="form.errors().get('name')"
                                    ></span>
                                </div>
                            </div>
                            <div
                                :class="['form-group row', form.errors().has('email') ? 'has-error' : '']"
                            >
                                <label for="email" class="col-sm-12 col-md-4 col-form-label">Correo</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="email"
                                        name="email"
                                        class="form-control"
                                        type="email"
                                        v-model="form.email"
                                    >
                                    <span
                                        v-if="form.errors().has('email')"
                                        class="label label-danger"
                                        v-text="form.errors().get('email')"
                                    ></span>
                                </div>

                            </div>
                            <div
                                :class="['form-group row', form.errors().has('password') ? 'has-error' : '']"
                            >
                                <label for="password" class="col-sm-12 col-md-4 col-form-label">Password</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        type="password"
                                        v-model="form.password"
                                    >
                                    <span
                                        v-if="form.errors().has('password')"
                                        class="label label-danger"
                                        v-text="form.errors().get('password')"
                                    ></span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-dismiss="modal"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                v-text="editando==0 ? 'Guardar' : 'Actualizar'"
                            ></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal USUARIOS DE REGISTRO -->
        <div
            class="modal fade"
            id="agregarRegistered"
            tabindex="-1"
            role="dialog"
            data-backdrop="static"
            aria-labelledby="Agregar/Editar Usuario Registro"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Datos Usuario de registro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" @submit.prevent="guardarRegistered">

                        <div class="modal-body">

                            <div
                                :class="['form-group row', formRegistered.errors().has('name') ? 'has-error' : '']"
                            >
                                <label for="reg_name" class="col-sm-12 col-md-4 col-form-label">Nombre</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="reg_name"
                                        name="reg_name"
                                        class="form-control"
                                        type="text"
                                        v-model="formRegistered.name"
                                        required
                                    >
                                    <span
                                        v-if="formRegistered.errors().has('name')"
                                        class="label label-danger"
                                        v-text="formRegistered.errors().get('name')"
                                    ></span>
                                </div>
                            </div>

                            <div
                                :class="['form-group row', formRegistered.errors().has('phone') ? 'has-error' : '']"
                            >
                                <label for="reg_phone" class="col-sm-12 col-md-4 col-form-label">Teléfono</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="reg_phone"
                                        name="reg_phone"
                                        class="form-control"
                                        type="text"
                                        v-model="formRegistered.phone"
                                    >
                                    <span
                                        v-if="formRegistered.errors().has('phone')"
                                        class="label label-danger"
                                        v-text="formRegistered.errors().get('phone')"
                                    ></span>
                                </div>
                            </div>

                            <div
                                :class="['form-group row', formRegistered.errors().has('pin') ? 'has-error' : '']"
                            >
                                <label for="reg_pin" class="col-sm-12 col-md-4 col-form-label">PIN (4 dígitos)</label>
                                <div class="col-sm-12 col-md-8">
                                    <input
                                        id="reg_pin"
                                        name="reg_pin"
                                        class="form-control"
                                        type="password"
                                        maxlength="4"
                                        v-model="formRegistered.pin"
                                    >
                                    <span
                                        v-if="formRegistered.errors().has('pin')"
                                        class="label label-danger"
                                        v-text="formRegistered.errors().get('pin')"
                                    ></span>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-dismiss="modal"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                v-text="editandoRegistered==0 ? 'Guardar' : 'Actualizar'"
                            ></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import form from 'vuejs-form'

export default {
    data() {
        return {
            // pestaña activa
            activeTab: 'usuarios',

            // usuarios normales
            editando: 0,
            id_edita: 0,
            form: form.default({
                name: '',
                email: '',
                password: '',
                estado: 1,
            })
            .rules({
                email: 'email|min:7|required',
                name: 'required|min:5|',
            })
            .messages({
                'name.name': 'El nombre es requerido',
                'email.email': 'Ingrese un correo válido',
            }),
            errores: [],
            success: false,
            listado: [],

            // usuarios de registro
            editandoRegistered: 0,
            id_edita_registered: 0,
            formRegistered: form.default({
                name: '',
                phone: '',
                pin: '',
            })
            .rules({
                name: 'required|min:3|',
                phone: 'required|min:7|',
                pin: 'required|min:4|max:4|',
            })
            .messages({
                'name.name': 'El nombre es requerido',
                'phone.phone': 'El teléfono es requerido',
                'pin.pin': 'El PIN es requerido',
            }),
            listadoRegistered: [],
        }
    },

    methods: {
        // ==== USUARIOS (ORIGINAL) ====
        limpiar() {
            this.form.name = '';
            this.form.email = '';
            this.form.password = '';
            this.id_edita = '';
            this.editando = 0;
        },
        eliminar(id_elim) {
            let me = this;
            if (confirm('Esta seguro de inactivar este usuario?')) {
                axios.delete("api/usuarios/" + id_elim)
                    .then(function (response) {
                        me.listar();
                    })
                    .catch(function (error) {
                    });
            }
        },
        guardar() {
            let me = this;
            if (!this.form.errors().any()) {
                if (me.editando == 0) {
                    if (me.form.password == '') {
                        alert("Debe digitar el password!!!");
                    } else {
                        axios.post("api/usuarios", this.form.all())
                            .then(function (response) {

                                me.form.email = '';
                                me.form.name = '';
                                me.form.password = '';
                                $('#agregar').modal('hide');
                                me.listar();
                            })
                            .catch(function (error) {
                            });
                    }
                } else {
                    axios.put("api/usuarios/" + this.id_edita, this.form.all())
                        .then(function (response) {
                            me.form.email = '';
                            me.form.name = '';
                            me.form.password = '';
                            $('#agregar').modal('hide');
                            me.listar();
                        })
                        .catch(function (error) {
                        });

                }
            }
            else {
            }
        },
        listar() {
            let me = this;
            axios.get("api/usuarios")
                .then(function (response) {
                    me.listado = response.data;
                });

        },

        editar(objeto) {
            this.id_edita = objeto.id;
            this.editando = 1;
            this.form.name = objeto.name;
            this.form.email = objeto.email;
            this.editando = 1;
        },

        // ==== USUARIOS DE REGISTRO ====
        limpiarRegistered() {
            this.formRegistered.name = '';
            this.formRegistered.phone = '';
            this.formRegistered.pin = '';
            this.id_edita_registered = '';
            this.editandoRegistered = 0;
        },
        listarRegistered() {
            let me = this;
            axios.get("api/registered-users")
                .then(function (response) {
                    me.listadoRegistered = response.data;
                })
                .catch(function (error) {
                });
        },
        guardarRegistered() {
            let me = this;
            if (!this.formRegistered.errors().any()) {
                if (me.editandoRegistered == 0) {
                    // crear
                    if (me.formRegistered.pin === '') {
                        alert("Debe digitar el PIN (4 dígitos)!!!");
                    } else {
                        axios.post("api/registered-users", this.formRegistered.all())
                            .then(function (response) {
                                me.formRegistered.name = '';
                                me.formRegistered.phone = '';
                                me.formRegistered.pin = '';
                                $('#agregarRegistered').modal('hide');
                                me.listarRegistered();
                            })
                            .catch(function (error) {
                            });
                    }
                } else {
                    // actualizar
                    axios.put("api/registered-users/" + this.id_edita_registered, this.formRegistered.all())
                        .then(function (response) {
                            me.formRegistered.name = '';
                            me.formRegistered.phone = '';
                            me.formRegistered.pin = '';
                            $('#agregarRegistered').modal('hide');
                            me.listarRegistered();
                        })
                        .catch(function (error) {
                        });
                }
            } else {
                // hay errores de validación en el front
            }
        },
        eliminarRegistered(id) {
            let me = this;
            if (confirm('Esta seguro de eliminar este usuario de registro?')) {
                axios.delete("api/registered-users/" + id)
                    .then(function (response) {
                        me.listarRegistered();
                    })
                    .catch(function (error) {
                    });
            }
        },
        editarRegistered(objeto) {
            this.id_edita_registered = objeto.id;
            this.editandoRegistered = 1;
            this.formRegistered.name = objeto.name;
            this.formRegistered.phone = objeto.phone;
            this.formRegistered.pin = objeto.pin; // si no quieres mostrar el PIN real, aquí puedes dejar vacío
        },
    },

    mounted() {
        this.listar();
        this.listarRegistered();
    }
}
</script>
