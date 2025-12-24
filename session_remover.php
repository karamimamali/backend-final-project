<?php
session_start();
require_once 'database_config.php';
require_once 'data_models.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['id'] ?? null;
    
    if (!$sessionId) {
        $_SESSION['error'] = 'Invalid request - session ID missing';
        header('Location: analytics_dashboard.php');
        exit();
    }
    
    try {
        $sessionRepository = new DrivingSessionRepository($dbConnection);
        $sessionRepository->deleteSession($sessionId);
        
        $_SESSION['success'] = 'Driving session deleted successfully!';
    } catch(PDOException $exception) {
        $_SESSION['error'] = 'Error deleting session: ' . $exception->getMessage();
    }
    
    header('Location: analytics_dashboard.php');
    exit();
} else {
    header('Location: analytics_dashboard.php');
    exit();
}
?>