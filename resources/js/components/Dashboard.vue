<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <!-- WIDGET BACKUP -->
                <div class="backup-widget mb-3" :class="estadoClase">
                    <div class="backup-widget-left">
                        <div class="backup-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="backup-info">
                            <div class="backup-titulo">Respaldo de base de datos</div>
                            <div v-if="cargandoBackup" class="backup-fecha">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Verificando...
                            </div>
                            <div v-else-if="ultimoBackup" class="backup-fecha">
                                <span class="backup-estado-badge" :class="estadoBadge">{{ estadoTexto }}</span>
                                Último: <strong>{{ ultimoBackup.created_at }}</strong>
                                &nbsp;·&nbsp; {{ ultimoBackup.size_fmt }}
                            </div>
                            <div v-else class="backup-fecha backup-sin-datos">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Nunca se ha realizado un respaldo
                            </div>
                            <div class="backup-aviso">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Descarga tu copia — si el servidor falla, solo tu descarga te salva
                            </div>
                        </div>
                    </div>
                    <div class="backup-actions">
                        <a v-if="ultimoBackup && ultimoBackup.existe"
                           :href="`backups/${ultimoBackup.id}/download`"
                           class="btn btn-backup-download">
                            <i class="fas fa-download mr-1"></i> Descargar mi copia
                        </a>
                        <button class="btn btn-backup-crear" :disabled="creando" @click="crearBackup">
                            <span v-if="creando"><i class="fas fa-spinner fa-spin mr-1"></i> Creando...</span>
                            <span v-else><i class="fas fa-sync-alt mr-1"></i> Crear respaldo</span>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Bienvenido a Aquamazonia</h3>
                        <h5>Panel de Administración</h5>

                    </div>

                    <div class="card-body">
                      <section class="content-dashboard">
                        <div class="card">
                          <router-link to="/siembras" class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/siembras.jpg" alt="" srcset="">
                            </div>

                            <div class="description-option bg-primary text-center">
                              <h4 class=" text-white">Siembras</h4>
                            </div>
                          </router-link>
                        </div>

                        <div class="card">
                          <router-link to="/recursos-necesarios" class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/recursos-necesarios.jpg" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                            <h4 class=" text-white">Recursos necesarios</h4>
                            </div>
                          </router-link>
                        </div>

                        <div class="card">
                          <router-link to="/alimentacion" class="box-option rounded" >
                            <div class="cover-option bg-light text-center">
                              <img src="img/alimentacion.jpg" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Alimentación</h4>
                            </div>
                          </router-link>
                        </div>

                         <div class="card">
                          <router-link to="/informes-biomasa-alimento"  class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/biomasa-alimentacion.jpg" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Biomasa por alimentacion</h4>
                            </div>
                          </router-link>
                        </div>

                        <div class="card">
                          <router-link to="/calidad-agua" class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/parametros-calidad.jpg" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Párametros de calidad</h4>
                            </div>
                          </router-link>
                        </div>

                        <div class="card">
                          <router-link to="/especies" class="box-option rounded" >
                            <div class="cover-option bg-light text-center">
                              <img src="img/especies.jpg" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                              <h4 class=" text-white">Especies</h4>
                            </div>
                          </router-link>
                        </div>

                         <div class="card">
                           <router-link to="/foods" class="box-option rounded" >
                              <div class="cover-option bg-light text-center">
                                <img src="img/alimentos.jpg" alt="" srcset="">
                              </div>
                              <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Alimentos</h4>
                              </div>
                           </router-link>
                        </div>

                         <div class="card">
                          <router-link to="/recursos"  class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/tools.png" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Recursos</h4>
                            </div>
                          </router-link>
                        </div>

                         <div class="card">
                          <router-link to="/usuarios" class="box-option rounded">
                            <div class="cover-option bg-light text-center">
                              <img src="img/user.png" alt="" srcset="">
                            </div>
                            <div class="description-option bg-primary text-center" >
                                <h4 class=" text-white">Usuarios</h4>
                            </div>
                          </router-link>
                        </div>
                        
                      </section>

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
                ultimoBackup: null,
                cargandoBackup: true,
                creando: false,
            };
        },
        computed: {
            diasDesde() {
                if (!this.ultimoBackup) return 999;
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                const fecha = new Date(this.ultimoBackup.created_at);
                fecha.setHours(0, 0, 0, 0);
                return Math.round((hoy - fecha) / 86400000);
            },
            estadoClase() {
                if (!this.ultimoBackup) return 'backup-widget--rojo';
                if (this.diasDesde === 0) return 'backup-widget--verde';
                if (this.diasDesde === 1) return 'backup-widget--amarillo';
                return 'backup-widget--rojo';
            },
            estadoBadge() {
                if (!this.ultimoBackup) return 'badge-rojo';
                if (this.diasDesde === 0) return 'badge-verde';
                if (this.diasDesde === 1) return 'badge-amarillo';
                return 'badge-rojo';
            },
            estadoTexto() {
                if (!this.ultimoBackup) return 'Sin respaldo';
                if (this.diasDesde === 0) return 'Hoy';
                if (this.diasDesde === 1) return 'Ayer';
                return 'Hace ' + this.diasDesde + ' días';
            },
        },
        mounted() {
            this.cargarUltimoBackup();
        },
        methods: {
            async cargarUltimoBackup() {
                try {
                    const res = await axios.get('api/backups');
                    this.ultimoBackup = res.data.length > 0 ? res.data[0] : null;
                } catch (e) {
                    console.error('Error backup', e);
                } finally {
                    this.cargandoBackup = false;
                }
            },
            async crearBackup() {
                this.creando = true;
                try {
                    await axios.post('api/backups');
                    await this.cargarUltimoBackup();
                } catch (e) {
                    console.error('Error creando backup', e);
                } finally {
                    this.creando = false;
                }
            },
        },
    }
