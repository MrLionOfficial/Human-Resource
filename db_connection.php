<?php
// db_connection.php

// Database connection parameters
$host = 'localhost';
$dbname = 'u256972392_workline';
$username = 'u256972392_workline';
$password = 'N@re$#@12345';

// Create a DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Options for PDO connection
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Global variable to store the PDO instance
$pdo = null;

// Function to log errors
function logDBError($message) {
    $logFile = __DIR__ . '/db_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Function to get database connection
function getDBConnection() {
    global $pdo, $dsn, $username, $password, $options;
    
    if ($pdo === null) {
        try {
            // Create a PDO instance (connect to the database)
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log the error
            logDBError("Connection failed: " . $e->getMessage());
            
            // If there is an error with the connection, throw an exception
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    return $pdo;
}

// Function to close the database connection
function closeDBConnection() {
    global $pdo;
    $pdo = null;
}

// Function to test the database connection
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        logDBError("Connection test failed: " . $e->getMessage());
        return false;
    }
}