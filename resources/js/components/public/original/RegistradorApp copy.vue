<template>
  <div class="registrador-root">
    <!-- SPINNER GLOBAL PARA ESTA PÁGINA -->
    <div class="container">
      <div v-if="isLoading" class="global-spinner">
        <div class="global-spinner-inner">
          <div class="global-spinner-ring"></div>
          <div class="global-spinner-logo">
            AQUAMAZONIA
          </div>
          <div class="global-spinner-text">
            Cargando información, por favor espera...
          </div>
        </div>
      </div>
    </div>

    <!-- LOGIN -->
    <div v-if="!user" class="registrador-login-container">
      <header class="registrador-header">
        <h1 class="app-title">{{ appName }}</h1>
        <p class="app-subtitle">
          Ingresa con tu PIN para comenzar
        </p>
      </header>

      <section class="registrador-card login-card">
        <h2 class="card-title">Ingresar con PIN</h2>

        <label class="field-label" for="pin-input">PIN de registrador</label>
        <input
          id="pin-input"
          type="password"
          inputmode="numeric"
          pattern="[0-9]*"
          maxlength="6"
          class="text-input large"
          v-model="pin"
          @keyup.enter="loginWithPin"
        />

        <button
          class="primary-button large"
          :disabled="loadingLogin || !pin"
          @click="loginWithPin"
        >
          <span v-if="loadingLogin">Validando…</span>
          <span v-else>Ingresar</span>
        </button>

        <p v-if="loginError" class="error-text">
          {{ loginError }}
        </p>
      </section>
    </div>

    <!-- APP AUTENTICADA -->
    <div v-else class="registrador-app-container">
      <!-- TOP BAR COMPACTO -->
      <header class="top-bar">
        <p class="top-section-label">REGISTRO DE</p>

        <div class="top-title-row">
          <h1 class="top-title">alimentación</h1>
          <button class="ghost-button" @click="lockSession">
            Cerrar sesión
          </button>
        </div>

        <p class="top-user">
          <span class="top-user-name">{{ user.name }}</span>
          <span class="top-dot">•</span>
          <span class="top-role">
            {{ user.rol ? user.rol.toUpperCase() : 'ALIMENTADOR' }}
          </span>
        </p>

        <div class="idle-bar-wrapper">
          <div class="idle-bar-bg">
            <div
              class="idle-bar-fg"
              :style="{ width: idlePercent + '%' }"
            ></div>
          </div>
          <p class="idle-text">
            Se bloqueará en {{ idleRemainingSeconds }}s
          </p>
        </div>
      </header>

      <!-- WIZARD PASOS -->
      <section class="wizard-steps">
        <button
          type="button"
          class="wizard-step"
          :class="{ active: step === 1 }"
          @click="goToStep(1)"
        >
          <span class="wizard-step-number">1</span>
          <span class="wizard-step-label">Escoger estanque</span>
        </button>

        <button
          type="button"
          class="wizard-step"
          :class="{ active: step === 2 }"
          :disabled="!selectedSiembra"
          @click="goToStep(2)"
        >
          <span class="wizard-step-number">2</span>
          <span class="wizard-step-label">Registrar alimentación</span>
        </button>
      </section>

      <!-- PASO 1: LISTA DE ESTANQUES -->
      <section
        v-if="step === 1"
        class="step-content"
      >
        <div class="step-header">
          <h2 class="step-title">Escoja el estanque</h2>
          <div class="step-right-info">
            <span class="badge"> {{ totalSiembras }} siembras activas </span>
          </div>
        </div>

        <div class="filter-bar">
          <label class="field-label" for="pond-filter">
            Filtrar estanque (número)
          </label>
          <input
            id="pond-filter"
            type="tel"
            inputmode="numeric"
            pattern="[0-9]*"
            class="text-input"
            placeholder="Ej. 5, 10, 81…"
            v-model="filterText"
          />
        </div>

        <div class="pond-grid">
          <button
            v-for="siembra in filteredSiembras"
            :key="siembra.id"
            class="pond-card"
            :class="pondCardClass(siembra.id)"
            @click="selectSiembra(siembra)"
          >
            <div class="pond-name">
              {{ siembra.contenedor }}
            </div>
            <div class="pond-hint" v-if="!isPondCompleted(siembra.id)">
              Tocar para seleccionar
            </div>
            <div class="pond-status-text" v-else>
              Día completo
            </div>
          </button>

          <p
            v-if="!filteredSiembras.length && !siembrasLoading"
            class="empty-text"
          >
            No se encontraron siembras con ese filtro.
          </p>
        </div>
      </section>

      <!-- PASO 2: REGISTRAR / HISTORIAL -->
      <section
        v-else
        class="step-content"
      >
        <div class="step-header">
          <button class="back-link" @click="backToPonds">
            <span class="back-icon">←</span>
            <span>Volver a lista de estanques</span>
          </button>
          <span v-if="selectedSiembra" class="current-pond-label">
            Estanque: <strong>{{ selectedSiembra.contenedor }}</strong>
          </span>
        </div>

        <!-- SUBPANELES: FORM / HISTORIAL -->
        <div class="subtabs">
          <button
            type="button"
            class="subtab-button"
            :class="{ active: subView === 'form' }"
            @click="subView = 'form'"
          >
            Registrar
          </button>
          <button
            type="button"
            class="subtab-button"
            :class="{ active: subView === 'history' }"
            @click="subView = 'history'; cargarHistorial()"
          >
            Historial
          </button>
        </div>

        <!-- FORMULARIO -->
        <section
          v-if="subView === 'form'"
          class="registrador-card form-card"
        >
          <h2 class="card-title">Registrar alimentación</h2>

          <div class="form-field">
            <label class="field-label">Fecha</label>
            <input
              type="date"
              class="text-input"
              v-model="form.fecha_ra"
              @input="onFechaChange"
            />
          </div>

          <div class="form-field">
            <label class="field-label">Alimento</label>
            <select
              class="text-input"
              v-model="form.id_alimento"
              @change="onAlimentoChange"
            >
              <option
                v-for="al in alimentos"
                :key="al.id"
                :value="al.id"
              >
                {{ al.alimento }}
              </option>
            </select>
            <p
              v-if="usoAlimentosRestringidos"
              class="hint-text"
            >
              Mostrando sólo alimentos asignados.
            </p>
          </div>

          <div class="form-row">
            <div class="form-field">
              <label class="field-label">Kg Mañana</label>
              <input
                ref="kgManana"
                type="number"
                step="any"
                class="text-input"
                v-model="form.cant_manana"
                @input="resetIdleTimer"
              />
            </div>

            <div class="form-field">
              <label class="field-label">Kg Tarde</label>
              <input
                ref="kgTarde"
                type="number"
                step="any"
                class="text-input"
                v-model="form.cant_tarde"
                @input="resetIdleTimer"
              />
            </div>
          </div>

          <!-- Minutos hombre / Conv / Detalles se mantienen en el modelo, pero ocultos -->

          <button
            class="primary-button large"
            :disabled="saving"
            @click="guardarAlimentacion"
          >
            <span v-if="saving">Guardando…</span>
            <span v-else>Guardar alimentación</span>
          </button>
        </section>

        <!-- HISTORIAL -->
        <section
          v-else
          class="registrador-card form-card"
        >
          <h2 class="card-title">Historial del día</h2>
          <p class="hist-date">
            {{ form.fecha_ra || today }}
          </p>

          <div v-if="historialLoading" class="history-loading">
            Cargando historial…
          </div>

          <div v-else>
            <p
              v-if="!historial.length"
              class="empty-text"
            >
              No hay registros para esta fecha.
            </p>

            <ul v-else class="history-list">
              <li
                v-for="log in historial"
                :key="log.id"
                class="history-item"
                @click="cargarPrelogEnFormulario(log)"
              >
                <div class="history-main">
                  <span class="history-alimento">
                    {{ labelAlimento(log.id_alimento) }}
                  </span>
                  <span class="history-date">
                    {{ log.fecha_ra }}
                  </span>
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
    </div>

    <!-- MODAL DE ÉXITO -->
    <div
      v-if="showSuccessModal"
      class="success-modal-overlay"
    >
      <div class="success-modal-card">
        <h3 class="success-modal-title">Registro guardado</h3>
        <p class="success-modal-text">
          {{ successMessage }}
        </p>
        <button
          type="button"
          class="primary-button success-modal-button"
          @click="closeSuccessModal"
        >
          Aceptar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "RegistradorApp",

  data() {
    return {
      appName: "AQUAMAZONIA",

      // auth
      pin: "",
      loadingLogin: false,
      loginError: "",
      user: null,

      // spinner global
      isLoading: false,

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
      saveMessage: "",
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
    totalSiembras() {
      return this.siembras.length;
    },

    filteredSiembras() {
      let list = this.siembras;

      // quitar estanques completados (mañana+tarde para todos sus registros)
      list = list.filter((s) => !this.isPondCompleted(s.id));

      if (!this.filterText) return list;

      const t = this.filterText.replace(/\D/g, "").trim();
      if (!t) return list;

      return list.filter((s) => {
        const digits = String(s.contenedor || "").replace(/\D/g, "");
        return digits.includes(t);
      });
    },

    idlePercent() {
      const pct = Math.max(
        0,
        100 - Math.round((this.idleSeconds / this.idleMaxSeconds) * 100)
      );
      return isNaN(pct) ? 100 : pct;
    },

    idleRemainingSeconds() {
      const remain = this.idleMaxSeconds - this.idleSeconds;
      return remain > 0 ? remain : 0;
    },

    idleCircumference() {
      const r = 52;
      return 2 * Math.PI * r;
    },

    idleDashOffset() {
      const pct = this.idlePercent / 100;
      return this.idleCircumference * (1 - pct);
    },
  },

  methods: {
    // NAV PASOS
    goToStep(stepNumber) {
      if (stepNumber === 2 && !this.selectedSiembra) return;
      this.step = stepNumber;
      if (stepNumber === 2) {
        this.subView = "form";
      }
    },

    // LOGIN
    async loginWithPin() {
      if (!this.pin) return;

      this.loadingLogin = true;
      this.loginError = "";

      try {
        const { data } = await axios.post("api/registered-users/login-pin", {
          pin: this.pin,
        });

        this.user = data.user;
        this.pin = "";
        this.step = 1;

        await this.cargarSiembras();
        await this.cargarEstadoPonds();
        this.iniciarTimerInactividad();
      } catch (error) {
        this.loginError = "PIN inválido o usuario inactivo.";
      } finally {
        this.loadingLogin = false;
      }
    },

    // SESIÓN / INACTIVIDAD
    iniciarTimerInactividad() {
      this.limpiarTimerInactividad();
      this.idleSeconds = 0;

      window.addEventListener("mousemove", this.resetIdleTimer);
      window.addEventListener("keydown", this.resetIdleTimer);
      window.addEventListener("touchstart", this.resetIdleTimer);

      this.idleInterval = setInterval(() => {
        this.idleSeconds++;
        if (this.idleSeconds >= this.idleMaxSeconds) {
          this.lockSession();
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

    resetIdleTimer() {
      this.idleSeconds = 0;
    },

    lockSession() {
      this.limpiarTimerInactividad();
      this.user = null;
      this.pin = "";
      this.loginError = "";
      this.step = 1;
      this.selectedSiembra = null;
      this.resetForm();
      this.pondStatus = {};
      this.historial = [];
      this.saveMessage = "";
      this.showSuccessModal = false;
    },

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
          params: {
            fecha_ra: this.today,
            no_paginate: 1,
          },
        });

        const list = Array.isArray(data.data) ? data.data : data;

        const map = {};
        (list || []).forEach((row) => {
          const id = row.id_siembra;
          if (!id) return;
          if (!map[id]) {
            map[id] = { items: [] };
          }
          map[id].items.push(row);
        });

        Object.keys(map).forEach((id) => {
          const items = map[id].items;
          const hasRecords = items.length > 0;

          const allFull =
            hasRecords &&
            items.every(
              (p) => p.cant_manana && p.cant_tarde
            );

          const hasPartial = items.some(
            (p) =>
              (p.cant_manana && !p.cant_tarde) ||
              (!p.cant_manana && p.cant_tarde)
          );

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
      this.saveMessage = "";
      await this.cargarAlimentosPorSiembra();
    },

    backToPonds() {
      this.step = 1;
      this.selectedSiembra = null;
      this.resetForm();
      this.subView = "form";
      this.historial = [];
      this.saveMessage = "";
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
          params: {
            id_siembra: this.selectedSiembra.id,
            tipo: "ALIMENTACION",
          },
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
        const { data } = await axios.get(
          "api/siembra-alimento-datos/buscar",
          {
            params: {
              siembra_id: this.selectedSiembra.id,
              alimento_id: this.form.id_alimento,
            },
          }
        );

        this.form.minutos_hombre =
          data.minutos_hombre !== null && data.minutos_hombre !== undefined
            ? data.minutos_hombre
            : "";

        this.form.conv_alimenticia =
          data.conv_alimenticia !== null &&
          data.conv_alimenticia !== undefined
            ? data.conv_alimenticia
            : "";

        this.form.detalles =
          data.detalles !== null && data.detalles !== undefined
            ? data.detalles
            : "";
      } catch (e) {
        // sin configuración previa
      }

      // buscar si ya hay un preregistro hoy para este alimento
      await this.buscarRegistroExistente();
    },

    async buscarRegistroExistente() {
      if (
        !this.selectedSiembra ||
        !this.form.fecha_ra ||
        !this.form.id_alimento
      ) {
        return;
      }

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
          // modo edición: un registro por día + alimento
          this.form.cant_manana =
            row.cant_manana !== null && row.cant_manana !== undefined
              ? row.cant_manana
              : "";
          this.form.cant_tarde =
            row.cant_tarde !== null && row.cant_tarde !== undefined
              ? row.cant_tarde
              : "";

          if (row.minutos_hombre !== null && row.minutos_hombre !== undefined) {
            this.form.minutos_hombre = row.minutos_hombre;
          }
          if (
            row.conv_alimenticia !== null &&
            row.conv_alimenticia !== undefined
          ) {
            this.form.conv_alimenticia = row.conv_alimenticia;
          }
          if (row.detalles !== null && row.detalles !== undefined) {
            this.form.detalles = row.detalles;
          }

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
          params: {
            fecha_ra: this.form.fecha_ra || this.today,
            id_siembra: this.selectedSiembra.id,
            no_paginate: 1,
          },
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
      this.form.cant_manana =
        log.cant_manana !== null && log.cant_manana !== undefined
          ? log.cant_manana
          : "";
      this.form.cant_tarde =
        log.cant_tarde !== null && log.cant_tarde !== undefined
          ? log.cant_tarde
          : "";
      this.form.minutos_hombre =
        log.minutos_hombre !== null && log.minutos_hombre !== undefined
          ? log.minutos_hombre
          : "";
      this.form.conv_alimenticia =
        log.conv_alimenticia !== null && log.conv_alimenticia !== undefined
          ? log.conv_alimenticia
          : "";
      this.form.detalles =
        log.detalles !== null && log.detalles !== undefined
          ? log.detalles
          : "";

      this.focusKGByExistingValues();
    },

    // GUARDAR ALIMENTACIÓN
    async guardarAlimentacion() {
      if (!this.selectedSiembra || !this.user) return;
      if (!this.form.fecha_ra || !this.form.id_alimento) return;

      this.saving = true;
      this.saveMessage = "";
      this.successMessage = "";
      this.isLoading = true;

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

        // refrescamos estados y listas
        await this.cargarEstadoPonds();
        await this.cargarHistorial();
        await this.buscarRegistroExistente();

        // volvemos a la pantalla de estanques
        this.backToPonds();

        // mensaje de éxito
        this.successMessage =
          data.message || "Preregistro de alimentación creado correctamente.";
        this.showSuccessModal = true;

        // cerrar modal automáticamente en 1 segundo
        setTimeout(() => {
          this.showSuccessModal = false;
        }, 1000);
      } catch (e) {
        this.successMessage = "No se pudo guardar, intente de nuevo.";
        this.showSuccessModal = true;
        setTimeout(() => {
          this.showSuccessModal = false;
        }, 1000);
      } finally {
        this.saving = false;
        this.isLoading = false;
      }
    },

    // modal éxito (por si el usuario toca el botón antes del timeout)
    closeSuccessModal() {
      this.showSuccessModal = false;
    },

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
        if (el && typeof el.focus === "function") {
          el.focus();
        }
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

  created() {
    this.setToday();
  },

  beforeDestroy() {
    this.limpiarTimerInactividad();
  },
};
</script>

<style scoped>
/* layout general */
.registrador-root {
  min-height: 100vh;
  background: radial-gradient(circle at top, #081326, #020617);
  color: #e5e7eb;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
    sans-serif;
}

/* LOGIN */
.registrador-login-container {
  max-width: 480px;
  margin: 0 auto;
  padding: 3rem 1.5rem 4rem;
}

.registrador-header {
  text-align: center;
  margin-bottom: 2rem;
}

.app-title {
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: 0.18em;
  color: #f9fafb;
}

.app-subtitle {
  margin-top: 0.75rem;
  font-size: 0.98rem;
  color: rgba(229, 231, 235, 0.85);
}

.registrador-card {
  background: radial-gradient(circle at top, #0f172a, #020617);
  border-radius: 1.2rem;
  padding: 1.9rem 1.7rem 2.2rem;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.24);
}

.login-card {
  text-align: center;
}

.card-title {
  font-size: 1.16rem;
  font-weight: 600;
  margin-bottom: 1.6rem;
  color: #e5e7eb;
}

.field-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.35rem;
  color: rgba(229, 231, 235, 0.85);
}

.text-input {
  width: 100%;
  border-radius: 0.9rem;
  border: 1px solid rgba(148, 163, 184, 0.55);
  background: rgba(15, 23, 42, 0.9);
  color: #f9fafb;
  padding: 0.55rem 0.9rem;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.18s ease, box-shadow 0.18s ease,
    background-color 0.18s ease;
}

.text-input.large {
  padding: 0.85rem 1.1rem;
  font-size: 1.1rem;
  text-align: center;
}

.text-input:focus {
  border-color: #34d399;
  box-shadow: 0 0 0 1px rgba(52, 211, 153, 0.6);
  background: rgba(15, 23, 42, 1);
}

.textarea {
  resize: vertical;
  min-height: 64px;
}

/* botones */
.primary-button {
  width: 100%;
  margin-top: 1.4rem;
  border: none;
  border-radius: 999px;
  background: linear-gradient(135deg, #22c55e, #14b8a6);
  color: #022c22;
  font-weight: 600;
  padding: 0.7rem 1.2rem;
  font-size: 0.98rem;
  cursor: pointer;
  box-shadow: 0 14px 35px rgba(34, 197, 94, 0.45);
  transition: transform 0.12s ease, box-shadow 0.12s ease,
    filter 0.12s ease;
}

.primary-button.large {
  padding: 0.95rem 1.4rem;
  font-size: 1.02rem;
}

.primary-button:hover:not(:disabled) {
  transform: translateY(-1px);
  filter: brightness(1.05);
  box-shadow: 0 18px 40px rgba(34, 197, 94, 0.6);
}

.primary-button:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  box-shadow: none;
}

/* mensajes */
.error-text {
  margin-top: 0.9rem;
  font-size: 0.85rem;
  color: #fb7185;
}

/* APP AUTENTICADA */
.registrador-app-container {
  max-width: 960px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
}

/* top bar compacto */
.top-bar {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-bottom: 0.9rem;
}

.top-section-label {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: rgba(148, 163, 184, 0.95);
}

.top-title-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.top-title {
  flex: 1;
  font-size: 1.4rem;
  font-weight: 700;
  color: #f9fafb;
  text-align: center;
}

.top-user {
  margin-top: 0.1rem;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.4rem;
  color: rgba(229, 231, 235, 0.85);
}

.top-user-name {
  font-weight: 500;
}

.top-dot {
  opacity: 0.8;
}

.top-role {
  color: #34d399;
  font-weight: 600;
}

/* botón cerrar sesión */
.ghost-button {
  border-radius: 999px;
  padding: 0.45rem 1.2rem;
  border: 1px solid #fb923c;
  background: rgba(15, 23, 42, 0.95);
  color: #fed7aa;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s ease, color 0.15s ease,
    border-color 0.15s ease, transform 0.1s ease;
}

.ghost-button:hover {
  background: rgba(248, 250, 252, 0.04);
  border-color: #fdba74;
  transform: translateY(-1px);
}

/* barra de inactividad */
.idle-bar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.15rem;
}

.idle-bar-bg {
  width: 150px;
  height: 7px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.9);
  border: 1px solid rgba(148, 163, 184, 0.6);
  overflow: hidden;
}

