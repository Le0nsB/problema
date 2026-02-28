@extends('layouts.app')

@section('title', 'Karte')

@section('styles')
    @vite(['resources/css/map.css'])
@endsection

@section('content')
    <div id="map" aria-label="Karte ar autobusu pieturām"></div>
    
    <button class="fab-button" id="openReportBtn" aria-label="Ziņot par autobusa kavēšanos">
        <span class="fab-tooltip">Ziņot par kavējumu</span>
        🚌
    </button>

    <div class="sidebar" id="reportSidebar">
        <div class="sidebar-header">
            <h3>Ziņot par autobusa kavējumu</h3>
            <button class="close-sidebar" id="closeSidebar" aria-label="Aizvērt sānu paneli">&times;</button>
        </div>
        <div class="sidebar-content">
            @auth
            <form id="reportForm" class="report-form">
                @csrf
                <h3>Iesniegt kavējuma ziņojumu</h3>
                <p class="form-subtitle">Dalies ar informāciju par maršrutu lai citi pasažieri var labāk plānot.</p>

                <div class="form-group">
                    <label for="origin_bus_stop_id">No (sākuma pietura)</label>
                    <select id="origin_bus_stop_id" name="origin_bus_stop_id" required>
                        <option value="" disabled selected>Izvēlies sākuma pieturu</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="destination_bus_stop_id">Uz (galamērķis)</label>
                    <select id="destination_bus_stop_id" name="destination_bus_stop_id" required>
                        <option value="" disabled selected>Izvēlies galamērķi</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="scheduled_arrival_time">Plānotais ierašanās laiks</label>
                        <input id="scheduled_arrival_time" name="scheduled_arrival_time" type="time" required>
                    </div>
                    <div class="form-group">
                        <label for="delay_minutes">Kavējums sākuma pieturā (min)</label>
                        <input id="delay_minutes" name="delay_minutes" type="number" min="-60" max="180" required>
                        <p class="field-hint">Izmanto negatīvu vērtību, ja autobuss bija agrāk.</p>
                    </div>
                </div>

                <label for="arrived_on_time" class="checkbox-field">
                    <input id="arrived_on_time" name="arrived_on_time" type="checkbox">
                    <span>Galamērķī ieradās laikā</span>
                </label>

                <div class="form-group">
                    <label for="comment">Komentārs (nav obligāti)</label>
                    <textarea id="comment" name="comment" rows="3" placeholder="Sastrēgums, laikapstākļi, tehniska problēma u.c."></textarea>
                </div>

                <button type="submit"><span class="btn-icon"></span>Iesniegt ziņojumu</button>
            </form>
            
            <div class="report-list" id="reportList">
                <div class="stop-stats" id="stopStats"></div>
                <h4>Jaunākie ziņojumi</h4>
            </div>
            @else
                <p>Lai iesniegtu ziņojumus, lūdzu <a href="{{ route('login') }}">piesakieties</a>.</p>
            @endauth
        </div>
    </div>
@endsection

