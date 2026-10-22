<template>
  <div class="registrador-root">
    <!-- SPINNER GLOBAL -->
    <div v-if="isLoading" class="global-spinner">
      <div class="global-spinner-inner">
        <div class="global-spinner-ring"></div>
        <div class="global-spinner-logo">AQUAMAZONIA</div>
        <div class="global-spinner-text">Cargando información, por favor espera...</div>
      </div>
    </div>

    <!-- LOGIN -->
    <div v-if="!user" class="registrador-login-container">
      <header class="registrador-header">
        <h1 class="app-title">{{ appName }}</h1>
        <p class="app-subtitle">Ingresa con tu PIN para comenzar</p>
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

        <p v-if="loginError" class="error-text">{{ loginError }}</p>
      </section>
    </div>

    <!-- APLICACIÓN SEGÚN ROL -->
    <div v-else>
      <!-- COMPONENTE ALIMENTADOR -->
      <AlimentadorApp
        v-if="user.rol === 'alimentador'"
        :user="user"
        @lock-session="lockSession"
      />

      <!-- COMPONENTE CELADOR -->
      <CeladorApp
        v-else-if="user.rol === 'celador'"
        :user="user"
        @lock-session="lockSession"
      />

      <!-- COMPONENTE PARÁMETROS -->
      <ParametrosApp
        v-else-if="user.rol === 'parametros'"
        :user="user"
        @lock-session="lockSession"
      />

      <!-- ROL NO RECONOCIDO -->
      <div v-else class="error-container">
        <div class="registrador-card">
          <h2 class="card-title">Rol no configurado</h2>
          <p class="error-text">Tu rol "{{ user.rol }}" no tiene una interfaz asignada.</p>
          <button class="primary-button" @click="lockSession">Cerrar sesión</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AlimentadorApp from './AlimentadorApp.vue';
import CeladorApp from './CeladorApp.vue';
import ParametrosApp from './ParametrosApp.vue';

export default {
  name: "RegistradorApp",
  components: {
    AlimentadorApp,
    CeladorApp,
    ParametrosApp
  },

  data() {
    return {
      appName: "AQUAMAZONIA",
      pin: "",
      loadingLogin: false,
      loginError: "",
      user: null,
      isLoading: false,
    };
  },

  computed: {
    tituloSegunRol() {
      if (!this.user) return "";
      const roles = {
        alimentador: "Registro de Alimentación",
        celador: "Registro de Calidad del Agua",
        parametros: "Registro de Parámetros Avanzados"
      };
      return roles[this.user.rol] || "Registro";
    }
  },

  methods: {
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
        
        console.log(`Usuario autenticado: ${this.user.name} (${this.user.rol})`);
        
      } catch (error) {
        console.error("Error login:", error);
        this.loginError = "PIN inválido o usuario inactivo.";
      } finally {
        this.loadingLogin = false;
      }
    },

    lockSession() {
      this.user = null;
      this.pin = "";
      this.loginError = "";
    },
  },

  created() {
    // Verificar si ya hay sesión activa en localStorage
    const savedUser = localStorage.getItem('registrador_user');
    if (savedUser) {
      try {
        this.user = JSON.parse(savedUser);
      } catch (e) {
        localStorage.removeItem('registrador_user');
      }
    }
  },

  watch: {
    user(newUser) {
      if (newUser) {
        localStorage.setItem('registrador_user', JSON.stringify(newUser));
      } else {
        localStorage.removeItem('registrador_user');
      }
    }
  }
};
</script>

<style scoped>
/* 
 * NOTA: Los estilos principales ya están en registrador-app.scss
 * Aquí solo mantenemos estilos únicos de este componente si los hay
 * 
 * Eliminamos todos los estilos duplicados que ya están en el SCSS
 */

/* Puedes mantener este archivo vacío si todos los estilos están en el SCSS */
/* O agregar solo estilos MUY específicos que no están en el SCSS */

/* Por ejemplo, si necesitas un ajuste muy específico: */
/* .registrador-root {
   ajuste-único: valor;
} */
</style>