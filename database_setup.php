<?php
require_once 'database_config.php';

try {
    // Read SQL setup file
    $setupSQL = file_get_contents('database_schema.sql');
    
    // Enable multiple statement execution
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
    
    // Execute the SQL setup
    $dbConnection->exec($setupSQL);
    
    echo "<!DOCTYPE html>";
    echo "<html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Database Setup Complete</title>";
    echo "<style>body{font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;background:#0A0E27;color:#E2E8F0;}";
    echo "h1{color:#3B82F6;}ul{line-height:1.8;}a{color:#06B6D4;text-decoration:none;font-weight:bold;}</style></head><body>";
    echo "<h1>✓ Database Setup Successful!</h1>";
    echo "<p>The following tables have been created or updated:</p>";
    echo "<ul>";
    echo "<li>weather_conditions</li>";
    echo "<li>traffic_conditions</li>";
    echo "<li>route_types</li>";
    echo "<li>maneuver_types</li>";
    echo "<li>driving_experience</li>";
    echo "</ul>";
    echo "<p>Default reference data has been inserted successfully.</p>";
    echo "<p><a href='analytics_dashboard.php'>→ Go to Analytics Dashboard</a></p>";
    echo "</body></html>";
    
} catch (PDOException $exception) {
    echo "<!DOCTYPE html>";
    echo "<html lang='en'><head><meta charset='UTF-8'><title>Database Setup Error</title>";
    echo "<style>body{font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;background:#0A0E27;color:#E2E8F0;}";
    echo "h1{color:#EF4444;}pre{background:#1E293B;padding:15px;border-radius:8px;overflow-x:auto;}</style></head><body>";
    echo "<h1>✗ Database Setup Error</h1>";
    echo "<p style='color:#F59E0B;'>Error occurred during setup:</p>";
    echo "<pre>" . htmlspecialchars($exception->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    echo "</body></html>";
}
?>