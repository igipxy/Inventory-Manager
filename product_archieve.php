<?php
// Include the database connection
require_once 'db.php';

session_start();
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$active_user_id = (int) $_SESSION['user_id'];

// Optional search term
$searchTerm = trim($_GET['search'] ?? '');

// ------------------------------
// Dynamic, parameter-safe query building
// ------------------------------
// Base query MUST ALWAYS include tenant filter: WHERE p.user_id = ?
$sql = "
    SELECT
        p.product_id,
        p.sku,
        p.product_name,
        p.unit_price,
        p.reorder_level,
        p.is_active,
        c.category_name,
        s.company_name AS supplier_name,
        COALESCE(SUM(
            CASE
                WHEN it.transaction_type = 'IN' THEN it.quantity
                WHEN it.transaction_type = 'OUT' THEN -it.quantity
                ELSE it.quantity
            END
        ), 0) AS stock_quantity
    FROM Products p
    INNER JOIN Categories c ON p.category_id = c.category_id
    INNER JOIN Suppliers s ON p.supplier_id = s.supplier_id
    LEFT JOIN Inventory_Transactions it ON it.product_id = p.product_id
    WHERE p.user_id = ? AND p.is_active = FALSE" ;

$params = [$active_user_id];

if (isset($_GET['search'])) {
    // If search exists, append AND p.product_name LIKE ?
    // (Keeping it strictly per requirement to avoid parameter mismatches.)
    if ($searchTerm !== '') {
        $sql .= " AND p.product_name LIKE ?";
        $params[] = '%' . $searchTerm . '%';
    }
}

$sql .= "
    GROUP BY
        p.product_id,
        p.sku,
        p.product_name,
        p.unit_price,
        p.reorder_level,
        p.is_active,
        c.category_name,
        s.company_name
    ORDER BY p.product_name ASC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Products - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php require_once 'navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark">Archived Products</h2>
            <a class="btn btn-primary" href="product_form.php"><i class="bi bi-plus-circle me-1"></i> Add Product</a>
        </div>

        <form class="mb-3" method="GET" action="product_archieve.php">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    name="search"
                    placeholder="Search products..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <button class="btn btn-outline-dark" type="submit">Search</button>
            </div>
        </form>

        <div class="mb-3 d-flex justify-content-end">
            <a class="btn btn-primary" href="products.php"><i class="bi  bi-archive me-1"></i> View Active Products</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock Quantity</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['product_id']) ?></td>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($product['product_name']) ?><br>
                                            <small class="text-muted">SKU: <?= htmlspecialchars($product['sku']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                                        <td>Rp <?= number_format((float)$product['unit_price'], 0, ',', '.') ?></td>
                                        <td>
                                            <?= htmlspecialchars($product['stock_quantity']) ?>
                                            <?php if (isset($product['stock_quantity']) && (int)$product['stock_quantity'] < 5): ?>
                                                <span class="badge bg-warning text-dark ms-2">Low Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['supplier_name']) ?></td>
                                        <td class="align-middle">
                                            <a href="toggle_status.php?id=<?= htmlspecialchars($product['product_id']) ?>"
                                               class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-danger' ?> text-decoration-none p-2 shadow-sm"
                                               title="Click to toggle status">
                                                <?php if ($product['is_active']): ?>
                                                    <i class="bi bi-check-circle me-1"></i> Active
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle me-1"></i> Inactive
                                                <?php endif; ?>
                                            </a>
                                        </td>


                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a class="btn btn-sm btn-outline-primary" href="product_form.php?id=<?= urlencode($product['product_id']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <!-- Restore product -->
                                                <a class="btn btn-sm btn-outline-success" 
                                                   href="process_product.php?action=restore&id=<?= urlencode($product['product_id']) ?>"
                                                   onclick="return confirm('Restore product (ID: <?= htmlspecialchars($product['product_id']) ?>)?');">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-danger" 
                                                   href="process_product.php?action=hard_delete&id=<?= urlencode($product['product_id']) ?>"
                                                   onclick="return confirm('Delete product (ID: <?= htmlspecialchars($product['product_id']) ?>)? This cannot be undone.');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No products found in the database.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>