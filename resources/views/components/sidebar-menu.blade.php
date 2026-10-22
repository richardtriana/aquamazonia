<!-- BOTÓN TOGGLE - Debe estar fuera del sidebar en el layout principal -->
<div class="sidebar-toggle-btn" id="sidebarToggleBtn">
    <i class="fas fa-chevron-left"></i>
</div>

<nav id="aquamazonia-sidebar" class="shadow-sm">
    <div class="sidebar-inner">

        <!-- Logo -->
        <div class="sidebar-logo mb-2">
            <img id="logo-app" src="{{ asset('img/logo-aquamazonia.jpg') }}" alt="Logo">
            <div class="sidebar-brand-text">
                <h5>{{ strtoupper(config('app.name', 'Aquamazonia')) }}</h5>
                <small>Plataforma de producción</small>
            </div>
        </div>

        @guest
            <div class="sidebar-menu-scroll">
                <ul class="nav flex-column mb-3">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Ingresar') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        @else
            <div class="sidebar-menu-scroll" id="sidebarMenuContent">

                <!-- PRINCIPAL -->
                <div class="menu-section-title">Principal</div>
                <ul class="nav flex-column" id="main-navigation">

                    <li class="nav-item">
                        <router-link class="nav-link" to="/dashboard">Panel principal</router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link" to="/siembras">Siembras</router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link d-flex align-items-center" to="/sync-feedings">
                            <span>Sincronizar alimentación</span>
                            <feeding-prelogs-badge class="ml-auto"></feeding-prelogs-badge>
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link" to="/recursos-necesarios">
                            Recursos necesarios
                        </router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link" to="/alimentacion">Alimentación</router-link>
                    </li>

                    <li class="nav-item">
                        <router-link class="nav-link" to="/calidad-agua">
                            Parámetros de agua
                        </router-link>
                    </li>

                    <!-- INFORMES -->
                    <div class="menu-section-title">Informes</div>

                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuInformes"
                           role="button"
                           aria-expanded="false"
                           aria-controls="submenuInformes">

                            <span>Informes de producción</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuInformes">
                            <ul class="nav flex-column">

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informe-costos">
                                        Informes Costos
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informes-biomasa-alimento">
                                        Informe Biomasa por Alimentación
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informe-consolidado">
                                        Informe consolidado variables de producción
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informes">
                                        Informe recursos
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informes-recursos-necesarios">
                                        Informe consolidado recursos necesarios
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informe-consolidado-alimentos">
                                        Informe consolidado alimentos
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informes-parametros">
                                        Informe parámetros de calidad del agua
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/ciclo-productivo">
                                        Informe ciclo productivo
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/informe-actividades">
                                        Informe muestreo y pesca
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/reporte-grafico-muestra">
                                        Reporte gráfico (demo)
                                    </router-link>
                                </li>

                            </ul>
                        </div>
                    </li>

                    <!-- ALMACÉN -->
                    <div class="menu-section-title">Almacén</div>

                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuAlmacen"
                           role="button"
                           aria-expanded="false">
                            <span>Gestión de almacén</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuAlmacen">
                            <ul class="nav flex-column">

                                <li class="nav-item"><router-link class="nav-link" to="/food-inventory">Alimentos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/resource-inventory">Recursos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/stock/foods">Ajustes inv. Alimentos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/stock/resources">Ajustes inv. Recursos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/purchases/foods">Compras Alimentos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/purchases/resources">Compras Recursos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/kardex/foods">Kardex Alimentos</router-link></li>

                                <li class="nav-item"><router-link class="nav-link" to="/kardex/resources">Kardex Recursos</router-link></li>

                            </ul>
                        </div>
                    </li>

                    <!-- CONFIGURACIÓN -->
                    <div class="menu-section-title">Configuración</div>

                    <!-- Config Almacén -->
                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuConfigAlmacen"
                           role="button"
                           aria-expanded="false">
                            <span>Configuración Almacén</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuConfigAlmacen">
                            <ul class="nav flex-column">
                                <li class="nav-item"><router-link class="nav-link" to="/warehouses">Bodegas</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/providers">Proveedores</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/brands">Marcas</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/food-categories">Categorías alimentos</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/resource-categories">Categorías recursos</router-link></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Config alimentos -->
                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuConfigAlimentos"
                           role="button"
                           aria-expanded="false">
                            <span>Configuración alimentos</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuConfigAlimentos">
                            <ul class="nav flex-column">
                                <li class="nav-item"><router-link class="nav-link" to="/foods">Alimentos</router-link></li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/historial-costos-alimentos">
                                        Historial Costos Alimentos
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/food-categories">
                                        Tipo de alimentos
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Config recursos -->
                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuConfigRecursos"
                           role="button"
                           aria-expanded="false">
                            <span>Configuración recursos</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuConfigRecursos">
                            <ul class="nav flex-column">
                                <li class="nav-item"><router-link class="nav-link" to="/recursos">Recursos</router-link></li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/historial-costos-recursos">
                                        Historial Costos Recursos
                                    </router-link>
                                </li>

                                <li class="nav-item">
                                    <router-link class="nav-link" to="/resource-categories">
                                        Tipo de recursos
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Config general -->
                    <li class="nav-item">
                        <a class="nav-link submenu-toggle"
                           href="#submenuConfigGeneral"
                           role="button"
                           aria-expanded="false">
                            <span>Configuración general</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuConfigGeneral">
                            <ul class="nav flex-column">
                                <li class="nav-item"><router-link class="nav-link" to="/contenedores">Estanques</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/especies">Especies</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/phases">Fases</router-link></li>
                                <li class="nav-item"><router-link class="nav-link" to="/usuarios">Usuarios</router-link></li>
                            </ul>
                        </div>
                    </li>

                    <!-- SISTEMA -->
                    <div class="menu-section-title">Sistema</div>

                    <li class="nav-item">
                        <router-link class="nav-link" to="/backup">
                            <i class="fas fa-database mr-2"></i> Respaldo BD
                        </router-link>
                    </li>

                    <!-- USUARIO -->
                    <li class="nav-item mt-3 user-section">
                        <a class="nav-link submenu-toggle"
                           href="#submenuUsuario"
                           role="button"
                           aria-expanded="false">
                            <span class="user-name">{{ Auth::user()->name }}</span>
                            <span class="chevron"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse submenu" id="submenuUsuario">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link"
                                       href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Salir
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </li>

                </ul>
            </div>
        @endguest
    </div>
