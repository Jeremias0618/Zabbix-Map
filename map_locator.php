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
        #filter-panel.is-hidden { display: none; }
        .filter-toggle-control button {
            background: rgba(0,0,0,0.75);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-family: system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
        }
        .filter-toggle-control button:hover {
            background: rgba(40,40,40,0.85);
        }
        .filter-toggle-control .mdi {
            font-size: 16px;
        }
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

    <div id="map-toolbar" style="position:fixed;top:12px;right:12px;z-index:1000;display:flex;flex-direction:row;gap:12px;align-items:flex-start;">
        <div id="filter-panel" style="background:rgba(0,0,0,0.7);color:#fff;padding:12px 16px;border-radius:10px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;width:240px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;font-weight:600;font-size:14px;">
                <span class="mdi mdi-filter-variant" style="font-size:16px;color:#60a5fa"></span>
                <span>Filtros</span>
            </div>
             <div style="display:flex;flex-direction:column;gap:12px;font-size:12px;">
                <label style="display:flex;flex-direction:column;gap:4px;">
                    <span>OLT</span>
                    <select id="filter-host" style="background:rgba(20,20,20,0.9);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:5px 8px;font-size:12px;">
                        <option value="">Todas las OLT</option>
                    </select>
                </label>
                <label style="display:flex;flex-direction:column;gap:4px;">
                    <span>Estado</span>
                    <select id="filter-state" style="background:rgba(20,20,20,0.9);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:5px 8px;font-size:12px;">
                        <option value="">Todos los estados</option>
                    </select>
                </label>
                 <label style="display:flex;flex-direction:column;gap:4px;">
                     <span>DNI</span>
                     <input type="text" id="filter-dni" placeholder="Ingresar DNI" style="background:rgba(20,20,20,0.9);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:5px 8px;font-size:12px;">
                 </label>
                <label style="display:flex;flex-direction:column;gap:4px;">
                    <span>Fecha</span>
                    <input type="text" id="filter-date" placeholder="dd/mm/aaaa" title="Selecciona una fecha" style="background:rgba(20,20,20,0.9);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:5px 8px;font-size:12px;cursor:pointer;" readonly>
                </label>
                <button id="clear-filters" type="button" style="margin-top:6px;background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;border:none;border-radius:6px;padding:6px 8px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <span class="mdi mdi-broom"></span>
                    Limpiar filtros
                </button>
            </div>
        </div>

        <div id="marker-counter" style="background:rgba(0, 0, 0, 0);color:#fff;padding:10px 14px;border-radius:10px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;min-width:220px;">

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([-12.0464, -77.0428], 12);

        const filterPanel = document.getElementById('filter-panel');
        const hostFilterSelect = document.getElementById('filter-host');
        const stateFilterSelect = document.getElementById('filter-state');
        const dniFilterInput = document.getElementById('filter-dni');
        const dateFilterInput = document.getElementById('filter-date');
        const clearFiltersButton = document.getElementById('clear-filters');

        const filterState = {
            host: '',
            state: '',
            date: '',
            dni: ''
        };
        let filtersVisible = true;
        let filterToggleButton = null;

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

        function updateFilterToggleButton() {
            if (!filterToggleButton) return;
            if (filtersVisible) {
                filterToggleButton.innerHTML = '<span class="mdi mdi-eye-off-outline"></span><span></span>';
                filterToggleButton.title = 'Ocultar filtros';
            } else {
                filterToggleButton.innerHTML = '<span class="mdi mdi-eye-outline"></span><span></span>';
                filterToggleButton.title = 'Mostrar filtros';
            }
            filterToggleButton.setAttribute('aria-pressed', filtersVisible ? 'true' : 'false');
        }

        const filterToggleControl = L.control({ position: 'topleft' });
        filterToggleControl.onAdd = function () {
            const container = L.DomUtil.create('div', 'leaflet-control filter-toggle-control');
            filterToggleButton = L.DomUtil.create('button', 'filter-toggle-button', container);
            filterToggleButton.type = 'button';
            updateFilterToggleButton();
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.disableScrollPropagation(container);
            L.DomEvent.on(filterToggleButton, 'click', (event) => {
                L.DomEvent.stop(event);
                filtersVisible = !filtersVisible;
                if (filterPanel) {
                    filterPanel.classList.toggle('is-hidden', !filtersVisible);
                }
                updateFilterToggleButton();
            });
            return container;
        };
        filterToggleControl.addTo(map);

        const markersByKey = {}; // key -> { marker, host }
        const STATE_LABELS = {
            planned: 'Visita Programada',
            unplanned: 'No Programado',
            without_message: 'Sin respuesta',
            no_visitors: 'No desea visita',
            its_not_a_problem: 'No tiene problemas',
            fieldwork: 'Trabajos en campo'
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
        const predefinedHostColors = {
            'SD-1': '#000000ff',
            'SD-2': '#1E88E5',
            'SD-3': '#FFC107',
            'SD-7': '#43A047',
            'SD-9': '#8E24AA',
            'INC-5': '#FB8C00',
            'JIC-8': '#E53935',
            'JIC2-8': '#00897B',
            'ATE-9': '#5E35B1',
            'SMP-10': '#F4511E',
            'CAMP-11': '#3949AB',
            'CAMP2-11': '#6D4C41',
            'PTP-12': '#00ACC1',
            'ANC-13': '#7CB342',
            'CHO-14': '#FF6F61',
            'LO-15': '#C2185B',
            'LO2-15': '#607D8B',
            'NEW_LO-15': '#303F9F',
            'VIR-16': '#0097A7',
            'PTP-17': '#AFB42B',
            'VENT-18': '#8D6E63'
        };

        const hostColorMap = {}; // HOST -> color
        let hostColorIndex = 0;
        const BLINK_DURATION_MS = 2 * 60 * 1000; // 2 minutos
        let firstLoadCompleted = false;
        let latestRecords = [];
        let lastHostOptionsSignature = '';

        if (stateFilterSelect) {
            stateFilterSelect.innerHTML = '<option value=\"\">Todos los estados</option>' + buildStateOptions(filterState.state);
        }

        if (hostFilterSelect) {
            hostFilterSelect.addEventListener('change', () => {
                filterState.host = hostFilterSelect.value;
                renderFilteredMarkers();
            });
        }

        if (stateFilterSelect) {
            stateFilterSelect.addEventListener('change', () => {
                filterState.state = stateFilterSelect.value;
                renderFilteredMarkers();
            });
        }

        if (dniFilterInput) {
            dniFilterInput.addEventListener('input', () => {
                filterState.dni = dniFilterInput.value.trim().toUpperCase();
                renderFilteredMarkers();
            });
        }

        if (dateFilterInput) {
            const flatpickrCss = document.createElement('link');
            flatpickrCss.rel = 'stylesheet';
            flatpickrCss.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
            document.head.appendChild(flatpickrCss);

            const flatpickrLoader = document.createElement('script');
            flatpickrLoader.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
            flatpickrLoader.onload = () => {
                const localeScript = document.createElement('script');
                localeScript.src = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js';
                localeScript.onload = () => {
                    if (window.flatpickr) {
                        flatpickr.localize(flatpickr.l10ns.es);
                        flatpickr(dateFilterInput, {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd/m/Y',
                            allowInput: false,
                            onChange: (selectedDates) => {
                                const value = selectedDates[0] ? formatDateForFilter(selectedDates[0]) : '';
                                filterState.date = value;
                                renderFilteredMarkers();
                            },
                            onClose: () => {
                                dateFilterInput.blur();
                            }
                        });
                    }
                };
                document.head.appendChild(localeScript);
            };
            document.head.appendChild(flatpickrLoader);
        }

        if (clearFiltersButton) {
            clearFiltersButton.addEventListener('click', () => {
                filterState.host = '';
                filterState.state = '';
                filterState.date = '';
                filterState.dni = '';

                if (hostFilterSelect) hostFilterSelect.value = '';
                if (stateFilterSelect) stateFilterSelect.value = '';
                if (dniFilterInput) dniFilterInput.value = '';
                if (dateFilterInput) {
                    if (dateFilterInput._flatpickr) {
                        dateFilterInput._flatpickr.clear();
                    } else {
                        dateFilterInput.value = '';
                    }
                }

                renderFilteredMarkers();
            });
        }

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

        function addOrUpdateMarker(key, lat, lon, popupHtml, host, eventTimeMs = null, stateFp = 'unplanned', fullPonLog = '', dniValue = '') {
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
                        setupDniCopy(popupEl);
                    }
                }
                markersByKey[key].eventTimeMs = eventTimeMs;
                markersByKey[key].isBlinking = shouldBlink;
                markersByKey[key].host = host;
                markersByKey[key].stateFp = stateFp;
                markersByKey[key].fullPonLog = fullPonLog;
                markersByKey[key].dni = dniValue;
                markersByKey[key].marker.setIcon(getIconForZoom(map.getZoom(), color, shouldBlink));
                return;
            }
            const marker = L.marker([lat, lon], { icon: getIconForZoom(map.getZoom(), color, shouldBlink) }).addTo(map);
            if (popupHtml) marker.bindPopup(popupHtml);
            marker.on('popupopen', (event) => {
                setupStateControls(event.popup.getElement(), key);
                setupDniCopy(event.popup.getElement());
            });
            markersByKey[key] = {
                marker,
                host,
                eventTimeMs,
                isBlinking: shouldBlink,
                stateFp,
                fullPonLog,
                dni: dniValue
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

        function formatDateForFilter(dateObj) {
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function updateFilterOptions(records) {
            if (!hostFilterSelect) {
                return;
            }

            const uniqueHosts = Array.from(new Set(records.map(record => record.host))).sort((a, b) =>
                a.localeCompare(b, 'es', { sensitivity: 'base' })
            );
            const signature = uniqueHosts.join('|');

            if (signature !== lastHostOptionsSignature) {
                const previousValue = hostFilterSelect.value;
                hostFilterSelect.innerHTML = '<option value="">Todas las OLT</option>' +
                    uniqueHosts.map(host => `<option value="${host}">${host}</option>`).join('');

                if (previousValue && uniqueHosts.includes(previousValue)) {
                    hostFilterSelect.value = previousValue;
                } else {
                    hostFilterSelect.value = '';
                }
                lastHostOptionsSignature = signature;
            }

            filterState.host = hostFilterSelect.value;
        }

        function applyFilters(records) {
            return records.filter(record => {
                if (filterState.host && record.host !== filterState.host) {
                    return false;
                }
                if (filterState.state && record.stateFp !== filterState.state) {
                    return false;
                }
                if (filterState.dni) {
                    if (!record.dniNormalized || record.dniNormalized !== filterState.dni) {
                        return false;
                    }
                }
                if (filterState.date && record.eventDate !== filterState.date) {
                    return false;
                }
                return true;
            });
        }

        function buildPopupHtml(record) {
            const popupParts = [];
            if (record.cliente) {
                popupParts.push(`<strong>${record.cliente}</strong>`);
            }
            popupParts.push(`${record.host}/${record.normalizedPon}`);

            const tipoEstado = [record.tipo, record.status].filter(Boolean).join(' - ');
            if (tipoEstado) {
                popupParts.push(tipoEstado);
            }

            if (record.formattedTime) {
                popupParts.push(record.formattedTime);
            }

            const baseInfoHtml = popupParts.map(part => `<div>${part}</div>`).join('');

            const dniSection = record.dniDisplay ? `
                <div class="flex items-center justify-center gap-2">
                    <span>DNI: ${record.dniDisplay}</span>
                    <button type="button" class="dni-copy-btn inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-800 text-gray-200 hover:bg-slate-600 transition"
                        title="Copiar DNI" data-dni="${record.dniDisplay}" data-key="${record.key}">
                        <span class="mdi mdi-content-copy text-xs"></span>
                    </button>
                </div>
            ` : '';

            return `
                <div class="space-y-2 text-sm text-gray-200 text-center">
                    ${baseInfoHtml}
                    ${dniSection}
                    <div><strong>Estado:</strong> <span class="state-label" data-state-key="${record.key}">${stateToLabel(record.stateFp)}</span></div>
                    <div class="flex flex-col gap-1 items-center">
                        <label class="text-xs text-gray-400">Actualizar estado</label>
                        <select class="state-select bg-slate-900/80 border border-slate-700 rounded px-2 py-1 text-sm text-white" data-key="${record.key}">
                            ${buildStateOptions(record.stateFp)}
                        </select>
                    </div>
                </div>
            `;
        }

        function renderFilteredMarkers() {
            const filteredRecords = applyFilters(latestRecords);
            const desiredKeys = new Set();
            const allRecordKeys = new Set(latestRecords.map(record => record.key));

            filteredRecords.forEach(record => {
                const popupHtml = buildPopupHtml(record);
                addOrUpdateMarker(
                    record.key,
                    record.lat,
                    record.lon,
                    popupHtml,
                    record.host,
                    record.eventTimeMs,
                    record.stateFp,
                    record.fullPonLog,
                    record.dniDisplay
                );
                desiredKeys.add(record.key);
            });

            const currentKeys = Object.keys(markersByKey);
            currentKeys.forEach(key => {
                if (!allRecordKeys.has(key)) {
                    missingCounts[key] = (missingCounts[key] || 0) + 1;
                    if (missingCounts[key] >= MISSING_THRESHOLD) {
                        delete missingCounts[key];
                        removeMarker(key);
                    }
                } else {
                    missingCounts[key] = 0;
                    if (!desiredKeys.has(key)) {
                        removeMarker(key);
                        delete missingCounts[key];
                    }
                }
            });

            const totalEl = document.getElementById('markedCountValue');
            if (totalEl) {
                totalEl.textContent = String(filteredRecords.length);
            }

            const hostBreakdownEl = document.getElementById('hostBreakdown');
            if (hostBreakdownEl) {
                if (filteredRecords.length === 0) {
                    hostBreakdownEl.innerHTML = '<span style="opacity:.7">Sin datos</span>';
                } else {
                    const hostCounts = {};
                    filteredRecords.forEach(record => {
                        hostCounts[record.host] = (hostCounts[record.host] || 0) + 1;
                    });
                    const html = Object.entries(hostCounts)
                        .sort((a, b) => b[1] - a[1])
                        .map(([host, count]) => {
                            const color = getColorForHost(host);
                            return `<div style="display:flex;align-items:center;gap:8px;margin:4px 0;">
                                <span class="mdi mdi-circle" style="font-size:12px;color:${color}"></span>
                                <span style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:.9">${host}</span>
                                <strong style="font-size:12px">${count}</strong>
                            </div>`;
                        }).join('');
                    hostBreakdownEl.innerHTML = html;
                }
            }

            refreshBlinkStates();
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
                    const datasetRecord = latestRecords.find(record => record.key === key);
                    if (datasetRecord) {
                        datasetRecord.stateFp = newState;
                    }
                    renderFilteredMarkers();
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

        function setupDniCopy(popupEl) {
            if (!popupEl) return;
            const copyBtn = popupEl.querySelector('.dni-copy-btn');
            if (!copyBtn) return;
            if (copyBtn.dataset.listenerAttached === 'true') return;
            copyBtn.dataset.listenerAttached = 'true';

            copyBtn.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopPropagation();

                const dni = copyBtn.dataset.dni || '';
                if (!dni) return;
                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(dni);
                    } else {
                        const textarea = document.createElement('textarea');
                        textarea.value = dni;
                        textarea.style.position = 'fixed';
                        textarea.style.opacity = '0';
                        document.body.appendChild(textarea);
                        textarea.focus();
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                    }
                    copyBtn.classList.add('copied');
                    setTimeout(() => copyBtn.classList.remove('copied'), 1200);
                } catch (error) {
                    console.error('[MAP] Error copiando DNI:', error);
                    alert('No se pudo copiar el DNI');
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

                const processedRecords = [];

                for (const record of rawRecords) {
                    if (!record || typeof record !== 'object') {
                        continue;
                    }

                    const hostRaw = record.host || record.olt || '';
                    const ponLogRaw = record.pon_log || record.intf || record.interface || '';
                    const statusValue = record.status || record.estado || record.STATUS || '';
                    const tipoValue = record.tipo || record.category || record.TIPO || '';
                    const ubicacion = record.ubicacion || record.location || record.maps_url || '';
                    const dniValue = record.dni || record.documento || record.doc || '';
                    const clienteRaw = record.cliente || record.nombre || record.name || '';
                    const timestamp = record.timestamp || record.fecha || record.time || '';

                    let eventTimeMs = null;
                    let eventDate = '';
                    const parsedTimestamp = parseLogTimestamp(timestamp);
                    if (parsedTimestamp instanceof Date && !Number.isNaN(parsedTimestamp.getTime())) {
                        eventTimeMs = parsedTimestamp.getTime();
                        eventDate = formatDateForFilter(parsedTimestamp);
                    } else {
                        const fallbackMs = Date.parse(timestamp);
                        if (!Number.isNaN(fallbackMs)) {
                            eventTimeMs = fallbackMs;
                            eventDate = formatDateForFilter(new Date(fallbackMs));
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

                    let dniDisplay = typeof dniValue === 'string' ? dniValue.trim() : String(dniValue ?? '').trim();
                    if (dniDisplay.toUpperCase() === 'N/A') {
                        dniDisplay = '';
                    }
                    const dniNormalized = dniDisplay ? dniDisplay.toUpperCase() : '';

                    const processedRecord = {
                        key,
                        host,
                        normalizedPon,
                        fullPonLog,
                        lat: coords.lat,
                        lon: coords.lon,
                        stateFp,
                        dniDisplay,
                        dniNormalized,
                        cliente: typeof clienteRaw === 'string' ? clienteRaw.trim() : '',
                        tipo: typeof tipoValue === 'string' ? tipoValue.trim() : '',
                        status: typeof statusValue === 'string' ? statusValue.trim() : '',
                        formattedTime: timestamp ? formatEventTime(timestamp) : '',
                        timestampRaw: timestamp,
                        eventTimeMs,
                        eventDate
                    };

                    processedRecords.push(processedRecord);
                }

                latestRecords = processedRecords;
                updateFilterOptions(latestRecords);
                renderFilteredMarkers();
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

