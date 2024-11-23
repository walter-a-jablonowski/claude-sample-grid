<?php

class WidgetRenderer {
    public function render($widget) {
        $type = $widget['type'] ?? 'unknown';
        $method = 'render' . ucfirst($type);
        
        if (method_exists($this, $method)) {
            return $this->$method($widget);
        }
        
        return $this->renderUnknown($widget);
    }
    
    private function renderChart($widget) {
        $chartId = 'chart_' . uniqid();
        $source = $widget['source'] ?? '';
        $chartType = $widget['chartType'] ?? 'line';
        $options = json_encode($widget['options'] ?? []);
        
        return <<<HTML
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="{$chartId}"></canvas>
        </div>
        <script>
            fetch('{$source}')
                .then(response => response.json())
                .then(response => {
                    const data = response.data || [];
                    const ctx = document.getElementById('{$chartId}').getContext('2d');
                    
                    // Prepare data based on chart type
                    let chartData = {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                                '#9966FF'
                            ]
                        }]
                    };
                    
                    if ('{$chartType}' === 'pie') {
                        // For pie charts, use label and value properties
                        chartData.labels = data.map(item => item.label);
                        chartData.datasets[0].data = data.map(item => item.value);
                    } else {
                        // For line charts, use x and y properties
                        chartData.labels = data.map(item => item.x);
                        chartData.datasets[0].data = data.map(item => item.y);
                        chartData.datasets[0].borderColor = '#36A2EB';
                        chartData.datasets[0].fill = false;
                    }
                    