</script>

<style scoped>
.backup-widget {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 10px;
    border-left: 6px solid;
}
.backup-widget--verde  { background: #f0fdf4; border-color: #22c55e; }
.backup-widget--amarillo { background: #fffbeb; border-color: #f59e0b; }
.backup-widget--rojo   { background: #fef2f2; border-color: #ef4444; }

.backup-widget-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }

.backup-icon {
    font-size: 2rem;
    opacity: .7;
}
.backup-widget--verde  .backup-icon { color: #16a34a; }
.backup-widget--amarillo .backup-icon { color: #d97706; }
.backup-widget--rojo   .backup-icon { color: #dc2626; }

.backup-titulo { font-weight: 700; font-size: .95rem; color: #1e293b; }
.backup-fecha  { font-size: .85rem; color: #475569; margin: 2px 0; }
.backup-sin-datos { color: #dc2626; font-weight: 600; }
.backup-aviso  { font-size: .78rem; color: #64748b; margin-top: 3px; }

.backup-estado-badge {
    display: inline-block;
    padding: 1px 8px;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    margin-right: 6px;
}
.badge-verde    { background: #dcfce7; color: #15803d; }
.badge-amarillo { background: #fef9c3; color: #92400e; }
.badge-rojo     { background: #fee2e2; color: #b91c1c; }

.backup-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn-backup-download {
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 8px 16px;
    font-size: .85rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    transition: background .2s;
}
.btn-backup-download:hover { background: #1d4ed8; color: #fff; text-decoration: none; }

.btn-backup-crear {
    background: #fff;
    color: #374151;
    border: 1.5px solid #d1d5db;
    border-radius: 7px;
    padding: 8px 14px;
    font-size: .85rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    transition: border-color .2s;
}
.btn-backup-crear:hover:not(:disabled) { border-color: #6b7280; }
.btn-backup-crear:disabled { opacity: .5; cursor: not-allowed; }
</style>
