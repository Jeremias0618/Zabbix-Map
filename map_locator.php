<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Mapa - OpenStreetMap</title>
    <link rel="icon" type="image/x-icon" href="include/ico/bms.ico">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css"/>
    <style>
        html, body { height: 100%; margin: 0; }
        #map { height: 100vh; width: 100vw; }
        /* Ocultar borde/fondo por defecto de divIcon para que no aparezca un cuadro encima */
        .mdi-marker.leaflet-div-icon { background: transparent; border: none; }
        .mdi-marker { line-height: 0; }
    </style>
</head>
<body>
    <div id="map"></div>

    <!-- Caja de contador de marcadores en el mapa (en tiempo real) -->
    <div id="marker-counter" style="position:fixed;top:12px;right:12px;z-index:1000;background:rgba(0,0,0,0.7);color:#fff;padding:10px 14px;border-radius:10px;display:flex;align-items:center;gap:8px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;">
        <span class="mdi mdi-map-marker-multiple" style="font-size:20px;color:#ef4444"></span>
        <span style="opacity:.85">Equipos alarmados:</span>
        <strong id="markedCountValue" style="font-size:14px">0</strong>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inicializar mapa OSM
        const map = L.map('map').setView([-12.0464, -77.0428], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Estado de marcadores actuales (clave -> marker)
        const markersByKey = {};

        // Icono personalizado usando MDI account-circle en rojo
        function createAccountIcon() {
            return L.divIcon({
                className: 'mdi-marker',
                html: '<span class="mdi mdi-circle" style="font-size:28px;color:#ef4444;"></span>',
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -14]
            });
        }

        function addOrUpdateMarker(key, lat, lon, popupHtml) {
            if (markersByKey[key]) {
                // mantener existente; opcionalmente actualizar popup
                if (popupHtml) markersByKey[key].bindPopup(popupHtml);
                return;
            }
            const marker = L.marker([lat, lon], { icon: createAccountIcon() }).addTo(map);
            if (popupHtml) marker.bindPopup(popupHtml);
            markersByKey[key] = marker;
        }

        function removeMarker(key) {
            const marker = markersByKey[key];
            if (marker) {
                map.removeLayer(marker);
                delete markersByKey[key];
            }
        }

        // Extraer coordenadas desde una URL de Google Maps (robusto)
        function extractLatLon(url) {
            if (!url) return null;
            let u = url.trim();
            try { u = decodeURIComponent(u); } catch(e) {}

            // Prioridad: coordenadas específicas del lugar (!3d !4d), luego variantes, luego @, luego query params, luego DMS
            const patterns = [
                /!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,           // !3dlat!4dlon
                /3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/,            // 3dlat!4dlon
                /@(-?\d+\.\d+),(-?\d+\.\d+)/,               // @lat,lon
                /[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/,          // q=lat,lon
                /[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/          // ll=lat,lon
            ];
            for (const re of patterns) {
                const m = u.match(re);
                if (m) {
                    const lat = parseFloat(m[1]);
                    const lon = parseFloat(m[2]);
                    if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                        return { lat, lon };
                    }
                }
            }

            // Intentar extraer coordenadas DMS (formato degrees/minutes/seconds en la URL)
            const dms = u.match(/place\/(\d+)%C2%B0(\d+)'([\d.]+)%22S\+(\d+)%C2%B0(\d+)'([\d.]+)%22W/);
            if (dms) {
                const latDeg = parseInt(dms[1]);
                const latMin = parseInt(dms[2]);
                const latSec = parseFloat(dms[3]);
                const lonDeg = parseInt(dms[4]);
                const lonMin = parseInt(dms[5]);
                const lonSec = parseFloat(dms[6]);
                const lat = -(latDeg + latMin/60 + latSec/3600);
                const lon = -(lonDeg + lonMin/60 + lonSec/3600);
                if (lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                    return { lat, lon };
                }
            }

            return null;
        }

        // addMarker ya no es necesario; usar addOrUpdateMarker(key,...)

        // Construir clave completa pon_log: HOST + '/' + PONLOG
        function buildPonLogFull(host, ponLog) {
            if (!host || !ponLog) return null;
            return host + '/' + ponLog;
        }

        // Distinguir si es evento individual (HOST/SLOT/PORT/LOG) o caida de hilo (HOST/SLOT/PORT)
        function isIndividualPon(pon) {
            if (!pon) return false;
            // cuenta de segmentos por '/'
            const parts = pon.split('/');
            return parts.length >= 3; // típicamente SLOT/PORT/LOG
        }

        async function fetchJson(url, opts) {
            const res = await fetch(url, opts);
            const text = await res.text();
            if (!res.ok) {
                console.error('[MAP] HTTP error', res.status, text?.slice(0, 300));
                throw new Error('HTTP ' + res.status);
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('[MAP] Invalid JSON from', url, text?.slice(0, 300));
                throw e;
            }
        }

        async function loadAndRender() {
            try {
                // 1) Obtener eventos Zabbix actuales
                const eventsResp = await fetchJson('api/get_events_data.php');
                if (!eventsResp.success) return;

                // 2) Filtrar solo PROBLEM
                const problemEvents = (eventsResp.events || []).filter(e => e.STATUS === 'PROBLEM');
                // Debug general (limitar tamaño)
                console.log('[MAP] PROBLEM events:', problemEvents.length);

                // 3) Determinar claves deseadas en este ciclo
                const desiredKeys = new Set();

                // 4) Procesar cada evento
                for (const ev of problemEvents) {
                    const host = ev.HOST;
                    const pon = ev['PON/LOG'] || ev.GPON || '';

                    if (!host || !pon) continue;

                    // Desactivar marcado para eventos de CAIDA DE HILO
                    if (ev.TIPO === 'CAIDA DE HILO') {
                        continue;
                    }

                    if (isIndividualPon(pon)) {
                        // Caso individual: búsqueda exacta por HOST/SLOT/PORT/LOG
                        const full = buildPonLogFull(host, pon);
                        if (!full) continue;
                        // clave única del evento en el mapa
                        const key = full; // HOST/SLOT/PORT/LOG
                        if (desiredKeys.has(key)) continue; // evitar duplicados en este ciclo
                        desiredKeys.add(key);
                        const data = await fetchJson('api/get_cliente_data.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ pon_log: full })
                        });
                        if (data.success && data.cliente && data.cliente.ubicacion) {
                            const coords = extractLatLon(data.cliente.ubicacion);
                            if (coords) {
                                const dniLine = ev.DNI ? `<br>DNI: ${ev.DNI}` : '';
                                const popup = `<strong>${data.cliente.cliente || 'Cliente'}</strong><br>` +
                                              `${data.cliente.pon_log || ''}<br>` +
                                              `${ev.TIPO || ''} - ${ev.STATUS || ''}` +
                                              dniLine;
                                addOrUpdateMarker(key, coords.lat, coords.lon, popup);
                            }
                        }
                    } else {
                        // No individual y no CAIDA DE HILO: omitir
                    }
                }

                // 5) Remover marcadores que ya no están en PROBLEM
                Object.keys(markersByKey).forEach((key) => {
                    if (!desiredKeys.has(key)) {
                        removeMarker(key);
                    }
                });

                // 6) Actualizar contador en tiempo real según marcadores visibles
                const counterEl = document.getElementById('markedCountValue');
                if (counterEl) counterEl.textContent = String(Object.keys(markersByKey).length);
            } catch (e) {
                console.error('[MAP] Error loadAndRender:', e);
            }
        }

        // Cargar al inicio y refrescar periódicamente
        loadAndRender();
        setInterval(loadAndRender, 4000); // cada 4s
    </script>
</body>
</html>

