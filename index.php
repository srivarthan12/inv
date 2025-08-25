<?php
require_once 'db_connect.php';
include 'includes/header.php';
include 'includes/nav.php';
require_once 'config.php';

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);
$allowedPages = ['stock_management', 'product_management', 'expo_stock'];

if (!$page || !in_array($page, $allowedPages)) {
    $page = 'stock_management';
}

$module_path = "modules/{$page}.php";
if (file_exists($module_path)) {
    include $module_path;
} else {
    echo "<h2>Error 404: Page not found.</h2>";
}

include 'includes/footer.php';
?>