<template>
    <div class="modal fade"
         id="modalRegisteredUser"
         tabindex="-1"
         role="dialog"
         data-backdrop="static"
         aria-labelledby="Agregar/Editar Usuario registro"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ editando ? 'Editar usuario de registro' : 'Nuevo usuario de registro' }}
                    </h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form method="POST" @submit.prevent="guardar">
                    <div class="modal-body">

                        <!-- BLOQUE ERRORES DEL BACKEND -->
                        <div v-if="form.errors().any()" class="alert alert-danger">
                            <strong>⚠ Ocurrieron errores al guardar:</strong>
                            <ul class="mt-2 mb-0">
                                <li v-for="(msgs, field) in form.errors().all()" :key="field">
                                    <span v-for="(msg, idx) in msgs" :key="field + '-' + idx">
                                        {{ msg }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <!-- NOMBRE -->
                        <div :class="['form-group row', form.errors().has('name') ? 'has-error' : '']">
                            <label for="reg_name" class="col-sm-12 col-md-4 col-form-label">
                                Nombre
                            </label>
                            <div class="col-sm-12 col-md-8">
                                <input id="reg_name" class="form-control" type="text" v-model="form.name">
                                <span v-if="form.errors().has('name')" class="label label-danger">
                                    {{ form.errors().get('name') }}
                                </span>
                            </div>
                        </div>

                        <!-- TELEFONO -->
                        <div :class="['form-group row', form.errors().has('phone') ? 'has-error' : '']">
                            <label for="reg_phone" class="col-sm-12 col-md-4 col-form-label">
                                Teléfono
                            </label>
                            <div class="col-sm-12 col-md-8">
                                <input id="reg_phone" class="form-control" type="text" v-model="form.phone">
                                <span v-if="form.errors().has('phone')" class="label label-danger">
                                    {{ form.errors().get('phone') }}
                                </span>
                            </div>
                        </div>

                        <!-- PIN -->
                        <div :class="['form-group row', form.errors().has('pin') ? 'has-error' : '']">
                            <label class="col-sm-12 col-md-4 col-form-label">
                                PIN (4 dígitos)
                            </label>
                            <div class="col-sm-12 col-md-8">
                                <input class="form-control" type="number" v-model="form.pin">
                                <span v-if="form.errors().has('pin')" class="label label-danger">
                                    {{ form.errors().get('pin') }}
                                </span>
                            </div>
                        </div>

                        <!-- ESTADO -->
                        <div :class="['form-group row', form.errors().has('estado') ? 'has-error' : '']">
                            <label class="col-sm-12 col-md-4 col-form-label">Estado</label>
                            <div class="col-sm-12 col-md-8">
                                <select class="form-control" v-model.number="form.estado">
                                    <option :value="1">Activo</option>
                                    <option :value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- ROL -->
                        <div :class="['form-group row', form.errors().has('rol') ? 'has-error' : '']">
                            <label class="col-sm-12 col-md-4 col-form-label">Rol</label>
                            <div class="col-sm-12 col-md-8">
                                <select class="form-control" v-model="form.rol">
                                    <option value="alimentador">Alimentador</option>
                                    <option value="parametros">Parámetros</option>
                                    <option value="mixto">Mixto</option>
                                    <option value="celador">Celador</option>
                                </select>
                                <span v-if="form.errors().has('rol')" class="label label-danger">
                                    {{ form.errors().get('rol') }}
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            {{ editando ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>

<script>
import form from 'vuejs-form'

export default {
    data() {
        return {
            editando: 0,
            id_edita: 0,
            form: form.default({
                name: '',
                phone: '',
                pin: '',
                estado: 1,
                rol: 'alimentador',
            })
        }
    },

    methods: {

        open(user = null) {
            this.form.errors().set({})   // limpiar errores

            if (user) {
                this.editando = 1
                this.id_edita = user.id
                this.form.name = user.name
                this.form.phone = user.phone
                this.form.pin = ''
                this.form.estado = user.estado
                this.form.rol = user.rol || 'alimentador'
            } else {
                this.editando = 0
                this.id_edita = 0
                this.form.name = ''
                this.form.phone = ''
                this.form.pin = ''
                this.form.estado = 1
                this.form.rol = 'alimentador'
            }

            $('#modalRegisteredUser').modal('show')
        },

        guardar() {

            const request = this.editando
                ? axios.put('api/registered-users/' + this.id_edita, this.form.all())
                : axios.post('api/registered-users', this.form.all())

            request
                .then(() => {
                    this.form.errors().set({})
                    $('#modalRegisteredUser').modal('hide')
                    this.$emit('list-registered')
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        this.form.errors().set(error.response.data.errors)
                    } else {
                        console.error(error)
                        // NO alert()
                        this.form.errors().set({
                            general: ['Ocurrió un error inesperado.']
                        })
                    }
                })
        }
    }
}
</script>
