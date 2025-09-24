<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barind Post - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Noto Sans Bengali', 'Noto Sans', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background: #f8f9fa; 
        }
        header { background: #343a40; color: #fff; padding: 1rem 2rem; }
        .container { max-width: 1200px; margin: 2rem auto; display: grid; grid-template-columns: 1fr 300px; gap: 2rem; }
        .main-content { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .sidebar { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); height: fit-content; }
        h1 { margin-top: 0; }
        .news-list { list-style: none; padding: 0; }
        .news-item { margin-bottom: 2rem; }
        .news-title { font-size: 1.5rem; color: #007bff; margin: 0; }
        .news-meta { color: #888; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .news-summary { margin: 0.5rem 0 0 0; }
        
        /* Prayer Times Box Styles */
        .prayer-times-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .prayer-times-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .prayer-times-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        .city-selector {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .city-selector option {
            background: #667eea;
            color: white;
        }
        .prayer-times-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .prayer-time-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .prayer-time-item:last-child {
            border-bottom: none;
        }
        .prayer-name {
            font-weight: 500;
            font-size: 0.95rem;
        }
        .prayer-time {
            font-weight: 600;
            font-size: 1rem;
        }
        .loading {
            text-align: center;
            padding: 1rem;
            color: rgba(255,255,255,0.8);
        }
        .error {
            text-align: center;
            padding: 1rem;
            color: #ff6b6b;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .sidebar {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Barind Post</h1>
        <p>Your trusted source for local and global news</p>
    </header>
    <div class="container">
        <div class="main-content">
            <h2>Latest News</h2>
            <ul class="news-list">
                <li class="news-item">
                    <div class="news-title">Sample News Headline 1</div>
                    <div class="news-meta">Posted on 2024-06-01 by Admin</div>
                    <div class="news-summary">This is a summary of the first news article. Stay tuned for more updates!</div>
                </li>
                <li class="news-item">
                    <div class="news-title">Sample News Headline 2</div>
                    <div class="news-meta">Posted on 2024-06-01 by Editor</div>
                    <div class="news-summary">This is a summary of the second news article. More news coming soon!</div>
                </li>
            </ul>
        </div>
        
        <div class="sidebar">
            <!-- Prayer Times Box -->
            <div class="prayer-times-box">
                <div class="prayer-times-header">
                    <h3 class="prayer-times-title">নামাজের সময়</h3>
                    <select id="citySelector" class="city-selector">
                        <option value="">Loading cities...</option>
                    </select>
                </div>
                <div id="prayerTimesContent">
                    <div class="loading">Loading prayer times...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prayer Times functionality
        let currentCityId = 1; // Default to Dhaka

        // Load cities for dropdown
        async function loadCities() {
            try {
                const response = await fetch('/prayer-time/cities');
                const data = await response.json();
                
                if (data.success) {
                    const citySelector = document.getElementById('citySelector');
                    citySelector.innerHTML = '';
                    
                    data.cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = city.name;
                        if (city.id == currentCityId) {
                            option.selected = true;
                        }
                        citySelector.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading cities:', error);
            }
        }

        // Load prayer times for selected city
        async function loadPrayerTimes(cityId = null) {
            const content = document.getElementById('prayerTimesContent');
            content.innerHTML = '<div class="loading">Loading prayer times...</div>';
            
            try {
                const url = cityId ? `/prayer-time/today/${cityId}` : '/prayer-time/today';
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    const prayerTimes = data.prayer_times;
                    content.innerHTML = `
                        <ul class="prayer-times-list">
                            <li class="prayer-time-item">
                                <span class="prayer-name">ফজর</span>
                                <span class="prayer-time">${prayerTimes.fajr}</span>
                            </li>
                            <li class="prayer-time-item">
                                <span class="prayer-name">সূর্যোদয়</span>
                                <span class="prayer-time">${prayerTimes.sunrise}</span>
                            </li>
                            <li class="prayer-time-item">
                                <span class="prayer-name">যোহর</span>
                                <span class="prayer-time">${prayerTimes.dhuhr}</span>
                            </li>
                            <li class="prayer-time-item">
                                <span class="prayer-name">আসর</span>
                                <span class="prayer-time">${prayerTimes.asr}</span>
                            </li>
                            <li class="prayer-time-item">
                                <span class="prayer-name">মাগরিব</span>
                                <span class="prayer-time">${prayerTimes.maghrib}</span>
                            </li>
                            <li class="prayer-time-item">
                                <span class="prayer-name">এশা</span>
                                <span class="prayer-time">${prayerTimes.isha}</span>
                            </li>
                        </ul>
                    `;
                } else {
                    content.innerHTML = `<div class="error">${data.error || 'Prayer times not available'}</div>`;
                }
            } catch (error) {
                console.error('Error loading prayer times:', error);
                content.innerHTML = '<div class="error">Error loading prayer times</div>';
            }
        }

        // Handle city selection change
        document.getElementById('citySelector').addEventListener('change', function() {
            const selectedCityId = this.value;
            if (selectedCityId) {
                currentCityId = selectedCityId;
                loadPrayerTimes(selectedCityId);
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCities();
            loadPrayerTimes(currentCityId);
        });
    </script>
</body>
</html> 