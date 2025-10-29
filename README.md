# Zabbix Map

## Descripción del Proyecto

**Zabbix Map** es una aplicación web que integra la API de Zabbix con una base de datos PostgreSQL para obtener alertas en tiempo real de clientes y visualizarlas en un mapa interactivo. El sistema permite monitorear eventos de infraestructura de red, equipos alarmados, problemas de potencia y caídas de hilo en tiempo real.

## Características Principales

- 🔄 **Monitoreo en Tiempo Real**: Actualización automática cada 4 segundos
- 🗺️ **Visualización en Mapa**: Ubicación geográfica de eventos y alertas
- 📊 **Dashboard Interactivo**: Interfaz moderna con filtros avanzados
- 🔍 **Filtros Avanzados**: Por OLT, PON/LOG, DNI, ODF, HILO, tipo, estado y fecha
- 📈 **Estadísticas Históricas**: Almacenamiento de datos para análisis
- 🎨 **UI Moderna**: Interfaz cyberpunk con efectos glassmorphism
- 📱 **Responsive**: Compatible con dispositivos móviles y desktop

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: PostgreSQL
- **API**: Zabbix JSON-RPC API
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Frameworks**: TailwindCSS, Alpine.js, AOS
- **Iconos**: Material Design Icons

## Estructura del Proyecto

```
zabbix-realtime-map/
├── include/
│   ├── config.php              # Configuración de la aplicación
│   ├── ZabbixApi.php           # Cliente API de Zabbix
│   └── routeros_api.class.php  # Clase para RouterOS
├── api/
│   └── get_events_data.php     # Endpoint para obtener eventos
├── configuration.php           # Panel de configuración
├── events_zabbix.php          # Vista principal de eventos
├── index.php                  # Página de inicio
└── README.md                  # Este archivo
```

## Instalación

### Requisitos del Sistema

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Servidor web (Apache/Nginx)
- Zabbix 5.0 o superior
- Extensiones PHP: curl, json, openssl, pdo_pgsql

## Configuración

### Zabbix

- **IP**: Dirección IP del servidor Zabbix
- **Puerto**: Puerto del servidor Zabbix (por defecto 80/443)
- **Token**: Token de autenticación API de Zabbix

### Base de Datos PostgreSQL

- **IP**: Dirección IP del servidor PostgreSQL
- **Puerto**: Puerto de PostgreSQL (por defecto 5432)
- **Base de Datos**: Nombre de la base de datos
- **Usuario**: Usuario de la base de datos
- **Clave**: Contraseña del usuario

## Uso

### Panel de Eventos

1. Acceder a `events_zabbix.php`
2. Los eventos se cargan automáticamente cada 4 segundos
3. Utilizar los filtros para buscar eventos específicos
4. Exportar datos a Excel si es necesario

### Filtros Disponibles

- **OLT**: Filtrar por servidor OLT específico
- **PON/LOG**: Filtrar por puerto PON o LOG
- **DNI**: Filtrar por DNI del cliente
- **ODF**: Filtrar por ODF (solo para caídas de hilo)
- **HILO**: Filtrar por número de hilo
- **Tipo**: Equipo alarmado, problemas de potencia, caída de hilo
- **Estado**: Problem, Resolved
- **Fecha**: Filtro por fecha específica
- **Hora**: Filtro por hora o rango horario

## API Endpoints

### GET /api/get_events_data.php

Obtiene todos los eventos de Zabbix en formato JSON.

**Respuesta**:
```json
{
  "success": true,
  "events": [
    {
      "HOST": "OLT-001",
      "PON/LOG": "4/0/35",
      "DNI": "12345678",
      "TIPO": "EQUIPO ALARMADO",
      "STATUS": "PROBLEM",
      "TIME": "2024-01-15 10:30:45 AM",
      "DESCRIPCION": "Descripción del evento"
    }
  ],
  "total": 150,
  "timestamp": "2024-01-15 10:30:45",
  "client_count": 120,
  "thread_count": 30
}
```

## Características Técnicas

### Monitoreo en Tiempo Real

- Actualización automática cada 4 segundos
- Detección de eventos nuevos con animación
- Agrupación de eventos simultáneos por colores
- Limpieza automática de eventos antiguos

### Optimización de Rendimiento

- Cache de sesiones de Zabbix
- Deduplicación de eventos
- Compresión gzip habilitada
- Límites de tiempo de ejecución configurados

### Seguridad

- Tokens de autenticación encriptados
- Validación de entrada en todos los formularios
- Headers de seguridad configurados
- Escape de datos de salida

## Desarrollo

### Estructura de Datos

Los eventos se estructuran con los siguientes campos:

- `HOST`: Nombre del servidor OLT
- `PON/LOG` o `GPON`: Puerto PON o LOG
- `DNI`: DNI del cliente (N/A para caídas de hilo)
- `TIPO`: Tipo de evento
- `STATUS`: Estado del evento (PROBLEM/RESOLVED)
- `TIME`: Timestamp del evento
- `DESCRIPCION`: Descripción detallada

### Personalización

El sistema es altamente personalizable:

- Colores y temas en CSS
- Filtros adicionales en JavaScript
- Nuevos tipos de eventos en PHP
- Integración con mapas externos

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para soporte técnico o preguntas:

- Crear un issue en GitHub
- Contactar al equipo de desarrollo
- Revisar la documentación de Zabbix API

## Changelog

### v1.0.0
- Lanzamiento inicial
- Integración con Zabbix API
- Panel de eventos en tiempo real
- Sistema de filtros avanzados
- Interfaz responsive

---
