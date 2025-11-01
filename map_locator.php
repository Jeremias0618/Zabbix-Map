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
        .mdi-marker.leaflet-div-icon, .svg-marker.leaflet-div-icon { background: transparent; border: none; }
        .mdi-marker, .svg-marker { line-height: 0; }
        .svg-marker.svg-marker-blink { animation: markerBlink 1.2s ease-in-out infinite; }
        @keyframes markerBlink {
            0%, 100% { opacity: 1; filter: drop-shadow(0 0 0 rgba(255,255,255,0.4)); }
            50% { opacity: 0.35; filter: drop-shadow(0 0 10px rgba(255,255,255,0.85)); }
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <div id="marker-counter" style="position:fixed;top:12px;right:12px;z-index:1000;background:rgba(0,0,0,0.7);color:#fff;padding:10px 14px;border-radius:10px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;min-width:220px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="mdi mdi-map-marker-multiple" style="font-size:20px;color:#ef4444"></span>
            <span style="opacity:.85">Equipos alarmados:</span>
            <strong id="markedCountValue" style="font-size:14px">0</strong>
        </div>
        <div id="hostBreakdown" style="margin-top:8px;font-size:12px;max-height:60vh;overflow:auto;">
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-12.0464, -77.0428], 12);

        const lightLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd',
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
        });

        const midLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd',
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        });

        const darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd',
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        });

        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri — Source: Esri, Maxar, Earthstar Geographics, and the GIS User Community'
        });

        midLayer.addTo(map);

        L.control.layers({ 'Claro': lightLayer, 'Intermedio': midLayer, 'Oscuro': darkLayer, 'Satélite': satelliteLayer }, {}, { position: 'topleft' }).addTo(map);

        const markersByKey = {}; // key -> { marker, host }
        const missingCounts = {}; // key -> consecutive cycles missing
        const MISSING_THRESHOLD = 3; // evitar parpadeo: quitar tras 3 ciclos sin aparecer

        const hostPalette = [
            '#e6194B', '#3cb44b', '#4363d8', '#f58231', '#911eb4', '#46f0f0',
            '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324',
            '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075',
            '#808080', '#ffe119', '#2E91E5', '#E15F99', '#1CA71C', '#FB0D0D',
            '#DA16FF', '#222A2A', '#B68100', '#750D86', '#EB663B', '#511CFB',
            '#00A08B', '#FB00D1'
        ];
        // Colores fijos por OLT (ajustables). Si no está en esta lista, se usa la paleta general
        const predefinedHostColors = {
            'SD-1': '#000000ff',      // Rosa fuerte
            'SD-2': '#1E88E5',      // Azul vivo
            'SD-3': '#FFC107',      // Amarillo dorado
            'SD-7': '#43A047',      // Verde medio
            'SD-9': '#8E24AA',      // Púrpura intenso
            'INC-5': '#FB8C00',     // Naranja vibrante
            'JIC-8': '#E53935',     // Rojo brillante
            'JIC2-8': '#00897B',    // Verde turquesa
            'ATE-9': '#5E35B1',     // Violeta oscuro
            'SMP-10': '#F4511E',    // Naranja rojizo
            'CAMP-11': '#3949AB',   // Azul profundo
            'CAMP2-11': '#6D4C41',  // Marrón
            'PTP-12': '#00ACC1',    // Celeste turquesa
            'ANC-13': '#7CB342',    // Verde lima (único en la lista)
            'CHO-14': '#FF6F61',    // Amarillo brillante
            'LO-15': '#C2185B',     // Magenta
            'LO2-15': '#607D8B',    // Gris azulado
            'NEW_LO-15': '#303F9F', // Azul índigo puro
            'VIR-16': '#0097A7',    // Azul petróleo
            'PTP-17': '#AFB42B',    // Amarillo oliva (distinto del resto)
            'VENT-18': '#8D6E63'    // Marrón suave
        };

        const hostColorMap = {}; // HOST -> color
        let hostColorIndex = 0;
        const BLINK_DURATION_MS = 2 * 60 * 1000; // 2 minutos
        let firstLoadCompleted = false;

        function getColorForHost(host) {
            if (!hostColorMap[host]) {
                if (predefinedHostColors[host]) {
                    hostColorMap[host] = predefinedHostColors[host];
                } else {
                    hostColorMap[host] = hostPalette[hostColorIndex % hostPalette.length];
                    hostColorIndex++;
                }
            }
            return hostColorMap[host];
        }

        function createIconBySize(sizePx, colorHex, isBlinking) {
            const half = Math.round(sizePx / 2);
            const svg = '<svg width="' + sizePx + '" height="' + sizePx + '" viewBox="0 0 100 100" style="display:block">' +
                        '<circle cx="50" cy="50" r="50" fill="' + colorHex + '" />' +
                        '</svg>';
            return L.divIcon({
                className: 'svg-marker' + (isBlinking ? ' svg-marker-blink' : ''),
                html: svg,
                iconSize: [sizePx, sizePx],
                iconAnchor: [half, half],
                popupAnchor: [0, -half]
            });
        }

        function getIconForZoom(zoom, colorHex, isBlinking) {
            const size = Math.max(6, Math.min(20, Math.round(zoom * 1.2)));
            return createIconBySize(size, colorHex, !!isBlinking);
        }

        function addOrUpdateMarker(key, lat, lon, popupHtml, host, shouldBlink = false) {
            const color = getColorForHost(host);
            if (markersByKey[key]) {
                if (popupHtml) markersByKey[key].marker.bindPopup(popupHtml);
                return;
            }
            const isBlinking = !!shouldBlink;
            const marker = L.marker([lat, lon], { icon: getIconForZoom(map.getZoom(), color, isBlinking) }).addTo(map);
            if (popupHtml) marker.bindPopup(popupHtml);
            markersByKey[key] = {
                marker,
                host,
                blinkUntil: isBlinking ? Date.now() + BLINK_DURATION_MS : null,
                isBlinking: isBlinking
            };
        }

        function removeMarker(key) {
            const obj = markersByKey[key];
            if (obj && obj.marker) {
                map.removeLayer(obj.marker);
                delete markersByKey[key];
            }
        }

        map.on('zoomend', () => {
            const z = map.getZoom();
            const now = Date.now();
            Object.values(markersByKey).forEach(obj => {
                const color = getColorForHost(obj.host);
                const shouldBlink = obj.blinkUntil && now < obj.blinkUntil;
                if (obj.isBlinking !== shouldBlink) {
                    obj.isBlinking = shouldBlink;
                }
                obj.marker.setIcon(getIconForZoom(z, color, obj.isBlinking));
            });
        });

        function refreshBlinkStates() {
            const now = Date.now();
            Object.values(markersByKey).forEach(obj => {
                const shouldBlink = obj.blinkUntil && now < obj.blinkUntil;
                if (obj.isBlinking !== shouldBlink) {
                    obj.isBlinking = shouldBlink;
                    if (!shouldBlink) {
                        obj.blinkUntil = null;
                    }
                    const color = getColorForHost(obj.host);
                    obj.marker.setIcon(getIconForZoom(map.getZoom(), color, shouldBlink));
                }
            });
        }

        function extractLatLon(url) {
            if (!url) return null;
            let u = url.trim();
            try { u = decodeURIComponent(u); } catch(e) {}

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


        function buildPonLogFull(host, ponLog) {
            if (!host || !ponLog) return null;
            return host + '/' + ponLog;
        }

        function isIndividualPon(pon) {
            if (!pon) return false;
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
                const eventsResp = await fetchJson('api/get_events_data.php');
                if (!eventsResp.success) return;

                const problemEvents = (eventsResp.events || []).filter(e => e.STATUS === 'PROBLEM');
                console.log('[MAP] PROBLEM events:', problemEvents.length);

                const desiredKeys = new Set();
                const processedKeys = new Set(); // Para evitar duplicados en el mismo ciclo

                for (const ev of problemEvents) {
                    const host = ev.HOST;
                    const pon = ev['PON/LOG'] || ev.GPON || '';

                    if (!host || !pon) continue;

                    if (ev.TIPO === 'CAIDA DE HILO') {
                        continue;
                    }

                    if (isIndividualPon(pon)) {
                        const full = buildPonLogFull(host, pon);
                        if (!full) continue;
                        const key = full; // HOST/SLOT/PORT/LOG
                        if (processedKeys.has(key)) continue; // evitar procesar duplicados en este ciclo
                        processedKeys.add(key);
                        
                        try {
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
                                    addOrUpdateMarker(key, coords.lat, coords.lon, popup, host, firstLoadCompleted);
                                    // Solo agregar a desiredKeys DESPUÉS de agregar el marcador exitosamente
                                    desiredKeys.add(key);
                                }
                            }
                        } catch (err) {
                            console.error('[MAP] Error procesando evento', key, err);
                            // No agregar a desiredKeys si falla
                        }
                    }
                }

                Object.keys(markersByKey).forEach((key) => {
                    if (!desiredKeys.has(key)) {
                        missingCounts[key] = (missingCounts[key] || 0) + 1;
                        if (missingCounts[key] >= MISSING_THRESHOLD) {
                            delete missingCounts[key];
                            removeMarker(key);
                        }
                    } else {
                        missingCounts[key] = 0;
                    }
                });

                // Recalcular métricas SOLO con lo que está marcado actualmente
                const currentMarkers = Object.values(markersByKey);
                const counterEl = document.getElementById('markedCountValue');
                if (counterEl) counterEl.textContent = String(currentMarkers.length);

                // Conteo por HOST basado en markers presentes
                const hostCounts = {};
                currentMarkers.forEach(obj => {
                    hostCounts[obj.host] = (hostCounts[obj.host] || 0) + 1;
                });

                // Actualizar ranking por HOST (mayor a menor)
                const hostListEl = document.getElementById('hostBreakdown');
                if (hostListEl) {
                    const entries = Object.entries(hostCounts).sort((a, b) => b[1] - a[1]);
                    const html = entries.map(([host, count]) => {
                        const color = getColorForHost(host);
                        return `<div style="display:flex;align-items:center;gap:8px;margin:4px 0;">
                            <span class="mdi mdi-circle" style="font-size:12px;color:${color}"></span>
                            <span style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:.9">${host}</span>
                            <strong style="font-size:12px">${count}</strong>
                        </div>`;
                    }).join('');
                    hostListEl.innerHTML = html || '<span style="opacity:.7">Sin datos</span>';
                }

                refreshBlinkStates();
            } catch (e) {
                console.error('[MAP] Error loadAndRender:', e);
            }
            if (!firstLoadCompleted) {
                firstLoadCompleted = true;
            }
        }

        loadAndRender();
        setInterval(loadAndRender, 4000); // cada 4s
    </script>
</body>
</html>

