<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

// For simplicity, we'll hardcode one active expo. A real app would let you create/select expos.
define('ACTIVE_EXPO_ID', 1);
define('ACTIVE_EXPO_ID', 1);

$action = $_POST['action'];
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    $pdo->beginTransaction();

    switch ($action) {
        case 'moveToExpo':
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            $serial_ids = $_POST['serial_ids'] ?? [];
            $serials_json = !empty($serial_ids) ? json_encode($serial_ids) : null;

            // 1. Check main stock
            $checkStmt = $pdo->prepare("SELECT quantity FROM stock WHERE product_id = ?");
            $checkStmt->execute([$product_id]);
            if ($checkStmt->fetchColumn() < $quantity) {
                throw new Exception("Not enough stock in main inventory.");
            }

            // 2. Deduct from main stock
            $stmt = $pdo->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
            $stmt->execute([$quantity, $product_id]);

            // 3. Update serial number status if applicable
            if (!empty($serial_ids)) {
                $placeholders = implode(',', array_fill(0, count($serial_ids), '?'));
                $serialStmt = $pdo->prepare("UPDATE serial_numbers SET status = 'In Expo' WHERE id IN ($placeholders)");
                $serialStmt->execute($serial_ids);
            }

            // 4. Add to or update expo_stock
            $expoCheck = $pdo->prepare("SELECT id, quantity, serial_numbers_json FROM expo_stock WHERE expo_id = ? AND product_id = ?");
            $expoCheck->execute([ACTIVE_EXPO_ID, $product_id]);
            $existingExpoStock = $expoCheck->fetch();

            if ($existingExpoStock) {
                // Update existing record
                $newQuantity = $existingExpoStock['quantity'] + $quantity;
                $existingSerials = json_decode($existingExpoStock['serial_numbers_json'] ?? '[]', true);
                $newSerials = array_merge($existingSerials, $serial_ids);
                $newSerialsJson = !empty($newSerials) ? json_encode($newSerials) : null;

                $updateStmt = $pdo->prepare("UPDATE expo_stock SET quantity = ?, serial_numbers_json = ? WHERE id = ?");
                $updateStmt->execute([$newQuantity, $newSerialsJson, $existingExpoStock['id']]);
            } else {
                // Insert new record
                $insertStmt = $pdo->prepare("INSERT INTO expo_stock (expo_id, product_id, quantity, serial_numbers_json) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([ACTIVE_EXPO_ID, $product_id, $quantity, $serials_json]);
            }

            $response = ['status' => 'success', 'message' => "{$quantity} units moved to expo successfully."];
            break;

        case 'returnAllFromExpo':
            $expoStmt = $pdo->prepare("SELECT * FROM expo_stock WHERE expo_id = ? AND quantity > 0");
            $expoStmt->execute([ACTIVE_EXPO_ID]);
            $itemsToReturn = $expoStmt->fetchAll();

            if (empty($itemsToReturn)) {
                throw new Exception("No stock found in expo to return.");
            }
            
            foreach ($itemsToReturn as $item) {
                // 1. Add back to main stock
                $stockStmt = $pdo->prepare("UPDATE stock SET quantity = quantity + ? WHERE product_id = ?");
                $stockStmt->execute([$item['quantity'], $item['product_id']]);

                // 2. Update serial statuses
                if (!empty($item['serial_numbers_json'])) {
                    $serial_ids = json_decode($item['serial_numbers_json'], true);
                    $placeholders = implode(',', array_fill(0, count($serial_ids), '?'));
                    $serialStmt = $pdo->prepare("UPDATE serial_numbers SET status = 'Available' WHERE id IN ($placeholders)");
                    $serialStmt->execute($serial_ids);
                }
            }

            // 3. Clear expo stock
            $clearStmt = $pdo->prepare("DELETE FROM expo_stock WHERE expo_id = ?");
            $clearStmt->execute([ACTIVE_EXPO_ID]);
            
            // For simplicity, we also mark the expo as finished
            $finishExpoStmt = $pdo->prepare("UPDATE expos SET status = 'Finished' WHERE id = ?");
            $finishExpoStmt->execute([ACTIVE_EXPO_ID]);

            $response = ['status' => 'success', 'message' => 'All remaining stock returned from expo.'];
            break;
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);