.idle-bar-fg {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #22c55e, #14b8a6);
}

.idle-text {
  font-size: 0.75rem;
  color: rgba(229, 231, 235, 0.8);
}

/* wizard */
.wizard-steps {
  display: flex;
  gap: 0.6rem;
  margin-bottom: 1.2rem;
}

.wizard-step {
  flex: 1;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.5);
  background: rgba(15, 23, 42, 0.9);
  color: rgba(229, 231, 235, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.4rem 0.1rem;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease,
    border-color 0.15s ease, transform 0.1s ease;
}

.wizard-step-number {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  border: 1px solid rgba(148, 163, 184, 0.7);
}

.wizard-step-label {
  white-space: nowrap;
}

.wizard-step.active {
  background: linear-gradient(135deg, #22c55e, #14b8a6);
  border-color: #22c55e;
  color: #022c22;
}

.wizard-step.active .wizard-step-number {
  border-color: #022c22;
  background: rgba(2, 44, 34, 0.15);
}

.wizard-step:disabled {
  opacity: 0.5;
  cursor: default;
}

/* contenido pasos */
.step-content {
  margin-top: 0.5rem;
}

.step-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.9rem;
}

.step-title {
  font-size: 1rem;
  font-weight: 600;
  color: #e5e7eb;
}

