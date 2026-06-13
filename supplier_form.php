<?php
require_once 'db.php';

// Default values for an INSERT operation
$supplier = [
    'supplier_id' => '',
    'company_name' => '',
    'contact_email' => '',
    'contact_phone' => ''
];
$action = 'insert';
$page_title = "Add New Supplier";

// Check if we are performing an UPDATE operation
if (isset($_GET['id'])) {
    $sql = "SELECT * FROM Suppliers WHERE supplier_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_GET['id']]);
    $fetched_supplier = $stmt->fetch();
    
    if ($fetched_supplier) {
        $supplier = $fetched_supplier;
        $action = 'update';
        $page_title = "Edit Supplier";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?> - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><?= $page_title ?></h4>
                    </div>
                    <div class="card-body">
                        <form action="process_supplier.php" method="POST">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            
                            <?php if ($action === 'update'): ?>
                                <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($supplier['supplier_id']) ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name *</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?= htmlspecialchars($supplier['company_name']) ?>" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                       value="<?= htmlspecialchars($supplier['contact_email'] ?? '') ?>" maxlength="100">
                            </div>

                            <div class="mb-4">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                       value="<?= htmlspecialchars($supplier['contact_phone'] ?? '') ?>" maxlength="20">
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="supplier.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Supplier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>