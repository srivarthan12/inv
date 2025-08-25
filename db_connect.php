<?php
// db_connect.php

// --- Database Credentials ---
// Best practice: Store these in a separate, non-version-controlled file in a real-world scenario.
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your DB username
define('DB_PASS', '');     // Your DB password
define('DB_NAME', 'aspa_inventory');

// --- Create a PDO instance ---
try {
    // DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // PDO Options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    // Create the PDO object
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // If connection fails, stop the script and show an error
    die("Database connection failed: " . $e->getMessage());
}

// The $pdo object is now available for use in any file that includes this script.
?>