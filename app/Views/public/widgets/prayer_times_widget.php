<?php
/**
 * Prayer Times Widget with Islamic Art Design
 * Displays prayer times with masjid-like decorative elements
 */
?>

<style>
/* Prayer Times Box Styles - Multifoil Lobed Arch Pine Green Theme */
.prayer-times-box {
    background: linear-gradient(135deg, #01796f 0%, #028a7b 25%, #039b87 50%, #028a7b 75%, #01796f 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 32px rgba(1, 121, 111, 0.4);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.15);
    clip-path: polygon(
        0% 0%,           /* Top left */
        100% 0%,         /* Top right */
        100% 85%,        /* Right side down */
        85% 100%,        /* Bottom right corner */
        15% 100%,        /* Bottom left corner */
        0% 85%           /* Left side up */
    );
}

/* Multifoil Lobed Arch - Main Structure */
.prayer-times-box::before {
    content: "";
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    width: 140px;
    height: 70px;
    background: linear-gradient(135deg, #01796f 0%, #028a7b 50%, #01796f 100%);
    border-radius: 70px 70px 0 0;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-bottom: none;
    box-shadow: 0 4px 12px rgba(1, 121, 111, 0.3);
    z-index: 1;
    clip-path: polygon(
        0% 100%,         /* Bottom left */
        20% 80%,         /* Left lobe start */
        15% 60%,         /* Left lobe peak */
        25% 45%,         /* Left lobe end */
        40% 30%,         /* Center lobe start */
        50% 20%,         /* Center lobe peak */
        60% 30%,         /* Center lobe end */
        75% 45%,         /* Right lobe start */
        85% 60%,         /* Right lobe peak */
        80% 80%,         /* Right lobe end */
        100% 100%        /* Bottom right */
    );
}

/* Multifoil Arch Inner Layer */
.prayer-times-box::after {
    content: "";
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    height: 60px;
    background: linear-gradient(135deg, rgba(1, 121, 111, 0.8) 0%, rgba(2, 138, 123, 0.6) 50%, rgba(1, 121, 111, 0.8) 100%);
    border-radius: 60px 60px 0 0;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-bottom: none;
    z-index: 2;
    clip-path: polygon(
        0% 100%,         /* Bottom left */
        22% 82%,         /* Left lobe start */
        18% 65%,         /* Left lobe peak */
        28% 50%,         /* Left lobe end */
        42% 35%,         /* Center lobe start */
        50% 25%,         /* Center lobe peak */
        58% 35%,         /* Center lobe end */
        72% 50%,         /* Right lobe start */
        82% 65%,         /* Right lobe peak */
        78% 82%,         /* Right lobe end */
        100% 100%        /* Bottom right */
    );
}

/* Islamic Geometric Pattern Background - Mihrab Theme */
.prayer-times-box .geometric-pattern {
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
    z-index: 0;
}

/* Mihrab Decorative Pillars */
.prayer-times-box .mihrab-pillar {
    position: absolute;
    top: 0;
    width: 8px;
    height: 100%;
    background: linear-gradient(to bottom, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
    border-radius: 4px;
    pointer-events: none;
}

.prayer-times-box .mihrab-pillar.left {
    left: 20px;
}

.prayer-times-box .mihrab-pillar.right {
    right: 20px;
}

/* Multifoil Arch Individual Lobe Decorations */
.prayer-times-box .lobe-decoration {
    position: absolute;
    top: -10px;
    width: 20px;
    height: 20px;
    background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.4) 0 40%, transparent 40% 100%);
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 2px 6px rgba(1, 121, 111, 0.3);
    z-index: 3;
}

.prayer-times-box .lobe-decoration.left {
    left: calc(50% - 60px);
    transform: translateX(-50%);
}

.prayer-times-box .lobe-decoration.center {
    left: 50%;
    transform: translateX(-50%);
    width: 24px;
    height: 24px;
}

.prayer-times-box .lobe-decoration.right {
    left: calc(50% + 60px);
    transform: translateX(-50%);
}

/* Multifoil Arch Keystone */
.prayer-times-box .keystone {
    position: absolute;
    top: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 18px;
    height: 18px;
    background: linear-gradient(135deg, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0.2) 100%);
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 2px 8px rgba(1, 121, 111, 0.3);
    z-index: 4;
}

/* Mihrab Inner Pattern */
.prayer-times-box .mihrab-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 50% 0%, rgba(255,255,255,0.1) 0 40%, transparent 40% 100%),
        linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%);
    pointer-events: none;
    z-index: 1;
}

/* Crescent Moon Decorative Element - Pine Green Theme */
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

/* Star Decorative Elements - Pine Green Theme */
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

