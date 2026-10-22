/**
 * Aplicación Vue para registradores públicos (separada del admin)
 */

require('./bootstrap');

// Vue 2 - Importación tradicional
import Vue from 'vue';

// Importar componentes específicos
import RegistradorApp from './components/public/RegistradorApp.vue';

// Importar SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Registrar componentes globalmente si es necesario
Vue.component('registrador-app', RegistradorApp);
Vue.component('alimentador-app', () => import('./components/public/AlimentadorApp.vue'));
Vue.component('celador-app', () => import('./components/public/CeladorApp.vue'));
Vue.component('parametros-app', () => import('./components/public/ParametrosApp.vue'));

// Configurar Vue 2
const app = new Vue({
    el: '#registrador-app',
    
    // Template para Vue 2
    template: '<registrador-app />',
    
    mounted() {
        console.log('✅ Aplicación pública de registrador montada');
        
        // Ocultar loading
        const loading = document.getElementById('app-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    },
    
    // Opcional: Manejar errores
    errorCaptured(err, vm, info) {
        console.error('Error en Vue:', err);
        console.error('Componente:', vm);
        console.error('Info:', info);
        
        // Mostrar error al usuario
        Swal.fire({
            icon: 'error',
            title: 'Error en la aplicación',
            text: 'Ocurrió un error al cargar la aplicación. Recarga la página.',
        });
        
        return false;
    }
});

// Hacer app disponible globalmente (opcional)
window.app = app;

console.log('🚀 Vue app inicializada correctamente');