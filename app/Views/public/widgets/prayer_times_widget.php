<style>
/* Prayer times — quiet rail module matching the editorial theme */
.prayer-times-widget {
    border: 1px solid var(--rule);
    background: var(--paper);
    padding: 0;
}

.prayer-times-widget .widget-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--rule);
    background: var(--paper-2);
}

.prayer-times-widget .widget-title {
    font-family: var(--sans);
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.prayer-times-widget .current-time {
    font-family: var(--sans);
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--accent);
    font-variant-numeric: tabular-nums;
}

.prayer-times-widget .prayer-times-list {
    list-style: none;
    margin: 0;
    padding: 0.35rem 1rem;
}

.prayer-times-widget .prayer-time {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.45rem 0;
    border-bottom: 1px solid var(--rule);
}

.prayer-times-widget .prayer-time:last-child { border-bottom: 0; }

.prayer-times-widget .prayer-name {
    font-family: var(--sans);
    font-size: 0.88rem;
    color: var(--ink-2);
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.prayer-times-widget .prayer-name i { color: var(--ink-4); font-size: 0.78rem; }

.prayer-times-widget .prayer-time-value {
    font-family: var(--sans);
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
}

.prayer-times-widget .prayer-time.current .prayer-name,
.prayer-times-widget .prayer-time.current .prayer-name i,
.prayer-times-widget .prayer-time.current .prayer-time-value {
    color: var(--accent);
    font-weight: 700;
}

.prayer-times-widget .city-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    cursor: pointer;
    font-family: var(--sans);
    font-size: 0.8rem;
    color: var(--ink-3);
    padding: 0.6rem 1rem;
    border-top: 1px solid var(--rule);
    background: var(--paper-2);
}

.prayer-times-widget .city-info:hover { color: var(--accent); }

.prayer-times-widget .city-selector { display: none !important; }

/* City picker modal */
.city-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(20, 20, 15, 0.45);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.city-popup {
    background: var(--paper);
    border: 1px solid var(--rule);
    width: 100%;
    max-width: 380px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0,0,0,0.22);
}

.city-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--rule);
    background: var(--paper-2);
}

.city-popup-header h4 {
    margin: 0;
    font-family: var(--sans);
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink);
}

.city-popup-close {
    background: none;
    border: 0;
    font-size: 1.4rem;
    line-height: 1;
    color: var(--ink-3);
    cursor: pointer;
    padding: 0 0.25rem;
}

.city-popup-close:hover { color: var(--accent); }

.city-popup-content { padding: 0.5rem 1rem 1rem; max-height: 60vh; overflow-y: auto; }

.city-popup-item {
    padding: 0.6rem 0.25rem;
    border-bottom: 1px solid var(--rule);
    cursor: pointer;
    font-family: var(--sans);
    font-size: 0.92rem;
    color: var(--ink-2);
}

.city-popup-item:last-child { border-bottom: 0; }
.city-popup-item:hover { color: var(--accent); }
.city-popup-item.selected { color: var(--accent); font-weight: 700; }
</style>

<div class="prayer-times-widget">
    <div class="widget-header">
        <h4 class="widget-title">নামাজের সময়</h4>
        <div class="current-time" id="currentTimeText">--:--:--</div>
    </div>
    
    <ul class="prayer-times-list" id="prayerTimesList">
        <li class="prayer-time" data-prayer="fajr">
            <span class="prayer-name">
                <i class="fas fa-sun"></i>
                ফজর
            </span>
            <span class="prayer-time-value" id="fajrTime">--:--</span>
        </li>
        <li class="prayer-time" data-prayer="dhuhr">
            <span class="prayer-name">
                <i class="fas fa-sun"></i>
                যোহর
            </span>
            <span class="prayer-time-value" id="dhuhrTime">--:--</span>
        </li>
        <li class="prayer-time" data-prayer="asr">
            <span class="prayer-name">
                <i class="fas fa-sun"></i>
                আসর
            </span>
            <span class="prayer-time-value" id="asrTime">--:--</span>
        </li>
        <li class="prayer-time" data-prayer="maghrib">
            <span class="prayer-name">
                <i class="fas fa-moon"></i>
                মাগরিব
            </span>
            <span class="prayer-time-value" id="maghribTime">--:--</span>
        </li>
        <li class="prayer-time" data-prayer="isha">
            <span class="prayer-name">
                <i class="fas fa-moon"></i>
                এশা
            </span>
            <span class="prayer-time-value" id="ishaTime">--:--</span>
        </li>
    </ul>
    <div class="city-info" id="cityInfo" onclick="showCityPopup()">
        <i class="fas fa-map-marker-alt"></i>
        <span id="cityName">লোড হচ্ছে...</span>
    </div>
    <select class="city-selector" id="citySelector" onchange="changeCity()" style="display: none;">
        <option value="">লোড হচ্ছে...</option>
    </select>
