<?php
// Cargar configuración principal
$config = require_once 'include/config.php';

// Leer versión del sistema desde archivo VERSION
$version = trim(file_get_contents('VERSION'));

// Configurar zona horaria
date_default_timezone_set($config['app']['timezone']);

// Variables para el menú de usuario (simuladas por ahora)
$usuario_actual = 'Administrador';
$rol_actual = 'Supervisor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Eventos Zabbix | <?= htmlspecialchars($config['app']['name']) ?></title>
  <link rel="icon" type="image/x-icon" href="include/ico/bms.ico">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            display: ['Outfit', 'sans-serif'],
          },
          colors: {
            primary: {
              50: '#eef2ff',
              100: '#e0e7ff',
              200: '#c7d2fe',
              300: '#a5b4fc',
              400: '#818cf8',
              500: '#6366f1',
              600: '#4f46e5',
              700: '#4338ca',
              800: '#3730a3',
              900: '#312e81',
              950: '#1e1b4b',
            },
            secondary: {
              50: '#f0fdfa',
              100: '#ccfbf1',
              200: '#99f6e4',
              300: '#5eead4',
              400: '#2dd4bf',
              500: '#14b8a6',
              600: '#0d9488',
              700: '#0f766e',
              800: '#115e59',
              900: '#134e4a',
              950: '#042f2e',
            },
            'cyber-blue': '#0077FF',
            'cyber-purple': '#7E22CE',
            'cyber-dark': '#0B1120',
            'cyber-neon': '#00FFAA'
          },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'float': 'float 3s ease-in-out infinite',
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' },
            }
          }
        }
      }
    }
  </script>

  <style>
    /* Configuración general */
    body {
        min-height: 100vh;
        background-color: #0B1120;
        font-family: 'Inter', sans-serif;
        color: white;
        overflow-x: hidden;
        position: relative;
    }
    
    /* Efectos de cristal */
    .glass {
        background: rgba(13, 19, 33, 0.65);
        backdrop-filter: blur(12px) saturate(180%);
        border-radius: 1rem;
        border: 1px solid rgba(128, 128, 255, 0.12);
        box-shadow: 
            0 8px 32px 0 rgba(31, 38, 135, 0.2),
            0 0 0 1px rgba(126, 34, 206, 0.05),
            inset 0 0 8px rgba(0, 119, 255, 0.2);
    }
    
    .glass-card {
        background: rgba(11, 17, 32, 0.75);
        backdrop-filter: blur(12px) saturate(180%);
        border-radius: 1rem;
        border: 1px solid rgba(0, 119, 255, 0.1);
        box-shadow: 
            0 8px 32px 0 rgba(0, 0, 0, 0.3),
            0 0 0 1px rgba(126, 34, 206, 0.05);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
        position: relative;
    }
    
    .glass-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 
            0 15px 35px rgba(0, 119, 255, 0.2),
            0 0 15px rgba(0, 119, 255, 0.15);
        border-color: rgba(0, 119, 255, 0.25);
    }
    
    .glass-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            to right,
            transparent,
            rgba(255, 255, 255, 0.05),
            transparent
        );
        transform: skewX(-25deg);
        transition: all 0.6s ease;
    }
    
    .glass-card:hover::before {
        left: 100%;
    }
    
    /* Efectos de iconos */
    .icon-glow {
        filter: drop-shadow(0 0 5px var(--glow-color));
        transition: all 0.3s ease;
    }
    
    .icon-glow:hover {
        filter: drop-shadow(0 0 10px var(--glow-color));
        transform: scale(1.1);
    }
    
    .icon-glow-yellow { --glow-color: #fde047; }
    .icon-glow-blue { --glow-color: #0077FF; }
    .icon-glow-green { --glow-color: #4ade80; }
    .icon-glow-purple { --glow-color: #7E22CE; }
    .icon-glow-cyan { --glow-color: #22d3ee; }
    .icon-glow-red { --glow-color: #f87171; }
    .icon-glow-orange { --glow-color: #fb923c; }
    .icon-glow-pink { --glow-color: #ec4899; }
    .icon-glow-emerald { --glow-color: #34d399; }
    .icon-glow-rose { --glow-color: #fb7185; }
    .icon-glow-indigo { --glow-color: #818cf8; }
    .icon-glow-teal { --glow-color: #2dd4bf; }
    .icon-glow-amber { --glow-color: #fbbf24; }
    .icon-glow-lime { --glow-color: #a3e635; }
    .icon-glow-pink { --glow-color: #ec4899; }
    .icon-glow-emerald { --glow-color: #34d399; }
    .icon-glow-rose { --glow-color: #fb7185; }
    .icon-glow-indigo { --glow-color: #818cf8; }
    .icon-glow-teal { --glow-color: #2dd4bf; }
    .icon-glow-amber { --glow-color: #fbbf24; }
    .icon-glow-lime { --glow-color: #a3e635; }
    
    
    /* Línea cyber */
    .cyber-line {
        height: 1px;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(0, 119, 255, 0.6),
            transparent
        );
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }
    
    /* Animaciones */
    .fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
        animation-fill-mode: both;
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    .staggered-fade-in > * {
        opacity: 1 !important;
        transform: translateY(0) !important;
        animation: fadeInUp 0.6s ease-out both;
    }
    
    @keyframes fadeInUp {
        0% { 
            opacity: 0; 
            transform: translateY(30px);
        }
        100% { 
            opacity: 1; 
            transform: translateY(0);
        }
    }
    
    /* Animaciones del logo Digital Matrix */
    @keyframes blink {
        0%, 70% { opacity: 1; }
        71%, 100% { opacity: 0.3; }
    }
    
    /* Estilo de texto animado para FiberOps */
    .gradient-text-full {
        background: linear-gradient(90deg, #00ff88, #00d4ff, #7c3aed);
        background-size: 200% 100%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: textFlow 3s linear infinite;
    }
    
    @keyframes textFlow {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    /* Grid de partículas de fondo */
    .particle-background {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: -1;
      background-image: 
        radial-gradient(rgba(99, 102, 241, 0.1) 1px, transparent 0),
        radial-gradient(rgba(20, 184, 166, 0.1) 1px, transparent 0);
      background-size: 40px 40px;
      background-position: 0 0, 20px 20px;
      animation: moveBackground 120s linear infinite;
    }
    
    @keyframes moveBackground {
      0% { background-position: 0 0, 20px 20px; }
      100% { background-position: 1000px 1000px, 1020px 1020px; }
    }
    
    /* Efecto resplandor en los bordes */
    .glow-border {
      position: relative;
    }
    
    .glow-border::after {
      content: '';
      position: absolute;
      top: -1px;
      left: -1px;
      right: -1px;
      bottom: -1px;
      border-radius: 1.5rem;
      background: linear-gradient(45deg, #6366f1, #14b8a6, #6366f1);
      z-index: -1;
      filter: blur(15px);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .glow-border:hover::after {
      opacity: 0.5;
    }
    
    
    
    /* Scrollbar personalizado */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 119, 255, 0.5);
        border-radius: 3px;
        transition: background 0.3s ease;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 119, 255, 0.8);
    }

    /* Pulse notification dot */
    .pulse-dot {
        position: relative;
    }
    
    .pulse-dot::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 8px;
        height: 8px;
        background-color: #00FFAA;
        border-radius: 50%;
        box-shadow: 0 0 8px #00FFAA;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0.8; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(0.8); opacity: 0.8; }
    }

    /* Tabla tipo Zabbix mejorada */
    .zabbix-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: rgba(11, 17, 32, 0.95);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 
            0 10px 25px rgba(0, 0, 0, 0.3),
            0 0 0 1px rgba(0, 119, 255, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    .zabbix-table th {
        background: linear-gradient(135deg, rgba(0, 119, 255, 0.15) 0%, rgba(0, 119, 255, 0.08) 100%);
        color: #60a5fa;
        font-weight: 700;
        padding: 16px 20px;
        text-align: center;
        border-bottom: 2px solid rgba(0, 119, 255, 0.3);
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
    }

    .zabbix-table th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(0, 119, 255, 0.5), transparent);
    }

    .zabbix-table td {
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        font-size: 13px;
        color: #e5e7eb;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
    }

    .zabbix-table tr {
        transition: all 0.3s ease;
    }

    .zabbix-table tr:hover {
        background: linear-gradient(135deg, rgba(0, 119, 255, 0.08) 0%, rgba(0, 119, 255, 0.03) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 119, 255, 0.1);
    }

    .zabbix-table tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.01);
    }

    .zabbix-table tr:nth-child(even):hover {
        background: linear-gradient(135deg, rgba(0, 119, 255, 0.08) 0%, rgba(0, 119, 255, 0.03) 100%);
    }

    /* Badges de estado mejorados */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .status-badge:hover::before {
        left: 100%;
    }

    .status-problem {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.6) 0%, rgba(220, 38, 38, 0.7) 100%);
        color: #ffffff;
        border: 1px solid rgba(239, 68, 68, 0.2);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
    }

    .status-resolved {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.6) 0%, rgba(22, 163, 74, 0.7) 100%);
        color: #ffffff;
        border: 1px solid rgba(34, 197, 94, 0.2);
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
    }

    /* Badges de tipo mejorados */
    .type-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .type-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .type-badge:hover::before {
        left: 100%;
    }

    .type-equipo-alarmado {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.6) 0%, rgba(220, 38, 38, 0.7) 100%);
        color: #ffffff;
        border: 1px solid rgba(239, 68, 68, 0.2);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
    }

    .type-potencia {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.6) 0%, rgba(217, 119, 6, 0.7) 100%);
        color: #ffffff;
        border: 1px solid rgba(245, 158, 11, 0.2);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
    }

    .type-caida-hilo {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.6) 0%, rgba(37, 99, 235, 0.7) 100%);
        color: #ffffff;
        border: 1px solid rgba(59, 130, 246, 0.2);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
    }

    /* Indicadores de carga */
    .loading-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid rgba(0, 119, 255, 0.3);
        border-radius: 50%;
        border-top-color: #0077FF;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Contador de registros */
    .record-counter {
        background: rgba(0, 119, 255, 0.1);
        color: #60a5fa;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid rgba(0, 119, 255, 0.2);
    }

    /* Animación de parpadeo simple para estado de eventos nuevos */
    .new-status-blink {
        animation: statusBlink 1s ease-in-out infinite;
    }

    @keyframes statusBlink {
        0%, 100% { 
            opacity: 1;
        }
        50% { 
            opacity: 0.3;
        }
    }

    /* Separador de hora mejorado */
    .hour-separator {
        background: linear-gradient(135deg, rgba(0, 119, 255, 0.15) 0%, rgba(0, 119, 255, 0.08) 100%);
        border-top: 3px solid rgba(0, 119, 255, 0.4);
        border-bottom: 2px solid rgba(0, 119, 255, 0.2);
        position: relative;
        overflow: hidden;
    }

    .hour-separator::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(0, 119, 255, 0.6), transparent);
    }

    .hour-separator::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(0, 119, 255, 0.3), transparent);
    }

    .hour-separator-cell {
        padding: 16px 20px !important;
        background: transparent !important;
        border: none !important;
        position: relative;
    }

    .hour-separator-content {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #60a5fa;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        z-index: 2;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .hour-separator-content::before {
        content: '';
        position: absolute;
        left: -20px;
        right: -20px;
        top: 50%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(0, 119, 255, 0.4), transparent);
        transform: translateY(-50%);
        z-index: 1;
    }

    .hour-separator-content .mdi {
        color: #60a5fa;
        font-size: 18px;
        margin-right: 8px;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { 
            opacity: 1;
            transform: scale(1);
        }
        50% { 
            opacity: 0.8;
            transform: scale(1.05);
        }
    }

    /* Estilos para input de fecha - hacer visible el icono del calendario */
    input[type="date"] {
        color-scheme: dark;
        position: relative;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24"><path fill="%2360a5fa" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>');
        background-color: transparent;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 16px;
        cursor: pointer;
        opacity: 1;
        filter: brightness(0) saturate(100%) invert(64%) sepia(11%) saturate(1357%) hue-rotate(183deg) brightness(96%) contrast(93%);
    }

    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        filter: brightness(0) saturate(100%) invert(53%) sepia(98%) saturate(1206%) hue-rotate(183deg) brightness(103%) contrast(94%);
        transform: scale(1.1);
        transition: all 0.2s ease;
    }

    /* Para Firefox */
    input[type="date"]::-moz-calendar-picker-indicator {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24"><path fill="%2360a5fa" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>');
        background-color: transparent;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 16px;
        cursor: pointer;
        opacity: 1;
    }

    /* Estilos mejorados para filtros */
    .filter-group {
        position: relative;
    }

    .filter-label {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: #e5e7eb;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filter-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(31, 41, 55, 0.6) 100%);
        border: 2px solid rgba(75, 85, 99, 0.4);
        border-radius: 12px;
        color: #ffffff;
        font-size: 0.875rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }

    .filter-input:focus {
        outline: none;
        border-color: #0077FF;
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.95) 0%, rgba(31, 41, 55, 0.8) 100%);
        box-shadow: 
            0 0 0 4px rgba(0, 119, 255, 0.1),
            0 8px 25px rgba(0, 119, 255, 0.15);
        transform: translateY(-1px);
    }

    .filter-input::placeholder {
        color: #9ca3af;
        font-style: italic;
    }

    .filter-input-icon {
        position: absolute;
        left: 14px;
        color: #60a5fa;
        font-size: 1.125rem;
        pointer-events: none;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .filter-input:focus + .filter-input-icon,
    .filter-input-wrapper:hover .filter-input-icon {
        color: #0077FF;
        transform: scale(1.1);
    }

    .filter-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .filter-select {
        width: 100%;
        padding: 12px 44px 12px 16px;
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(31, 41, 55, 0.6) 100%);
        border: 2px solid rgba(75, 85, 99, 0.4);
        border-radius: 12px;
        color: #ffffff;
        font-size: 0.875rem;
        cursor: pointer;
        appearance: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }

    .filter-select:focus {
        outline: none;
        border-color: #0077FF;
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.95) 0%, rgba(31, 41, 55, 0.8) 100%);
        box-shadow: 
            0 0 0 4px rgba(0, 119, 255, 0.1),
            0 8px 25px rgba(0, 119, 255, 0.15);
        transform: translateY(-1px);
    }

    .filter-select option {
        background: #1f2937;
        color: #ffffff;
        padding: 8px 12px;
    }

    .filter-select-arrow {
        position: absolute;
        right: 14px;
        color: #60a5fa;
        font-size: 1.125rem;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .filter-select:focus + .filter-select-arrow,
    .filter-select-wrapper:hover .filter-select-arrow {
        color: #0077FF;
        transform: rotate(180deg);
    }

    .filter-clear-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 10px 20px;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
        border: 2px solid rgba(239, 68, 68, 0.2);
        border-radius: 12px;
        color: #fca5a5;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }

    .filter-clear-btn:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.1) 100%);
        border-color: rgba(239, 68, 68, 0.4);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.15);
    }

    .filter-date {
        color-scheme: dark;
    }

    /* Hover effects para los grupos de filtros */
    .filter-group:hover .filter-label {
        color: #ffffff;
    }

    .filter-group:hover .filter-input-icon {
        transform: scale(1.05);
    }

    /* Animaciones suaves */
    .filter-input,
    .filter-select {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .filter-input:hover,
    .filter-select:hover {
        border-color: rgba(96, 165, 250, 0.6);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 119, 255, 0.1);
    }

    /* Colores para agrupación de eventos simultáneos */
    .group-color-1 { background-color: rgba(99, 102, 241, 0.1) !important; border-left: 4px solid #6366f1 !important; }
    .group-color-2 { background-color: rgba(16, 185, 129, 0.1) !important; border-left: 4px solid #10b981 !important; }
    .group-color-3 { background-color: rgba(245, 158, 11, 0.1) !important; border-left: 4px solid #f59e0b !important; }
    .group-color-4 { background-color: rgba(239, 68, 68, 0.1) !important; border-left: 4px solid #ef4444 !important; }
    .group-color-5 { background-color: rgba(168, 85, 247, 0.1) !important; border-left: 4px solid #a855f7 !important; }
    .group-color-6 { background-color: rgba(34, 197, 94, 0.1) !important; border-left: 4px solid #22c55e !important; }
    .group-color-7 { background-color: rgba(59, 130, 246, 0.1) !important; border-left: 4px solid #3b82f6 !important; }
    .group-color-8 { background-color: rgba(236, 72, 153, 0.1) !important; border-left: 4px solid #ec4899 !important; }
    .group-color-9 { background-color: rgba(20, 184, 166, 0.1) !important; border-left: 4px solid #14b8a6 !important; }
    .group-color-10 { background-color: rgba(249, 115, 22, 0.1) !important; border-left: 4px solid #f97316 !important; }

    /* Estilos para enlaces de DNI e INTF */
    .dni-link-icon {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dni-link-icon:hover {
        background: rgba(59, 130, 246, 0.6) !important;
        color: #ffffff !important;
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .dni-link-icon:active {
        transform: scale(0.95);
    }

    /* Estilo específico para enlaces de INTF (Thread Status) */
    .intf-link-icon {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .intf-link-icon:hover {
        background: rgba(147, 51, 234, 0.6) !important;
        color: #ffffff !important;
        transform: scale(1.15);
        box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
    }

    .intf-link-icon:active {
        transform: scale(0.95);
    }
  </style>
</head>
<body class="min-h-screen bg-[#0a0e17] bg-gradient-to-br from-gray-950 via-slate-900 to-gray-950 text-white font-sans flex overflow-x-hidden" x-data="{showUserMenu: false}">
  <!-- Fondo de partículas -->
  <div class="particle-background"></div>
  
  <!-- Orbe decorativo -->
  <div class="fixed -top-[35%] -right-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-primary-600/20 to-secondary-600/10 blur-3xl"></div>
  <div class="fixed -bottom-[35%] -left-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-secondary-600/10 to-primary-600/20 blur-3xl"></div>
  

  <!-- Contenido principal -->
  <div class="w-full min-h-screen">
      <!-- Barra superior -->
      <header class="h-16 bg-gradient-to-r from-cyber-dark/40 to-cyber-dark/30 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
          <div class="max-w-7xl mx-auto h-full flex items-center justify-between px-6">
              <!-- Logo y título a la izquierda -->
              <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                      <span class="mdi mdi-react text-xl text-white"></span>
                  </div>
                  <div>
                      <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($config['app']['name']) ?></h1>
                      <p class="text-xs text-gray-400">Eventos Zabbix v<?= htmlspecialchars($version) ?></p>
                  </div>
              </div>
              
              <!-- Navegación a la derecha -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
                <nav class="flex items-center gap-2">
                    <a href="index.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='index.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                        <span class="mdi mdi-home"></span>
                        <span>Inicio</span>
                    </a>
                    <a href="events_zabbix.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='events_zabbix.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                        <span class="mdi mdi-chart-line"></span>
                        <span>Eventos</span>
                    </a>
                    <a href="basic_data.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='basic_data.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                        <span class="mdi mdi-database"></span>
                        <span>Datos</span>
                    </a>
                    <a href="map_locator.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='map_locator.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                        <span class="mdi mdi-map"></span>
                        <span>Mapa</span>
                    </a>
                    <a href="configuration.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='configuration.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                        <span class="mdi mdi-wrench"></span>
                        <span>Configuración</span>
                    </a>
                </nav>
          </div>
      </header>

        <!-- Contenido principal -->
        <main class="px-6 py-6 pb-20">
            <div class="max-w-7xl mx-auto">
            <!-- Header de la página -->
            <div class="mb-8" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold font-display mb-1 text-transparent bg-clip-text bg-gradient-to-r from-orange-300 to-amber-300">
                                Eventos Zabbix
                            </h1>
                            <p class="text-gray-400 text-sm">Monitoreo y gestión de eventos de infraestructura en tiempo real</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="glass-card px-4 py-2">
                            <div class="flex items-center gap-2">
                                <span class="mdi mdi-clock-outline text-orange-400"></span>
                                <span id="hora-peru" class="text-sm text-gray-300"><?= date('g:i:s A', time()) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="glass-card p-8 mb-8" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-gradient-to-br from-cyber-blue to-blue-600 rounded-xl p-2.5 shadow-lg">
                            <span class="mdi mdi-filter-variant text-xl text-white"></span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Filtros Avanzados</h3>
                            <p class="text-sm text-gray-400">Personaliza la búsqueda de eventos</p>
                        </div>
                    </div>
                    <button id="clear-filters-btn" class="filter-clear-btn group">
                        <span class="mdi mdi-broom text-lg group-hover:animate-bounce"></span>
                        <span>Limpiar Todo</span>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Filtro OLT -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-server text-cyber-blue mr-2"></span>
                            OLT
                        </label>
                        <div class="filter-select-wrapper">
                            <select id="filter-host" class="filter-select">
                                <option value="">Todas las OLT</option>
                            </select>
                            <span class="filter-select-arrow mdi mdi-chevron-down"></span>
                        </div>
                    </div>
                    
                    <!-- Filtro PON/LOG -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-lan text-orange-400 mr-2"></span>
                            PON/LOG
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-ethernet"></span>
                            <input type="text" id="filter-pon" placeholder="Ej: 4/0/35" class="filter-input">
                        </div>
                    </div>
                    
                    <!-- Filtro DNI -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-card-account-details-outline text-green-400 mr-2"></span>
                            DNI
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-account"></span>
                            <input type="text" id="filter-dni" placeholder="Filtrar por DNI..." class="filter-input">
                        </div>
                    </div>
                    
                    <!-- Filtro ODF -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-cable-data text-blue-400 mr-2"></span>
                            ODF
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-router-network"></span>
                            <input type="text" id="filter-odf" placeholder="Ej: 3, 15..." class="filter-input">
                        </div>
                    </div>
                    
                    <!-- Filtro HILO -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-cable-data text-yellow-400 mr-2"></span>
                            HILO
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-cable-data"></span>
                            <input type="text" id="filter-hilo" placeholder="Ej: 24, 78..." class="filter-input">
                        </div>
                    </div>
                    
                    <!-- Filtro Tipo -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-tag text-purple-400 mr-2"></span>
                            Tipo
                        </label>
                        <div class="filter-select-wrapper">
                            <select id="filter-tipo" class="filter-select">
                                <option value="">Todos los tipos</option>
                                <option value="EQUIPO ALARMADO">🚨 Equipo Alarmado</option>
                                <option value="PROBLEMAS DE POTENCIA">⚡ Problemas de Potencia</option>
                                <option value="CAIDA DE HILO">🔌 Caída de Hilo</option>
                                <option value="OTRO">📋 Otro</option>
                            </select>
                            <span class="filter-select-arrow mdi mdi-chevron-down"></span>
                        </div>
                    </div>
                    
                    <!-- Filtro Estado -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-check-circle text-teal-400 mr-2"></span>
                            Estado
                        </label>
                        <div class="filter-select-wrapper">
                            <select id="filter-estado" class="filter-select">
                                <option value="">Todos los estados</option>
                                <option value="PROBLEM">🔴 Problem</option>
                                <option value="RESOLVED">🟢 Resolved</option>
                            </select>
                            <span class="filter-select-arrow mdi mdi-chevron-down"></span>
                        </div>
                    </div>
                    
                    <!-- Filtro Fecha -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-calendar text-pink-400 mr-2"></span>
                            Fecha
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-calendar-today"></span>
                            <input type="date" id="filter-fecha" class="filter-input filter-date">
                        </div>
                    </div>
                </div>
                
                <!-- Filtros de Hora (Segunda fila) -->
                <div class="mt-6 pt-6 border-t border-cyber-blue/10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl p-2 shadow-lg">
                            <span class="mdi mdi-clock-time-four text-lg text-white"></span>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-white">Filtros de Hora</h4>
                            <p class="text-sm text-gray-400">Filtrar eventos por rango horario específico</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Filtro Hora Específica -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-clock-outline text-indigo-400 mr-2"></span>
                            Hora Específica
                        </label>
                        <div class="filter-select-wrapper">
                            <select id="filter-hora" class="filter-select">
                                <option value="">Todas las horas</option>
                            </select>
                            <span class="filter-select-arrow mdi mdi-chevron-down"></span>
                        </div>
                    </div>
                    
                    <!-- Filtro Hora Desde -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-clock-start text-emerald-400 mr-2"></span>
                            Desde Hora
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-clock-time-two"></span>
                            <input type="time" id="filter-hora-desde" class="filter-input filter-date">
                        </div>
                    </div>
                    
                    <!-- Filtro Hora Hasta -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <span class="mdi mdi-clock-end text-rose-400 mr-2"></span>
                            Hasta Hora
                        </label>
                        <div class="filter-input-wrapper">
                            <span class="filter-input-icon mdi mdi-clock-time-eight"></span>
                            <input type="time" id="filter-hora-hasta" class="filter-input filter-date">
                        </div>
                    </div>
                    </div>
                </div>

            <!-- Controles de la tabla -->
            <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="record-counter">
                            <span class="mdi mdi-database mr-2"></span>
                            <span id="total-records">0</span> registros
                            <span id="filtered-records" class="text-gray-400 ml-2">(sin filtros)</span>
                  </div>
                  </div>
              </div>
            </div>

            <!-- Tabla de eventos -->
            <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="300">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="zabbix-table w-full" id="events-table">
                        <thead>
                            <tr>
                                <th><span class="mdi mdi-clock mr-2"></span>Hora</th>
                                <th><span class="mdi mdi-signal mr-2"></span>Estado</th>
                                <th><span class="mdi mdi-tag mr-2"></span>Tipo</th>
                                <th><span class="mdi mdi-server mr-2"></span>INTF</th>
                                <th><span class="mdi mdi-account mr-2"></span>DNI</th>
                                <th><span class="mdi mdi-information mr-2"></span>Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="events-tbody">
                            <!-- Los datos se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
          </div>
      </div>
            </div>
  </main>
  </div>

  <script>
  // Inicializar AOS (Animate on Scroll)
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
      AOS.init({
          duration: 800,
          easing: 'ease-in-out',
          once: true
      });
    }

    // Inicializar carga de datos
    cargarDatosEventos();
    
    // Auto-actualización desde configuración
    setInterval(cargarDatosEventos, <?= $config['update']['interval_seconds'] * 1000 ?>);
  });

  // Variables globales para datos y filtros
  let allEventsData = [];
  let filteredEventsData = [];
  let lastUpdateTime = null;
  let newEventIds = new Set();

  // Función para actualizar el select de hosts
  function actualizarSelectHosts() {
    const hostSelect = document.getElementById('filter-host');
    const currentValue = hostSelect.value;
    
    // Obtener hosts únicos de los datos
    const uniqueHosts = [...new Set(allEventsData.map(event => event.HOST))].sort();
    
    // Limpiar opciones existentes (excepto la primera)
    hostSelect.innerHTML = '<option value="">Todas las OLT</option>';
    
    // Agregar hosts únicos
    uniqueHosts.forEach(host => {
      const option = document.createElement('option');
      option.value = host;
      option.textContent = host;
      hostSelect.appendChild(option);
    });
    
    // Restaurar valor seleccionado si existe
    if (currentValue && uniqueHosts.includes(currentValue)) {
      hostSelect.value = currentValue;
    }
  }

  // Función para actualizar el select de horas
  function actualizarSelectHoras() {
    const horaSelect = document.getElementById('filter-hora');
    const currentValue = horaSelect.value;
    
    // Obtener horas únicas de los datos (formato HH:00)
    const uniqueHours = [...new Set(allEventsData.map(event => {
      const eventTime = new Date(event.TIME);
      return String(eventTime.getHours()).padStart(2, '0') + ':00';
    }))].sort();
    
    // Limpiar opciones existentes (excepto la primera)
    horaSelect.innerHTML = '<option value="">Todas las horas</option>';
    
    // Agregar horas únicas
    uniqueHours.forEach(hour => {
      const option = document.createElement('option');
      option.value = hour;
      // Formatear para mostrar en formato 12 horas
      const hourNum = parseInt(hour.split(':')[0]);
      const displayHour = hourNum === 0 ? '12:00 AM' : 
                         hourNum < 12 ? `${hourNum}:00 AM` : 
                         hourNum === 12 ? '12:00 PM' : 
                         `${hourNum - 12}:00 PM`;
      option.textContent = `🕐 ${displayHour}`;
      horaSelect.appendChild(option);
    });
    
    // Restaurar valor seleccionado si existe
    if (currentValue && uniqueHours.includes(currentValue)) {
      horaSelect.value = currentValue;
    }
  }

  // Función para cargar datos de eventos
  async function cargarDatosEventos() {
    try {
        const response = await fetch('api/get_events_data.php', {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        const data = await response.json();
        
        if (data.success) {
            const currentTime = new Date();
            const currentMinute = currentTime.getFullYear() + '-' + 
                                 String(currentTime.getMonth() + 1).padStart(2, '0') + '-' + 
                                 String(currentTime.getDate()).padStart(2, '0') + ' ' + 
                                 String(currentTime.getHours()).padStart(2, '0') + ':' + 
                                 String(currentTime.getMinutes()).padStart(2, '0');
            
            // Detectar eventos nuevos del minuto actual
            if (lastUpdateTime !== null) {
                data.events.forEach(event => {
                    const eventTime = new Date(event.TIME);
                    const eventMinute = eventTime.getFullYear() + '-' + 
                                      String(eventTime.getMonth() + 1).padStart(2, '0') + '-' + 
                                      String(eventTime.getDate()).padStart(2, '0') + ' ' + 
                                      String(eventTime.getHours()).padStart(2, '0') + ':' + 
                                      String(eventTime.getMinutes()).padStart(2, '0');
                    
                    if (eventMinute === currentMinute) {
                        newEventIds.add(event.HOST + '_' + event['PON/LOG'] + '_' + event.TIME);
                    }
                });
            }
            
            lastUpdateTime = currentTime;
            allEventsData = data.events;
            actualizarSelectHosts();
            actualizarSelectHoras();
            aplicarFiltros();
            document.getElementById('total-records').textContent = allEventsData.length;
        }
    } catch (error) {
        // Error silencioso
    }
  }

  // Función para extraer ODF de la descripción
  function extraerODF(descripcion) {
    if (!descripcion) return '';
    
    // Buscar patrones: O:3, ODF:15
    const matchO = descripcion.match(/\bO:(\d+)/);
    const matchODF = descripcion.match(/\bODF:(\d+)/);
    
    if (matchODF) return matchODF[1];
    if (matchO) return matchO[1];
    
    return '';
  }

  // Función para extraer HILO de la descripción
  function extraerHILO(descripcion) {
    if (!descripcion) return '';
    
    // Buscar patrones: H:24, HILO:78
    const matchH = descripcion.match(/\bH:(\d+)/);
    const matchHILO = descripcion.match(/\bHILO:(\d+)/);
    
    if (matchHILO) return matchHILO[1];
    if (matchH) return matchH[1];
    
    return '';
  }

  // Función para aplicar filtros
  function aplicarFiltros() {
    const hostFilter = document.getElementById('filter-host').value;
    const ponFilter = document.getElementById('filter-pon').value.toLowerCase();
    const dniFilter = document.getElementById('filter-dni').value.toLowerCase();
    const odfFilter = document.getElementById('filter-odf').value.toLowerCase();
    const hiloFilter = document.getElementById('filter-hilo').value.toLowerCase();
    const tipoFilter = document.getElementById('filter-tipo').value;
    const estadoFilter = document.getElementById('filter-estado').value;
    const fechaFilter = document.getElementById('filter-fecha').value;
    const horaFilter = document.getElementById('filter-hora').value;
    const horaDesdeFilter = document.getElementById('filter-hora-desde').value;
    const horaHastaFilter = document.getElementById('filter-hora-hasta').value;

    filteredEventsData = allEventsData.filter(event => {
      // Filtro por Host
      if (hostFilter && event.HOST !== hostFilter) {
        return false;
      }

      // Filtro por PON/LOG
      const ponLog = (event['PON/LOG'] || event.GPON || '').toLowerCase();
      if (ponFilter && !ponLog.includes(ponFilter)) {
        return false;
      }

      // Filtro por DNI
      if (dniFilter && !event.DNI.toLowerCase().includes(dniFilter)) {
        return false;
      }

      // Filtro por ODF (solo para eventos de CAIDA DE HILO)
      if (odfFilter && event.TIPO === 'CAIDA DE HILO') {
        const odfEvento = extraerODF(event.DESCRIPCION || '');
        if (!odfEvento.toLowerCase().includes(odfFilter)) {
          return false;
        }
      }

      // Filtro por HILO (solo para eventos de CAIDA DE HILO)
      if (hiloFilter && event.TIPO === 'CAIDA DE HILO') {
        const hiloEvento = extraerHILO(event.DESCRIPCION || '');
        if (!hiloEvento.toLowerCase().includes(hiloFilter)) {
          return false;
        }
      }

      // Filtro por Tipo
      if (tipoFilter && event.TIPO !== tipoFilter) {
        return false;
      }

      // Filtro por Estado
      if (estadoFilter && event.STATUS !== estadoFilter) {
        return false;
      }

      // Filtro por Fecha específica
      if (fechaFilter) {
        const eventDate = new Date(event.TIME).toISOString().split('T')[0];
        if (eventDate !== fechaFilter) {
          return false;
        }
      }

      // Filtro por Hora específica
      if (horaFilter) {
        const eventTime = new Date(event.TIME);
        const eventHour = String(eventTime.getHours()).padStart(2, '0') + ':00';
        if (eventHour !== horaFilter) {
          return false;
        }
      }

      // Filtro por rango de hora
      if (horaDesdeFilter || horaHastaFilter) {
        const eventTime = new Date(event.TIME);
        const eventHourMinute = String(eventTime.getHours()).padStart(2, '0') + ':' + String(eventTime.getMinutes()).padStart(2, '0');
        
        if (horaDesdeFilter && eventHourMinute < horaDesdeFilter) {
          return false;
        }
        
        if (horaHastaFilter && eventHourMinute > horaHastaFilter) {
          return false;
        }
      }

      return true;
    });

    // Actualizar contador de registros filtrados
    const totalRecords = allEventsData.length;
    const filteredRecords = filteredEventsData.length;
    
    document.getElementById('total-records').textContent = totalRecords;
    
    if (filteredRecords < totalRecords) {
      document.getElementById('filtered-records').textContent = `(${filteredRecords} filtrados)`;
      document.getElementById('filtered-records').className = 'text-yellow-400 ml-2';
    } else {
      document.getElementById('filtered-records').textContent = '(sin filtros)';
      document.getElementById('filtered-records').className = 'text-gray-400 ml-2';
    }

    // Actualizar tabla con datos filtrados
    actualizarTabla(filteredEventsData);
  }

  // Función para limpiar filtros
  function limpiarFiltros() {
    document.getElementById('filter-host').value = '';
    document.getElementById('filter-pon').value = '';
    document.getElementById('filter-dni').value = '';
    document.getElementById('filter-odf').value = '';
    document.getElementById('filter-hilo').value = '';
    document.getElementById('filter-tipo').value = '';
    document.getElementById('filter-estado').value = '';
    document.getElementById('filter-fecha').value = '';
    document.getElementById('filter-hora').value = '';
    document.getElementById('filter-hora-desde').value = '';
    document.getElementById('filter-hora-hasta').value = '';
    
    aplicarFiltros();
  }

  // Función para actualizar la tabla
  function actualizarTabla(events) {
    const tbody = document.getElementById('events-tbody');
    
    if (events.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-8 text-gray-400">
                    <span class="mdi mdi-check-circle text-green-400 text-2xl mb-2 block"></span>
                    No se encontraron eventos - Todos los sistemas operativos
                </td>
            </tr>
        `;
        return;
    }

    // Identificar grupos de eventos simultáneos con mismo HOST/SLOT/PORT
    const gruposSimultaneos = identificarGruposSimultaneos(events);

    // Agrupar eventos por hora
    const eventsByHour = {};
    events.forEach(event => {
        const eventTime = new Date(event.TIME);
        const hourKey = eventTime.getFullYear() + '-' + 
                       String(eventTime.getMonth() + 1).padStart(2, '0') + '-' + 
                       String(eventTime.getDate()).padStart(2, '0') + ' ' + 
                       String(eventTime.getHours()).padStart(2, '0') + ':00:00';
        
        if (!eventsByHour[hourKey]) {
            eventsByHour[hourKey] = [];
        }
        eventsByHour[hourKey].push(event);
    });

    // Generar HTML con separadores por hora
    let html = '';
    const sortedHours = Object.keys(eventsByHour).sort((a, b) => new Date(b) - new Date(a));
    
    sortedHours.forEach((hourKey, hourIndex) => {
        const hourEvents = eventsByHour[hourKey];
        const hourDate = new Date(hourKey);
        const hourLabel = hourDate.toLocaleString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'America/Lima'
        }).replace(/(\d{2})\/(\d{2})\/(\d{4}), (\d{1,2}):(\d{2}):(\d{2}) (AM|PM)/, '$3-$1-$2 $4:$5:$6 $7');

        // Agregar separador de hora
        if (hourIndex > 0) {
            html += `
                <tr class="hour-separator">
                    <td colspan="6" class="hour-separator-cell">
                        <div class="hour-separator-content">
                            <span class="mdi mdi-clock-outline mr-2"></span>
                            ${hourLabel}
                        </div>
                    </td>
                </tr>
            `;
        }

            // Agregar eventos de esta hora
        hourEvents.forEach(event => {
            const statusClass = event.STATUS === 'PROBLEM' ? 'status-problem' : 'status-resolved';
            const typeClass = getTypeClass(event.TIPO);
            const dni = event.DNI && event.DNI !== 'N/A' ? event.DNI : '-';
            
            // Mejorar la descripción para eventos de CAIDA DE HILO
            let descripcion = event.DNI && event.DNI !== 'N/A' ? '-' : (event.DESCRIPCION || '-');
            if (event.TIPO === 'CAIDA DE HILO' && event.DESCRIPCION) {
                const odf = extraerODF(event.DESCRIPCION);
                const hilo = extraerHILO(event.DESCRIPCION);
                if (odf || hilo) {
                    // Extraer la parte base (antes de -O: o -ODF:)
                    const baseMatch = event.DESCRIPCION.match(/^(.*?)(?:-O:\d+|-ODF:\d+)/);
                    const basePart = baseMatch ? baseMatch[1] : event.DESCRIPCION;
                    
                    const baseText = `<span class="inline-block bg-gray-600/30 text-gray-200 px-3 py-1 rounded-lg text-sm font-mono mr-2">${basePart}</span>`;
                    const odfText = odf ? `<span class="inline-block bg-blue-500/20 text-blue-300 px-2 py-1 rounded-lg text-xs mr-1 font-semibold">ODF:${odf}</span>` : '';
                    const hiloText = hilo ? `<span class="inline-block bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded-lg text-xs mr-1 font-semibold">HILO:${hilo}</span>` : '';
                    
                    descripcion = `<div class="flex flex-wrap items-center justify-center gap-2 py-1">
                        ${baseText}${odfText}${hiloText}
                    </div>`;
                }
            }
            
            // Crear PON/LOG combinado: HOST/PON/LOG
            const ponLogCombinado = event.HOST + '/' + (event['PON/LOG'] || event.GPON || '-');
            
            // Verificar si es un evento nuevo
            const eventId = event.HOST + '_' + (event['PON/LOG'] || event.GPON || '') + '_' + event.TIME;
            const isNewEvent = newEventIds.has(eventId);
            const statusBlinkClass = isNewEvent ? 'new-status-blink' : '';

            // Obtener clase de color de grupo si pertenece a un grupo simultáneo
            const groupColorClass = obtenerColorGrupo(event, gruposSimultaneos);

            // Crear enlace para DNI si no es '-' o 'N/A'
            const dniContent = (dni !== '-' && dni !== 'N/A') ? 
                `<div class="flex items-center justify-center gap-2">
                    <span class="font-mono">${dni}</span>
                    <a href="../noc/client_final.php?dni_ruc=${encodeURIComponent(dni)}&auto_search=1" 
                       target="_blank" 
                       class="dni-link-icon inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-500/20 hover:bg-blue-500/40 text-blue-400 hover:text-blue-300"
                       title="Buscar cliente en NOC">
                        <span class="mdi mdi-account-search text-sm"></span>
                    </a>
                </div>` : dni;

            // Crear enlace para INTF (HOST/SLOT/PORT) si tiene datos válidos
            const ponLogEvento = event['PON/LOG'] || event.GPON || '';
            const intfContent = (event.HOST && ponLogEvento && ponLogEvento !== '-') ? 
                `<div class="flex items-center justify-center gap-2">
                    <span class="font-mono">${ponLogCombinado}</span>
                    <a href="../noc/olt_status.php?olt=${encodeURIComponent(event.HOST)}&slot_port=${encodeURIComponent(ponLogEvento.split('/').slice(0, 2).join('/'))}&autoload=1&highlight_dni=${encodeURIComponent(dni)}" 
                       target="_blank" 
                       class="intf-link-icon inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-500/20 hover:bg-purple-500/40 text-purple-400 hover:text-purple-300"
                       title="Ver Thread Status en OLT">
                        <span class="mdi mdi-chart-box text-sm"></span>
                    </a>
                </div>` : ponLogCombinado;

            html += `
                <tr class="${groupColorClass}">
                    <td>${event.TIME}</td>
                    <td><span class="status-badge ${statusClass} ${statusBlinkClass}">${event.STATUS}</span></td>
                    <td><span class="type-badge ${typeClass}">${event.TIPO}</span></td>
                    <td>${intfContent}</td>
                    <td>${dniContent}</td>
                    <td>${descripcion}</td>
                </tr>
            `;
        });
    });

    tbody.innerHTML = html;
  }

  // Función para identificar grupos de eventos simultáneos
  function identificarGruposSimultaneos(events) {
    const grupos = {};
    let colorIndex = 1;
    
    events.forEach(event => {
        const eventTime = new Date(event.TIME);
        // Crear clave para fecha-hora-minuto
        const fechaHoraMinuto = eventTime.getFullYear() + '-' + 
                              String(eventTime.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(eventTime.getDate()).padStart(2, '0') + ' ' + 
                              String(eventTime.getHours()).padStart(2, '0') + ':' + 
                              String(eventTime.getMinutes()).padStart(2, '0');
        
        // Crear clave para HOST/SLOT/PORT (sin el último número que es el ONU)
        const ponLog = event['PON/LOG'] || event.GPON || '';
        const hostSlotPort = event.HOST + '/' + ponLog.split('/').slice(0, 2).join('/');
        
        // Crear clave única para el grupo: fecha-hora-minuto + HOST/SLOT/PORT
        const grupoKey = fechaHoraMinuto + '|' + hostSlotPort;
        
        if (!grupos[grupoKey]) {
            grupos[grupoKey] = {
                colorIndex: colorIndex,
                eventos: []
            };
            colorIndex = (colorIndex % 10) + 1; // Ciclar entre 1-10 colores
        }
        
        grupos[grupoKey].eventos.push(event);
    });
    
    // Filtrar solo grupos que tengan más de 1 evento (eventos simultáneos)
    const gruposFiltrados = {};
    Object.keys(grupos).forEach(key => {
        if (grupos[key].eventos.length > 1) {
            gruposFiltrados[key] = grupos[key];
        }
    });
    
    return gruposFiltrados;
  }

  // Función para obtener la clase de color de grupo para un evento
  function obtenerColorGrupo(event, gruposSimultaneos) {
    const eventTime = new Date(event.TIME);
    const fechaHoraMinuto = eventTime.getFullYear() + '-' + 
                           String(eventTime.getMonth() + 1).padStart(2, '0') + '-' + 
                           String(eventTime.getDate()).padStart(2, '0') + ' ' + 
                           String(eventTime.getHours()).padStart(2, '0') + ':' + 
                           String(eventTime.getMinutes()).padStart(2, '0');
    
    const ponLog = event['PON/LOG'] || event.GPON || '';
    const hostSlotPort = event.HOST + '/' + ponLog.split('/').slice(0, 2).join('/');
    const grupoKey = fechaHoraMinuto + '|' + hostSlotPort;
    
    if (gruposSimultaneos[grupoKey]) {
        return `group-color-${gruposSimultaneos[grupoKey].colorIndex}`;
    }
    
    return ''; // Sin color de grupo
  }

  // Función para obtener la clase CSS del tipo
  function getTypeClass(tipo) {
    switch (tipo) {
        case 'EQUIPO ALARMADO':
            return 'type-equipo-alarmado';
        case 'PROBLEMAS DE POTENCIA':
            return 'type-potencia';
        case 'CAIDA DE HILO':
            return 'type-caida-hilo';
        default:
            return 'type-equipo-alarmado';
    }
  }

  // Event listeners para filtros
  document.getElementById('filter-host').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-pon').addEventListener('input', aplicarFiltros);
  document.getElementById('filter-dni').addEventListener('input', aplicarFiltros);
  document.getElementById('filter-odf').addEventListener('input', aplicarFiltros);
  document.getElementById('filter-hilo').addEventListener('input', aplicarFiltros);
  document.getElementById('filter-tipo').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-estado').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-fecha').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-hora').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-hora-desde').addEventListener('change', aplicarFiltros);
  document.getElementById('filter-hora-hasta').addEventListener('change', aplicarFiltros);
  
  // Event listener para limpiar filtros
  document.getElementById('clear-filters-btn').addEventListener('click', limpiarFiltros);

  // Función para limpiar eventos antiguos del set de eventos nuevos
  function limpiarEventosAntiguos() {
    const currentTime = new Date();
    const currentMinute = currentTime.getFullYear() + '-' + 
                         String(currentTime.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(currentTime.getDate()).padStart(2, '0') + ' ' + 
                         String(currentTime.getHours()).padStart(2, '0') + ':' + 
                         String(currentTime.getMinutes()).padStart(2, '0');
    
    // Limpiar eventos que ya no son del minuto actual
    for (let eventId of newEventIds) {
      const timePart = eventId.split('_').pop();
      const eventTime = new Date(timePart);
      const eventMinute = eventTime.getFullYear() + '-' + 
                         String(eventTime.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(eventTime.getDate()).padStart(2, '0') + ' ' + 
                         String(eventTime.getHours()).padStart(2, '0') + ':' + 
                         String(eventTime.getMinutes()).padStart(2, '0');
      
      if (eventMinute !== currentMinute) {
        newEventIds.delete(eventId);
      }
    }
  }

  // Limpiar eventos antiguos cada minuto
  setInterval(limpiarEventosAntiguos, 60000);

  // Actualizar reloj del menú de usuario en tiempo real
  function actualizarHoraUsuario() {
    const horaActualElement = document.getElementById('hora-actual');
    if (horaActualElement) {
      const ahora = new Date();
      const horaFormateada = ahora.toLocaleTimeString('es-PE', { hour12: false });
      horaActualElement.textContent = horaFormateada;
    }
  }

  // Actualizar hora de Perú en tiempo real
  function actualizarHoraPeru() {
    const horaPeruElement = document.getElementById('hora-peru');
    if (horaPeruElement) {
      const ahora = new Date();
      const horaFormateada = ahora.toLocaleTimeString('es-PE', { 
        hour12: true,
        timeZone: 'America/Lima'
      });
      horaPeruElement.textContent = horaFormateada;
    }
  }

  setInterval(actualizarHoraUsuario, 1000);
  setInterval(actualizarHoraPeru, 1000);
  actualizarHoraUsuario();
  actualizarHoraPeru();
  </script>

  <!-- Sistema de Notificaciones - Script simplificado y estático -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const notificationBtn = document.getElementById('notification-btn');
      const notificationBadge = document.getElementById('notification-badge');
      
      // Hacer el botón de notificaciones estático (sin funcionalidad)
      if (notificationBtn) {
          notificationBtn.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              // Función estática - sin funcionalidad
          });
          
          // Mantener el badge visible pero sin funcionalidad
          if (notificationBadge) {
              notificationBadge.style.display = 'block';
          }
      }
  });

  </script>

<?php 
// Función simple de keepalive (puede ser expandida según necesidades)
function generar_js_keepalive() {
    return '<script>
        // Keepalive simple - mantener sesión activa
        setInterval(function() {
            fetch("api/get_events_data.php", {method: "HEAD"})
                .catch(function() { /* Ignorar errores de keepalive */ });
        }, 300000); // Cada 5 minutos
    </script>';
}
echo generar_js_keepalive(); 
?>
</body>
</html>
