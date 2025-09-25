<?php
/**
 * Prayer Times Widget with Islamic Art Design
 * Displays prayer times with masjid-like decorative elements
 */
?>

<style>
/* Prayer Times Box Styles - Islamic Green Theme */
.prayer-times-box {
    background: linear-gradient(135deg, #2d5016 0%, #3d6b1a 25%, #4a7c1f 50%, #3d6b1a 75%, #2d5016 100%);
    color: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 32px rgba(45, 80, 22, 0.4);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.15);
}

/* Islamic Geometric Pattern Background - Green Theme */
.prayer-times-box:before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: 
        radial-gradient(circle at 30% 30%, rgba(255,255,255,0.2) 0 8%, transparent 8% 12%),
        radial-gradient(circle at 70% 70%, rgba(255,255,255,0.15) 0 6%, transparent 6% 10%),
        radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0 4%, transparent 4% 8%);
    border-radius: 50%;
    filter: blur(2px);
    pointer-events: none;
    animation: rotate 20s linear infinite;
}

/* Masjid Dome Decorative Element - Green Theme */
.prayer-times-box:after {
    content: "";
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 40px;
    background: linear-gradient(135deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.1) 100%);
    border-radius: 50px 50px 0 0;
    border: 2px solid rgba(255,255,255,0.4);
    pointer-events: none;
}

/* Islamic Geometric Border Pattern - Green Theme */
.prayer-times-box::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(255,255,255,0.15) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.15) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.15) 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    opacity: 0.4;
    pointer-events: none;
}

/* Crescent Moon Decorative Element - Green Theme */
.prayer-times-box .crescent-moon {
    position: absolute;
    top: 15px;
    right: 20px;
    width: 30px;
    height: 30px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9) 0 40%, transparent 40% 100%);
    border-radius: 50%;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    pointer-events: none;
}

/* Star Decorative Elements - Green Theme */
.prayer-times-box .star {
    position: absolute;
    width: 12px;
    height: 12px;
    background: rgba(255,255,255,0.95);
    clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
    pointer-events: none;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
}

.prayer-times-box .star:nth-child(1) {
    top: 25px;
    right: 60px;
    animation: twinkle 2s ease-in-out infinite;
}

.prayer-times-box .star:nth-child(2) {
    top: 45px;
    right: 45px;
    animation: twinkle 2s ease-in-out infinite 0.5s;
}

.prayer-times-box .star:nth-child(3) {
    top: 35px;
    right: 80px;
    animation: twinkle 2s ease-in-out infinite 1s;
}

/* Minaret Decorative Element - Green Theme */
.prayer-times-box .minaret {
    position: absolute;
    bottom: 0;
    left: 20px;
    width: 8px;
    height: 60px;
    background: linear-gradient(to top, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0.15) 100%);
    border-radius: 4px;
    pointer-events: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.prayer-times-box .minaret:before {
    content: "";
    position: absolute;
    top: -8px;
    left: -2px;
    width: 12px;
    height: 12px;
    background: rgba(255,255,255,0.5);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* Islamic Calligraphy Style Header - Green Theme */
.prayer-times-title {
    font-family: Arial, sans-serif;
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
    letter-spacing: 1px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
    position: relative;
    z-index: 10;
    color: #ffffff;
    text-align: center;
}


/* Enhanced Prayer Time Items - Green Theme */
.prayer-time-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.9rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.25);
    position: relative;
    transition: all 0.3s ease;
}

.prayer-time-item:hover {
    background: rgba(255,255,255,0.08);
    border-radius: 8px;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.prayer-time-item:last-child {
    border-bottom: none;
}

.prayer-name {
    font-weight: 600;
    font-size: 1rem;
    position: relative;
    color: #ffffff;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Per-prayer icons - Green Theme */
.prayer-time-item .prayer-name:before {
    display: inline-block;
    margin-right: 6px;
    opacity: 0.95;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
}
.prayer-time-item.fajr .prayer-name:before { content: "🌙"; }
.prayer-time-item.sunrise .prayer-name:before { content: "🌄"; }
.prayer-time-item.dhuhr .prayer-name:before { content: "☀️"; }
.prayer-time-item.asr .prayer-name:before { content: "🌤️"; }
.prayer-time-item.maghrib .prayer-name:before { content: "🌇"; }
.prayer-time-item.isha .prayer-name:before { content: "🌌"; }

.prayer-time {
    font-weight: 700;
    font-size: 1.1rem;
    font-family: Courier New, monospace;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.4);
    color: #ffffff;
}

/* Islamic Pattern Overlay */
.prayer-times-box .islamic-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,255,255,0.03) 2px, rgba(255,255,255,0.03) 4px),
        repeating-linear-gradient(-45deg, transparent, transparent 2px, rgba(255,255,255,0.03) 2px, rgba(255,255,255,0.03) 4px);
    pointer-events: none;
    z-index: 1;
}

