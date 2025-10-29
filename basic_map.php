<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TelecomMap Pro - Sistema de Geolocalización</title>
    <link rel="icon" type="image/x-icon" href="include/ico/bms.ico">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066cc;
            --secondary-color: #00a8ff;
            --accent-color: #ff6b35;
            --success-color: #00d4aa;
            --warning-color: #ffa726;
            --error-color: #f44336;
            --dark-bg: #0a0e27;
            --card-bg: #1a1f3a;
            --text-primary: #ffffff;
            --text-secondary: #b0b3c7;
            --border-color: #2d3748;
            --gradient-primary: linear-gradient(135deg, #0066cc 0%, #00a8ff 100%);
            --gradient-secondary: linear-gradient(135deg, #1a1f3a 0%, #2d3748 100%);
            --shadow-primary: 0 20px 40px rgba(0, 102, 204, 0.3);
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--dark-bg);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(0, 102, 204, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 168, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 107, 53, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Top Navigation Bar */
        .navbar {
            background: var(--gradient-secondary);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-card);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .navbar-brand .logo {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .navbar-brand h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .stat-item {
            text-align: center;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        .header h2 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .main-content {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Control Panel */
        .control-panel {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .control-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .control-panel h3 {
            color: var(--text-primary);
            margin-bottom: 25px;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .control-panel h3 i {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        /* Input Tabs */
        .input-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 5px;
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .tab-btn.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
        }

        .tab-btn i {
            font-size: 12px;
        }

        /* Input Modes */
        .input-mode {
            display: none;
        }

        .input-mode.active {
            display: block;
        }

        .urls-textarea {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }

        .urls-textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.2);
        }

        .urls-textarea::placeholder {
            color: var(--text-secondary);
        }

        .input-group {
            margin-bottom: 25px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .url-input {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }

        .url-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.2);
        }

        .url-input::placeholder {
            color: var(--text-secondary);
        }

        .btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-primary);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            margin-top: 12px;
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        /* Map Container */
        .map-container {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .map-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .map-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .map-header h3 {
            color: var(--text-primary);
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .map-header h3 i {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        .map-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .map-control-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .map-control-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--secondary-color);
        }

        .map-control-btn.active {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--secondary-color);
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
        }

        #map {
            height: 600px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
        }

        /* Info Panel */
        .info-panel {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .info-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .info-panel h3 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-panel h3 i {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .coordinates-display {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            font-family: 'Inter', monospace;
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .coordinates-display strong {
            color: var(--secondary-color);
            font-weight: 600;
        }

        .status-message {
            padding: 18px 20px;
            border-radius: 12px;
            margin-top: 20px;
            font-weight: 500;
            display: none;
            border: 1px solid;
            backdrop-filter: blur(10px);
        }

        .status-success {
            background: rgba(0, 212, 170, 0.1);
            color: var(--success-color);
            border-color: var(--success-color);
        }

        .status-error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border-color: var(--error-color);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 25px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-top: 20px;
        }

        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--secondary-color);
            border-radius: 50%;
            width: 35px;
            height: 35px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .marker-count {
            background: var(--gradient-primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.3);
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .navbar-stats {
                justify-content: center;
            }
            
            .header h2 {
                font-size: 1.8rem;
            }
            
            .container {
                padding: 15px;
            }
            
            #map {
                height: 400px;
            }
            
            .control-panel {
                padding: 20px;
            }
        }

        /* Custom marker styles */
        .custom-marker {
            background: none !important;
            border: none !important;
        }

        /* Leaflet popup customization */
        .leaflet-popup-content-wrapper {
            background: var(--card-bg) !important;
            color: var(--text-primary) !important;
            border-radius: 12px !important;
            border: 1px solid var(--border-color) !important;
        }

        .leaflet-popup-tip {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
        }

        /* Telecom lines styling */
        .telecom-line {
            animation: pulse-line 2s ease-in-out infinite;
        }

        @keyframes pulse-line {
            0%, 100% { 
                opacity: 0.8; 
            }
            50% { 
                opacity: 1; 
            }
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(20px);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Search Panel */
        .search-panel {
            background: var(--gradient-secondary);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(20px);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .search-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .search-panel h3 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-panel h3 i {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .search-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.2);
        }

        .search-input::placeholder {
            color: var(--text-secondary);
        }

        .search-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            min-width: 50px;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.4);
        }

        .search-results {
            max-height: 200px;
            overflow-y: auto;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
        }

        .search-result-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-primary);
        }

        .search-result-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--secondary-color);
        }

        .search-result-address {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="navbar-brand">
                <div class="logo">
                    <i class="fas fa-satellite-dish"></i>
                </div>
                <h1>TelecomMap Pro</h1>
            </div>
            <div class="navbar-stats">
                <div class="stat-item">
                    <div class="stat-value" id="totalMarkers">0</div>
                    <div class="stat-label">Marcadores</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="systemStatus">Online</div>
                    <div class="stat-label">Estado</div>
                </div>
            </div>
        </nav>

        <!-- Header Section -->
        <div class="header">
            <h2><i class="fas fa-map-marked-alt"></i> Sistema de Geolocalización</h2>
            <p>Plataforma avanzada para el mapeo y análisis de ubicaciones de infraestructura de telecomunicaciones</p>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Control Panel -->
            <div class="control-panel">
                <h3><i class="fas fa-cogs"></i> Panel de Control</h3>
                
                <!-- Pestañas para diferentes modos de entrada -->
                <div class="input-tabs">
                    <button class="tab-btn active" onclick="switchTab('single')">
                        <i class="fas fa-map-pin"></i> Individual
                    </button>
                    <button class="tab-btn" onclick="switchTab('multiple')">
                        <i class="fas fa-layer-group"></i> Múltiple
                    </button>
                </div>

                <!-- Modo Individual -->
                <div id="single-input" class="input-mode active">
                    <div class="input-group">
                        <label for="urlInput">URL de Google Maps</label>
                        <input 
                            type="url" 
                            id="urlInput" 
                            class="url-input" 
                            placeholder="Ingresa la URL de Google Maps para geolocalizar..."
                            value="https://www.google.com/maps/place/FiberPRO/@-11.8819253,-77.0343507,17z/data=!3m1!4b1!4m6!3m5!1s0x9105d1caa600f1c5:0xd6a634c0a5245ab3!8m2!3d-11.8819253!4d-77.0343507!16s%2Fg%2F11df2k15k_?entry=ttu&g_ep=EgoyMDI1MDkwOS4wIKXMDSoASAFQAw%3D%3D"
                        >
                    </div>
                </div>

                <!-- Modo Múltiple -->
                <div id="multiple-input" class="input-mode">
                    <div class="input-group">
                        <label for="urlsInput">URLs de Google Maps (una por línea)</label>
                        <textarea 
                            id="urlsInput" 
                            class="urls-textarea" 
                            placeholder="Pega aquí múltiples URLs de Google Maps, una por línea..."
                            rows="6"
                        ></textarea>
                    </div>
                </div>

                <button id="markBtn" class="btn" onclick="marcarUbicacion()">
                    <i class="fas fa-map-pin"></i>
                    Geolocalizar
                </button>

                <button id="clearBtn" class="btn btn-secondary" onclick="limpiarMapa()">
                    <i class="fas fa-trash-alt"></i>
                    Limpiar Mapa
                </button>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Procesando geolocalización...</p>
                </div>

                <div id="statusMessage" class="status-message"></div>
            </div>

            <!-- Map Container -->
            <div class="map-container">
                <div class="map-header">
                    <h3><i class="fas fa-globe-americas"></i> Mapa de Infraestructura</h3>
                    <div class="map-controls">
                        <button class="map-control-btn" onclick="centrarMapa()">
                            <i class="fas fa-crosshairs"></i> Centrar
                        </button>
                        <button class="map-control-btn" id="toggleLinesBtn" onclick="toggleLines()">
                            <i class="fas fa-route"></i> Líneas
                        </button>
                        <button class="map-control-btn" id="measureBtn" onclick="toggleMeasure()">
                            <i class="fas fa-ruler"></i> Medir
                        </button>
                        <button class="map-control-btn" onclick="toggleLayers()">
                            <i class="fas fa-layer-group"></i> Capas
                        </button>
                        <button class="map-control-btn" onclick="exportarDatos()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <button class="map-control-btn" onclick="importarDatos()">
                            <i class="fas fa-upload"></i> Importar
                        </button>
                        <button class="map-control-btn" onclick="mostrarFormatoJSON()">
                            <i class="fas fa-question-circle"></i> Ayuda
                        </button>
                    </div>
                </div>
                <div id="map"></div>
            </div>
        </div>

        <!-- Input oculto para importar archivos -->
        <input type="file" id="fileInput" accept=".json" style="display: none;" onchange="procesarArchivoImportado(event)">

        <!-- Statistics Panel -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalMarkersStat">0</div>
                    <div class="stat-label">Puntos de Infraestructura</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalConnectionsStat">0</div>
                    <div class="stat-label">Conexiones</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-ruler"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalDistanceStat">0.00</div>
                    <div class="stat-label">Distancia Total (km)</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="sessionTimeStat">00:00</div>
                    <div class="stat-label">Tiempo de Sesión</div>
                </div>
            </div>
        </div>

        <!-- Search Panel -->
        <div class="search-panel">
            <h3><i class="fas fa-search"></i> Búsqueda de Ubicaciones</h3>
            <div class="search-controls">
                <input 
                    type="text" 
                    id="searchInput" 
                    class="search-input" 
                    placeholder="Buscar dirección, lugar o coordenadas..."
                >
                <button class="search-btn" onclick="buscarUbicacion()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div id="searchResults" class="search-results"></div>
        </div>

        <!-- Information Panel -->
        <div class="info-panel">
            <h3><i class="fas fa-database"></i> Información de Geolocalización</h3>
            <div id="coordinatesDisplay" class="coordinates-display">
                <strong>Sistema:</strong> TelecomMap Pro v2.0<br>
                <strong>Estado:</strong> Esperando coordenadas...<br>
                <strong>Última actualización:</strong> <span id="lastUpdate">--</span>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Variables globales
        let map;
        let markers = [];
        let markerCount = 0;
        let lastUpdateTime = new Date();
        let currentMode = 'single';
        let polylines = [];
        let showLines = false;
        let measureMode = false;
        let measureLayer = null;
        let measureMarkers = [];
        let measureLines = [];
        let sessionStartTime = new Date();
        let currentLayer = 'osm';
        let layers = {};

        // Inicializar mapa
        function initMap() {
            map = L.map('map').setView([-11.8819253, -77.0343507], 16);

            // Definir capas de mapa
            layers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

            layers.satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: '© <a href="https://www.esri.com/">Esri</a>'
            });

            layers.terrain = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17,
                attribution: '© <a href="https://opentopomap.org/">OpenTopoMap</a>'
            });

            // Agregar capa por defecto
            layers.osm.addTo(map);
            currentLayer = 'osm';

            // Agregar control de escala
            L.control.scale().addTo(map);

            // Inicializar capa de medición
            measureLayer = L.layerGroup().addTo(map);

            // Actualizar tiempo inicial
            actualizarTiempo();
            actualizarEstadisticas();
            iniciarTemporizadorSesion();
        }

        // Función para actualizar el tiempo
        function actualizarTiempo() {
            const now = new Date();
            const timeString = now.toLocaleString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const lastUpdateElement = document.getElementById('lastUpdate');
            if (lastUpdateElement) {
                lastUpdateElement.textContent = timeString;
            }
        }

        // Función para cambiar entre pestañas
        function switchTab(mode) {
            currentMode = mode;
            
            // Actualizar botones de pestaña
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Mostrar/ocultar modos de entrada
            document.querySelectorAll('.input-mode').forEach(mode => {
                mode.classList.remove('active');
            });
            document.getElementById(mode + '-input').classList.add('active');
            
            // Actualizar texto del botón
            const markBtn = document.getElementById('markBtn');
            if (markBtn) {
                if (mode === 'single') {
                    markBtn.innerHTML = '<i class="fas fa-map-pin"></i> Geolocalizar';
                } else {
                    markBtn.innerHTML = '<i class="fas fa-layer-group"></i> Geolocalizar Múltiple';
                }
            }
        }

        // Función para extraer coordenadas de diferentes formatos de URL
        function extraerCoordenadas(url) {
            const patterns = [
                // Formato: @lat,lon
                /@(-?\d+\.\d+),(-?\d+\.\d+)/,
                // Formato: !3dlat!4dlon
                /!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,
                // Formato: 3dlat!4dlon (sin ! inicial)
                /3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,
                // Formato: q=lat,lon
                /[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/,
                // Formato: ll=lat,lon
                /[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/
            ];

            for (let pattern of patterns) {
                const match = url.match(pattern);
                if (match) {
                    return {
                        lat: parseFloat(match[1]),
                        lon: parseFloat(match[2])
                    };
                }
            }

            return null;
        }

        // Función para mostrar mensaje de estado
        function mostrarMensaje(mensaje, tipo = 'success') {
            const statusDiv = document.getElementById('statusMessage');
            if (statusDiv) {
                statusDiv.textContent = mensaje;
                statusDiv.className = `status-message status-${tipo}`;
                statusDiv.style.display = 'block';

                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 5000);
            }
        }

        // Función para mostrar/ocultar loading
        function toggleLoading(mostrar) {
            const loadingElement = document.getElementById('loading');
            const markBtnElement = document.getElementById('markBtn');
            
            if (loadingElement) {
                loadingElement.style.display = mostrar ? 'block' : 'none';
            }
            if (markBtnElement) {
                markBtnElement.disabled = mostrar;
            }
        }

        // Función para actualizar contador de marcadores
        function actualizarContador() {
            const countElement = document.getElementById('totalMarkers');
            if (countElement) {
                countElement.textContent = markerCount;
            }
        }

        // Función para actualizar información de coordenadas
        function actualizarInfoCoordenadas(lat, lon) {
            const infoDiv = document.getElementById('coordinatesDisplay');
            if (infoDiv) {
                const now = new Date();
                const timeString = now.toLocaleString('es-ES');
                
                infoDiv.innerHTML = `
                    <strong>Sistema:</strong> TelecomMap Pro v2.0<br>
                    <strong>Estado:</strong> Activo - ${markerCount} ubicaciones<br>
                    <strong>Última ubicación:</strong> ${lat}, ${lon}<br>
                    <strong>Última actualización:</strong> ${timeString}
                `;
            }
        }

        // Función principal para marcar ubicación
        function marcarUbicacion() {
            if (currentMode === 'single') {
                marcarUbicacionIndividual();
            } else {
                marcarUbicacionesMultiples();
            }
        }

        // Función para marcar una ubicación individual
        function marcarUbicacionIndividual() {
            const urlInput = document.getElementById('urlInput');
            const url = urlInput.value.trim();

            if (!url) {
                mostrarMensaje('Por favor, ingresa una URL de Google Maps', 'error');
                return;
            }

            toggleLoading(true);

            setTimeout(() => {
                try {
                    const coordenadas = extraerCoordenadas(url);
                    
                    if (!coordenadas) {
                        mostrarMensaje('No se pudieron extraer coordenadas de la URL. Verifica que sea una URL válida de Google Maps.', 'error');
                        toggleLoading(false);
                        return;
                    }

                    crearMarcador(coordenadas.lat, coordenadas.lon);

                    // Centrar mapa en la nueva ubicación
                    map.setView([coordenadas.lat, coordenadas.lon], 16);

            // Actualizar UI
            actualizarContador();
            actualizarInfoCoordenadas(coordenadas.lat, coordenadas.lon);
            actualizarTiempo();
            actualizarEstadisticas();
            mostrarMensaje(`✅ Punto de infraestructura geolocalizado exitosamente! (${coordenadas.lat}, ${coordenadas.lon})`, 'success');

                    // Limpiar input
                    urlInput.value = '';

                } catch (error) {
                    console.error('Error al procesar la URL:', error);
                    mostrarMensaje('Error al procesar la URL. Intenta con otra URL.', 'error');
                } finally {
                    toggleLoading(false);
                }
            }, 1000);
        }

        // Función para marcar múltiples ubicaciones
        function marcarUbicacionesMultiples() {
            const urlsInput = document.getElementById('urlsInput');
            const urlsText = urlsInput.value.trim();

            if (!urlsText) {
                mostrarMensaje('Por favor, ingresa al menos una URL de Google Maps', 'error');
                return;
            }

            const urls = urlsText.split('\n').filter(url => url.trim() !== '');
            
            if (urls.length === 0) {
                mostrarMensaje('No se encontraron URLs válidas', 'error');
                return;
            }

            toggleLoading(true);
            mostrarMensaje(`🔄 Procesando ${urls.length} ubicaciones...`, 'success');

            let processedCount = 0;
            let successCount = 0;
            let errorCount = 0;
            const coordenadasValidas = [];

            // Procesar cada URL
            urls.forEach((url, index) => {
                setTimeout(() => {
                    try {
                        const coordenadas = extraerCoordenadas(url.trim());
                        
                        if (coordenadas) {
                            coordenadasValidas.push(coordenadas);
                            successCount++;
                        } else {
                            errorCount++;
                        }
                        
                        processedCount++;
                        
                        // Si es la última URL, crear todos los marcadores
                        if (processedCount === urls.length) {
                            if (coordenadasValidas.length > 0) {
                                // Crear marcadores
                                coordenadasValidas.forEach((coord, idx) => {
                                    setTimeout(() => {
                                        crearMarcador(coord.lat, coord.lon);
                                        
                                        // Si es el último marcador, actualizar UI
                                        if (idx === coordenadasValidas.length - 1) {
                                            // Centrar mapa para mostrar todos los marcadores
                                            if (markers.length > 0) {
                                                const group = new L.featureGroup(markers);
                                                map.fitBounds(group.getBounds().pad(0.1));
                                            }
                                            
                                            actualizarContador();
                                            actualizarTiempo();
                                            actualizarEstadisticas();
                                            
                                            let mensaje = `✅ Procesamiento completado: ${successCount} ubicaciones marcadas`;
                                            if (errorCount > 0) {
                                                mensaje += `, ${errorCount} errores`;
                                            }
                                            mostrarMensaje(mensaje, 'success');
                                            
                                            // Limpiar textarea
                                            urlsInput.value = '';
                                        }
                                    }, idx * 200); // Pequeño delay entre marcadores para efecto visual
                                });
                            } else {
                                mostrarMensaje('❌ No se pudieron procesar ninguna de las URLs', 'error');
                            }
                            
                            toggleLoading(false);
                        }
                    } catch (error) {
                        console.error('Error al procesar URL:', url, error);
                        errorCount++;
                        processedCount++;
                        
                        if (processedCount === urls.length) {
                            toggleLoading(false);
                            mostrarMensaje(`❌ Error al procesar las URLs. ${errorCount} errores encontrados.`, 'error');
                        }
                    }
                }, index * 100); // Pequeño delay entre procesamiento de URLs
            });
        }

        // Función para crear un marcador
        function crearMarcador(lat, lon) {
            // Crear marcador personalizado con icono de telecomunicaciones
            const marker = L.marker([lat, lon], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="
                        background: linear-gradient(135deg, #0066cc 0%, #00a8ff 100%);
                        border: 2px solid #ffffff;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.4);
                    ">
                        <i class="fas fa-satellite-dish" style="color: white; font-size: 14px;"></i>
                    </div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                })
            }).addTo(map);

            // Agregar popup informativo con estilo telecom
            marker.bindPopup(`
                <div style="text-align: center; font-family: 'Inter', sans-serif;">
                    <h4 style="margin: 0 0 15px 0; color: #ffffff; font-size: 16px; font-weight: 600;">
                        <i class="fas fa-satellite-dish" style="color: #00a8ff; margin-right: 8px;"></i>
                        Punto de Infraestructura ${markerCount + 1}
                    </h4>
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                        <p style="margin: 5px 0; font-size: 13px; color: #b0b3c7;">
                            <strong style="color: #00a8ff;">Latitud:</strong> ${lat}
                        </p>
                        <p style="margin: 5px 0; font-size: 13px; color: #b0b3c7;">
                            <strong style="color: #00a8ff;">Longitud:</strong> ${lon}
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 11px; color: #8a8d9f;">
                            TelecomMap Pro v2.0
                        </p>
                    </div>
                </div>
            `);

            // Agregar a la lista de marcadores
            markers.push(marker);
            markerCount++;

            // Actualizar líneas si están activas
            if (showLines) {
                actualizarLineas();
            }
            
            // Actualizar estadísticas
            actualizarEstadisticas();
        }

        // Función para alternar la visualización de líneas
        function toggleLines() {
            showLines = !showLines;
            const toggleBtn = document.getElementById('toggleLinesBtn');
            
            if (showLines) {
                toggleBtn.classList.add('active');
                toggleBtn.innerHTML = '<i class="fas fa-route"></i> Ocultar Líneas';
                actualizarLineas();
                mostrarMensaje('🔗 Líneas de conexión activadas', 'success');
            } else {
                toggleBtn.classList.remove('active');
                toggleBtn.innerHTML = '<i class="fas fa-route"></i> Líneas';
                limpiarLineas();
                mostrarMensaje('🔗 Líneas de conexión desactivadas', 'success');
            }
        }

        // Función para actualizar las líneas entre marcadores
        function actualizarLineas() {
            // Limpiar líneas existentes
            limpiarLineas();
            
            if (markers.length < 2) {
                return;
            }

            // Crear líneas entre todos los marcadores consecutivos
            for (let i = 0; i < markers.length - 1; i++) {
                const startPoint = markers[i].getLatLng();
                const endPoint = markers[i + 1].getLatLng();
                
                const polyline = L.polyline([startPoint, endPoint], {
                    color: '#00a8ff',
                    weight: 3,
                    opacity: 0.8,
                    dashArray: '10, 10',
                    className: 'telecom-line'
                }).addTo(map);

                // Agregar popup a la línea
                polyline.bindPopup(`
                    <div style="text-align: center; font-family: 'Inter', sans-serif;">
                        <h4 style="margin: 0 0 10px 0; color: #ffffff; font-size: 14px; font-weight: 600;">
                            <i class="fas fa-route" style="color: #00a8ff; margin-right: 8px;"></i>
                            Conexión ${i + 1} → ${i + 2}
                        </h4>
                        <div style="background: rgba(255, 255, 255, 0.1); padding: 8px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <p style="margin: 3px 0; font-size: 12px; color: #b0b3c7;">
                                <strong style="color: #00a8ff;">Distancia:</strong> ${calcularDistancia(startPoint, endPoint).toFixed(2)} km
                            </p>
                            <p style="margin: 3px 0; font-size: 12px; color: #b0b3c7;">
                                <strong style="color: #00a8ff;">Tipo:</strong> Fibra Óptica
                            </p>
                        </div>
                    </div>
                `);

                polylines.push(polyline);
            }
        }

        // Función para limpiar todas las líneas
        function limpiarLineas() {
            polylines.forEach(polyline => map.removeLayer(polyline));
            polylines = [];
        }

        // Función para calcular distancia entre dos puntos (fórmula de Haversine)
        function calcularDistancia(point1, point2) {
            const R = 6371; // Radio de la Tierra en km
            const dLat = (point2.lat - point1.lat) * Math.PI / 180;
            const dLon = (point2.lng - point1.lng) * Math.PI / 180;
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(point1.lat * Math.PI / 180) * Math.cos(point2.lat * Math.PI / 180) * 
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Función para alternar modo de medición
        function toggleMeasure() {
            measureMode = !measureMode;
            const measureBtn = document.getElementById('measureBtn');
            
            if (measureMode) {
                measureBtn.classList.add('active');
                measureBtn.innerHTML = '<i class="fas fa-ruler"></i> Cancelar Medición';
                map.dragging.disable();
                map.doubleClickZoom.disable();
                map.on('click', onMapClickMeasure);
                mostrarMensaje('📏 Modo de medición activado. Haz clic en el mapa para medir distancias.', 'success');
            } else {
                measureBtn.classList.remove('active');
                measureBtn.innerHTML = '<i class="fas fa-ruler"></i> Medir';
                map.dragging.enable();
                map.doubleClickZoom.enable();
                map.off('click', onMapClickMeasure);
                
                // Limpiar capa y variables de medición
                measureLayer.clearLayers();
                measureMarkers = [];
                measureLines = [];
                
                mostrarMensaje('📏 Modo de medición desactivado', 'success');
            }
        }

        // Función para manejar clics en modo medición
        function onMapClickMeasure(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            // Crear marcador de medición
            const measureMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'measure-marker',
                    html: `<div style="
                        background: linear-gradient(135deg, #ff6b35 0%, #ffa726 100%);
                        border: 2px solid #ffffff;
                        border-radius: 50%;
                        width: 20px;
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.4);
                    "></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            }).addTo(measureLayer);

            // Agregar a la lista de marcadores de medición
            measureMarkers.push(measureMarker);

            // Si hay más de un punto, crear línea de medición
            if (measureMarkers.length > 1) {
                const prevMarker = measureMarkers[measureMarkers.length - 2];
                const currentMarker = measureMarkers[measureMarkers.length - 1];
                
                const distance = calcularDistancia(prevMarker.getLatLng(), currentMarker.getLatLng());
                
                const measureLine = L.polyline([prevMarker.getLatLng(), currentMarker.getLatLng()], {
                    color: '#ff6b35',
                    weight: 2,
                    opacity: 0.8,
                    dashArray: '5, 5'
                }).addTo(measureLayer);

                measureLine.bindPopup(`
                    <div style="text-align: center; font-family: 'Inter', sans-serif;">
                        <h4 style="margin: 0 0 10px 0; color: #ffffff; font-size: 14px; font-weight: 600;">
                            <i class="fas fa-ruler" style="color: #ff6b35; margin-right: 8px;"></i>
                            Medición
                        </h4>
                        <div style="background: rgba(255, 255, 255, 0.1); padding: 8px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.2);">
                            <p style="margin: 3px 0; font-size: 12px; color: #b0b3c7;">
                                <strong style="color: #ff6b35;">Distancia:</strong> ${distance.toFixed(2)} km
                            </p>
                        </div>
                    </div>
                `);

                // Agregar a la lista de líneas de medición
                measureLines.push(measureLine);
            }
        }

        // Función para alternar capas de mapa
        function toggleLayers() {
            const layerNames = {
                'osm': 'OpenStreetMap',
                'satellite': 'Satélite',
                'terrain': 'Terreno'
            };
            
            const layerKeys = Object.keys(layerNames);
            const currentIndex = layerKeys.indexOf(currentLayer);
            const nextIndex = (currentIndex + 1) % layerKeys.length;
            const nextLayer = layerKeys[nextIndex];
            
            // Remover capa actual
            map.removeLayer(layers[currentLayer]);
            
            // Agregar nueva capa
            layers[nextLayer].addTo(map);
            currentLayer = nextLayer;
            
            mostrarMensaje(`🗺️ Capa cambiada a: ${layerNames[nextLayer]}`, 'success');
        }

        // Función para buscar ubicaciones
        function buscarUbicacion() {
            const searchInput = document.getElementById('searchInput');
            const query = searchInput.value.trim();
            
            if (!query) {
                mostrarMensaje('Por favor, ingresa un término de búsqueda', 'error');
                return;
            }

            // Simular búsqueda (en una implementación real usarías un servicio de geocodificación)
            const searchResults = document.getElementById('searchResults');
            searchResults.innerHTML = '<div class="search-result-item">Buscando...</div>';
            
            setTimeout(() => {
                // Resultados simulados
                const resultados = [
                    {
                        title: 'Lima, Perú',
                        address: 'Lima, Lima, Perú',
                        lat: -12.0464,
                        lng: -77.0428
                    },
                    {
                        title: 'Aeropuerto Jorge Chávez',
                        address: 'Callao, Perú',
                        lat: -12.0219,
                        lng: -77.1143
                    }
                ];
                
                searchResults.innerHTML = '';
                resultados.forEach(result => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <div class="search-result-title">${result.title}</div>
                        <div class="search-result-address">${result.address}</div>
                    `;
                    resultItem.onclick = () => {
                        map.setView([result.lat, result.lng], 15);
                        searchResults.innerHTML = '';
                        searchInput.value = '';
                        mostrarMensaje(`📍 Ubicación encontrada: ${result.title}`, 'success');
                    };
                    searchResults.appendChild(resultItem);
                });
            }, 1000);
        }

        // Función para actualizar estadísticas
        function actualizarEstadisticas() {
            // Actualizar contador de marcadores
            const totalMarkersElement = document.getElementById('totalMarkersStat');
            if (totalMarkersElement) {
                totalMarkersElement.textContent = markerCount;
            }
            
            // Actualizar contador de conexiones
            const totalConnectionsElement = document.getElementById('totalConnectionsStat');
            if (totalConnectionsElement) {
                totalConnectionsElement.textContent = polylines.length;
            }
            
            // Calcular distancia total
            let totalDistance = 0;
            for (let i = 0; i < markers.length - 1; i++) {
                totalDistance += calcularDistancia(markers[i].getLatLng(), markers[i + 1].getLatLng());
            }
            
            const totalDistanceElement = document.getElementById('totalDistanceStat');
            if (totalDistanceElement) {
                totalDistanceElement.textContent = totalDistance.toFixed(2);
            }
        }

        // Función para iniciar temporizador de sesión
        function iniciarTemporizadorSesion() {
            setInterval(() => {
                const now = new Date();
                const diff = now - sessionStartTime;
                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                
                const sessionTimeElement = document.getElementById('sessionTimeStat');
                if (sessionTimeElement) {
                    sessionTimeElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }

        // Función para limpiar todos los marcadores
        function limpiarMapa() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            markerCount = 0;
            
            // Limpiar también las líneas
            limpiarLineas();
            
            // Resetear estado de líneas
            showLines = false;
            const toggleBtn = document.getElementById('toggleLinesBtn');
            if (toggleBtn) {
                toggleBtn.classList.remove('active');
                toggleBtn.innerHTML = '<i class="fas fa-route"></i> Líneas';
            }
            
            actualizarContador();
            actualizarTiempo();
            const coordinatesDisplay = document.getElementById('coordinatesDisplay');
            if (coordinatesDisplay) {
                coordinatesDisplay.innerHTML = `
                    <strong>Sistema:</strong> TelecomMap Pro v2.0<br>
                    <strong>Estado:</strong> Esperando coordenadas...<br>
                    <strong>Última actualización:</strong> <span id="lastUpdate">${new Date().toLocaleString('es-ES')}</span>
                `;
            }
            mostrarMensaje('🗑️ Mapa de infraestructura limpiado exitosamente', 'success');
        }

        // Función para centrar el mapa
        function centrarMapa() {
            if (markers.length > 0) {
                const group = new L.featureGroup([...markers, ...polylines]);
                map.fitBounds(group.getBounds().pad(0.1));
            } else {
                map.setView([-11.8819253, -77.0343507], 16);
            }
        }

        // Función para exportar datos
        function exportarDatos() {
            if (markers.length === 0) {
                mostrarMensaje('No hay datos para exportar', 'error');
                return;
            }

            const data = markers.map((marker, index) => ({
                id: index + 1,
                latitud: marker.getLatLng().lat,
                longitud: marker.getLatLng().lng,
                timestamp: new Date().toISOString()
            }));

            const jsonData = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonData], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `telecom-infrastructure-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            mostrarMensaje('📊 Datos de infraestructura exportados exitosamente', 'success');
        }

        // Función para importar datos
        function importarDatos() {
            const fileInput = document.getElementById('fileInput');
            fileInput.click();
        }

        // Función para procesar archivo importado
        function procesarArchivoImportado(event) {
            const file = event.target.files[0];
            
            if (!file) {
                return;
            }

            if (!file.name.toLowerCase().endsWith('.json')) {
                mostrarMensaje('Por favor, selecciona un archivo JSON válido', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    
                    if (!Array.isArray(data)) {
                        mostrarMensaje('El archivo JSON debe contener un array de ubicaciones', 'error');
                        return;
                    }

                    if (data.length === 0) {
                        mostrarMensaje('El archivo no contiene ubicaciones válidas', 'error');
                        return;
                    }

                    // Limpiar mapa actual
                    limpiarMapa();

                    // Procesar cada ubicación
                    let successCount = 0;
                    let errorCount = 0;

                    data.forEach((item, index) => {
                        try {
                            if (item.latitud && item.longitud) {
                                const lat = parseFloat(item.latitud);
                                const lng = parseFloat(item.longitud);
                                
                                if (!isNaN(lat) && !isNaN(lng)) {
                                    // Crear marcador con delay para efecto visual
                                    setTimeout(() => {
                                        crearMarcador(lat, lng);
                                        
                                        // Si es el último elemento, actualizar UI
                                        if (index === data.length - 1) {
                                            // Centrar mapa para mostrar todos los marcadores
                                            if (markers.length > 0) {
                                                const group = new L.featureGroup(markers);
                                                map.fitBounds(group.getBounds().pad(0.1));
                                            }
                                            
                                            actualizarContador();
                                            actualizarTiempo();
                                            actualizarEstadisticas();
                                            
                                            let mensaje = `✅ Importación completada: ${successCount} ubicaciones cargadas`;
                                            if (errorCount > 0) {
                                                mensaje += `, ${errorCount} errores`;
                                            }
                                            mostrarMensaje(mensaje, 'success');
                                        }
                                    }, index * 200);
                                    
                                    successCount++;
                                } else {
                                    errorCount++;
                                }
                            } else {
                                errorCount++;
                            }
                        } catch (error) {
                            console.error('Error al procesar ubicación:', item, error);
                            errorCount++;
                        }
                    });

                    if (successCount === 0) {
                        mostrarMensaje('No se pudieron cargar ubicaciones válidas del archivo', 'error');
                    }

                } catch (error) {
                    console.error('Error al parsear JSON:', error);
                    mostrarMensaje('Error al leer el archivo JSON. Verifica que el formato sea correcto.', 'error');
                }
            };

            reader.onerror = function() {
                mostrarMensaje('Error al leer el archivo', 'error');
            };

            reader.readAsText(file);
            
            // Limpiar el input para permitir seleccionar el mismo archivo nuevamente
            event.target.value = '';
        }

        // Función para mostrar formato JSON de ejemplo
        function mostrarFormatoJSON() {
            const ejemploJSON = [
                {
                    "id": 1,
                    "latitud": -11.8819253,
                    "longitud": -77.0343507,
                    "timestamp": "2024-01-15T10:30:00.000Z"
                },
                {
                    "id": 2,
                    "latitud": -11.830783,
                    "longitud": -77.0705067,
                    "timestamp": "2024-01-15T10:35:00.000Z"
                },
                {
                    "id": 3,
                    "latitud": -11.8301485,
                    "longitud": -77.0735133,
                    "timestamp": "2024-01-15T10:40:00.000Z"
                }
            ];

            const jsonString = JSON.stringify(ejemploJSON, null, 2);
            
            // Crear ventana modal o alert con el formato
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;

            const content = document.createElement('div');
            content.style.cssText = `
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 15px;
                padding: 30px;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                color: var(--text-primary);
                font-family: 'Inter', sans-serif;
            `;

            content.innerHTML = `
                <h3 style="color: var(--secondary-color); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-file-code"></i> Formato JSON para Importación
                </h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                    El archivo JSON debe contener un array de objetos con las siguientes propiedades:
                </p>
                <div style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <pre style="color: var(--text-primary); font-family: 'Courier New', monospace; font-size: 12px; white-space: pre-wrap; margin: 0;">${jsonString}</pre>
                </div>
                <div style="background: rgba(0, 168, 255, 0.1); padding: 15px; border-radius: 10px; border: 1px solid var(--secondary-color); margin-bottom: 20px;">
                    <h4 style="color: var(--secondary-color); margin: 0 0 10px 0;">📋 Propiedades requeridas:</h4>
                    <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary);">
                        <li><strong>id:</strong> Identificador único (número)</li>
                        <li><strong>latitud:</strong> Coordenada de latitud (número decimal)</li>
                        <li><strong>longitud:</strong> Coordenada de longitud (número decimal)</li>
                        <li><strong>timestamp:</strong> Fecha y hora (opcional)</li>
                    </ul>
                </div>
                <button onclick="this.closest('.modal').remove()" style="
                    background: var(--gradient-primary);
                    color: white;
                    border: none;
                    padding: 12px 24px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: 600;
                    width: 100%;
                ">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            `;

            modal.className = 'modal';
            modal.appendChild(content);
            document.body.appendChild(modal);

            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }

        // Permitir marcar con Enter en ambos modos
        document.getElementById('urlInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                marcarUbicacion();
            }
        });

        document.getElementById('urlsInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                marcarUbicacion();
            }
        });

        // Event listener para búsqueda con Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarUbicacion();
            }
        });

        // Inicializar mapa cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
</body>
</html>