@section('scripts')
    @if(config('services.google_maps.key'))
        <script>
    
            function showMapError(msg) {
                const mapEl = document.getElementById('map');
                if (mapEl) mapEl.innerHTML = '<div class="alert alert-error">' + msg + '</div>';
            }

    
            const CESIS_CENTER = { lat: 57.3125, lng: 25.2680 };

            function openSidebar(busStopId, busStopName, location) {
                const sidebar = document.getElementById('reportSidebar');
                const originSelect = document.getElementById('origin_bus_stop_id');
                const destinationSelect = document.getElementById('destination_bus_stop_id');
                const header = sidebar.querySelector('.sidebar-header h3');
                const sidebarHeader = sidebar.querySelector('.sidebar-header');
                
                if (sidebarHeader) {
                    sidebarHeader.style.background = getAccentColor();
                }
                
                if (sidebar) {
                    sidebar.classList.add('open');
                }
                
                if (header && busStopName) {
                    header.textContent = `Ziņot par kavējumu - ${busStopName}`;
                }
                
                if (originSelect && busStopName) {
                    if (busStopId) {
                        originSelect.value = busStopId;
                        loadReports(busStopId);
                    } else if (location) {
                        const stopStats = document.getElementById('stopStats');
                        if (stopStats) {
                            stopStats.textContent = 'Vidējais kavējums uz šo pieturu: nav datu';
                        }

                        const tempId = `google_${location.lat}_${location.lng}`;
                        
                        const existingOriginOption = Array.from(originSelect.options).find(opt => opt.value === tempId);
                        if (!existingOriginOption) {
                            const option = document.createElement('option');
                            option.value = tempId;
                            option.textContent = busStopName + ' (Google Maps)';
                            option.dataset.lat = location.lat;
                            option.dataset.lng = location.lng;
                            option.dataset.isGoogle = 'true';
                            originSelect.insertBefore(option, originSelect.firstChild);
                        }
                        
                        if (destinationSelect) {
                            const existingDestOption = Array.from(destinationSelect.options).find(opt => opt.value === tempId);
                            if (!existingDestOption) {
                                const option = document.createElement('option');
                                option.value = tempId;
                                option.textContent = busStopName + ' (Google Maps)';
                                option.dataset.lat = location.lat;
                                option.dataset.lng = location.lng;
                                option.dataset.isGoogle = 'true';
                                destinationSelect.appendChild(option);
                            }
                        }
                        
                        originSelect.value = tempId;
                    }
                }
            }

            function closeSidebar() {
                const sidebar = document.getElementById('reportSidebar');
                const header = sidebar.querySelector('.sidebar-header h3');
                
                if (sidebar) {
                    sidebar.classList.remove('open');
                }
                
                if (header) {
                    header.textContent = 'Ziņot par autobusa kavējumu';
                }
            }
            
            async function createBusStop(name, lat, lng) {
                try {
                    const res = await fetch('/api/bus-stops', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            latitude: lat,
                            longitude: lng
                        })
                    });
                    
                    if (!res.ok) {
                        const errorText = await res.text();
                        console.error('Server response:', errorText);
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    
                    const data = await res.json();
                    if (data.id) {
                        return data.id;
                    } else {
                        throw new Error('No ID returned from server');
                    }
                } catch (e) {
                    console.error('Failed to create bus stop:', e);
                    throw e;
                }
            }

        
            async function loadReports(busStopId) {
                try {
                    const res = await fetch(`/api/bus-stops/${busStopId}`);
                    if (!res.ok) {
                        console.error(`HTTP error! status: ${res.status}`);
                        return;
                    }
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', text);
                        return;
                    }

                    const list = document.getElementById('reportList');
                    if (list) {
                        list.innerHTML = '<div class="stop-stats" id="stopStats"></div><h4>Jaunākie ziņojumi</h4>';
                        renderStopStats(data);

                        const reports = data.delay_reports || [];
                        if (reports.length === 0) {
                            const empty = document.createElement('div');
                            empty.className = 'report-empty';
                            empty.textContent = 'Šai pieturai ziņojumu vēl nav.';
                            list.appendChild(empty);
                        }

                        (data.delay_reports || []).forEach(rep => {
                            list.appendChild(createReportItem(rep));
                        });
                    }
                } catch (e) {
                    console.error(e);
                }
            }

            function renderStopStats(data) {
                const stopStats = document.getElementById('stopStats');
                if (!stopStats) {
                    return;
                }

                const count = data?.to_reports_count ?? 0;
                const average = data?.average_delay_to_stop;

                if (!count || average === null || average === undefined) {
                    stopStats.textContent = 'Vidējais kavējums uz šo pieturu: nav pietiekamu datu';
                    return;
                }

                stopStats.textContent = `Vidējais kavējums uz šo pieturu: ${average} min (no ${count} ziņojumiem)`;
            }

            function createReportItem(rep) {
                const item = document.createElement('div');
                item.className = 'report-item';
                item.style.background = getAccentColor();

                const originName = rep.origin_bus_stop?.name ? escapeHtml(rep.origin_bus_stop.name) : '';
                const destinationName = rep.destination_bus_stop?.name ? escapeHtml(rep.destination_bus_stop.name) : '';
                const username = rep.user?.username ? escapeHtml(rep.user.username) : 'Lietotājs';
                const comment = rep.comment ? escapeHtml(rep.comment) : '';
                const delayMinutes = Number(rep.delay_minutes);
                const createdAt = new Date(rep.created_at).toLocaleString();

                let routeInfo = '';
                if (rep.origin_bus_stop && rep.destination_bus_stop) {
                    routeInfo = `<div style="font-weight:500;margin-bottom:0.25rem;">${originName} → ${destinationName}</div>`;
                }

                let timeInfo = rep.scheduled_arrival_time ? `<div style="font-size:0.85rem;">Plānots: ${escapeHtml(rep.scheduled_arrival_time)}</div>` : '';
                let arrivedInfo = rep.arrived_on_time !== null ? `<div style="font-size:0.85rem;">Ieradās laikā: ${rep.arrived_on_time ? '✅ Jā' : '❌ Nē'}</div>` : '';

                item.innerHTML = `${routeInfo}<strong>${username}</strong>: kavējums ${Number.isFinite(delayMinutes) ? delayMinutes : 0} min — ${escapeHtml(createdAt)}${timeInfo}${arrivedInfo}<div style="font-size:.9rem;color:rgba(255,255,255,0.9);margin-top:0.25rem">${comment}</div>`;
                return item;
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }


            function getAccentColor() {
                const colors = [
                    '#0f172a', '#1e293b', '#334155', '#0b3b2e', '#1f2937',
                    '#2d1b35', '#3f1d2e', '#2c2f36', '#1d3557', '#3a506b'
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            }

            window._mapInitCalled = false;
            window._mapInitialized = false;

            window.initMap = function() {
                window._mapInitCalled = true;
                const busStops = @json($busStops);
                const first = busStops && busStops.length ? busStops[0] : null;
                const center = first ? { lat: parseFloat(first.latitude), lng: parseFloat(first.longitude) } : CESIS_CENTER;
                const MAP_ID = '{{ config("services.google_maps.map_id") }}';
                const CESIS_BOUNDS = {
                    north: 57.38,
                    south: 57.23,
                    east: 25.37,
                    west: 25.15,
                };
                
                const mapStyles = [
                    {
                        featureType: "poi.business",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.attraction",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.medical",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.place_of_worship",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.school",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.sports_complex",
                        stylers: [{ visibility: "off" }]
                    },
                    {
                        featureType: "poi.park",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ];
                
                const mapOptions = { 
                    center, 
                    zoom: first ? 14 : 12,
                    styles: mapStyles,
                    disableDefaultUI: true,
                    minZoom: 11,
                    restriction: {
                        latLngBounds: CESIS_BOUNDS,
                        strictBounds: true,
                    }
                };
                if (MAP_ID) mapOptions.mapId = MAP_ID;
                if (!window.google || !google.maps) {
                    showMapError('Google Maps neielādējās korekti. Pārbaudi konsoles kļūdas un API atslēgu.');
                    console.error('initMap called but google.maps is undefined');
                    return;
                }

                const map = new google.maps.Map(document.getElementById('map'), mapOptions);
                window._mapInitialized = true;
                
                const transitLayer = new google.maps.TransitLayer();
                transitLayer.setMap(map);
                
                const originSelect = document.getElementById('origin_bus_stop_id');
                const destinationSelect = document.getElementById('destination_bus_stop_id');

                const service = new google.maps.places.PlacesService(map);
                
                const request = {
                    location: center,
                    radius: 5000,
                    type: 'transit_station'
                };
                
                service.nearbySearch(request, (results, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                        results.forEach(place => {
                            if (!place.geometry || !place.geometry.location) return;
                            
                            const marker = new google.maps.Marker({
                                map: map,
                                position: place.geometry.location,
                                title: place.name,
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: 10,
                                    fillColor: '#2196F3',
                                    fillOpacity: 0.4,
                                    strokeColor: '#1976D2',
                                    strokeWeight: 2,
                                },
                                cursor: 'pointer',
                                optimized: false
                            });
                            
                            marker.addListener('click', () => {
                                const dbStop = busStops.find(s => 
                                    Math.abs(parseFloat(s.latitude) - place.geometry.location.lat()) < 0.001 &&
                                    Math.abs(parseFloat(s.longitude) - place.geometry.location.lng()) < 0.001
                                );
                                
                                const location = {
                                    lat: place.geometry.location.lat(),
                                    lng: place.geometry.location.lng()
                                };
                                
                                if (dbStop) {
                                    openSidebar(dbStop.id, dbStop.name, location);
                                } else {
                                    openSidebar(null, place.name, location);
                                }
                            });
                        });
                    } else {
                        console.error('Places search failed:', status);
                    }
                });

                busStops.forEach(stop => {
                    if (originSelect) {
                        const option = document.createElement('option');
                        option.value = stop.id;
                        option.textContent = stop.name;
                        originSelect.appendChild(option);
                    }
                    if (destinationSelect) {
                        const option = document.createElement('option');
                        option.value = stop.id;
                        option.textContent = stop.name;
                        destinationSelect.appendChild(option);
                    }
                });
            };

            function _mapDiagnosticCheck() {
                setTimeout(() => {
                    const mapEl = document.getElementById('map');
                    if (!window._mapInitCalled) {
                        showMapError('Kartes inicializācija netika palaista. Iespējams neizpildījās ielādes skripts. Pārbaudi vai assets ir būvēti un Vite ielādē bundle.');
                        console.warn('Map init was not called (window._mapInitCalled is false)');
                        return;
                    }
                    if (!window._mapInitialized) {
                        if (mapEl && mapEl.innerHTML.trim() === '') {
                            showMapError('Neizdevās attēlot karti — pārbaudi Google Maps API kļūdas konsolē.');
                        }
                    }
                }, 2500);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', _mapDiagnosticCheck);
            } else {
                _mapDiagnosticCheck();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const openBtn = document.getElementById('openReportBtn');
                if (openBtn) {
                    openBtn.addEventListener('click', () => openSidebar());
                }
                
                const closeBtn = document.getElementById('closeSidebar');
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeSidebar);
                }

                const form = document.getElementById('reportForm');
                if (form) form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    
                    const originId = formData.get('origin_bus_stop_id');
                    const destId = formData.get('destination_bus_stop_id');
                    
                    const originSelect = document.getElementById('origin_bus_stop_id');
                    const destSelect = document.getElementById('destination_bus_stop_id');
                    
                    try {
                        if (originId && originId.startsWith('google_')) {
                            const originOption = originSelect.querySelector(`option[value="${originId}"]`);
                            if (originOption) {
                                const newOriginId = await createBusStop(
                                    originOption.textContent.replace(' (Google Maps)', ''),
                                    originOption.dataset.lat,
                                    originOption.dataset.lng
                                );
                                formData.set('origin_bus_stop_id', newOriginId);
                            }
                        }
                        
                        if (destId && destId.startsWith('google_')) {
                            const destOption = destSelect.querySelector(`option[value="${destId}"]`);
                            if (destOption) {
                                const newDestId = await createBusStop(
                                    destOption.textContent.replace(' (Google Maps)', ''),
                                    destOption.dataset.lat,
                                    destOption.dataset.lng
                                );
                                formData.set('destination_bus_stop_id', newDestId);
                            }
                        }
                        
                        if (!formData.has('arrived_on_time')) {
                            formData.append('arrived_on_time', '0');
                        } else {
                            formData.set('arrived_on_time', '1');
                        }
                        
                        const res = await fetch('/reports', { 
                            method: 'POST', 
                            headers: { 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            }, 
                            body: formData 
                        });
                        
                        if (!res.ok) {
                            const errorText = await res.text();
                            console.error('Server error response:', errorText);
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        
                        const responseText = await res.text();
                        
                        let json;
                        try {
                            json = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('Failed to parse JSON:', responseText);
                            throw new Error('Server returned invalid JSON');
                        }
                        
                        if (json.success) {
                            const finalOriginId = formData.get('origin_bus_stop_id');
                            if (finalOriginId) loadReports(finalOriginId);
                            form.reset();
                            alert('Maršruta kavējuma ziņojums veiksmīgi iesniegts!');
                            closeSidebar();
                        } else {
                            alert(json.message || 'Neizdevās iesniegt ziņojumu');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Neizdevās iesniegt ziņojumu: ' + err.message);
                    }
                });
            });
        </script>
    @else
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                const mapEl = document.getElementById('map');
                if (mapEl) {
                    mapEl.innerHTML = '<div class="alert alert-error">Google Maps API atslēga nav konfigurēta. Iestati <strong>GOOGLE_MAPS_API_KEY</strong> failā <code>.env</code> un palaid <code>php artisan config:clear</code>.</div>';
                }
            });
        </script>
    @endif
@endsection
