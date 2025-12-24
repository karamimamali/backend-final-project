<?php
session_start();
require_once 'database_config.php';
require_once 'data_models.php';

try {
    $sessionRepository = new DrivingSessionRepository($dbConnection);
    $allSessions = $sessionRepository->fetchAllSessions();
    
    // Calculate summary statistics
    $totalDistance = $sessionRepository->calculateTotalDistance();
    $totalSessions = count($allSessions);
    $averageDistance = $totalSessions > 0 ? $totalDistance / $totalSessions : 0;
    
    // Fetch category statistics
    $weatherStatistics = $sessionRepository->fetchCategoryStatistics('weather');
    $trafficStatistics = $sessionRepository->fetchCategoryStatistics('traffic');
    $routeStatistics = $sessionRepository->fetchCategoryStatistics('route');
    $maneuverStatistics = $sessionRepository->fetchCategoryStatistics('maneuver');
    
    // Fetch distance-based analytics
    $distanceByMonth = $sessionRepository->fetchMonthlyKilometers(6);
    $distanceByWeather = $sessionRepository->fetchKilometersByWeather();
    $distanceByRoute = $sessionRepository->fetchKilometersByRoute();
    
} catch(PDOException $exception) {
    die("Error loading dashboard data: " . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard | DriveLog Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-dark: #050A1E; --bg-surface: #0F1629; --bg-elevated: #1A2332;
            --accent-cyan: #00F5FF; --accent-pink: #FF007A; --accent-purple: #A855F7;
            --text-primary: #FFFFFF; --text-secondary: #8B9DBA; --text-muted: #4A5568;
            --success: #00FF88; --warning: #FFB800; --danger: #FF3366;
            --glow-cyan: rgba(0, 245, 255, 0.5); --glow-pink: rgba(255, 0, 122, 0.5);
        }
        body {
            font-family: 'Rajdhani', sans-serif; background: var(--bg-dark);
            color: var(--text-primary); min-height: 100vh; position: relative;
        }
        body::before {
            content: ''; position: fixed; inset: 0; z-index: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(0, 245, 255, 0.08) 0%, transparent 30%),
                radial-gradient(circle at 90% 80%, rgba(255, 0, 122, 0.08) 0%, transparent 30%),
                linear-gradient(180deg, transparent 0%, rgba(168, 85, 247, 0.05) 100%);
            animation: pulse 8s ease-in-out infinite;
        }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        .container { max-width: 1600px; margin: 0 auto; padding: 20px; position: relative; z-index: 1; }
        
        /* Header Styling */
        .header {
            background: linear-gradient(135deg, var(--bg-surface) 0%, var(--bg-elevated) 100%);
            padding: 32px 40px; border-radius: 16px; margin-bottom: 24px;
            border: 1px solid rgba(0, 245, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 245, 255, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            position: relative; overflow: hidden;
        }
        .header::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-pink), var(--accent-purple));
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif; font-size: 36px; font-weight: 900;
            text-transform: uppercase; letter-spacing: 2px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-pink));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 12px;
            text-shadow: 0 0 20px var(--glow-cyan);
        }
        .header-actions {
            display: flex; gap: 16px; margin-top: 20px; flex-wrap: wrap;
        }
        
        /* Button Styling */
        .btn {
            padding: 14px 28px; border: none; border-radius: 8px; font-weight: 700;
            font-size: 14px; cursor: pointer; text-decoration: none; display: inline-flex;
            align-items: center; gap: 10px; transition: all 0.3s;
            text-transform: uppercase; letter-spacing: 0.5px; position: relative;
            overflow: hidden; font-family: 'Rajdhani', sans-serif;
        }
        .btn::before {
            content: ''; position: absolute; inset: 0; opacity: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: opacity 0.3s; z-index: 0;
        }
        .btn:hover::before { opacity: 1; animation: shimmer 1.5s infinite; }
        @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
            color: var(--bg-dark); box-shadow: 0 4px 16px var(--glow-cyan);
            border: 1px solid rgba(0, 245, 255, 0.5);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 24px var(--glow-cyan); }
        .btn span { position: relative; z-index: 1; }
        
        /* Alert Messages */
        .alert {
            padding: 16px 24px; border-radius: 12px; margin-bottom: 20px;
            font-weight: 600; border-left: 4px solid; animation: slideIn 0.4s;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        .alert-success {
            background: rgba(0, 255, 136, 0.15); color: var(--success);
            border-color: var(--success); box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }
        .alert-danger {
            background: rgba(255, 51, 102, 0.15); color: var(--danger);
            border-color: var(--danger); box-shadow: 0 0 20px rgba(255, 51, 102, 0.2);
        }
        
        /* Statistics Grid */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px; margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-surface); padding: 28px; border-radius: 16px;
            border: 1px solid rgba(139, 157, 186, 0.1); position: relative; overflow: hidden;
            transition: all 0.3s; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-cyan), transparent);
        }
        .stat-card:hover {
            transform: translateY(-4px); border-color: var(--accent-cyan);
            box-shadow: 0 8px 24px rgba(0, 245, 255, 0.2);
        }
        .stat-card h3 {
            color: var(--text-secondary); font-size: 13px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px;
        }
        .stat-card .value {
            font-family: 'Orbitron', sans-serif; font-size: 42px; font-weight: 700;
            color: var(--accent-cyan); text-shadow: 0 0 10px var(--glow-cyan);
        }
        .stat-card .unit {
            color: var(--text-muted); font-size: 16px; margin-left: 8px;
        }
        
        /* Charts Grid */
        .chart-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 20px; margin-bottom: 24px;
        }
        .chart-card {
            background: var(--bg-surface); padding: 28px; border-radius: 16px;
            border: 1px solid rgba(139, 157, 186, 0.1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        .chart-card h3 {
            font-family: 'Orbitron', sans-serif; font-size: 18px; font-weight: 700;
            color: var(--text-primary); margin-bottom: 24px; text-transform: uppercase;
            letter-spacing: 1px; text-shadow: 0 0 8px var(--glow-cyan);
        }
        .chart-canvas { position: relative; height: 300px; }
        
        /* Table Card */
        .table-card {
            background: var(--bg-surface); padding: 32px; border-radius: 16px;
            border: 1px solid rgba(139, 157, 186, 0.1); overflow-x: auto;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        .table-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; flex-wrap: wrap; gap: 16px;
        }
        .table-card h2 {
            font-family: 'Orbitron', sans-serif; font-size: 24px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.5px;
            background: linear-gradient(90deg, var(--accent-cyan), var(--accent-pink));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        #tableSearch {
            padding: 12px 20px; background: var(--bg-elevated);
            border: 1px solid rgba(0, 245, 255, 0.3); border-radius: 8px;
            color: var(--text-primary); font-family: 'Rajdhani', sans-serif;
            font-size: 14px; font-weight: 600; transition: all 0.3s;
        }
        #tableSearch:focus {
            outline: none; border-color: var(--accent-cyan);
            box-shadow: 0 0 12px var(--glow-cyan);
        }
        
        table {
            width: 100%; border-collapse: separate; border-spacing: 0 8px;
        }
        thead th {
            background: var(--bg-elevated); padding: 16px; text-align: left;
            font-weight: 700; color: var(--accent-cyan); border: none;
            text-transform: uppercase; letter-spacing: 0.5px; font-size: 12px;
            cursor: pointer; transition: all 0.2s; position: sticky; top: 0; z-index: 10;
        }
        thead th:hover { color: var(--accent-pink); }
        tbody tr {
            background: rgba(15, 22, 41, 0.6); transition: all 0.3s;
            border-left: 2px solid transparent;
        }
        tbody tr:hover {
            background: rgba(0, 245, 255, 0.05); border-left-color: var(--accent-cyan);
            transform: translateX(4px);
        }
        tbody td {
            padding: 18px 16px; border: none; color: var(--text-secondary);
            font-weight: 600; font-size: 14px;
        }
        
        /* Action Buttons */
        .action-btn {
            padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 700;
            border: none; cursor: pointer; display: inline-flex; align-items: center;
            gap: 6px; transition: all 0.3s; text-decoration: none;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .edit-btn {
            background: rgba(255, 184, 0, 0.15); color: var(--warning);
            border: 1px solid var(--warning);
        }
        .edit-btn:hover {
            background: var(--warning); color: var(--bg-dark);
            box-shadow: 0 0 16px rgba(255, 184, 0, 0.4); transform: translateY(-2px);
        }
        .delete-btn {
            background: rgba(255, 51, 102, 0.15); color: var(--danger);
            border: 1px solid var(--danger);
        }
        .delete-btn:hover {
            background: var(--danger); color: var(--bg-dark);
            box-shadow: 0 0 16px rgba(255, 51, 102, 0.4); transform: translateY(-2px);
        }
        
        .no-data {
            text-align: center; padding: 60px 20px; color: var(--text-muted);
            font-size: 16px; font-weight: 600;
        }
        .no-data a { color: var(--accent-cyan); text-decoration: none; font-weight: 700; }
        .no-data a:hover { text-shadow: 0 0 8px var(--glow-cyan); }
        
        .footer {
            text-align: center; margin-top: 40px; padding: 24px;
            color: var(--text-muted); font-size: 13px; font-weight: 600;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header { padding: 24px 20px; }
            .header h1 { font-size: 24px; }
            .stats-grid, .chart-grid { grid-template-columns: 1fr; }
            .stat-card .value { font-size: 32px; }
            .table-card { padding: 20px; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tbody tr { border: 1px solid rgba(139, 157, 186, 0.2); margin-bottom: 12px; border-radius: 8px; padding: 12px; }
            td { border: none; position: relative; padding: 10px 10px 10px 45%; text-align: right; }
            td::before {
                content: attr(data-label); position: absolute; left: 12px; top: 50%;
                transform: translateY(-50%); font-weight: 700; color: var(--accent-cyan);
                text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚡ Analytics Dashboard</h1>
            <div class="header-actions">
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>New Session</span>
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Distance</h3>
                <div class="value"><?php echo number_format($totalDistance, 2); ?><span class="unit">km</span></div>
            </div>
            <div class="stat-card">
                <h3>Total Sessions</h3>
                <div class="value"><?php echo $totalSessions; ?><span class="unit">sessions</span></div>
            </div>
            <div class="stat-card">
                <h3>Average Distance</h3>
                <div class="value"><?php echo number_format($averageDistance, 2); ?><span class="unit">km/session</span></div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Weather Conditions</h3>
                <div class="chart-canvas"><canvas id="weatherChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Traffic Conditions</h3>
                <div class="chart-canvas"><canvas id="trafficChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Route Distribution</h3>
                <div class="chart-canvas"><canvas id="routeChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Maneuver Types</h3>
                <div class="chart-canvas"><canvas id="maneuverChart"></canvas></div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Distance by Weather</h3>
                <div class="chart-canvas"><canvas id="kmWeatherChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Distance by Route</h3>
                <div class="chart-canvas"><canvas id="kmRouteChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Monthly Trend</h3>
                <div class="chart-canvas"><canvas id="monthlyChart"></canvas></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>All Sessions</h2>
                <input type="text" id="tableSearch" placeholder="Search sessions...">
            </div>
            <?php if (!empty($allSessions)): ?>
                <table id="sessionsTable">
                    <thead>
                        <tr>
                            <th onclick="sortTable(0)">Date ↕</th>
                            <th onclick="sortTable(1)">Time ↕</th>
                            <th onclick="sortTable(2)">Distance ↕</th>
                            <th onclick="sortTable(3)">Weather ↕</th>
                            <th onclick="sortTable(4)">Traffic ↕</th>
                            <th onclick="sortTable(5)">Route ↕</th>
                            <th onclick="sortTable(6)">Maneuver ↕</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allSessions as $session): ?>
                            <tr>
                                <td data-label="Date"><?php echo date('M d, Y', strtotime($session['date'])); ?></td>
                                <td data-label="Time"><?php echo date('H:i', strtotime($session['start_time'])) . ' - ' . date('H:i', strtotime($session['end_time'])); ?></td>
                                <td data-label="Distance"><?php echo number_format($session['kilometers'], 2); ?> km</td>
                                <td data-label="Weather"><?php echo htmlspecialchars($session['weather']); ?></td>
                                <td data-label="Traffic"><?php echo htmlspecialchars($session['traffic']); ?></td>
                                <td data-label="Route"><?php echo htmlspecialchars($session['route']); ?></td>
                                <td data-label="Maneuver"><?php echo htmlspecialchars($session['maneuver']); ?></td>
                                <td data-label="Actions" style="white-space: nowrap;">
                                    <a href="session_editor.php?id=<?php echo $session['id']; ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="session_remover.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this session?');">
                                        <input type="hidden" name="id" value="<?php echo $session['id']; ?>">
                                        <button type="submit" class="action-btn delete-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    No sessions recorded yet. <a href="index.html">Create your first session</a>
                </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <p>&copy; 2025 DriveLog Tracker • Developed by Karam Imamali CS-23</p>
        </footer>
    </div>

    <script>
        Chart.defaults.color = '#8B9DBA';
        Chart.defaults.borderColor = 'rgba(139, 157, 186, 0.1)';
        Chart.defaults.font.family = 'Rajdhani';
        Chart.defaults.font.weight = '600';

        const chartColors = ['#00F5FF', '#FF007A', '#A855F7', '#00FF88', '#FFB800', '#FF3366'];
        
        new Chart(document.getElementById('weatherChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($weatherStatistics, 'label')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($weatherStatistics, 'count')); ?>,
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#0F1629'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('trafficChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($trafficStatistics, 'label')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($trafficStatistics, 'count')); ?>,
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#0F1629'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('routeChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($routeStatistics, 'label')); ?>,
                datasets: [{
                    label: 'Sessions',
                    data: <?php echo json_encode(array_column($routeStatistics, 'count')); ?>,
                    backgroundColor: '#00F5FF',
                    borderColor: '#00F5FF',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('maneuverChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($maneuverStatistics, 'label')); ?>,
                datasets: [{
                    label: 'Sessions',
                    data: <?php echo json_encode(array_column($maneuverStatistics, 'count')); ?>,
                    backgroundColor: '#FF007A',
                    borderColor: '#FF007A',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('kmWeatherChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($distanceByWeather, 'weather')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($distanceByWeather, 'total')); ?>,
                    backgroundColor: chartColors,
                    borderWidth: 2,
                    borderColor: '#0F1629'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('kmRouteChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($distanceByRoute, 'route')); ?>,
                datasets: [{
                    label: 'Kilometers',
                    data: <?php echo json_encode(array_column($distanceByRoute, 'total')); ?>,
                    backgroundColor: '#A855F7',
                    borderColor: '#A855F7',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($item) {
                    return date('M Y', strtotime($item['month'].'-01'));
                }, array_reverse($distanceByMonth))); ?>,
                datasets: [{
                    label: 'Distance (km)',
                    data: <?php echo json_encode(array_reverse(array_column($distanceByMonth, 'total'))); ?>,
                    borderColor: '#00F5FF',
                    backgroundColor: 'rgba(0, 245, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Table search
        document.getElementById('tableSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('sessionsTable');
            if (!table) return;
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if ((cells[j].textContent || cells[j].innerText).toLowerCase().indexOf(searchValue) > -1) {
                        found = true;
                        break;
                    }
                }
                row.style.display = found ? '' : 'none';
            }
        });

        // Table sort
        function sortTable(n) {
            const table = document.getElementById("sessionsTable");
            if (!table) return;
            let switching = true, dir = "asc", switchcount = 0;
            while (switching) {
                switching = false;
                const rows = table.rows;
                for (let i = 1; i < (rows.length - 1); i++) {
                    let shouldSwitch = false;
                    const x = rows[i].getElementsByTagName("TD")[n];
                    const y = rows[i + 1].getElementsByTagName("TD")[n];
                    let xContent = x.innerText.toLowerCase();
                    let yContent = y.innerText.toLowerCase();
                    if (n === 2) {
                        xContent = parseFloat(xContent.replace(' km', '').replace(',', ''));
                        yContent = parseFloat(yContent.replace(' km', '').replace(',', ''));
                    } else if (n === 0) {
                        xContent = new Date(xContent).getTime();
                        yContent = new Date(yContent).getTime();
                    }
                    if (dir == "asc") {
                        if (xContent > yContent) { shouldSwitch = true; break; }
                    } else if (dir == "desc") {
                        if (xContent < yContent) { shouldSwitch = true; break; }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>