# Zabbix Map

[![Repositorio](https://img.shields.io/badge/GitHub-Zabbix--Map-181717?logo=github)](https://github.com/Jeremias0618/Zabbix-Map)
![Visitas](https://visitor-badge.laobi.icu/badge?page_id=Jeremias0618.Zabbix-Map)
![Zabbix](https://img.shields.io/badge/Zabbix-API%20Integration-DC382D?logo=zabbix&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-12%2B-336791?logo=postgresql&logoColor=white)
![Leaflet](https://img.shields.io/badge/Leaflet-1.9.4-199900?logo=leaflet&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu%20Server-22.04%20LTS-E95420?logo=ubuntu&logoColor=white)

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

### Instalación del servidor (install.sh)

Este proyecto incluye un script de instalación para Ubuntu Server 22.04 que prepara el entorno con Apache, PHP 8.1 (PDO PostgreSQL), PostgreSQL, desactiva IPv6 y configura el firewall UFW (incluyendo reglas para SSH/HTTP/HTTPS/Zabbix/PostgreSQL y restricción de ICMP).

1. Clonar el repositorio:

```
git clone https://github.com/Jeremias0618/Zabbix-Map.git
cd Zabbix-Map
```

2. Dar permisos y ejecutar el instalador (como root):

```
sudo chmod +x deploy/install.sh
sudo ./deploy/install.sh
```

3. Verificar servicios y firewall:

```
systemctl status apache2
systemctl status postgresql
sudo ufw status verbose
```

Notas:
- El script establece PHP 8.1 como predeterminado y habilita `pdo_pgsql`.
- UFW queda activado con políticas por defecto: deny incoming / allow outgoing.
- ICMP (ping) se restringe por defecto a la subred `10.80.80.0/24`. Ajusta esta red en `/etc/ufw/before.rules` si lo necesitas y ejecuta `sudo ufw reload`.
- Si necesitas permitir otros puertos o redes, añade reglas adicionales con `sudo ufw allow ...`.

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

---