.step-right-info {
  font-size: 0.85rem;
  color: rgba(229, 231, 235, 0.8);
}

.badge {
  padding: 0.2rem 0.7rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.6);
}

/* filtro */
.filter-bar {
  margin-bottom: 0.7rem;
}

/* grid estanques */
.pond-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1.8rem;
}

.pond-card {
  border-radius: 1rem;
  border: none;
  background: #f9fafb;
  color: #020617;
  padding: 1.1rem 0.9rem;
  box-shadow: 0 15px 30px rgba(15, 23, 42, 0.4);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}

.pond-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.6);
}

.pond-card-partial {
  background: #fef3c7;
  color: #92400e;
}

.pond-card-completed {
  background: #dcfce7;
  color: #14532d;
}

.pond-name {
  font-size: 1.35rem;
  font-weight: 700;
}

.pond-hint {
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.pond-status-text {
  margin-top: 0.25rem;
  font-size: 0.85rem;
}

.empty-text {
  font-size: 0.9rem;
  color: rgba(148, 163, 184, 0.9);
}

/* paso 2 */
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  border-radius: 999px;
  border: 1px solid #fb923c;
  background: rgba(251, 146, 60, 0.18);
  color: #ffedd5;
  font-size: 0.86rem;
  padding: 0.32rem 0.95rem;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease,
    color 0.15s ease, transform 0.1s ease;
}

