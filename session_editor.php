<?php
session_start();
require_once 'database_config.php';
require_once 'data_models.php';

$sessionId = $_GET['id'] ?? null;
$sessionData = null;

if ($sessionId) {
    try {
        $sessionRepository = new DrivingSessionRepository($dbConnection);
        $sessionData = $sessionRepository->fetchSessionById($sessionId);
        
        if (!$sessionData) {
            $_SESSION['error'] = 'Session not found';
            header('Location: analytics_dashboard.php');
            exit();
        }
    } catch(PDOException $exception) {
        $_SESSION['error'] = 'Error loading session: ' . $exception->getMessage();
        header('Location: analytics_dashboard.php');
        exit();
    }
} else {
    header('Location: analytics_dashboard.php');
    exit();
}

// Fetch dropdown options
try {
    $referenceRepository = new ReferenceDataRepository($dbConnection);
    $weatherOptions = $referenceRepository->getWeatherConditions();
    $trafficOptions = $referenceRepository->getTrafficConditions();
    $routeOptions = $referenceRepository->getRouteTypes();
    $maneuverOptions = $referenceRepository->getManeuverTypes();
} catch(PDOException $exception) {
    die("Error loading options: " . $exception->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Session | DriveLog Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary-dark: #0A0E27; --primary-blue: #3B82F6; --accent-cyan: #06B6D4;
            --accent-purple: #A855F7; --text-light: #E2E8F0; --text-muted: #94A3B8;
            --surface: #1E293B; --surface-elevated: #334155; --border-subtle: rgba(148, 163, 184, 0.1);
        }
        body {
            font-family: 'Outfit', sans-serif; background: var(--primary-dark);
            min-height: 100vh; color: var(--text-light); position: relative;
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.15) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite; pointer-events: none; z-index: 0;
        }
        @keyframes gradientShift { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
        .page-container {
            min-height: 100vh; display: flex; flex-direction: column;
            align-items: center; justify-content: center; padding: 40px 20px; position: relative; z-index: 1;
        }
        .content-wrapper { width: 100%; max-width: 900px; animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .nav-container { display: flex; justify-content: flex-end; margin-bottom: 32px; }
        .dashboard-link {
            display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px;
            background: var(--surface-elevated); color: var(--text-light); font-weight: 600;
            font-size: 14px; border-radius: 12px; text-decoration: none;
            transition: all 0.3s; border: 1px solid var(--border-subtle);
        }
        .dashboard-link:hover { transform: translateY(-2px); border-color: var(--primary-blue); }
        .form-card {
            background: var(--surface); border-radius: 24px; padding: 48px;
            border: 1px solid var(--border-subtle); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .form-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-cyan), var(--accent-purple));
        }
        .form-header { text-align: center; margin-bottom: 40px; }
        .form-icon {
            width: 72px; height: 72px; margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.4);
        }
        .form-icon svg { width: 40px; height: 40px; stroke: white; }
        h1 { color: var(--text-light); font-size: 36px; font-weight: 800; margin-bottom: 12px; }
        .form-subtitle { color: var(--text-muted); font-size: 16px; }
        .form-grid { display: grid; gap: 24px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label {
            display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
            color: var(--text-light); font-weight: 600; font-size: 13px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        label svg { width: 16px; height: 16px; stroke: var(--accent-cyan); }
        input, select {
            width: 100%; padding: 14px 18px; border: 1px solid var(--border-subtle);
            border-radius: 12px; font-size: 15px; font-family: 'Outfit', sans-serif;
            background: rgba(30, 41, 59, 0.5); color: var(--text-light); transition: all 0.2s;
        }
        input:focus, select:focus {
            outline: none; border-color: var(--primary-blue);
            background: rgba(30, 41, 59, 0.9); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        select {
            cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%233B82F6' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 15px center; padding-right: 40px;
        }
        .time-section {
            background: rgba(51, 65, 85, 0.5); border: 1px solid var(--border-subtle);
            border-radius: 16px; padding: 28px;
        }
        .time-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
        .time-header svg { width: 24px; height: 24px; stroke: var(--accent-cyan); }
        .time-header h3 { font-size: 16px; font-weight: 700; text-transform: uppercase; }
        .time-inputs { display: grid; grid-template-columns: 1fr auto 1fr; gap: 20px; align-items: center; }
        .time-field label { font-size: 11px; }
        .time-input-wrapper { position: relative; }
        .time-input-wrapper input[type="time"] {
            padding: 16px 20px 16px 48px; font-size: 17px; font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }
        .time-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; stroke: var(--accent-cyan); pointer-events: none;
        }
        .time-separator { display: flex; justify-content: center; }
        .time-separator svg { width: 28px; height: 28px; stroke: var(--accent-cyan); opacity: 0.6; }
        .submit-button {
            width: 100%; padding: 18px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-purple));
            color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 16px; text-transform: uppercase; letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .submit-button:hover { transform: translateY(-3px); }
        .submit-button svg { width: 20px; height: 20px; }
        .page-footer {
            text-align: center; margin-top: 40px; padding: 24px;
            color: var(--text-muted); font-size: 13px;
        }
        @media (max-width: 768px) {
            .form-card { padding: 32px 24px; }
            h1 { font-size: 28px; }
            .form-row, .time-inputs { grid-template-columns: 1fr; }
            .time-separator { transform: rotate(90deg); }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="content-wrapper">
            <div class="nav-container">
                <a class="dashboard-link" href="analytics_dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            <div class="form-card">
                <div class="form-header">
                    <div class="form-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h1>Edit Driving Session</h1>
                    <p class="form-subtitle">Update your session details</p>
                </div>
                <form id="sessionForm" action="session_handler.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($sessionData['id']); ?>">
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="sessionDate">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Session Date
                            </label>
                            <input type="date" id="sessionDate" name="date" value="<?php echo htmlspecialchars($sessionData['date']); ?>" required>
                        </div>
                        <div class="time-section">
                            <div class="time-header">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3>Time Duration</h3>
                            </div>
                            <div class="time-inputs">
                                <div class="time-field">
                                    <label for="startTime">Start Time</label>
                                    <div class="time-input-wrapper">
                                        <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <input type="time" id="startTime" name="start_time" value="<?php echo htmlspecialchars($sessionData['start_time']); ?>" required>
                                    </div>
                                </div>
                                <div class="time-separator">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </div>
                                <div class="time-field">
                                    <label for="endTime">End Time</label>
                                    <div class="time-input-wrapper">
                                        <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <input type="time" id="endTime" name="end_time" value="<?php echo htmlspecialchars($sessionData['end_time']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="distance">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                Distance (Kilometers)
                            </label>
                            <input type="number" id="distance" name="kilometers" min="0" step="0.1" value="<?php echo htmlspecialchars($sessionData['kilometers']); ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="field-group">
                                <label for="weatherCondition">Weather</label>
                                <select id="weatherCondition" name="weather_id" required>
                                    <option value="">Select weather</option>
                                    <?php foreach ($weatherOptions as $option): ?>
                                        <option value="<?php echo $option['id']; ?>" <?php echo ($sessionData['weather_id'] == $option['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field-group">
                                <label for="trafficCondition">Traffic</label>
                                <select id="trafficCondition" name="traffic_id" required>
                                    <option value="">Select traffic</option>
                                    <?php foreach ($trafficOptions as $option): ?>
                                        <option value="<?php echo $option['id']; ?>" <?php echo ($sessionData['traffic_id'] == $option['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="field-group">
                                <label for="routeType">Route Type</label>
                                <select id="routeType" name="route_id" required>
                                    <option value="">Select route</option>
                                    <?php foreach ($routeOptions as $option): ?>
                                        <option value="<?php echo $option['id']; ?>" <?php echo ($sessionData['route_id'] == $option['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field-group">
                                <label for="maneuverType">Maneuver Type</label>
                                <select id="maneuverType" name="maneuver_id" required>
                                    <option value="">Select maneuver</option>
                                    <?php foreach ($maneuverOptions as $option): ?>
                                        <option value="<?php echo $option['id']; ?>" <?php echo ($sessionData['maneuver_id'] == $option['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="submit-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Update Session</span>
                        </button>
                    </div>
                </form>
            </div>
            <footer class="page-footer">
                <p>&copy; 2025 DriveLog Tracker • Developed by Karam Imamali CS-23</p>
            </footer>
        </div>
    </div>
    <script src="form_validation.js"></script>
</body>
</html>