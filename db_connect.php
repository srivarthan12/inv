<?php
// db_connect.php - UPDATED FOR RENDER POSTGRESQL

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

try {
    // DSN for PostgreSQL
    $dsn = "pgsql:host={$db_host};port=5432;dbname={$db_name}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
