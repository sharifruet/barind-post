
<style>
/* Compact Prayer Times Widget */
.prayer-times-widget {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
    position: relative;
    overflow: hidden;
}

.prayer-times-widget::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #e74c3c, #f39c12, #27ae60, #3498db, #9b59b6);
}

.prayer-times-widget .widget-header {
    text-align: center;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.prayer-times-widget .widget-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: #ecf0f1;
}

.prayer-times-widget .current-time {
    font-size: 1.1rem;
    color: #bdc3c7;
    margin-top: 0.2rem;
    font-weight: 600;
}

.prayer-times-widget .city-info {
    font-size: 0.9rem;
    color: #95a5a6;
    margin-top: 0.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    cursor: pointer;
    padding: 0.3rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.prayer-times-widget .city-info:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ecf0f1;
    transform: scale(1.05);
}

/* City Popup Modal Styles */
.city-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.city-popup {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: popupSlideIn 0.3s ease-out;
}

@keyframes popupSlideIn {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.city-popup-header {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.city-popup-header h4 {
    margin: 0;
    color: #ecf0f1;
    font-size: 1.1rem;
    font-weight: 600;
}

.city-popup-close {
    background: none;
    border: none;
    color: #ecf0f1;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.city-popup-close:hover {
    background: rgba(255, 255, 255, 0.1);
}

.city-popup-content {
    padding: 1rem;
    max-height: 60vh;
    overflow-y: auto;
}

.city-popup-item {
    padding: 0.8rem 1rem;
    margin-bottom: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    cursor: pointer;
    color: #ecf0f1;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.city-popup-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
    border-color: rgba(52, 152, 219, 0.3);
}

.city-popup-item.selected {
    background: rgba(52, 152, 219, 0.2);
    border-color: #3498db;
    color: #3498db;
    font-weight: 600;
}

.city-popup-item:last-child {
    margin-bottom: 0;
}

.prayer-times-widget .city-selector {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    color: #ecf0f1;
    font-size: 0.9rem;
    padding: 0.2rem 0.4rem;
    margin-top: 0.3rem;
    width: 100%;
    outline: none;
}

.prayer-times-widget .city-selector:focus {
    border-color: #3498db;
    background: rgba(255, 255, 255, 0.15);
}

.prayer-times-widget .prayer-times-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.prayer-times-widget .prayer-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0.6rem;
    margin-bottom: 0.3rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.prayer-times-widget .prayer-time:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(2px);
}

.prayer-times-widget .prayer-time.current {
    background: rgba(231, 76, 60, 0.2);
    border-left-color: #e74c3c;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.prayer-times-widget .prayer-name {
    font-size: 1rem;
    font-weight: 600;
    color: #ecf0f1;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.prayer-times-widget .prayer-time-value {
    font-size: 1.0rem;
    font-weight: 700;
    color: #fff;
    font-family: 'Courier New', monospace;
}

.prayer-times-widget .prayer-time.current .prayer-time-value {
    color: #e74c3c;
    font-weight: 700;
}

.prayer-times-widget .prayer-time.current .prayer-name {
    color: #e74c3c;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .prayer-times-widget {
        padding: 0.8rem;
    }
    
    .prayer-times-widget .prayer-time {
        padding: 0.3rem 0.5rem;
        margin-bottom: 0.2rem;
    }
    
}

/* Compact version for 2-column layout */
.col-md-2 .prayer-times-widget {
    padding: 0.6rem;
    margin-bottom: 0.8rem;
}


.col-md-2 .prayer-times-widget .prayer-time {
    padding: 0.25rem 0.4rem;
    margin-bottom: 0.2rem;
}

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