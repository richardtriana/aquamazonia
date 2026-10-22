<template>
  <div class="registrador-app-container">
    <!-- TOP BAR COMPACTO -->
    <header class="top-bar">
      <p class="top-section-label">REGISTRO DE</p>

      <div class="top-title-row">
        <h1 class="top-title">parámetros avanzados</h1>
        <button class="ghost-button" @click="$emit('lock-session')">
          Cerrar sesión
        </button>
      </div>

      <p class="top-user">
        <span class="top-user-name">{{ user.name }}</span>
        <span class="top-dot">•</span>
        <span class="top-role">{{ user.rol.toUpperCase() }}</span>
      </p>

      <div class="idle-bar-wrapper">
        <div class="idle-bar-bg">
          <div class="idle-bar-fg" :style="{ width: idlePercent + '%' }"></div>
        </div>
        <p class="idle-text">Se bloqueará en {{ idleRemainingSeconds }}s</p>
      </div>
    </header>

    <!-- WIZARD PASOS -->
    <section class="wizard-steps">
      <button type="button" class="wizard-step" :class="{ active: step === 1 }" @click="goToStep(1)">
        <span class="wizard-step-number">1</span>
        <span class="wizard-step-label">Escoger estanque</span>
      </button>

      <button type="button" class="wizard-step" :class="{ active: step === 2 }" :disabled="!selectedSiembra" @click="goToStep(2)">
        <span class="wizard-step-number">2</span>
        <span class="wizard-step-label">Registrar parámetros</span>
      </button>
    </section>

    <!-- PASO 1: LISTA DE ESTANQUES -->
    <section v-if="step === 1" class="step-content">
      <div class="step-header">
        <h2 class="step-title">Escoja el estanque</h2>
        <div class="step-right-info">
          <span class="badge"> {{ totalSiembras }} siembras activas </span>
        </div>
      </div>

      <div class="filter-bar">
        <label class="field-label" for="pond-filter">Filtrar estanque (código o número)</label>
        <input id="pond-filter" type="text" autocapitalize="characters" autocomplete="off" class="text-input" placeholder="Ej. T7, E17, 81…" v-model="filterText" />
      </div>

      <div class="pond-grid">
        <button v-for="siembra in filteredSiembras" :key="siembra.id" class="pond-card" @click="selectSiembra(siembra)">
          <div class="pond-name">{{ siembra.contenedor }}</div>
          <div class="pond-hint">Tocar para seleccionar</div>
        </button>

        <p v-if="!filteredSiembras.length && !siembrasLoading" class="empty-text">No se encontraron siembras con ese filtro.</p>
      </div>
    </section>

    <!-- PASO 2: REGISTRAR PARÁMETROS AVANZADOS -->
    <section v-else class="step-content">
      <div class="step-header">
        <button class="back-link" @click="backToPonds">
          <span class="back-icon">←</span>
          <span>Volver a lista de estanques</span>
        </button>
        <span v-if="selectedSiembra" class="current-pond-label">Estanque: <strong>{{ selectedSiembra.contenedor }}</strong></span>
      </div>

      <!-- SUBPANELES: FORM / HISTORIAL -->
      <div class="subtabs">
        <button type="button" class="subtab-button" :class="{ active: subView === 'form' }" @click="subView = 'form'">Registrar</button>
        <button type="button" class="subtab-button" :class="{ active: subView === 'history' }" @click="subView = 'history'; cargarHistorial()">Historial</button>
      </div>

      <!-- FORMULARIO PARÁMETROS AVANZADOS -->
      <section v-if="subView === 'form'" class="registrador-card form-card">
        <h2 class="card-title">Registrar parámetros avanzados</h2>

        <div class="form-field">
          <label class="field-label">Fecha registro</label>
          <input type="date" class="text-input" v-model="form.fecha_parametro" @change="onFechaChange" required />
        </div>

        <!-- pH con validación -->
        <div class="form-field">
          <label class="field-label">pH</label>
          <input type="number" class="text-input" v-model.number="form.ph" step="0.001" min="0" max="14" placeholder="0.000" :class="{ 'is-invalid': phError }" @blur="validarPH" />
          <div v-if="phError" class="invalid-feedback d-block">{{ phError }}</div>
          <div v-if="phSugerencias.length > 0" class="mt-1">
            <small class="text-muted">Valores válidos aproximados:</small>
            <div>
              <button v-for="sug in phSugerencias" :key="sug" type="button" class="btn btn-sm btn-outline-primary me-1 mt-1" @click="form.ph = sug">{{ sug }}</button>
            </div>
          </div>
          <small class="form-text text-muted">Hasta 3 decimales (0-14)</small>
        </div>

        <!-- Amonio -->
        <div class="form-field">
          <label class="field-label">Amonio (mg/L)</label>
          <div class="input-group">
            <input type="number" class="text-input" v-model.number="form.amonio" step="0.001" min="0" placeholder="0.000" />
            <span class="input-group-text">mg/L</span>
          </div>
          <small class="form-text text-muted">Hasta 3 decimales</small>
        </div>

        <!-- Nitrito -->
        <div class="form-field">
          <label class="field-label">Nitrito (mg/L)</label>
          <div class="input-group">
            <input type="number" class="text-input" v-model.number="form.nitrito" step="0.001" min="0" placeholder="0.000" />
            <span class="input-group-text">mg/L</span>
          </div>
          <small class="form-text text-muted">Hasta 3 decimales</small>
        </div>

        <!-- Nitrato -->
        <div class="form-field">
          <label class="field-label">Nitrato (mg/L)</label>
          <div class="input-group">
            <input type="number" class="text-input" v-model.number="form.nitrato" step="0.001" min="0" placeholder="0.000" />
            <span class="input-group-text">mg/L</span>
          </div>
          <small class="form-text text-muted">Hasta 3 decimales</small>
        </div>

        <!-- Otros -->
        <div class="form-field">
          <label class="field-label">Otros parámetros</label>
          <textarea class="text-input textarea" v-model="form.otros" rows="2" placeholder="Notas adicionales..."></textarea>
          <small class="form-text text-muted">Texto libre</small>
        </div>

        <!-- CAMPOS OCULTOS PARA TEMPERATURA Y SATURACIÓN -->
        <input type="hidden" v-model="form.temperatura" />
        <input type="hidden" v-model="form['12_am']" />
        <input type="hidden" v-model="form['7_am']" />
        <input type="hidden" v-model="form['8_pm']" />

        <button class="primary-button large" :disabled="saving" @click="guardarParametros">
          <span v-if="saving">Guardando…</span>
          <span v-else>Guardar parámetros</span>
        </button>
      </section>

      <!-- HISTORIAL -->
      <section v-else class="registrador-card form-card">
        <h2 class="card-title">Historial del día</h2>
        <p class="hist-date">{{ form.fecha_parametro || today }}</p>

        <div v-if="historialLoading" class="history-loading">Cargando historial…</div>

        <div v-else>
          <p v-if="!historial.length" class="empty-text">No hay registros para esta fecha.</p>

          <ul v-else class="history-list">
            <li v-for="log in historial" :key="log.id" class="history-item" @click="cargarRegistroEnFormulario(log)">
              <div class="history-main">
                <span class="history-alimento">Parámetros avanzados</span>
                <span class="history-date">{{ formatDate(log.fecha_parametro) }}</span>
              </div>
              <div class="history-sub">
                <span>pH: <strong>{{ log.ph ?? '-' }}</strong></span>
                <span>Amonio: <strong>{{ log.amonio ?? '-' }} mg/L</strong></span>
                <span>Nitrito: <strong>{{ log.nitrito ?? '-' }} mg/L</strong></span>
                <span>Nitrato: <strong>{{ log.nitrato ?? '-' }} mg/L</strong></span>
              </div>
              <div v-if="log.otros" class="history-otros">
                <small>Notas: {{ log.otros }}</small>
              </div>
              <span class="history-edit-hint">Tocar para editar</span>
            </li>
          </ul>
        </div>
      </section>
    </section>

    <!-- MODAL DE ÉXITO -->
    <div v-if="showSuccessModal" class="success-modal-overlay">
      <div class="success-modal-card">
        <h3 class="success-modal-title">Registro guardado</h3>
        <p class="success-modal-text">{{ successMessage }}</p>
        <button type="button" class="primary-button success-modal-button" @click="closeSuccessModal">Aceptar</button>
      </div>
    </div>
  </div>
