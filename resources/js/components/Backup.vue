<template>
    <div class="container-fluid pt-3">
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="text-dark font-weight-bold mb-0">
                    <i class="fas fa-database mr-2 text-primary"></i> Respaldo de Base de Datos
                </h4>
                <small class="text-muted">Rotación semanal automática — un archivo por día de la semana</small>
            </div>
        </div>

        <!-- Tarjeta último respaldo + acción manual -->
        <div class="row mb-4">
            <div class="col-md-5 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            <i class="fas fa-clock mr-1"></i> Último respaldo
                        </h6>
                        <div v-if="ultimoBackup">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge badge-pill mr-2"
                                      :class="ultimoBackup.tipo === 'manual' ? 'badge-primary' : 'badge-secondary'">
                                    {{ ultimoBackup.tipo }}
                                </span>
                                <strong class="text-capitalize">{{ ultimoBackup.dia_semana }}</strong>
                            </div>
                            <p class="mb-1 text-dark">
                                <i class="fas fa-file-archive mr-1 text-muted"></i>
                                {{ ultimoBackup.filename }}
                            </p>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-weight-hanging mr-1"></i> {{ ultimoBackup.size_fmt }}
                                &nbsp;|&nbsp;
                                <i class="fas fa-user mr-1"></i> {{ ultimoBackup.creado_por }}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-calendar-alt mr-1"></i> {{ ultimoBackup.created_at }}
                            </p>
                        </div>
                        <div v-else class="text-muted">
                            <i class="fas fa-exclamation-circle mr-1"></i> Sin respaldos registrados
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="card-title text-muted mb-3">
                            <i class="fas fa-hand-pointer mr-1"></i> Respaldo manual
                        </h6>
                        <p class="text-muted small mb-3">
                            Genera un respaldo ahora mismo. Si ya existe un archivo del día de hoy, será reemplazado.
                        </p>
                        <button class="btn btn-primary"
                                :disabled="creando"
                                @click="crearBackup">
                            <span v-if="creando">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Generando respaldo...
                            </span>
                            <span v-else>
                                <i class="fas fa-download mr-1"></i> Crear respaldo ahora
                            </span>
                        </button>
                        <div v-if="mensajeCreacion" class="alert mt-3 mb-0 py-2 small"
                             :class="errorCreacion ? 'alert-danger' : 'alert-success'">
                            {{ mensajeCreacion }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de historial -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 font-weight-bold">
                    <i class="fas fa-history mr-1 text-primary"></i> Historial de respaldos
                </h6>
                <button class="btn btn-sm btn-outline-secondary" @click="cargarBackups">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div v-if="cargando" class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Cargando...
                </div>
                <div v-else-if="backups.length === 0" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox mr-1"></i> No hay respaldos registrados
                </div>
                <div v-else class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Archivo</th>
                                <th>Día</th>
                                <th>Tamaño</th>
                                <th>Tipo</th>
                                <th>Generado por</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in backups" :key="b.id">
                                <td>
                                    <i class="fas fa-file-code mr-1 text-muted"></i>
                                    <small>{{ b.filename }}</small>
                                </td>
                                <td class="text-capitalize">{{ b.dia_semana }}</td>
                                <td><small>{{ b.size_fmt }}</small></td>
                                <td>
                                    <span class="badge badge-pill"
                                          :class="b.tipo === 'manual' ? 'badge-primary' : 'badge-secondary'">
                                        {{ b.tipo }}
                                    </span>
                                </td>
                                <td><small>{{ b.creado_por }}</small></td>
                                <td><small>{{ b.created_at }}</small></td>
                                <td>
                                    <span v-if="b.existe" class="badge badge-success badge-pill">
                                        <i class="fas fa-check mr-1"></i>Disponible
                                    </span>
                                    <span v-else class="badge badge-warning badge-pill">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Sin archivo
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a v-if="b.existe"
                                       :href="`backups/${b.id}/download`"
                                       class="btn btn-sm btn-outline-success mr-1"
                                       title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger"
                                            title="Eliminar"
                                            @click="eliminarBackup(b)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            backups: [],
            cargando: false,
            creando: false,
            mensajeCreacion: '',
            errorCreacion: false,
        };
    },
    computed: {
        ultimoBackup() {
            return this.backups.length > 0 ? this.backups[0] : null;
        },
    },
    mounted() {
        this.cargarBackups();
    },
    methods: {
        async cargarBackups() {
            this.cargando = true;
            try {
                const res = await axios.get('api/backups');
                this.backups = res.data;
            } catch (e) {
                console.error('Error cargando backups', e);
            } finally {
                this.cargando = false;
            }
        },

        async crearBackup() {
            this.creando = true;
            this.mensajeCreacion = '';
            this.errorCreacion = false;
            try {
                const res = await axios.post('api/backups');
                this.mensajeCreacion = res.data.message + ' — ' + (res.data.backup ? res.data.backup.filename : '');
                await this.cargarBackups();
            } catch (e) {
                this.errorCreacion = true;
                this.mensajeCreacion = (e.response && e.response.data && e.response.data.message)
                    ? e.response.data.message : 'Error al crear el respaldo';
            } finally {
                this.creando = false;
            }
        },

        async eliminarBackup(b) {
            const confirm = await Swal.fire({
                title: '¿Eliminar respaldo?',
                text: 'Se eliminará ' + b.filename + ' del historial y del servidor.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            });
            if (!confirm.isConfirmed) return;

            try {
                await axios.delete('api/backups/' + b.id);
                this.backups = this.backups.filter(x => x.id !== b.id);
                Swal.fire('Eliminado', 'El respaldo fue eliminado.', 'success');
            } catch (e) {
                Swal.fire('Error', 'No se pudo eliminar el respaldo.', 'error');
            }
        },
    },
};
</script>