</div>

<!-- City Selection Popup Modal -->
<div class="city-popup-overlay" id="cityPopupOverlay" onclick="hideCityPopup()">
    <div class="city-popup" onclick="event.stopPropagation()">
        <div class="city-popup-header">
            <h4>শহর নির্বাচন করুন</h4>
            <button class="city-popup-close" onclick="hideCityPopup()">&times;</button>
        </div>
        <div class="city-popup-content" id="cityPopupContent">
            <!-- Cities will be populated here -->
        </div>
    </div>
</div>

<script>
// Convert English numerals to Bangla
function toBanglaNumerals(str) {
    const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const bangla = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    
    return str.replace(/[0-9]/g, function(match) {
        return bangla[english.indexOf(match)];
    });
}

// Update current time
function updateCurrentTime() {
    const now = new Date();
    const time = now.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    const banglaTime = toBanglaNumerals(time);
    const timeElement = document.getElementById('currentTimeText');
    if (timeElement) {
        timeElement.textContent = banglaTime;
    }
    
    // Update current prayer highlighting
    updateCurrentPrayer();
}

// Change city function
function changeCity() {
    const citySelector = document.getElementById('citySelector');
    const cityName = document.getElementById('cityName');
    const selectedCityId = citySelector.value;
    
    // Show loading state
    showLoadingState();
    
    // Fetch prayer times for selected city
    fetchPrayerTimes(selectedCityId, cityName);
}

// Show loading state
function showLoadingState() {
    const prayerTimes = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
    prayerTimes.forEach(prayer => {
        const timeElement = document.getElementById(prayer + 'Time');
        if (timeElement) {
            timeElement.textContent = '--:--';
        }
    });
}

// Fetch prayer times from API
async function fetchPrayerTimes(cityId, cityNameElement) {
    try {
        const response = await fetch(`/api/prayer-times/today/${cityId}`);
        const data = await response.json();
        
        if (data.success && data.prayer_times) {
            // Update city name
            if (cityNameElement) {
                cityNameElement.textContent = data.city + 'র জন্য';
            }
            
            // Update prayer times with Bangla numerals
            const prayerTimes = data.prayer_times;
            document.getElementById('fajrTime').textContent = timeToBangla(prayerTimes.fajr.substring(0, 5));
            document.getElementById('dhuhrTime').textContent = timeToBangla(prayerTimes.dhuhr.substring(0, 5));
            document.getElementById('asrTime').textContent = timeToBangla(prayerTimes.asr.substring(0, 5));
            document.getElementById('maghribTime').textContent = timeToBangla(prayerTimes.maghrib.substring(0, 5));
            document.getElementById('ishaTime').textContent = timeToBangla(prayerTimes.isha.substring(0, 5));
            
            // Update current prayer highlighting
            updateCurrentPrayer(prayerTimes);
        } else {
            console.error('Failed to fetch prayer times:', data.error);
            showErrorState();
        }
    } catch (error) {
        console.error('Error fetching prayer times:', error);
        showErrorState();
    }
}

// Show error state
function showErrorState() {
    const prayerTimes = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
    prayerTimes.forEach(prayer => {
        const timeElement = document.getElementById(prayer + 'Time');
        if (timeElement) {
            timeElement.textContent = '--:--';
        }
    });
}

