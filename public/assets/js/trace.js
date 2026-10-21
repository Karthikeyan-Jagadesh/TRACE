/**
 * TRACE — Lost & Found Intelligence Network
 * Frontend Client Interactions & Spatial Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initNotificationDropdown();
    initMultiStepWizard();
    initAlertDismissal();
    initSpatialMap();
});

/* ========================================================
   Theme Toggle System
   ======================================================== */
function initTheme() {
    const toggleBtns = document.querySelectorAll('[data-action="toggle-theme"]');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const nextTheme = (currentTheme === 'dark') ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', nextTheme);

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch('/theme/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrf
                    },
                    body: JSON.stringify({ _csrf_token: csrf })
                });
            } catch (err) {
                console.warn('Theme preference sync error:', err);
            }
        });
    });
}

/* ========================================================
   Notification Bell & Dropdown
   ======================================================== */
function initNotificationDropdown() {
    const trigger = document.getElementById('notif-trigger');
    const dropdown = document.getElementById('notif-dropdown');

    if (!trigger || !dropdown) return;

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Mark single notification read
    document.querySelectorAll('[data-action="mark-read"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            const item = btn.closest('.notif-item');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const res = await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _csrf_token: csrf })
                });
                if (res.ok) {
                    item.classList.add('opacity-50');
                    btn.remove();
                    decrementBadge();
                }
            } catch (err) {
                console.error('Notification mark read failed:', err);
            }
        });
    });
}

function decrementBadge() {
    const badge = document.getElementById('notif-badge');
    if (!badge) return;
    let count = parseInt(badge.textContent.trim(), 10) || 0;
    count = Math.max(0, count - 1);
    if (count === 0) {
        badge.remove();
    } else {
        badge.textContent = count;
    }
}

/* ========================================================
   Multi-Step Wizard for Reporting
   ======================================================== */
function initMultiStepWizard() {
    const wizard = document.getElementById('report-wizard');
    if (!wizard) return;

    const steps = wizard.querySelectorAll('.wizard-step-pane');
    const tabs = wizard.querySelectorAll('.wizard-step-tab');
    let currentStep = 1;

    function showStep(stepNum) {
        steps.forEach(pane => {
            const num = parseInt(pane.getAttribute('data-step'), 10);
            if (num === stepNum) {
                pane.classList.remove('hidden');
            } else {
                pane.classList.add('hidden');
            }
        });

        tabs.forEach(tab => {
            const num = parseInt(tab.getAttribute('data-step'), 10);
            tab.classList.remove('active', 'completed');
            if (num === stepNum) {
                tab.classList.add('active');
            } else if (num < stepNum) {
                tab.classList.add('completed');
            }
        });

        currentStep = stepNum;
        window.scrollTo({ top: wizard.offsetTop - 80, behavior: 'smooth' });
    }

    wizard.querySelectorAll('[data-action="next-step"]').forEach(btn => {
        btn.addEventListener('click', () => {
            // Validate required inputs in current pane
            const currentPane = wizard.querySelector(`.wizard-step-pane[data-step="${currentStep}"]`);
            const requiredInputs = currentPane.querySelectorAll('[required]');
            let valid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.classList.add('border-red-500');
                    input.focus();
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (valid && currentStep < steps.length) {
                showStep(currentStep + 1);
            }
        });
    });

    wizard.querySelectorAll('[data-action="prev-step"]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });
    });
}

/* ========================================================
   Alert Dismissal
   ======================================================== */
function initAlertDismissal() {
    document.querySelectorAll('[data-dismiss="alert"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 200);
            }
        });
    });
}

/* ========================================================
   Spatial Map (Leaflet.js + OpenStreetMap / CARTO Tiles)
   ======================================================== */
function initSpatialMap() {
    const mapEl = document.getElementById('trace-map');
    if (!mapEl || typeof L === 'undefined') return;

    const defaultLat = 13.0827; // Chennai / Campus default
    const defaultLng = 80.2707;
    const isDark = (document.documentElement.getAttribute('data-theme') || 'dark') === 'dark';

    const map = L.map('trace-map', {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], 13);

    // Standard OpenStreetMap tiles (free, reliable, unwatermarked)
    const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    L.tileLayer(tileUrl, {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Load points from data attribute or API
    const rawPoints = mapEl.getAttribute('data-points');
    let points = [];
    if (rawPoints) {
        try {
            points = JSON.parse(rawPoints);
        } catch (e) {
            console.error('Error parsing map points:', e);
        }
    }

    if (points && points.length > 0) {
        const bounds = [];
        points.forEach(p => {
            if (!p.latitude || !p.longitude) return;
            const lat = parseFloat(p.latitude);
            const lng = parseFloat(p.longitude);
            bounds.push([lat, lng]);

            const isLost = (p.report_type === 'lost');
            const color = isLost ? '#F2B84B' : '#D7FF64';

            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: color,
                color: isDark ? '#111310' : '#FFFFFF',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.85
            }).addTo(map);

            const popupHtml = `
                <div style="font-family: var(--font-sans); padding: 4px; min-width: 180px;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: ${color}; font-weight: 700; text-transform: uppercase;">
                        ${p.report_type} &bull; ${p.identifier}
                    </div>
                    <div style="font-weight: 700; font-size: 13px; margin: 4px 0;">${escapeHtml(p.item_title)}</div>
                    <div style="font-size: 11px; color: #888; margin-bottom: 8px;">${escapeHtml(p.general_location)}</div>
                    <a href="/reports/${p.id}" style="display: block; text-align: center; background: ${color}; color: #111; padding: 4px 8px; font-weight: 600; font-size: 11px; text-decoration: none;">View Signal</a>
                </div>
            `;
            marker.bindPopup(popupHtml);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
        }
    }

    // Coordinate picker for report creation maps
    const pickerLat = document.getElementById('report-lat');
    const pickerLng = document.getElementById('report-lng');
    if (pickerLat && pickerLng) {
        let pickerMarker = null;
        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            pickerLat.value = lat.toFixed(6);
            pickerLng.value = lng.toFixed(6);

            if (pickerMarker) {
                pickerMarker.setLatLng([lat, lng]);
            } else {
                pickerMarker = L.marker([lat, lng]).addTo(map);
            }
        });
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
