<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'];
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    switch ($action) {
        case 'createCategory':
            $name = trim($_POST['category_name'] ?? '');
            if (empty($name)) throw new Exception("Category name cannot be empty.");
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            $response = ['status' => 'success', 'message' => 'Category created!'];
            break;

        case 'createBrand':
            $name = trim($_POST['brand_name'] ?? '');
            if (empty($name)) throw new Exception("Brand name cannot be empty.");
            $stmt = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
            $stmt->execute([$name]);
            $response = ['status' => 'success', 'message' => 'Brand created!'];
            break;

        case 'createProductType':
            $cat_id = $_POST['category_id'] ?? null;
            $name = trim($_POST['type_name'] ?? '');
            if (empty($cat_id) || empty($name)) throw new Exception("Category and Type Name are required.");
            $stmt = $pdo->prepare("INSERT INTO product_types (category_id, name) VALUES (?, ?)");
            $stmt->execute([$cat_id, $name]);
            $response = ['status' => 'success', 'message' => 'Product Type created!'];
            break;
        
        case 'createVariant':
            $type_id = $_POST['type_id'] ?? null;
            $name = trim($_POST['variant_name'] ?? '');
            if (empty($type_id) || empty($name)) throw new Exception("Product Type and Variant Name are required.");
            $stmt = $pdo->prepare("INSERT INTO variants (type_id, name) VALUES (?, ?)");
            $stmt->execute([$type_id, $name]);
            $response = ['status' => 'success', 'message' => 'Variant created!'];
            break;

        case 'createProduct':
             $sql = "INSERT INTO products (name, category_id, type_id, variant_id, brand_id, supplier_id, mrp, sales_price, supplier_price, min_stock, requires_serial) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
             $stmt = $pdo->prepare($sql);
             $requires_serial = isset($_POST['requires_serial']) ? 1 : 0;
             $stmt->execute([
                 trim($_POST['product_name'] ?? 'Untitled'), $_POST['category_id'] ?: null, $_POST['type_id'] ?: null,
                 $_POST['variant_id'] ?: null, $_POST['brand_id'] ?: null, $_POST['supplier_id'] ?: null,
                 $_POST['mrp'] ?: 0.00, $_POST['sales_price'] ?? 0.00, $_POST['supplier_price'] ?: 0.00, 
                 $_POST['min_stock'] ?? 0, $requires_serial
             ]);
             $productId = $pdo->lastInsertId();
             $stockStmt = $pdo->prepare("INSERT INTO stock (product_id, quantity) VALUES (?, 0)");
             $stockStmt->execute([$productId]);
             $response = ['status' => 'success', 'message' => 'Product created successfully!'];
             break;
        
        case 'delete':
            $id = $_POST['id'] ?? null;
            $table = $_POST['table'] ?? null;
            if (empty($id) || empty($table)) throw new Exception("Missing ID or table for deletion.");
            
            $allowedTables = ['categories', 'brands', 'product_types', 'variants', 'products'];
            if (!in_array($table, $allowedTables)) throw new Exception("Deletion from table '{$table}' is not allowed.");

            $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['status' => 'success', 'message' => 'Item deleted successfully.'];
            break;
            
        default:
            $response['message'] = "Action '{$action}' not recognized.";
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = ($e->getCode() == 23000) ? 'This item already exists or is in use.' : 'Database Error: ' . $e->getMessage();
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);