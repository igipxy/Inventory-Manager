<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // HANDLE INSERT
    // ==========================================
    // Tenant enforcement
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
        http_response_code(403);
        die("CRITICAL ERROR: Unauthorized action. Session is dead or user_id is missing. Supplier insert aborted.");
    }
    $active_user_id = (int)$_SESSION['user_id'];

    if ($action === 'insert') {
        // Sanitize incoming form data
        $company_name = trim($_POST['company_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');

        // Basic validation
        if (empty($company_name)) {
            die("Validation Error: Company name is required.");
        }

        $sql = "INSERT INTO Suppliers (user_id, company_name, contact_email, contact_phone) 
                VALUES (:user_id, :company_name, :contact_email, :contact_phone)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $active_user_id,
                ':company_name' => $company_name,
                ':contact_email' => empty($contact_email) ? null : $contact_email,
                ':contact_phone' => empty($contact_phone) ? null : $contact_phone
            ]);
            header("Location: supplier.php?status=added");
            exit;
        } catch (PDOException $e) {
            die("Insert failed: " . $e->getMessage());
        }
    }

    // ==========================================
    // HANDLE UPDATE
    // ==========================================
    elseif ($action === 'update') {
        // Sanitize incoming form data
        $company_name = trim($_POST['company_name'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $supplier_id = $_POST['supplier_id'] ?? null;

        if (empty($company_name)) {
            die("Validation Error: Company name is required.");
        }
        if (empty($supplier_id) || !is_numeric($supplier_id)) {
            die("Validation Error: supplier_id is required.");
        }

        $sql = "UPDATE Suppliers 
                SET company_name = :company_name, 
                    contact_email = :contact_email, 
                    contact_phone = :contact_phone
                WHERE supplier_id = :supplier_id AND user_id = :user_id";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':company_name' => $company_name,
                ':contact_email' => empty($contact_email) ? null : $contact_email,
                ':contact_phone' => empty($contact_phone) ? null : $contact_phone,
                ':supplier_id'  => (int)$supplier_id,
                ':user_id'       => $active_user_id
            ]);
            header("Location: supplier.php?status=updated");
            exit;
        } catch (PDOException $e) {
            die("Update failed: " . $e->getMessage());
        }
    }
}

// ==========================================
// HANDLE DELETE
// ==========================================
// Triggered by a GET request (e.g., process_supplier.php?action=delete&id=3)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        
        // Note: Because we set ON DELETE SET NULL in the DDL schema, 
        // deleting a supplier will safely orphan their products rather than destroying them.
        $sql = "DELETE FROM Suppliers WHERE supplier_id = :supplier_id AND user_id = :user_id";

        try {
            $stmt = $pdo->prepare($sql);
            // Hard stop: enforce tenant ownership even for deletes
            $stmt->execute([
                ':supplier_id' => (int)$_GET['id'],
                ':user_id'    => $active_user_id
            ]);
            header("Location: supplier.php?status=deleted");
            exit;
        } catch (PDOException $e) {
            die("Delete failed: " . $e->getMessage());
        }
    }
}

// Fallback redirect
header("Location: supplier.php");
exit;
?>