/* Animations */
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes twinkle {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.1); }
}

/* Enhanced Footer */
.prayer-times-footer {
    margin-top: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    position: relative;
    z-index: 10;
}

.prayer-area-text {
    font-size: 0.95rem;
    opacity: 0.95;
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.settings-button {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 10px;
    padding: 0.4rem 0.7rem;
    cursor: pointer;
    line-height: 1;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.settings-button:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.05);
}

.prayer-times-header {
    margin-bottom: 1rem;
}

/* Date/Time bar - Green Theme */
.date-time-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.date-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    font-size: 0.9rem;
    white-space: nowrap;
    color: #ffffff;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.date-chip .icon { 
    opacity: 0.95; 
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
}

.city-selector {
    background: rgba(255,255,255,0.25);
    border: 1px solid rgba(255,255,255,0.4);
    color: white;
    padding: 0.5rem;
    border-radius: 6px;
    font-size: 0.9rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.city-selector option {
    background: #2d5016;
    color: white;
}

.city-selector.hidden {
    display: none;
}

.prayer-times-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.loading {
    text-align: center;
    padding: 1rem;
    color: rgba(255,255,255,0.95);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.error {
    text-align: center;
    padding: 1rem;
    color: #ffd1d1;
    background: rgba(255,255,255,0.15);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<!-- Prayer Times Box with Islamic Art Design -->
<div class="prayer-times-box">
    <!-- Islamic Pattern Overlay -->
    <div class="islamic-pattern"></div>
    
    <!-- Decorative Elements -->
    <div class="crescent-moon"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="minaret"></div>
    
    <div class="prayer-times-header">
        <h3 class="prayer-times-title">নামাজের সময়</h3>
    </div>
    <div class="date-time-bar">
        <div class="date-chip"><span class="icon">📅</span><span id="englishDateText">—</span></div>
        <div class="date-chip"><span class="icon">🗓️</span><span id="hijriDateText">—</span></div>
        <div class="date-chip"><span class="icon">⏱️</span><span id="currentTimeText">—</span></div>
    </div>
    <div id="prayerTimesContent">
        <div class="loading">Loading prayer times...</div>
    </div>
    <div class="prayer-times-footer">
        <span id="prayerAreaText" class="prayer-area-text">ঢাকা ও পার্শবর্তী এলাকার জন্য</span>
        <button id="settingsButton" type="button" class="settings-button" aria-label="Change city">⚙️</button>
    </div>
    <div style="margin-top: 0.5rem;">
        <select id="citySelector" class="city-selector hidden" aria-label="Select city">
            <option value="">Loading cities...</option>
        </select>
    </div>
</div>

<script>
// Prayer Times functionality
let currentCityId = 1; // Default to Dhaka
let currentCityName = 'ঢাকা';
const BASE_URL = '<?= base_url() ?>';

function updateAreaText() {
    const areaText = document.getElementById('prayerAreaText');
    if (areaText) {
        areaText.textContent = `${currentCityName} ও পার্শবর্তী এলাকার জন্য`;
    }
}

// Load cities for dropdown
async function loadCities() {
    try {
        const response = await fetch(`${BASE_URL}prayer-time/cities`);
        const data = await response.json();
        
        if (data.success) {
            const citySelector = document.getElementById('citySelector');
            citySelector.innerHTML = '';
            
            // Find Dhaka city and set it as default
            let dhakaCity = null;
            data.cities.forEach(city => {
                const lower = (city.name || '').toLowerCase();
                if (lower.includes('ঢাকা') || lower.includes('dhaka')) {
                    dhakaCity = city;
                }
            });
            
            // If Dhaka found, set it as current city
            if (dhakaCity) {
                currentCityId = dhakaCity.id;
                currentCityName = dhakaCity.name;
            }

            // Build options
            data.cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.id;
                option.textContent = city.name;
                if (city.id == currentCityId) {
                    option.selected = true;
                }
                citySelector.appendChild(option);
            });

            updateAreaText();
        }
    } catch (error) {
        console.error('Error loading cities:', error);
        // Fallback: Set default city if cities can't be loaded
        currentCityId = 1;
        currentCityName = 'ঢাকা';
        updateAreaText();
    }
}

// Load prayer times for selected city
async function loadPrayerTimes(cityId = null) {
    const content = document.getElementById('prayerTimesContent');
    content.innerHTML = '<div class="loading">Loading prayer times...</div>';
    
    try {
        const url = cityId ? `${BASE_URL}prayer-time/today/${cityId}` : `${BASE_URL}prayer-time/today`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const prayerTimes = data.prayer_times;
            // If backend returns city name, sync it
            if (data.city) {
                currentCityName = data.city;
                updateAreaText();
            }
            content.innerHTML = `
                <ul class="prayer-times-list">
                    <li class="prayer-time-item fajr">
                        <span class="prayer-name">ফজর</span>
                        <span class="prayer-time">${prayerTimes.fajr}</span>
                    </li>
                    <li class="prayer-time-item sunrise">
                        <span class="prayer-name">সূর্যোদয়</span>
                        <span class="prayer-time">${prayerTimes.sunrise}</span>
                    </li>
                    <li class="prayer-time-item dhuhr">
                        <span class="prayer-name">যোহর</span>
                        <span class="prayer-time">${prayerTimes.dhuhr}</span>
                    </li>
                    <li class="prayer-time-item asr">
                        <span class="prayer-name">আসর</span>
                        <span class="prayer-time">${prayerTimes.asr}</span>
                    </li>
                    <li class="prayer-time-item maghrib">
                        <span class="prayer-name">মাগরিব</span>
                        <span class="prayer-time">${prayerTimes.maghrib}</span>
                    </li>
                    <li class="prayer-time-item isha">
                        <span class="prayer-name">এশা</span>
                        <span class="prayer-time">${prayerTimes.isha}</span>
                    </li>
                </ul>
            `;
        } else {
            content.innerHTML = `
                <div class="error">
                    <div style="margin-bottom: 0.5rem;">${data.error || 'Prayer times not available'}</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">
                        Prayer times data needs to be populated in the admin panel first.
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading prayer times:', error);
        content.innerHTML = `
            <div class="error">
                <div style="margin-bottom: 0.5rem;">Error loading prayer times</div>
                <div style="font-size: 0.8rem; opacity: 0.8;">
                    Please check your internet connection and try again.
                </div>
            </div>
        `;
    }
}

// Settings toggle to show/hide selector
document.addEventListener('click', function(e) {
    const btn = document.getElementById('settingsButton');
    const selector = document.getElementById('citySelector');
    if (!btn || !selector) return;
    if (e.target === btn) {
        selector.classList.toggle('hidden');
    }
});

// Handle city selection change
document.getElementById('citySelector').addEventListener('change', function() {
    const selectedCityId = this.value;
    const selectedText = this.options[this.selectedIndex]?.text || currentCityName;
    if (selectedCityId) {
        currentCityId = selectedCityId;
        currentCityName = selectedText;
        updateAreaText();
        loadPrayerTimes(selectedCityId);
        // Collapse selector after choosing
        this.classList.add('hidden');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadCities();
    updateAreaText();
    loadPrayerTimes(currentCityId);
    // Initialize dates/time
    updateClockAndDates();
    setInterval(updateClockAndDates, 1000);
});

// Format and render English date, Hijri date, and current time
function updateClockAndDates() {
    const now = new Date();
    const english = new Intl.DateTimeFormat('en-GB', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    }).format(now);
    let hijri;
    try {
        // Hijri date using Islamic calendar via Intl API (fallback if unsupported)
        hijri = new Intl.DateTimeFormat('en-TN-u-ca-islamic', {
            weekday: 'short', year: 'numeric', month: 'long', day: 'numeric'
        }).format(now);
    } catch (e) {
        hijri = 'Hijri date unavailable';
    }
    const time = new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).format(now);

    const englishEl = document.getElementById('englishDateText');
    const hijriEl = document.getElementById('hijriDateText');
    const timeEl = document.getElementById('currentTimeText');
    if (englishEl) englishEl.textContent = english;
    if (hijriEl) hijriEl.textContent = hijri;
    if (timeEl) timeEl.textContent = time;
}
</script>
