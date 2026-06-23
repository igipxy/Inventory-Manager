<?php
require_once 'db.php';

$mode = 'insert';
$product = [
    'product_id' => '',
    'sku' => '',
    'product_name' => '',
    'category_id' => '',
    'supplier_id' => '',
    'unit_price' => '',
    'reorder_level' => ''
];

// If id is provided, load product for UPDATE form
if (isset($_GET['id'])) {
    $mode = 'update';
    $sql = "SELECT product_id, sku, product_name, category_id, supplier_id, unit_price, reorder_level
            FROM Products WHERE product_id = :id";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $_GET['id']]);
        $fetched = $stmt->fetch();
        if ($fetched) {
            $product = $fetched;
        }
    } catch (PDOException $e) {
        die('Error loading product for edit: ' . $e->getMessage());
    }
}

// Fetch Categories and Suppliers for the dropdown menus
try {
    $categories = $pdo->query("SELECT category_id, category_name FROM Categories ORDER BY category_name")->fetchAll();
    $suppliers = $pdo->query("SELECT supplier_id, company_name FROM Suppliers ORDER BY company_name")->fetchAll();
} catch (PDOException $e) {
    die("Error loading form data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><?= $mode === 'update' ? 'Edit Inventory Item' : 'Add New Inventory Item' ?></h4>
                    </div>
                    <div class="card-body">
                        <form action="process_product.php" method="POST">
                            <input type="hidden" name="action" value="<?= $mode ?>">
                            <?php if ($mode === 'update'): ?>
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['product_id']) ?>">
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sku" class="form-label">SKU (Stock Keeping Unit)</label>
                                    <input type="text" class="form-control" id="sku" name="sku" required maxlength="50" placeholder="e.g., SKU-123" value="<?= htmlspecialchars($product['sku']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" required maxlength="100" value="<?= htmlspecialchars($product['product_name']) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select a Category...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['category_id'] ?>" <?= ($mode === 'update' && (string)$product['category_id'] === (string)$cat['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['category_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                                        <option value="">Select a Supplier...</option>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?= $sup['supplier_id'] ?>" <?= ($mode === 'update' && (string)$product['supplier_id'] === (string)$sup['supplier_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sup['company_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="unit_price" class="form-label">Unit Price (Rp)</label>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" required min="0.01" step="0.01" value="<?= htmlspecialchars($product['unit_price']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="reorder_level" class="form-label">Reorder Level</label>
                                    <input type="number" class="form-control" id="reorder_level" name="reorder_level" required min="0" step="1" value="<?= $mode === 'update' ? htmlspecialchars($product['reorder_level']) : '0' ?>">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $mode === 'update' ? 'Update Product' : 'Save Product' ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>