                    new Chart(ctx, {
                        type: '{$chartType}',
                        data: chartData,
                        options: {$options}
                    });
                });
        </script>
        HTML;
    }
    
    private function renderWeather($widget) {
        // Stegaurach, Germany coordinates
        $lat = '49.8667';
        $lon = '10.8833';
        $widgetId = 'weather_' . uniqid();
        
        return <<<HTML
        <div class="weather-widget" id="{$widgetId}">
            <div class="weather-current">
                <div class="weather-icon"></div>
                <div class="weather-temp"></div>
                <div class="weather-desc"></div>
            </div>
        </div>
        <script>
            // First get current weather
            fetch('https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,weather_code&timezone=Europe/Berlin')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Weather API error: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.current) {
                        const widget = document.getElementById('{$widgetId}');
                        const temp = Math.round(data.current.temperature_2m);
                        const weatherCode = data.current.weather_code;
                        
                        // Weather code to description and icon mapping
                        const weatherMap = {
                            0: { desc: 'Clear sky', icon: '☀️' },
                            1: { desc: 'Mainly clear', icon: '🌤️' },
                            2: { desc: 'Partly cloudy', icon: '⛅' },
                            3: { desc: 'Overcast', icon: '☁️' },
                            45: { desc: 'Foggy', icon: '🌫️' },
                            48: { desc: 'Depositing rime fog', icon: '🌫️' },
                            51: { desc: 'Light drizzle', icon: '🌧️' },
                            53: { desc: 'Moderate drizzle', icon: '🌧️' },
                            55: { desc: 'Dense drizzle', icon: '🌧️' },
                            61: { desc: 'Slight rain', icon: '🌧️' },
                            63: { desc: 'Moderate rain', icon: '🌧️' },
                            65: { desc: 'Heavy rain', icon: '🌧️' },
                            71: { desc: 'Slight snow', icon: '🌨️' },
                            73: { desc: 'Moderate snow', icon: '🌨️' },
                            75: { desc: 'Heavy snow', icon: '🌨️' },
                            77: { desc: 'Snow grains', icon: '🌨️' },
                            80: { desc: 'Slight rain showers', icon: '🌦️' },
                            81: { desc: 'Moderate rain showers', icon: '🌦️' },
                            82: { desc: 'Violent rain showers', icon: '🌦️' },
                            85: { desc: 'Slight snow showers', icon: '🌨️' },
                            86: { desc: 'Heavy snow showers', icon: '🌨️' },
                            95: { desc: 'Thunderstorm', icon: '⛈️' },
                            96: { desc: 'Thunderstorm with slight hail', icon: '⛈️' },
                            99: { desc: 'Thunderstorm with heavy hail', icon: '⛈️' }
                        };
                        
                        const weather = weatherMap[weatherCode] || { desc: 'Unknown', icon: '❓' };
                        
                        widget.querySelector('.weather-temp').textContent = temp + '°C';
                        widget.querySelector('.weather-desc').textContent = weather.desc;
                        widget.querySelector('.weather-icon').innerHTML = 
                            '<span style="font-size: 2em;">' + weather.icon + '</span>';
                    } else {
                        throw new Error('Invalid weather data format');
                    }
                })
                .catch(error => {
                    const widget = document.getElementById('{$widgetId}');
                    widget.innerHTML = '<div class="alert alert-warning">Weather data unavailable</div>';
                    console.error('Weather widget error:', error);
                });
        </script>
        HTML;
    }
    
    private function renderTable($widget) {
        $source = $widget['source'] ?? '';
        $tableId = 'table_' . uniqid();
        
        return <<<HTML
        <div class="table-responsive">
            <style>
                #{$tableId}.table-sm td, #{$tableId}.table-sm th {
                    padding: 0.1rem 0.3rem;
                    font-size: 0.9rem;
                }
            </style>
            <table id="{$tableId}" class="table table-sm table-striped table-hover">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
        <script>
            fetch('{$source}')
                .then(response => response.json())
                .then(response => {
                    const data = response.data || [];
                    if (data.length > 0) {
                        const table = document.getElementById('{$tableId}');
                        const headers = Object.keys(data[0]);
                        
                        // Create header
                        table.querySelector('thead').innerHTML = '<tr>' + 
                            headers.map(h => '<th>' + h.charAt(0).toUpperCase() + h.slice(1) + '</th>').join('') + 
                            '</tr>';
                        
                        // Create body
                        table.querySelector('tbody').innerHTML = data
                            .map(row => '<tr>' + headers.map(h => '<td>' + row[h] + '</td>').join('') + '</tr>')
                            .join('');
                    }
                });
        </script>
        HTML;
    }
    
    private function renderFile($widget) {
        $source = $widget['source'] ?? '';
        $fileId = 'file_' . uniqid();
        $filename = basename($source);
        
        return <<<HTML
        <div class="file-widget" id="{$fileId}">
            <h4 class="file-title">{$filename}</h4>
            <pre class="file-content"></pre>
        </div>
        <script>
            fetch('{$source}')
                .then(response => response.text())
                .then(content => {
                    const widget = document.getElementById('{$fileId}');
                    const lines = content.split('\\n').slice(0, 10); // Show first 10 lines
                    widget.querySelector('.file-content').textContent = lines.join('\\n');
                });
        </script>
        HTML;
    }
    
    private function renderStats($widget) {
        $source = $widget['source'] ?? '';
        $options = $widget['options'] ?? [];
        $statsId = 'stats_' . uniqid();
        
        return <<<HTML
        <div class="stats-grid" id="{$statsId}">
            <!-- Stats will be populated here -->
        </div>
        <script>
            function updateStats_{$statsId}() {
                fetch('{$source}')
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('{$statsId}');
                        const html = data.data.map(item => 
                            '<div class="stat-item">' +
                                '<div class="stat-label">' + item.metric + '</div>' +
                                '<div class="stat-value">' + item.value + '</div>' +
                                '<div class="stat-trend ' + item.trend + '">' + item.trend + '</div>' +
                            '</div>'
                        ).join('');
                        container.innerHTML = html;
                    });
            }
            updateStats_{$statsId}();
            setInterval(updateStats_{$statsId}, {$options['refreshInterval']} * 1000);
        </script>
        HTML;
    }
    
    private function renderUnknown($widget) {
        return '<div class="alert alert-warning">Unknown widget type: ' . htmlspecialchars($widget['type']) . '</div>';
    }
    
    private function renderWeatherForecast($options) {
        if (!($options['showForecast'] ?? false)) {
            return '';
        }
        
        return '<div class="weather-forecast"></div>';
    }
}