.back-icon {
  font-size: 1rem;
}

.back-link:hover {
  background: rgba(251, 146, 60, 0.32);
  border-color: #fdba74;
  transform: translateY(-1px);
}

.current-pond-label {
  font-size: 0.9rem;
  color: rgba(229, 231, 235, 0.9);
}

.form-card {
  margin-top: 0.9rem;
}

.form-field {
  margin-bottom: 0.9rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.8rem;
  margin-bottom: 0.4rem;
}

.hint-text {
  font-size: 0.8rem;
  color: rgba(148, 163, 184, 0.95);
  margin-top: 0.35rem;
}

/* subtabs */
.subtabs {
  display: inline-flex;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.5);
  overflow: hidden;
  margin-bottom: 0.7rem;
}

.subtab-button {
  border: none;
  background: transparent;
  color: rgba(229, 231, 235, 0.75);
  padding: 0.35rem 0.95rem;
  font-size: 0.86rem;
  cursor: pointer;
}

.subtab-button.active {
  background: rgba(34, 197, 94, 0.14);
  color: #bbf7d0;
}

/* historial */
.hist-date {
  font-size: 0.86rem;
  color: rgba(148, 163, 184, 0.95);
  margin-bottom: 0.7rem;
}

.history-loading {
  font-size: 0.9rem;
  color: rgba(229, 231, 235, 0.85);
}

