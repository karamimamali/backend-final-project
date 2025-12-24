<?php
session_start();
require_once 'database_config.php';
require_once 'data_models.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['id'] ?? null;
    $sessionDate = $_POST['date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $distanceKm = $_POST['kilometers'] ?? '';
    $weatherConditionId = $_POST['weather_id'] ?? '';
    $trafficConditionId = $_POST['traffic_id'] ?? '';
    $routeTypeId = $_POST['route_id'] ?? '';
    $maneuverTypeId = $_POST['maneuver_id'] ?? '';
    
    // Validate required fields
    if (empty($sessionDate) || empty($startTime) || empty($endTime) || 
        empty($distanceKm) || empty($weatherConditionId) || empty($trafficConditionId) || 
        empty($routeTypeId) || empty($maneuverTypeId)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ' . ($sessionId ? "session_editor.php?id=$sessionId" : 'index.html'));
        exit();
    }
    
    // Validate distance
    if (!is_numeric($distanceKm) || $distanceKm <= 0) {
        $_SESSION['error'] = 'Invalid distance value. Must be greater than 0.';
        header('Location: ' . ($sessionId ? "session_editor.php?id=$sessionId" : 'index.html'));
        exit();
    }
    
    // Validate date format
    $dateObject = DateTime::createFromFormat('Y-m-d', $sessionDate);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $sessionDate) {
        $_SESSION['error'] = 'Invalid date format';
        header('Location: ' . ($sessionId ? "session_editor.php?id=$sessionId" : 'index.html'));
        exit();
    }
    
    // Check date is not in future
    if ($dateObject > new DateTime()) {
        $_SESSION['error'] = 'Session date cannot be in the future';
        header('Location: ' . ($sessionId ? "session_editor.php?id=$sessionId" : 'index.html'));
        exit();
    }

    // Prepare session data
    $sessionData = [
        'date' => $sessionDate,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'kilometers' => $distanceKm,
        'weather_id' => $weatherConditionId,
        'traffic_id' => $trafficConditionId,
        'route_id' => $routeTypeId,
        'maneuver_id' => $maneuverTypeId
    ];
    
    try {
        $sessionRepository = new DrivingSessionRepository($dbConnection);
        
        if ($sessionId) {
            // Update existing session
            $sessionRepository->updateSession($sessionId, $sessionData);
            $_SESSION['success'] = 'Driving session updated successfully!';
        } else {
            // Create new session
            $sessionRepository->createSession($sessionData);
            $_SESSION['success'] = 'Driving session saved successfully!';
        }
        
        header('Location: analytics_dashboard.php');
        exit();
    } catch(PDOException $exception) {
        $_SESSION['error'] = 'Error saving session: ' . $exception->getMessage();
        header('Location: ' . ($sessionId ? "session_editor.php?id=$sessionId" : 'index.html'));
        exit();
    }
} else {
    header('Location: index.html');
    exit();
}
?>