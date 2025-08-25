<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$action = $_POST['action'];
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    $pdo->beginTransaction();

    switch ($action) {
        case 'addStock':
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $serials = $_POST['serial_numbers'] ?? [];

            $stmt = $pdo->prepare("UPDATE stock SET quantity = quantity + ? WHERE product_id = ?");
            $stmt->execute([$quantity, $product_id]);

            if (!empty($serials)) {
                $serialStmt = $pdo->prepare("INSERT INTO serial_numbers (product_id, serial_no, status) VALUES (?, ?, 'Available')");
                foreach ($serials as $serial_no) {
                    if (!empty(trim($serial_no))) {
                        $serialStmt->execute([$product_id, trim($serial_no)]);
                    }
                }
            }
            $response = ['status' => 'success', 'message' => 'Stock added successfully!'];
            break;

        case 'removeStock':
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $serial_ids_to_remove = $_POST['serial_ids'] ?? [];

            $checkStmt = $pdo->prepare("SELECT quantity FROM stock WHERE product_id = ?");
            $checkStmt->execute([$product_id]);
            $currentStock = $checkStmt->fetchColumn();

            if ($currentStock < $quantity) {
                throw new Exception("Not enough stock available to remove {$quantity} units.");
            }

            if (!empty($serial_ids_to_remove) && count($serial_ids_to_remove) != $quantity) {
                throw new Exception("Mismatch: You intended to remove {$quantity} units but selected " . count($serial_ids_to_remove) . " serial numbers.");
            }

            $stmt = $pdo->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->execute([$quantity, $product_id]);

            if (!empty($serial_ids_to_remove)) {
                $placeholders = implode(',', array_fill(0, count($serial_ids_to_remove), '?'));
                $serialStmt = $pdo->prepare("UPDATE serial_numbers SET status = 'Removed' WHERE id IN ($placeholders) AND status = 'Available'");
                $serialStmt->execute($serial_ids_to_remove);
            }
            
            $response = ['status' => 'success', 'message' => 'Stock removed successfully!'];
            break;
            
        default:
            $response['message'] = "Action '{$action}' not recognized.";
            break;
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);