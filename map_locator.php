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
        const STATE_LABELS = {
            planned: 'Visita Programada',
            unplanned: 'No Programado',
            without_message: 'Sin respuesta',
            no_visitors: 'No desea visita'
        };
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

        function addOrUpdateMarker(key, lat, lon, popupHtml, host, eventTimeMs = null, stateFp = 'unplanned', fullPonLog = '') {
            const color = getColorForHost(host);
            const shouldBlink = eventTimeMs !== null && (Date.now() - eventTimeMs) <= BLINK_DURATION_MS;

            if (markersByKey[key]) {
                if (popupHtml) {
                    const existingPopup = markersByKey[key].marker.getPopup();
                    if (existingPopup) {
                        existingPopup.setContent(popupHtml);
                    } else {
                        markersByKey[key].marker.bindPopup(popupHtml);
                    }
                    if (markersByKey[key].marker.isPopupOpen()) {
                        const popupEl = markersByKey[key].marker.getPopup().getElement();
                        setupStateControls(popupEl, key);
                    }
                }
                markersByKey[key].eventTimeMs = eventTimeMs;
                markersByKey[key].isBlinking = shouldBlink;
                markersByKey[key].host = host;
                markersByKey[key].stateFp = stateFp;
                markersByKey[key].fullPonLog = fullPonLog;
                markersByKey[key].marker.setIcon(getIconForZoom(map.getZoom(), color, shouldBlink));
                return;
            }
            const marker = L.marker([lat, lon], { icon: getIconForZoom(map.getZoom(), color, shouldBlink) }).addTo(map);
            if (popupHtml) marker.bindPopup(popupHtml);
            marker.on('popupopen', (event) => {
                setupStateControls(event.popup.getElement(), key);
            });
            markersByKey[key] = {
                marker,
                host,
                eventTimeMs,
                isBlinking: shouldBlink,
                stateFp,
                fullPonLog
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
            Object.values(markersByKey).forEach(obj => {
                const color = getColorForHost(obj.host);
                const shouldBlink = obj.eventTimeMs !== null && (Date.now() - obj.eventTimeMs) <= BLINK_DURATION_MS;
                obj.isBlinking = shouldBlink;
                obj.marker.setIcon(getIconForZoom(z, color, obj.isBlinking));
            });
        });

        function refreshBlinkStates() {
            const now = Date.now();
            Object.values(markersByKey).forEach(obj => {
                const shouldBlink = obj.eventTimeMs !== null && (now - obj.eventTimeMs) <= BLINK_DURATION_MS;
                if (obj.isBlinking !== shouldBlink) {
                    obj.isBlinking = shouldBlink;
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


        function formatEventTime(timeStr) {
            if (!timeStr) return '';
            // Convierte "2025-11-25 4:12:00 PM" a "2025/11/25 04:12PM"
            try {
                const date = new Date(timeStr);
                if (isNaN(date.getTime())) {
                    // Si no se puede parsear, intentar formato manual
                    const match = timeStr.match(/(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2}):(\d{2})\s+(AM|PM)/i);
                    if (match) {
                        let year = match[1];
                        let month = match[2];
                        let day = match[3];
                        let hour = parseInt(match[4]);
                        let minute = match[5];
                        let ampm = match[6].toUpperCase();
                        // Asegurar formato de hora con 2 dígitos
                        hour = hour.toString().padStart(2, '0');
                        return `${year}/${month}/${day} ${hour}:${minute}${ampm}`;
                    }
                    return timeStr;
                }
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                let hours = date.getHours();
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 debería ser 12
                hours = String(hours).padStart(2, '0');
                return `${year}/${month}/${day} ${hours}:${minutes}${ampm}`;
            } catch (e) {
                return timeStr;
            }
        }

        function parseLogTimestamp(timeStr) {
            if (!timeStr) return null;
            const trimmed = String(timeStr).trim();
            const match = trimmed.match(/^(\d{4})[\/-](\d{2})[\/-](\d{2})\s+(\d{1,2}):(\d{2})(?:\s*([ap]m))?$/i);
            if (!match) {
                return null;
            }

            let [, yearStr, monthStr, dayStr, hourStr, minuteStr, ampmRaw] = match;
            let year = parseInt(yearStr, 10);
            let month = parseInt(monthStr, 10) - 1;
            let day = parseInt(dayStr, 10);
            let hour = parseInt(hourStr, 10);
            const minute = parseInt(minuteStr, 10);

            if ([year, month, day, hour, minute].some((v) => Number.isNaN(v))) {
                return null;
            }

            if (ampmRaw) {
                const ampm = ampmRaw.toLowerCase();
                hour = hour % 12 + (ampm === 'pm' ? 12 : 0);
            }

            return new Date(year, month, day, hour, minute);
        }

        function stateToLabel(state) {
            return STATE_LABELS[state] || STATE_LABELS.unplanned;
        }

        function buildStateOptions(selectedState) {
            return Object.entries(STATE_LABELS)
                .map(([value, label]) => `<option value="${value}"${value === selectedState ? ' selected' : ''}>${label}</option>`)
                .join('');
        }

        function isIndividualPon(pon) {
            if (!pon) return false;
            const parts = pon.split('/');
            return parts.length >= 3; // típicamente SLOT/PORT/LOG
        }

        async function fetchJson(url, opts = {}) {
            const res = await fetch(url, { cache: 'no-store', ...opts });
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

        async function updateMarkerStateOnServer(key, newState) {
            const markerData = markersByKey[key];
            if (!markerData) {
                throw new Error('Marcador no disponible');
            }

            const payload = {
                host: markerData.host,
                pon_log: markerData.fullPonLog,
                state_fp: newState,
            };

            const response = await fetch('api/update_map_locator_state.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                cache: 'no-store',
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'No se pudo actualizar el estado');
            }

            return data;
        }

        function setupStateControls(popupEl, key) {
            if (!popupEl) return;
            const markerData = markersByKey[key];
            if (!markerData) return;

            const select = popupEl.querySelector('.state-select');
            const label = popupEl.querySelector('.state-label');

            if (!select || !label) return;

            const currentState = markerData.stateFp || 'unplanned';
            select.value = currentState;
            label.textContent = stateToLabel(currentState);

            if (select.dataset.listenerAttached === 'true') {
                return;
            }

            select.dataset.listenerAttached = 'true';

            select.addEventListener('change', async (event) => {
                const newState = event.target.value;
                if (!STATE_LABELS[newState]) {
                    event.target.value = markerData.stateFp || 'unplanned';
                    return;
                }
                if (newState === markerData.stateFp) {
                    label.textContent = stateToLabel(newState);
                    return;
                }

                const previousState = markerData.stateFp || 'unplanned';
                event.target.disabled = true;

                try {
                    await updateMarkerStateOnServer(key, newState);
                    markerData.stateFp = newState;
                    label.textContent = stateToLabel(newState);
                } catch (error) {
                    console.error('[MAP] Error actualizando estado:', error);
                    alert(error.message || 'No se pudo actualizar el estado');
                    markerData.stateFp = previousState;
                    event.target.value = previousState;
                    label.textContent = stateToLabel(previousState);
                } finally {
                    event.target.disabled = false;
                }
            });
        }

        async function loadAndRender() {
            try {
                const logResp = await fetchJson(`api/get_map_locator_data.php?ts=${Date.now()}`);
                if (!logResp.success) {
                    if (logResp.message) {
                        console.warn('[MAP] Log warning:', logResp.message);
                    }
                    return;
                }

                const rawRecords = Array.isArray(logResp.records)
                    ? logResp.records
                    : (Array.isArray(logResp.events) ? logResp.events : []);

                const desiredKeys = new Set();

                for (const record of rawRecords) {
                    if (!record || typeof record !== 'object') {
                        continue;
                    }

                    const hostRaw = record.host || record.olt || '';
                    const ponLogRaw = record.pon_log || record.intf || record.interface || '';
                    const status = record.status || record.estado || '';
                    const tipo = record.tipo || record.category || '';
                    const ubicacion = record.ubicacion || record.location || record.maps_url || '';
                    const dni = record.dni || record.documento || record.doc || '';
                    const cliente = record.cliente || record.nombre || record.name || '';
                    const timestamp = record.timestamp || record.fecha || record.time || '';

                    let eventTimeMs = null;
                    const parsedTimestamp = parseLogTimestamp(timestamp);
                    if (parsedTimestamp instanceof Date && !Number.isNaN(parsedTimestamp.getTime())) {
                        eventTimeMs = parsedTimestamp.getTime();
                    } else {
                        const fallbackMs = Date.parse(timestamp);
                        if (!Number.isNaN(fallbackMs)) {
                            eventTimeMs = fallbackMs;
                        }
                    }

                    let host = typeof hostRaw === 'string' ? hostRaw.trim() : '';
                    let ponLog = typeof ponLogRaw === 'string' ? ponLogRaw.trim() : '';

                    if (!host && ponLog.includes('/')) {
                        host = ponLog.split('/')[0];
                    }

                    if (!host || !ponLog) {
                        continue;
                    }

                    let normalizedPon = ponLog;
                    if (normalizedPon.startsWith(host + '/')) {
                        normalizedPon = normalizedPon.substring(host.length + 1);
                    }

                    if (!isIndividualPon(normalizedPon) && !isIndividualPon(ponLog)) {
                        continue;
                    }

                    normalizedPon = normalizedPon.replace(/^\/+/, '');
                    const key = `${host}::${normalizedPon}`;

                    const fullPonLog = record.pon_log ? String(record.pon_log).trim() : `${host}/${normalizedPon}`;

                    const rawState = typeof record.state_fp === 'string' ? record.state_fp.trim() : '';
                    const stateFp = STATE_LABELS[rawState] ? rawState : 'unplanned';

                    let coords = null;
                    const latCandidate = record.lat ?? record.latitude ?? record.latitud;
                    const lonCandidate = record.lon ?? record.lng ?? record.longitude ?? record.longitud;

                    if (latCandidate !== undefined && lonCandidate !== undefined) {
                        const lat = parseFloat(latCandidate);
                        const lon = parseFloat(lonCandidate);
                        if (!Number.isNaN(lat) && !Number.isNaN(lon)) {
                            coords = { lat, lon };
                        }
                    }

                    if (!coords && typeof ubicacion === 'string' && ubicacion.trim() !== '') {
                        coords = extractLatLon(ubicacion);
                    }

                    if (!coords) {
                        continue;
                    }

                    const popupParts = [];
                    if (cliente) {
                        popupParts.push(`<strong>${cliente}</strong>`);
                    }
                    popupParts.push(`${host}/${normalizedPon}`);

                    const tipoEstado = [tipo, status].filter(Boolean).join(' - ');
                    if (tipoEstado) {
                        popupParts.push(tipoEstado);
                    }

                    if (timestamp) {
                        popupParts.push(formatEventTime(timestamp));
                    }

                    if (dni) {
                        popupParts.push(`DNI: ${dni}`);
                    }

                    const baseInfoHtml = popupParts.map(part => `<div>${part}</div>`).join('');
                    const popupHtml = `
                        <div class="space-y-2 text-sm text-gray-200 text-center">
                            ${baseInfoHtml}
                            <div><strong>Estado:</strong> <span class="state-label" data-state-key="${key}">${stateToLabel(stateFp)}</span></div>
                            <div class="flex flex-col gap-1 items-center">
                                <label class="text-xs text-gray-400">Actualizar estado</label>
                                <select class="state-select bg-slate-900/80 border border-slate-700 rounded px-2 py-1 text-sm text-white" data-key="${key}">
                                    ${buildStateOptions(stateFp)}
                                </select>
                            </div>
                        </div>
                    `;

                    desiredKeys.add(key);

                    addOrUpdateMarker(key, coords.lat, coords.lon, popupHtml, host, eventTimeMs, stateFp, fullPonLog);

                    const markerItem = markersByKey[key];
                    if (markerItem) {
                        markerItem.lastSeen = Date.now();
                        markerItem.host = host;
                        markerItem.eventTimeMs = eventTimeMs;
                        markerItem.stateFp = stateFp;
                        markerItem.fullPonLog = fullPonLog;
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

                const currentMarkers = Object.values(markersByKey);
                const counterEl = document.getElementById('markedCountValue');
                if (counterEl) {
                    counterEl.textContent = String(currentMarkers.length);
                }

                const hostCounts = {};
                currentMarkers.forEach(obj => {
                    hostCounts[obj.host] = (hostCounts[obj.host] || 0) + 1;
                });

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
            } catch (error) {
                console.error('[MAP] Error loadAndRender:', error);
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