</nav>

{{-- JS COMPLETO para el menú: toggle + acordeón --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    var sidebar = document.getElementById('aquamazonia-sidebar');
    var toggleBtn = document.getElementById('sidebarToggleBtn');
    var body = document.body;

    if (!sidebar || !toggleBtn) return;

    // Estado guardado
    var isCollapsed = localStorage.getItem('aquamazoniaSidebarCollapsed') === 'true';

    if (isCollapsed) collapseSidebar(); else expandSidebar();

    function collapseSidebar() {
        body.classList.add('sidebar-collapsed');
        toggleBtn.querySelector('i').className = 'fas fa-chevron-right';
        localStorage.setItem('aquamazoniaSidebarCollapsed', 'true');
        toggleBtn.style.left = '20px';
    }

    function expandSidebar() {
        body.classList.remove('sidebar-collapsed');
        toggleBtn.querySelector('i').className = 'fas fa-chevron-left';
        localStorage.setItem('aquamazoniaSidebarCollapsed', 'false');
        toggleBtn.style.left = (window.innerWidth <= 767.98 ? 230 : 260) + 'px';
    }

    toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (body.classList.contains('sidebar-collapsed')) expandSidebar();
        else collapseSidebar();
    });

    // Acordeón
    var toggles = sidebar.querySelectorAll('.submenu-toggle');
    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (!target) return;

            var isOpen = target.classList.contains('show');

            sidebar.querySelectorAll('.submenu.show').forEach(function (sub) {
                if (sub !== target) sub.classList.remove('show');
            });

            if (isOpen) target.classList.remove('show');
            else target.classList.add('show');
        });
    });

});
</script>

<style>
/* BOTÓN DE TOGGLE */
.sidebar-toggle-btn {
    position: fixed;
    left: 260px;
    top: 20px;
    z-index: 1001;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0ea5e9, #22c55e);
    border: 2px solid #ffffff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sidebar-collapsed #aquamazonia-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
}

.sidebar-collapsed .sidebar-toggle-btn {
    left: 20px !important;
}

#sidebarToggleBtn i {
    transition: transform 0.3s ease;
}

.sidebar-collapsed #sidebarToggleBtn i {
    transform: rotate(180deg);
}

/* Sidebar ancho responsivo */
#aquamazonia-sidebar {
    width: 260px;
    transition: transform 0.3s ease;
}

@media (max-width: 767.98px) {
    #aquamazonia-sidebar {
        width: 230px;
    }
}
</style>
