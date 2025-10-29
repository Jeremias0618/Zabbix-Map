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
  <title>Datos Básicos Zabbix | <?= htmlspecialchars($config['app']['name']) ?></title>
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
              <nav class="flex items-center gap-4">
                  <a href="index.php" class="px-4 py-2 rounded-lg hover:bg-cyber-blue/10 transition-all duration-200 text-sm text-gray-300 hover:text-white flex items-center gap-2">
                      <span class="mdi mdi-home"></span>
                      <span>Inicio</span>
                  </a>
                  <a href="configuration.php" class="px-4 py-2 rounded-lg hover:bg-cyber-blue/10 transition-all duration-200 text-sm text-gray-300 hover:text-white flex items-center gap-2">
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
                                Datos Básicos Zabbix
                            </h1>
                            <p class="text-gray-400 text-sm">Visualización de eventos con información detallada del cliente</p>
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

            <!-- Controles de la tabla -->
            <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="record-counter">
                            <span class="mdi mdi-database mr-2"></span>
                            <span id="total-records">0</span> registros
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
                                <th><span class="mdi mdi-server mr-2"></span>HOST</th>
                                <th><span class="mdi mdi-ethernet mr-2"></span>PON/LOG</th>
                                <th><span class="mdi mdi-account mr-2"></span>DNI</th>
                                <th><span class="mdi mdi-information mr-2"></span>Acciones</th>
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

  <!-- Modal para mostrar datos del cliente -->
  <div id="cliente-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="glass-card max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
              <span class="mdi mdi-account-details text-xl text-white"></span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white">Información del Cliente</h3>
              <p class="text-sm text-gray-400">Datos obtenidos de PostgreSQL</p>
            </div>
          </div>
          <button id="close-modal" class="text-gray-400 hover:text-white transition-colors">
            <span class="mdi mdi-close text-2xl"></span>
          </button>
        </div>
        
        <div id="cliente-content" class="space-y-4">
          <!-- Los datos del cliente se cargarán aquí -->
        </div>
      </div>
    </div>
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

  // Variables globales para datos
  let allEventsData = [];
  let lastUpdateTime = null;
  let newEventIds = new Set();

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
            actualizarTabla(allEventsData);
            document.getElementById('total-records').textContent = allEventsData.length;
        }
    } catch (error) {
        // Error silencioso
    }
  }


  // Función para actualizar la tabla
  function actualizarTabla(events) {
    const tbody = document.getElementById('events-tbody');
    
    if (events.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8 text-gray-400">
                    <span class="mdi mdi-check-circle text-green-400 text-2xl mb-2 block"></span>
                    No se encontraron eventos - Todos los sistemas operativos
                </td>
            </tr>
        `;
        return;
    }

    // Generar HTML de la tabla básica
    let html = '';
    
    events.forEach(event => {
        const statusClass = event.STATUS === 'PROBLEM' ? 'status-problem' : 'status-resolved';
        const typeClass = getTypeClass(event.TIPO);
        const dni = event.DNI && event.DNI !== 'N/A' ? event.DNI : '-';
        const host = event.HOST || '-';
        const ponLog = event['PON/LOG'] || event.GPON || '-';
        
        // Crear PON/LOG para la consulta a PostgreSQL (formato: HOST/PON/LOG)
        const ponLogForQuery = host + '/' + ponLog;
        
        html += `
            <tr>
                <td class="font-mono text-sm">${event.TIME}</td>
                <td><span class="status-badge ${statusClass}">${event.STATUS}</span></td>
                <td><span class="type-badge ${typeClass}">${event.TIPO}</span></td>
                <td class="font-mono text-sm">${host}</td>
                <td class="font-mono text-sm">${ponLog}</td>
                <td class="font-mono text-sm">${dni}</td>
                <td>
                    <button onclick="mostrarDatosCliente('${ponLogForQuery}')" 
                            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 hover:text-blue-300 rounded-lg transition-all duration-200 text-sm font-medium">
                        <span class="mdi mdi-account-details"></span>
                        <span>Ver Cliente</span>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
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

  // Función para mostrar datos del cliente
  async function mostrarDatosCliente(ponLog) {
    const modal = document.getElementById('cliente-modal');
    const content = document.getElementById('cliente-content');
    
    // Mostrar modal con loading
    content.innerHTML = `
      <div class="flex items-center justify-center py-8">
        <div class="loading-indicator"></div>
        <span class="ml-3 text-gray-400">Buscando datos del cliente...</span>
      </div>
    `;
    modal.classList.remove('hidden');
    
    try {
      const response = await fetch('api/get_cliente_data.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          pon_log: ponLog
        })
      });
      
      const data = await response.json();
      
      if (data.success && data.cliente) {
        const cliente = data.cliente;
        content.innerHTML = `
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Información Personal -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="mdi mdi-account text-blue-400"></span>
                Información Personal
              </h4>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-400">Cliente:</span>
                  <span class="text-white font-medium">${cliente.cliente || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">DNI/RUC:</span>
                  <span class="text-white font-mono">${cliente.dni_ruc || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Teléfono:</span>
                  <span class="text-white">${cliente.telefono || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Email:</span>
                  <span class="text-white">${cliente.correo_electronico || 'N/A'}</span>
                </div>
              </div>
            </div>
            
            <!-- Información de Ubicación -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="mdi mdi-map-marker text-green-400"></span>
                Ubicación
              </h4>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-400">Dirección:</span>
                  <span class="text-white text-right">${cliente.nombre_direccion || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Distrito:</span>
                  <span class="text-white">${cliente.distrito || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Provincia:</span>
                  <span class="text-white">${cliente.provincia || 'N/A'}</span>
                </div>
                ${cliente.ubicacion ? `
                <div class="mt-4">
                  <a href="${cliente.ubicacion}" target="_blank" 
                     class="inline-flex items-center gap-2 px-4 py-2 bg-green-500/20 hover:bg-green-500/30 text-green-400 hover:text-green-300 rounded-lg transition-all duration-200">
                    <span class="mdi mdi-google-maps"></span>
                    <span>Ver en Google Maps</span>
                  </a>
                </div>
                ` : ''}
              </div>
            </div>
            
            <!-- Información Técnica -->
            <div class="space-y-4 md:col-span-2">
              <h4 class="text-lg font-semibold text-white flex items-center gap-2">
                <span class="mdi mdi-cog text-purple-400"></span>
                Información Técnica
              </h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                  <div class="text-gray-400 text-sm">PON/LOG</div>
                  <div class="text-white font-mono text-sm">${cliente.pon_log || 'N/A'}</div>
                </div>
                <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                  <div class="text-gray-400 text-sm">Troncal</div>
                  <div class="text-white font-mono text-sm">${cliente.troncal || 'N/A'}</div>
                </div>
                <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                  <div class="text-gray-400 text-sm">ODF</div>
                  <div class="text-white font-mono text-sm">${cliente.odf || 'N/A'}</div>
                </div>
                <div class="text-center p-3 bg-gray-800/50 rounded-lg">
                  <div class="text-gray-400 text-sm">Hilo</div>
                  <div class="text-white font-mono text-sm">${cliente.nro_hilo || 'N/A'}</div>
                </div>
              </div>
            </div>
          </div>
        `;
      } else {
        content.innerHTML = `
          <div class="text-center py-8">
            <span class="mdi mdi-alert-circle text-yellow-400 text-4xl mb-4 block"></span>
            <h4 class="text-lg font-semibold text-white mb-2">Cliente no encontrado</h4>
            <p class="text-gray-400">No se encontraron datos del cliente para PON/LOG: ${ponLog}</p>
          </div>
        `;
      }
    } catch (error) {
      content.innerHTML = `
        <div class="text-center py-8">
          <span class="mdi mdi-alert-circle text-red-400 text-4xl mb-4 block"></span>
          <h4 class="text-lg font-semibold text-white mb-2">Error al cargar datos</h4>
          <p class="text-gray-400">No se pudo obtener la información del cliente</p>
        </div>
      `;
    }
  }

  // Event listeners para el modal
  document.getElementById('close-modal').addEventListener('click', function() {
    document.getElementById('cliente-modal').classList.add('hidden');
  });

  // Cerrar modal al hacer clic fuera de él
  document.getElementById('cliente-modal').addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.add('hidden');
    }
  });
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