// Update current prayer highlighting
function updateCurrentPrayer(prayerTimes = null) {
    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();
    
    // Remove current class from all prayer times
    document.querySelectorAll('.prayer-time').forEach(el => {
        el.classList.remove('current');
    });
    
    // If no prayer times provided, get from DOM
    if (!prayerTimes) {
        prayerTimes = {
            fajr: document.getElementById('fajrTime').textContent,
            dhuhr: document.getElementById('dhuhrTime').textContent,
            asr: document.getElementById('asrTime').textContent,
            maghrib: document.getElementById('maghribTime').textContent,
            isha: document.getElementById('ishaTime').textContent
        };
    }
    
    // Convert prayer times to English for comparison
    const prayerTimesArray = [
        { name: 'fajr', time: prayerTimes.fajr },
        { name: 'dhuhr', time: prayerTimes.dhuhr },
        { name: 'asr', time: prayerTimes.asr },
        { name: 'maghrib', time: prayerTimes.maghrib },
        { name: 'isha', time: prayerTimes.isha }
    ];
    
    for (let i = 0; i < prayerTimesArray.length; i++) {
        const prayer = prayerTimesArray[i];
        const englishTime = toEnglishNumerals(prayer.time);
        const [hours, minutes] = englishTime.split(':').map(Number);
        const prayerTime = hours * 60 + minutes;
        
        if (currentTime >= prayerTime && (i === prayerTimesArray.length - 1 || currentTime < (() => {
            const nextPrayer = prayerTimesArray[i + 1];
            const nextEnglishTime = toEnglishNumerals(nextPrayer.time);
            const [nextHours, nextMinutes] = nextEnglishTime.split(':').map(Number);
            return nextHours * 60 + nextMinutes;
        })())) {
            const prayerElement = document.querySelector(`[data-prayer="${prayer.name}"]`);
            if (prayerElement) {
                prayerElement.classList.add('current');
            }
            break;
        }
    }
}

// Convert English numerals to Bangla
function timeToBangla(str) {
    const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const bangla = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    
    return str.replace(/[0-9]/g, function(match) {
        return bangla[english.indexOf(match)];
    });
}

// Convert Bangla numerals to English
function toEnglishNumerals(str) {
    const bangla = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    
    return str.replace(/[০-৯]/g, function(match) {
        return english[bangla.indexOf(match)];
    });
}

// Store cities list for dropdown
let citiesList = [];
let currentCityId = null;

// Load cities from API
async function loadCities() {
    try {
        const response = await fetch('/api/prayer-times/cities');
        const data = await response.json();
        
        if (data.success && data.cities && data.cities.length > 0) {
            citiesList = data.cities; // Store cities for popup
            
            let defaultCityId = null;
            let defaultCityName = 'ঢাকা';
            
            // Check if Dhaka is available
            data.cities.forEach((city, index) => {
                if (city.name === 'ঢাকা' || city.name.includes('ঢাকা')) {
                    defaultCityId = city.id;
                    defaultCityName = city.name;
                }
            });
            
            // If Dhaka not found, use first city as fallback
            if (!defaultCityId) {
                defaultCityId = data.cities[0].id;
                defaultCityName = data.cities[0].name;
            }
            
            currentCityId = defaultCityId;
            
            // Load prayer times for the default city
            const cityName = document.getElementById('cityName');
            fetchPrayerTimes(defaultCityId, cityName);
        }
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

// Show city selection popup
function showCityPopup() {
    const popup = document.getElementById('cityPopupOverlay');
    const content = document.getElementById('cityPopupContent');
    
    // Clear existing content
    content.innerHTML = '';
    
    // Add all cities to popup
    citiesList.forEach(city => {
        const cityItem = document.createElement('div');
        cityItem.className = 'city-popup-item';
        cityItem.textContent = city.name;
        cityItem.onclick = () => selectCityFromPopup(city.id, city.name);
        
        // Mark current city as selected
        if (city.id === currentCityId) {
            cityItem.classList.add('selected');
        }
        
        content.appendChild(cityItem);
    });
    
    // Show popup
    popup.style.display = 'flex';
}

// Hide city selection popup
function hideCityPopup() {
    const popup = document.getElementById('cityPopupOverlay');
    popup.style.display = 'none';
}

// Select a city from popup
function selectCityFromPopup(cityId, cityName) {
    currentCityId = cityId;
    
    // Update city name display
    document.getElementById('cityName').textContent = cityName + 'র জন্য';
    
    // Hide popup
    hideCityPopup();
    
    // Load prayer times for selected city
    fetchPrayerTimes(cityId, document.getElementById('cityName'));
}

// Close popup when clicking outside
document.addEventListener('click', function(event) {
    const popup = document.getElementById('cityPopupOverlay');
    if (event.target === popup) {
        hideCityPopup();
    }
});

// Initialize and update every second
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    // Load cities and prayer times
    loadCities();
    
    // Update current prayer highlighting every minute
    setInterval(updateCurrentPrayer, 60000);
});
</script>