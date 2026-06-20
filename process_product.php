<?php
require_once 'db.php';

// Route the request based on the method (POST for form submissions, GET for delete links)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // HANDLE INSERT
    // ==========================================
    if ($action === 'insert') {
        $sql = "INSERT INTO Products (sku, product_name, category_id, supplier_id, unit_price, reorder_level, is_active) 
                VALUES (:sku, :product_name, :category_id, :supplier_id, :unit_price, :reorder_level, TRUE)";
        
        try {
            $stmt = $pdo->prepare($sql);
            // Execute the prepared statement with sanitized data mapping
            $stmt->execute([
                ':sku' => $_POST['sku'],
                ':product_name' => $_POST['product_name'],
                ':category_id' => $_POST['category_id'],
                ':supplier_id' => $_POST['supplier_id'],
                ':unit_price' => $_POST['unit_price'],
                ':reorder_level' => $_POST['reorder_level']
            ]);
            
            // Redirect back to the display page on success
            header("Location: products.php?status=success");
            exit;
        } catch (PDOException $e) {
            die("Insert failed: " . $e->getMessage());
        }
    }

    // ==========================================
    // HANDLE UPDATE
    // ==========================================
    elseif ($action === 'update') {
        $sql = "UPDATE Products 
                SET sku = :sku, 
                    product_name = :product_name, 
                    category_id = :category_id, 
                    supplier_id = :supplier_id, 
                    unit_price = :unit_price, 
                    reorder_level = :reorder_level
                WHERE product_id = :product_id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':sku' => $_POST['sku'],
                ':product_name' => $_POST['product_name'],
                ':category_id' => $_POST['category_id'],
                ':supplier_id' => $_POST['supplier_id'],
                ':unit_price' => $_POST['unit_price'],
                ':reorder_level' => $_POST['reorder_level'],
                ':product_id' => $_POST['product_id'] // Requires a hidden input in your edit form
            ]);
            
            header("Location: products.php?status=updated");
            exit;
        } catch (PDOException $e) {
            die("Update failed: " . $e->getMessage());
        }
    }
}

// ==========================================
// HANDLE DELETE (Soft Delete via GET)
// ==========================================
// Triggered by a link like: <a href="process_product.php?action=delete&id=1">Delete</a>
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        
        // Using a soft delete (is_active = FALSE) as established in the DB schema
        $sql = "UPDATE Products SET is_active = FALSE WHERE product_id = :product_id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':product_id' => $_GET['id']]);
            
            header("Location: products.php?status=deleted");
            exit;
        } catch (PDOException $e) {
            die("Delete failed: " . $e->getMessage());
        }
    }


// ==========================================
// HANDLE DELETE (Hard Delete via GET)
// ==========================================
// Triggered by a link like: <a href="process_product.php?action=hard_delete&id=1">Delete</a>
    elseif (isset($_GET['action']) && $_GET['action'] === 'hard_delete' && isset($_GET['id'])) {
        $sql = "DELETE FROM Products WHERE product_id = :product_id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':product_id' => $_GET['id']]);
            
            header("Location: product_archieve.php?status=hard_deleted");
            exit;
        } catch (PDOException $e) {
            die("Hard delete failed: " . $e->getMessage());
        }
    }

// ==========================================
// Restore Product
// ==========================================
// Triggered by a link like: <a href="process_product.php?action=restore&id=1">Restore</a>
    elseif (isset($_GET['action']) && $_GET['action'] === 'restore' && isset($_GET['id'])) {
        $sql = "UPDATE Products SET is_active = TRUE WHERE product_id = :product_id";
        echo $sql; // Debugging line to check the generated SQL query
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':product_id' => $_GET['id']]);
            
            header("Location: product_archieve.php?status=restored");
            exit;
        } catch (PDOException $e) {
            die("Restore failed: " . $e->getMessage());
        }
    }
}
// If accessed directly without valid parameters, send back to dashboard
header("Location: index.php");
exit;
?>