</template>

<script>
import { filterPonds } from "./pondFilter";

export default {
  name: "ParametrosApp",
  props: {
    user: { type: Object, required: true }
  },
  
  data() {
    return {
      // pasos
      step: 1,
      subView: "form",

      // siembras
      siembras: [],
      siembrasLoading: false,
      filterText: "",

      // selección
      selectedSiembra: null,

      // formulario parámetros avanzados
      form: {
        fecha_parametro: "",
        ph: "",
        amonio: "",
        nitrito: "",
        nitrato: "",
        otros: "",
        // Campos ocultos para parámetros
        temperatura: "",
        "12_am": "",
        "7_am": "",
        "8_pm": ""
      },
      saving: false,
      today: "",

      // validación pH
      phError: '',
      phSugerencias: [],

      // historial
      historial: [],
      historialLoading: false,

      // modal de éxito
      successMessage: "",
      showSuccessModal: false,

      // inactividad
      idleSeconds: 0,
      idleMaxSeconds: 120,
      idleInterval: null,
    };
  },

  computed: {
    totalSiembras() { return this.siembras.length; },

    filteredSiembras() {
      let list = this.siembras;
      if (!this.filterText) return list;
      return filterPonds(list, this.filterText);
    },

    idlePercent() {
      const pct = Math.max(0, 100 - Math.round((this.idleSeconds / this.idleMaxSeconds) * 100));
      return isNaN(pct) ? 100 : pct;
    },

    idleRemainingSeconds() {
      const remain = this.idleMaxSeconds - this.idleSeconds;
      return remain > 0 ? remain : 0;
    },
  },

  watch: {
    'form.ph': {
      handler(nuevoValor) {
        if (nuevoValor !== null && nuevoValor !== '') {
          this.validarPH();
        }
      },
      deep: true
    }
  },

  methods: {
    // NAV PASOS
    goToStep(stepNumber) {
      if (stepNumber === 2 && !this.selectedSiembra) return;
      this.step = stepNumber;
      if (stepNumber === 2) this.subView = "form";
    },

    // INACTIVIDAD
    iniciarTimerInactividad() {
      this.limpiarTimerInactividad();
      this.idleSeconds = 0;

      window.addEventListener("mousemove", this.resetIdleTimer);
      window.addEventListener("keydown", this.resetIdleTimer);
      window.addEventListener("touchstart", this.resetIdleTimer);

      this.idleInterval = setInterval(() => {
        this.idleSeconds++;
        if (this.idleSeconds >= this.idleMaxSeconds) {
          this.$emit('lock-session');
        }
      }, 1000);
    },

    limpiarTimerInactividad() {
      if (this.idleInterval) {
        clearInterval(this.idleInterval);
        this.idleInterval = null;
      }
      window.removeEventListener("mousemove", this.resetIdleTimer);
      window.removeEventListener("keydown", this.resetIdleTimer);
      window.removeEventListener("touchstart", this.resetIdleTimer);
    },

    resetIdleTimer() { this.idleSeconds = 0; },

    // DATOS
    async cargarSiembras() {
      this.siembrasLoading = true;
      try {
        const { data } = await axios.get("api/siembras", {
          params: {
            estado_siembra: 1,
            id_siembra: -1,
            contenedor_id: -1,
            phase: -1,
            page: 1,
            type: "",
            nro_results: 200,
          },
        });

        if (data.siembra && Array.isArray(data.siembra.data)) {
          this.siembras = data.siembra.data;
        } else if (Array.isArray(data.data)) {
          this.siembras = data.data;
        } else {
          this.siembras = [];
        }
      } catch (e) {
        this.siembras = [];
      } finally {
        this.siembrasLoading = false;
      }
    },

    async selectSiembra(siembra) {
      this.selectedSiembra = siembra;
      this.step = 2;
      this.subView = "form";
      this.resetForm();
      this.iniciarTimerInactividad();
    },

    backToPonds() {
      this.step = 1;
      this.selectedSiembra = null;
      this.resetForm();
      this.subView = "form";
      this.historial = [];
      this.limpiarTimerInactividad();
    },

    // FORMULARIO
    resetForm() {
      this.setToday();
      this.form = {
        fecha_parametro: this.today,
        ph: "",
        amonio: "",
        nitrito: "",
        nitrato: "",
        otros: "",
        temperatura: "",
        "12_am": "",
        "7_am": "",
        "8_pm": ""
      };
      this.phError = '';
      this.phSugerencias = [];
    },

    // VALIDACIÓN pH
    validarPH() {
      this.phError = '';
      this.phSugerencias = [];
      
      if (this.form.ph === null || this.form.ph === '') {
        return;
      }
      
      const ph = parseFloat(this.form.ph);
      
      // Validar rango
      if (ph < 0 || ph > 14) {
        this.phError = 'El pH debe estar entre 0 y 14';
        return;
      }
      
      // Validar valores específicos (ejemplo: si está cerca de 12 o 13)
      if (ph >= 11.5 && ph <= 13.5) {
        const dif12 = Math.abs(ph - 12);
        const dif13 = Math.abs(ph - 13);
        
        if (dif12 < 0.5 || dif13 < 0.5) {
          this.phSugerencias = [12, 13];
          this.phError = 'Ingresa un valor válido. Los dos valores válidos más aproximados son 12 y 13.';
        }
      }
    },

    onFechaChange() {
      this.resetIdleTimer();
      this.cargarHistorial();
      this.buscarRegistroExistente();
    },

    // HISTORIAL
    async cargarHistorial() {
      if (!this.selectedSiembra) return;
      this.historialLoading = true;
      try {
        const { data } = await axios.get("api/parametros-agua-avanzados-prelogs", {
          params: { 
            fecha_parametro: this.form.fecha_parametro || this.today, 
            id_siembra: this.selectedSiembra.id, 
            no_paginate: 1 
          }
        });
        // Si la respuesta es un objeto con propiedad data, usa esa, sino usa la respuesta directa
        const list = data && data.data ? data.data : (Array.isArray(data) ? data : []);
        this.historial = list || [];
      } catch (e) {
        console.error("Error cargando historial:", e);
        this.historial = [];
      } finally {
        this.historialLoading = false;
      }
    },

    async buscarRegistroExistente() {
      if (!this.selectedSiembra || !this.form.fecha_parametro) return;
      try {
        const { data } = await axios.post("api/parametros-agua-avanzados-prelogs/buscar", {
          fecha_parametro: this.form.fecha_parametro,
          id_siembra: this.selectedSiembra.id,
          registered_user_id: this.user ? this.user.id : null,
        });

        const row = data;
        if (row) {
          this.form.ph = row.ph !== null ? row.ph : "";
          this.form.amonio = row.amonio !== null ? row.amonio : "";
          this.form.nitrito = row.nitrito !== null ? row.nitrito : "";
          this.form.nitrato = row.nitrato !== null ? row.nitrato : "";
          this.form.otros = row.otros !== null ? row.otros : "";
          // Campos ocultos
          this.form.temperatura = row.temperatura !== null ? row.temperatura : "";
          this.form['12_am'] = row['12_am'] !== null ? row['12_am'] : "";
          this.form['7_am'] = row['7_am'] !== null ? row['7_am'] : "";
          this.form['8_pm'] = row['8_pm'] !== null ? row['8_pm'] : "";
        }
      } catch (e) {
        // No hay registro existente
        console.log("No se encontró registro existente para esta fecha");
      }
    },

    cargarRegistroEnFormulario(log) {
      this.subView = "form";
      this.form.fecha_parametro = log.fecha_parametro;
      this.form.ph = log.ph !== null ? log.ph : "";
      this.form.amonio = log.amonio !== null ? log.amonio : "";
      this.form.nitrito = log.nitrito !== null ? log.nitrito : "";
      this.form.nitrato = log.nitrato !== null ? log.nitrato : "";
      this.form.otros = log.otros !== null ? log.otros : "";
      this.form.temperatura = log.temperatura !== null ? log.temperatura : "";
      this.form['12_am'] = log['12_am'] !== null ? log['12_am'] : "";
      this.form['7_am'] = log['7_am'] !== null ? log['7_am'] : "";
      this.form['8_pm'] = log['8_pm'] !== null ? log['8_pm'] : "";
    },

    // GUARDAR - CORREGIDO PARA ENVIAR 0 COMO 0, NO COMO NULL
    async guardarParametros() {
      // Validar pH si tiene valor
      if (this.form.ph !== null && this.form.ph !== '') {
        this.validarPH();
        if (this.phError && this.phError.includes('Ingresa un valor válido')) {
          this.$swal({
            icon: "warning",
            title: "Valor de pH no válido",
            html: `<div class="text-left">
                    <p>El valor de pH debe ser uno de los siguientes:</p>
                    <ul><li><strong>12</strong></li><li><strong>13</strong></li></ul>
                    <p>Valor ingresado: ${this.form.ph}</p>
                  </div>`,
            showCancelButton: true,
            confirmButtonText: 'Usar valor sugerido',
            cancelButtonText: 'Corregir manualmente'
          }).then((result) => {
            if (result.value && this.phSugerencias.length > 0) {
              this.form.ph = this.phSugerencias[0];
              this.guardarParametros(); // Reintentar guardar
            }
          });
          return;
        }
      }

      if (!this.selectedSiembra || !this.user) return;
      if (!this.form.fecha_parametro) return;

      this.saving = true;
      this.successMessage = "";

      try {
        // Formatear fecha y hora actual
        const now = new Date();
        const fechaRegistro = now.toISOString().split('T')[0];
        const horaRegistro = now.toTimeString().split(' ')[0].substring(0, 5);

        // FUNCIÓN AUXILIAR: Convierte valores vacíos a null, pero mantiene 0 como 0
      const prepararValor = (valor) => {
        // Si es exactamente 0 o "0", retornamos el número 0
        if (valor === 0 || valor === "0") return 0;
        
        if (valor === "" || valor === null || valor === undefined) {
          return null;
        }
        
        const num = parseFloat(valor);
        return isNaN(num) ? null : num;
      };

        const payload = {
          registered_user_id: this.user.id,
          id_siembra: this.selectedSiembra.id,
          fecha_parametro: this.form.fecha_parametro,
          ph: prepararValor(this.form.ph),
          amonio: prepararValor(this.form.amonio),
          nitrito: prepararValor(this.form.nitrito),
          nitrato: prepararValor(this.form.nitrato),
          otros: this.form.otros || null,
          temperatura: prepararValor(this.form.temperatura),
          "12_am": prepararValor(this.form['12_am']),
          "7_am": prepararValor(this.form['7_am']),
          "8_pm": prepararValor(this.form['8_pm']),
          tipo: "PARAMETROS_AVANZADOS",
          descripcion: "Parámetros avanzados de calidad del agua",
          fecha_registro: fechaRegistro,
          hora_registro: horaRegistro
        };

        const { data } = await axios.post("api/parametros-agua-avanzados-prelogs", payload);
        
        // Actualizar historial después de guardar
        await this.cargarHistorial();
        
        // Mostrar mensaje de éxito
        this.successMessage = data.message || "Parámetros avanzados registrados correctamente.";
        this.showSuccessModal = true;
        
        // Cerrar modal después de 2 segundos y volver
        setTimeout(() => {
          this.showSuccessModal = false;
          this.backToPonds();
        }, 2000);
        
      } catch (e) {
        console.error("Error guardando parámetros avanzados:", e);
        this.successMessage = "No se pudo guardar, intente de nuevo.";
        this.showSuccessModal = true;
        setTimeout(() => { this.showSuccessModal = false; }, 3000);
      } finally {
        this.saving = false;
      }
    },

    closeSuccessModal() { 
      this.showSuccessModal = false; 
    },

    // UTILIDADES
    setToday() {
      const hoy = new Date();
      const mes = String(hoy.getMonth() + 1).padStart(2, "0");
      const dia = String(hoy.getDate()).padStart(2, "0");
      this.today = `${hoy.getFullYear()}-${mes}-${dia}`;
      this.form.fecha_parametro = this.today;
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
      });
    }
  },

  mounted() {
    this.setToday();
    this.cargarSiembras();
  },

  beforeDestroy() {
    this.limpiarTimerInactividad();
  },
};
</script>

<style scoped>
/* 
 * NOTA: Los estilos principales ya están en registrador-app.scss
 * Eliminamos todos los estilos duplicados que ya están en el SCSS
 * 
 * Solo mantenemos el comentario de que los estilos están en el SCSS
 */

/* Los estilos de .input-group, .input-group-text, .form-text, etc.
   ya están definidos en registrador-app.scss */
</style>