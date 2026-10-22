<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}" />
    
    <!-- Estilos -->
    <link href="{{ asset('css/registrador-app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <title>Aquamazonia | Registrador</title>
    
    <style>
        /* ESTILO MINIMALISTA Y ELEGANTE */
        :root {
            --dark-bg: #020617;
            --card-bg: #0f172a;
            --accent: #34d399;
            --text: #f9fafb;
            --text-light: #94a3b8;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Loading minimalista */
        .minimal-loading {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--dark-bg);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.4s ease;
        }
        
        .minimal-loading.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .minimal-logo {
            font-size: 4rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
            animation: subtle-float 3s ease-in-out infinite;
        }
        
        @keyframes subtle-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .minimal-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .minimal-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 300;
            letter-spacing: 0.5px;
            margin-bottom: 3rem;
        }
        
        .minimal-spinner {
            width: 50px;
            height: 50px;
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 2rem;
        }
        
        .minimal-status {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 300;
        }
        
        .status-dots::after {
            content: '';
            animation: status-dots 1.5s infinite;
        }
        
        @keyframes status-dots {
            0% { content: ''; }
            33% { content: '.'; }
            66% { content: '..'; }
            100% { content: '...'; }
        }
    </style>
</head>
<body>
    <!-- Loading Minimalista -->
    <div class="minimal-loading" id="minimal-loading">
        <div class="minimal-logo">
            <i class="fas fa-tint"></i>
        </div>
        <h1 class="minimal-title">Aquamazonia</h1>
        <p class="minimal-subtitle">Registro de Parámetros</p>
        <div class="minimal-spinner"></div>
        <p class="minimal-status">
            Cargando módulo<span class="status-dots"></span>
        </p>
    </div>
    
    <!-- Contenedor Vue -->
    <div id="registrador-app"></div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/public-app.js') }}" defer></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loading = document.getElementById('minimal-loading');
            
            // Ocultar cuando Vue esté listo
            document.addEventListener('vue-mounted', function() {
                if (loading) {
                    loading.classList.add('hidden');
                    setTimeout(() => loading.remove(), 400);
                }
            });
            
            // Fallback después de 3 segundos
            setTimeout(() => {
                if (loading && !loading.classList.contains('hidden')) {
                    loading.classList.add('hidden');
                }
            }, 3000);
        });
    </script>
</body>
</html>