/* Minaret Decorative Element - Pine Green Theme */
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

/* Islamic Calligraphy Style Header - Mihrab Theme */
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
    margin-top: 1rem;
}

/* Multifoil Arch Decoration Lines */
.prayer-times-box .arch-lines {
    position: absolute;
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 50px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-bottom: none;
    border-radius: 50px 50px 0 0;
    pointer-events: none;
    z-index: 2;
    clip-path: polygon(
        0% 100%,         /* Bottom left */
        20% 80%,         /* Left lobe start */
        15% 60%,         /* Left lobe peak */
        25% 45%,         /* Left lobe end */
        40% 30%,         /* Center lobe start */
        50% 20%,         /* Center lobe peak */
        60% 30%,         /* Center lobe end */
        75% 45%,         /* Right lobe start */
        85% 60%,         /* Right lobe peak */
        80% 80%,         /* Right lobe end */
        100% 100%        /* Bottom right */
    );
}

.prayer-times-box .arch-lines::before {
    content: "";
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 40px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-bottom: none;
    border-radius: 40px 40px 0 0;
    clip-path: polygon(
        0% 100%,         /* Bottom left */
        22% 82%,         /* Left lobe start */
        18% 65%,         /* Left lobe peak */
        28% 50%,         /* Left lobe end */
        42% 35%,         /* Center lobe start */
        50% 25%,         /* Center lobe peak */
        58% 35%,         /* Center lobe end */
        72% 50%,         /* Right lobe start */
        82% 65%,         /* Right lobe peak */
        78% 82%,         /* Right lobe end */
        100% 100%        /* Bottom right */
    );
}

/* Multifoil Arch Floral Decorations */
.prayer-times-box .floral-decoration {
    position: absolute;
    top: 5px;
    width: 12px;
    height: 12px;
    background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 2;
}

.prayer-times-box .floral-decoration.left {
    left: calc(50% - 45px);
    transform: translateX(-50%);
}

.prayer-times-box .floral-decoration.center-left {
    left: calc(50% - 20px);
    transform: translateX(-50%);
}

.prayer-times-box .floral-decoration.center-right {
    left: calc(50% + 20px);
    transform: translateX(-50%);
}

.prayer-times-box .floral-decoration.right {
    left: calc(50% + 45px);
    transform: translateX(-50%);
}


/* Enhanced Prayer Time Items - Pine Green Theme */
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

/* Per-prayer icons - Pine Green Theme */
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

/* Date/Time bar - Pine Green Theme */
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
    background: #01796f;
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

<!-- Prayer Times Box with Multifoil Lobed Arch Design -->
<div class="prayer-times-box">
    <!-- Mihrab Pattern Overlay -->
    <div class="mihrab-pattern"></div>
    
    <!-- Geometric Pattern Background -->
    <div class="geometric-pattern"></div>
    
    <!-- Multifoil Lobed Arch Decorative Elements -->
    <div class="mihrab-pillar left"></div>
    <div class="mihrab-pillar right"></div>
    <div class="lobe-decoration left"></div>
    <div class="lobe-decoration center"></div>
    <div class="lobe-decoration right"></div>
    <div class="keystone"></div>
    <div class="arch-lines"></div>
    
    <!-- Floral Decorations for Multifoil Arch -->
    <div class="floral-decoration left"></div>
    <div class="floral-decoration center-left"></div>
    <div class="floral-decoration center-right"></div>
    <div class="floral-decoration right"></div>
    
    <!-- Traditional Decorative Elements -->
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
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.fajr)}</span>
                    </li>
                    <li class="prayer-time-item sunrise">
                        <span class="prayer-name">সূর্যোদয়</span>
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.sunrise)}</span>
                    </li>
                    <li class="prayer-time-item dhuhr">
                        <span class="prayer-name">যোহর</span>
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.dhuhr)}</span>
                    </li>
                    <li class="prayer-time-item asr">
                        <span class="prayer-name">আসর</span>
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.asr)}</span>
                    </li>
                    <li class="prayer-time-item maghrib">
                        <span class="prayer-name">মাগরিব</span>
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.maghrib)}</span>
                    </li>
                    <li class="prayer-time-item isha">
                        <span class="prayer-name">এশা</span>
                        <span class="prayer-time">${toBanglaNumerals(prayerTimes.isha)}</span>
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

// Convert English numerals to Bangla numerals
function toBanglaNumerals(str) {
    const banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return str.replace(/\d/g, (digit) => banglaDigits[parseInt(digit)]);
}

