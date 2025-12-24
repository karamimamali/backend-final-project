<?php
/**
 * Database Configuration
 * Establishes PDO connection with error handling
 */

$databaseHost = 'mysql-karam.alwaysdata.net';
$databaseName = 'karam_database';
$databaseUser = 'karam';
$databasePassword = 'Krm.2005';

try {
    $dbConnection = new PDO(
        "mysql:host=$databaseHost;dbname=$databaseName;charset=utf8mb4", 
        $databaseUser, 
        $databasePassword
    );
    
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $exception) {
    die("Database connection failed: " . $exception->getMessage());
}
?>