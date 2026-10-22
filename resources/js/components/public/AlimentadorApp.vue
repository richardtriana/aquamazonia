<template>
  <div class="registrador-app-container">
    <!-- TOP BAR COMPACTO -->
    <header class="top-bar">
      <p class="top-section-label">REGISTRO DE</p>

      <div class="top-title-row">
        <h1 class="top-title">alimentación</h1>
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
        <span class="wizard-step-label">Registrar alimentación</span>
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
        <button v-for="siembra in filteredSiembras" :key="siembra.id" class="pond-card" :class="pondCardClass(siembra.id)" @click="selectSiembra(siembra)">
          <div class="pond-name">{{ siembra.contenedor }}</div>
          <div class="pond-hint" v-if="!isPondCompleted(siembra.id)">Tocar para seleccionar</div>
          <div class="pond-status-text" v-else>Día completo</div>
        </button>

        <p v-if="!filteredSiembras.length && !siembrasLoading" class="empty-text">No se encontraron siembras con ese filtro.</p>
      </div>
    </section>

    <!-- PASO 2: REGISTRAR / HISTORIAL -->
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

      <!-- FORMULARIO -->
      <section v-if="subView === 'form'" class="registrador-card form-card">
        <h2 class="card-title">Registrar alimentación</h2>

        <div class="form-field">
          <label class="field-label">Fecha</label>
          <input type="date" class="text-input" v-model="form.fecha_ra" @input="onFechaChange" />
        </div>

        <div class="form-field">
          <label class="field-label">Alimento</label>
          <select class="text-input" v-model="form.id_alimento" @change="onAlimentoChange">
            <option v-for="al in alimentos" :key="al.id" :value="al.id">{{ al.alimento }}</option>
          </select>
          <p v-if="usoAlimentosRestringidos" class="hint-text">Mostrando sólo alimentos asignados.</p>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label class="field-label">Kg Mañana</label>
            <input ref="kgManana" type="number" step="any" class="text-input" v-model="form.cant_manana" @input="resetIdleTimer" />
          </div>

          <div class="form-field">
            <label class="field-label">Kg Tarde</label>
            <input ref="kgTarde" type="number" step="any" class="text-input" v-model="form.cant_tarde" @input="resetIdleTimer" />
          </div>
        </div>

        <button class="primary-button large" :disabled="saving" @click="guardarAlimentacion">
          <span v-if="saving">Guardando…</span>
          <span v-else>Guardar alimentación</span>
        </button>
      </section>

      <!-- HISTORIAL -->
      <section v-else class="registrador-card form-card">
        <h2 class="card-title">Historial del día</h2>
        <p class="hist-date">{{ form.fecha_ra || today }}</p>

        <div v-if="historialLoading" class="history-loading">Cargando historial…</div>

        <div v-else>
          <p v-if="!historial.length" class="empty-text">No hay registros para esta fecha.</p>

          <ul v-else class="history-list">
            <li v-for="log in historial" :key="log.id" class="history-item" @click="cargarPrelogEnFormulario(log)">
              <div class="history-main">
                <span class="history-alimento">{{ labelAlimento(log.id_alimento) }}</span>
                <span class="history-date">{{ log.fecha_ra }}</span>
              </div>
              <div class="history-sub">
                <span>Kg Mañana: <strong>{{ log.cant_manana ?? '-' }}</strong></span>
                <span>Kg Tarde: <strong>{{ log.cant_tarde ?? '-' }}</strong></span>
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
  name: "AlimentadorApp",
  props: {
    user: { type: Object, required: true }
  },
  
  data() {
    return {
      // pasos
      step: 1,
      subView: "form", // 'form' | 'history'

      // siembras
      siembras: [],
      siembrasLoading: false,
      filterText: "",

      // estado de siembras para el día
      pondStatus: {}, // { [id_siembra]: { completed, partial } }

      // selección
      selectedSiembra: null,

      // alimentos
      alimentos: [],
      usoAlimentosRestringidos: false,

      // formulario alimentación
      form: {
        fecha_ra: "",
        id_alimento: "",
        cant_manana: "",
        cant_tarde: "",
        minutos_hombre: "",
        conv_alimenticia: "",
        detalles: "",
      },
      saving: false,
      today: "",

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
      list = list.filter((s) => !this.isPondCompleted(s.id));
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

    async cargarEstadoPonds() {
      if (!this.today) this.setToday();
      try {
        const { data } = await axios.get("api/feeding-prelogs", {
          params: { fecha_ra: this.today, no_paginate: 1 }
        });
        const list = Array.isArray(data.data) ? data.data : data;
        const map = {};
        (list || []).forEach((row) => {
          const id = row.id_siembra;
          if (!id) return;
          if (!map[id]) map[id] = { items: [] };
          map[id].items.push(row);
        });

        Object.keys(map).forEach((id) => {
          const items = map[id].items;
          const hasRecords = items.length > 0;
          const allFull = hasRecords && items.every((p) => p.cant_manana && p.cant_tarde);
          const hasPartial = items.some((p) => (p.cant_manana && !p.cant_tarde) || (!p.cant_manana && p.cant_tarde));
          map[id].completed = allFull;
          map[id].partial = hasPartial;
        });

        this.pondStatus = map;
      } catch (e) {
        this.pondStatus = {};
      }
    },

    isPondCompleted(id) {
      return !!(this.pondStatus[id] && this.pondStatus[id].completed);
    },

    isPondPartial(id) {
      return !!(this.pondStatus[id] && this.pondStatus[id].partial);
    },

    pondCardClass(id) {
      return {
        "pond-card-partial": this.isPondPartial(id),
        "pond-card-completed": this.isPondCompleted(id),
      };
    },

    async selectSiembra(siembra) {
      this.selectedSiembra = siembra;
      this.step = 2;
      this.subView = "form";
      this.resetForm();
      await this.cargarAlimentosPorSiembra();
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

    // ALIMENTOS
    async cargarAlimentosPorSiembra() {
      if (!this.selectedSiembra) return;
      this.alimentos = [];
      this.usoAlimentosRestringidos = false;

      try {
        const allResp = await axios.get("api/alimentos");
        const all = allResp.data || [];

        const asigResp = await axios.get("api/recursos-siembras", {
          params: { id_siembra: this.selectedSiembra.id, tipo: "ALIMENTACION" }
        });

        const ids = asigResp.data?.alimentos || [];

        if (!ids.length) {
          this.alimentos = all;
          this.usoAlimentosRestringidos = false;
        } else {
          this.alimentos = all.filter((a) => ids.includes(a.id));
          this.usoAlimentosRestringidos = true;
        }

        this.aplicarDefaultsFormulario();
      } catch (e) {
        this.alimentos = [];
        this.usoAlimentosRestringidos = false;
      }
    },

    aplicarDefaultsFormulario() {
      this.setToday();
      this.form.fecha_ra = this.today;
      if (this.alimentos.length > 0) {
        this.form.id_alimento = this.alimentos[0].id;
        this.onAlimentoChange();
      } else {
        this.focusOnKg("kgManana");
      }
    },

    async onAlimentoChange() {
      if (!this.selectedSiembra || !this.form.id_alimento) return;
      try {
        const { data } = await axios.get("api/siembra-alimento-datos/buscar", {
          params: { siembra_id: this.selectedSiembra.id, alimento_id: this.form.id_alimento }
        });

        this.form.minutos_hombre = data.minutos_hombre !== null && data.minutos_hombre !== undefined ? data.minutos_hombre : "";
        this.form.conv_alimenticia = data.conv_alimenticia !== null && data.conv_alimenticia !== undefined ? data.conv_alimenticia : "";
        this.form.detalles = data.detalles !== null && data.detalles !== undefined ? data.detalles : "";
      } catch (e) {}

      await this.buscarRegistroExistente();
    },

    async buscarRegistroExistente() {
      if (!this.selectedSiembra || !this.form.fecha_ra || !this.form.id_alimento) return;
      try {
        const { data } = await axios.get("api/feeding-prelogs", {
          params: {
            fecha_ra: this.form.fecha_ra,
            id_siembra: this.selectedSiembra.id,
            id_alimento: this.form.id_alimento,
            registered_user_id: this.user ? this.user.id : null,
            no_paginate: 1,
          },
        });

        const list = Array.isArray(data.data) ? data.data : data;
        const row = (list || [])[0];

        if (row) {
          this.form.cant_manana = row.cant_manana !== null && row.cant_manana !== undefined ? row.cant_manana : "";
          this.form.cant_tarde = row.cant_tarde !== null && row.cant_tarde !== undefined ? row.cant_tarde : "";
          if (row.minutos_hombre !== null && row.minutos_hombre !== undefined) this.form.minutos_hombre = row.minutos_hombre;
          if (row.conv_alimenticia !== null && row.conv_alimenticia !== undefined) this.form.conv_alimenticia = row.conv_alimenticia;
          if (row.detalles !== null && row.detalles !== undefined) this.form.detalles = row.detalles;
          this.focusKGByExistingValues();
        } else {
          this.form.cant_manana = "";
          this.form.cant_tarde = "";
          this.focusOnKg("kgManana");
        }
      } catch (e) {
        this.focusOnKg("kgManana");
      }
    },

    resetForm() {
      this.form = {
        fecha_ra: this.today || "",
        id_alimento: "",
        cant_manana: "",
        cant_tarde: "",
        minutos_hombre: "",
        conv_alimenticia: "",
        detalles: "",
      };
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
        const { data } = await axios.get("api/feeding-prelogs", {
          params: { fecha_ra: this.form.fecha_ra || this.today, id_siembra: this.selectedSiembra.id, no_paginate: 1 }
        });
        const list = Array.isArray(data.data) ? data.data : data;
        this.historial = list || [];
      } catch (e) {
        this.historial = [];
      } finally {
        this.historialLoading = false;
      }
    },

    labelAlimento(id) {
      const al = this.alimentos.find((a) => a.id === id);
      return al ? al.alimento : `Alimento #${id}`;
    },

    cargarPrelogEnFormulario(log) {
      this.subView = "form";
      this.form.fecha_ra = log.fecha_ra;
      this.form.id_alimento = log.id_alimento;
      this.form.cant_manana = log.cant_manana !== null && log.cant_manana !== undefined ? log.cant_manana : "";
      this.form.cant_tarde = log.cant_tarde !== null && log.cant_tarde !== undefined ? log.cant_tarde : "";
      this.form.minutos_hombre = log.minutos_hombre !== null && log.minutos_hombre !== undefined ? log.minutos_hombre : "";
      this.form.conv_alimenticia = log.conv_alimenticia !== null && log.conv_alimenticia !== undefined ? log.conv_alimenticia : "";
      this.form.detalles = log.detalles !== null && log.detalles !== undefined ? log.detalles : "";
      this.focusKGByExistingValues();
    },

    // GUARDAR
    async guardarAlimentacion() {
      if (!this.selectedSiembra || !this.user) return;
      if (!this.form.fecha_ra || !this.form.id_alimento) return;

      this.saving = true;
      this.successMessage = "";

      try {
        const payload = {
          registered_user_id: this.user.id,
          id_siembra: this.selectedSiembra.id,
          id_alimento: this.form.id_alimento,
          fecha_ra: this.form.fecha_ra,
          cant_manana: this.form.cant_manana || null,
          cant_tarde: this.form.cant_tarde || null,
          minutos_hombre: this.form.minutos_hombre || null,
          conv_alimenticia: this.form.conv_alimenticia || null,
          detalles: this.form.detalles || null,
          tipo_actividad: 1,
          tipo: "ALIMENTACION",
          descripcion: "ALIMENTACION SIEMBRAS (app pública)",
        };

        const { data } = await axios.post("api/feeding-prelogs", payload);
        await this.cargarEstadoPonds();
        await this.cargarHistorial();
        await this.buscarRegistroExistente();
        this.backToPonds();
        this.successMessage = data.message || "Preregistro de alimentación creado correctamente.";
        this.showSuccessModal = true;
        setTimeout(() => { this.showSuccessModal = false; }, 1000);
      } catch (e) {
        this.successMessage = "No se pudo guardar, intente de nuevo.";
        this.showSuccessModal = true;
        setTimeout(() => { this.showSuccessModal = false; }, 1000);
      } finally {
        this.saving = false;
      }
    },

    closeSuccessModal() { this.showSuccessModal = false; },

    // UTILIDADES
    setToday() {
      const hoy = new Date();
      const mes = String(hoy.getMonth() + 1).padStart(2, "0");
      const dia = String(hoy.getDate()).padStart(2, "0");
      this.today = `${hoy.getFullYear()}-${mes}-${dia}`;
    },

    focusOnKg(refName) {
      this.$nextTick(() => {
        const el = this.$refs[refName];
        if (el && typeof el.focus === "function") el.focus();
      });
    },

    focusKGByExistingValues() {
      if (this.form.cant_manana && !this.form.cant_tarde) {
        this.focusOnKg("kgTarde");
      } else {
        this.focusOnKg("kgManana");
      }
    },
  },

  mounted() {
    this.setToday();
    this.cargarSiembras();
    this.cargarEstadoPonds();
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
 */

/* Mantenemos solo estilos MUY específicos si son necesarios */
/* Por ejemplo, ajustes de comportamiento único para este componente */

/* Si necesitas un estilo específico para algo que no está en el SCSS: */
/* .algo-muy-especifico-de-alimentador {
     propiedad: valor;
   } */
</style>