// Convert English weekday names to Bangla
function getBanglaWeekday(weekday) {
    const weekdays = {
        'Sunday': 'রবিবার',
        'Monday': 'সোমবার', 
        'Tuesday': 'মঙ্গলবার',
        'Wednesday': 'বুধবার',
        'Thursday': 'বৃহস্পতিবার',
        'Friday': 'শুক্রবার',
        'Saturday': 'শনিবার'
    };
    return weekdays[weekday] || weekday;
}

// Convert English month names to Bangla
function getBanglaMonth(month) {
    const months = {
        'January': 'জানুয়ারি',
        'February': 'ফেব্রুয়ারি',
        'March': 'মার্চ',
        'April': 'এপ্রিল',
        'May': 'মে',
        'June': 'জুন',
        'July': 'জুলাই',
        'August': 'আগস্ট',
        'September': 'সেপ্টেম্বর',
        'October': 'অক্টোবর',
        'November': 'নভেম্বর',
        'December': 'ডিসেম্বর'
    };
    return months[month] || month;
}

// Convert Hijri month names to Bangla
function getBanglaHijriMonth(month) {
    const hijriMonths = {
        'Muharram': 'মুহাররম',
        'Safar': 'সফর',
        'Rabiʻ I': 'রবিউল আউয়াল',
        'Rabiʻ II': 'রবিউস সানি',
        'Jumada I': 'জুমাদাল আউয়াল',
        'Jumada II': 'জুমাদাস সানি',
        'Rajab': 'রজব',
        'Shaʻban': 'শাবান',
        'Ramadan': 'রমজান',
        'Shawwal': 'শাওয়াল',
        'Dhuʻl-Qiʻdah': 'জিলকদ',
        'Dhuʻl-Hijjah': 'জিলহজ'
    };
    return hijriMonths[month] || month;
}

// Convert Hijri weekday names to Bangla
function getBanglaHijriWeekday(weekday) {
    const hijriWeekdays = {
        'Sun': 'রবি',
        'Mon': 'সোম',
        'Tue': 'মঙ্গল',
        'Wed': 'বুধ',
        'Thu': 'বৃহস্পতি',
        'Fri': 'শুক্র',
        'Sat': 'শনি'
    };
    return hijriWeekdays[weekday] || weekday;
}

// Format and render English date, Hijri date, and current time in Bangla
function updateClockAndDates() {
    const now = new Date();
    
    // English date in Bangla
    const englishWeekday = new Intl.DateTimeFormat('en-GB', { weekday: 'long' }).format(now);
    const englishMonth = new Intl.DateTimeFormat('en-GB', { month: 'long' }).format(now);
    const englishDay = new Intl.DateTimeFormat('en-GB', { day: 'numeric' }).format(now);
    const englishYear = new Intl.DateTimeFormat('en-GB', { year: 'numeric' }).format(now);
    
    const banglaWeekday = getBanglaWeekday(englishWeekday);
    const banglaMonth = getBanglaMonth(englishMonth);
    const banglaDay = toBanglaNumerals(englishDay);
    const banglaYear = toBanglaNumerals(englishYear);
    
    const english = `${banglaWeekday}, ${banglaDay} ${banglaMonth} ${banglaYear}`;
    
    // Hijri date in Bangla
    let hijri;
    try {
        const hijriWeekday = new Intl.DateTimeFormat('en-TN-u-ca-islamic', { weekday: 'short' }).format(now);
        const hijriMonth = new Intl.DateTimeFormat('en-TN-u-ca-islamic', { month: 'long' }).format(now);
        const hijriDay = new Intl.DateTimeFormat('en-TN-u-ca-islamic', { day: 'numeric' }).format(now);
        const hijriYear = new Intl.DateTimeFormat('en-TN-u-ca-islamic', { year: 'numeric' }).format(now);
        
        const banglaHijriWeekday = getBanglaHijriWeekday(hijriWeekday);
        const banglaHijriMonth = getBanglaHijriMonth(hijriMonth);
        const banglaHijriDay = toBanglaNumerals(hijriDay);
        const banglaHijriYear = toBanglaNumerals(hijriYear);
        
        hijri = `${banglaHijriWeekday}, ${banglaHijriMonth} ${banglaHijriDay}, ${banglaHijriYear} হিজরি`;
    } catch (e) {
        hijri = 'হিজরি তারিখ পাওয়া যায়নি';
    }
    
    // Time in Bangla numerals
    const time = new Intl.DateTimeFormat('en-GB', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).format(now);
    const banglaTime = toBanglaNumerals(time);

    const englishEl = document.getElementById('englishDateText');
    const hijriEl = document.getElementById('hijriDateText');
    const timeEl = document.getElementById('currentTimeText');
    if (englishEl) englishEl.textContent = english;
    if (hijriEl) hijriEl.textContent = hijri;
    if (timeEl) timeEl.textContent = banglaTime;
}
</script>
