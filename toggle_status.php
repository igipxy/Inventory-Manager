<?php
// 1. Session Lock: Ensure only logged-in users can trigger this action
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

// 2. Validate that an ID was passed in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $active_user_id = (int)$_SESSION['user_id'];

    try {
        // 3. FETCH & VERIFY: Check current status AND ensure the user owns this product
        $checkQuery = "SELECT is_active
                        FROM Products
                        WHERE product_id = :product_id AND user_id = :user_id";

        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([
            ':product_id' => $product_id,
            ':user_id' => $active_user_id
        ]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the product exists and belongs to the user, proceed with the flip
        if ($product !== false) {
            // 4. THE FLIP: If it's 1 (true), make it 0 (false). If it's 0, make it 1.
            $new_status = ((int)$product['is_active'] === 1) ? 0 : 1;

            // 5. UPDATE: Apply the new status securely
            $updateQuery = "UPDATE Products
                             SET is_active = :new_status
                             WHERE product_id = :product_id AND user_id = :user_id";

            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([
                ':new_status' => $new_status,
                ':product_id' => $product_id,
                ':user_id' => $active_user_id
            ]);
        }
    } catch (PDOException $e) {
        error_log("Status toggle failed: " . $e->getMessage());
    }
}

// 6. REDIRECT
header("Location: products.php");
exit;
?>