.history-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 0.6rem;
}

.history-item {
  border-radius: 0.9rem;
  border: 1px solid rgba(148, 163, 184, 0.5);
  background: rgba(15, 23, 42, 0.9);
  padding: 0.65rem 0.8rem;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.history-item:hover {
  background: rgba(30, 64, 175, 0.35);
  border-color: rgba(129, 140, 248, 0.8);
}

.history-main {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  margin-bottom: 0.2rem;
}

.history-alimento {
  font-weight: 600;
}

.history-date {
  color: rgba(148, 163, 184, 0.95);
}

.history-sub {
  display: flex;
  flex-wrap: wrap;
  gap: 0.7rem;
  font-size: 0.8rem;
  color: rgba(229, 231, 235, 0.88);
}

.history-edit-hint {
  display: block;
  margin-top: 0.2rem;
  font-size: 0.78rem;
  color: rgba(191, 219, 254, 0.95);
}

/* modal de éxito */
.success-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.success-modal-card {
  width: 100%;
  max-width: 320px;
  padding: 1.6rem 1.5rem 1.9rem;
  border-radius: 1.2rem;
  background: radial-gradient(circle at top, #0f172a, #020617);
  border: 1px solid rgba(34, 197, 94, 0.6);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.75);
  text-align: center;
}

.success-modal-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #bbf7d0;
  margin-bottom: 0.5rem;
}

.success-modal-text {
  font-size: 0.9rem;
  color: rgba(229, 231, 235, 0.9);
  margin-bottom: 1.1rem;
}

.success-modal-button {
  width: 100%;
}

/* responsive */
@media (max-width: 640px) {
  .registrador-app-container {
    padding-top: 1rem;
  }

  .top-title-row {
    align-items: flex-start;
  }

  .wizard-step-label {
    font-size: 0.8rem;
  }
}
</style>
