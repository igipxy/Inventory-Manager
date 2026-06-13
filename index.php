<?php
session_start();

// ==========================================
// Hard stop: validate session for multi-tenant isolation
// ==========================================
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    http_response_code(403);
    die('CRITICAL ERROR: Unauthorized access. Session is invalid.');
}

$active_user_id = (int) $_SESSION['user_id'];

require_once 'db.php';

// ------------------------------
// Aggregate statistics for dashboard (tenant-filtered)
// ------------------------------
try {
    // STAT 1: Total Suppliers (tenant only)
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_suppliers FROM Suppliers WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $active_user_id]);
    $suppliersCount = (int) ($stmt->fetch()['total_suppliers'] ?? 0);

    // STAT 2: Total Active Products (tenant only)
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_products FROM Products WHERE is_active = 1 AND user_id = :user_id");
    $stmt->execute([':user_id' => $active_user_id]);
    $productsCount = (int) ($stmt->fetch()['total_products'] ?? 0);

    // STAT 3: Total Stock (tenant only)
    // Filter tenant via Products.user_id to guarantee stock isolation.
    $stockSql = "
        SELECT
            COALESCE(SUM(
                CASE
                    WHEN t.transaction_type = 'IN' THEN t.quantity
                    WHEN t.transaction_type = 'OUT' THEN -t.quantity
                    ELSE t.quantity
                END
            ), 0) AS total_stock
        FROM Inventory_Transactions t
        INNER JOIN Products p ON t.product_id = p.product_id
        WHERE p.user_id = :user_id";
    $stmt = $pdo->prepare($stockSql);
    $stmt->execute([':user_id' => $active_user_id]);
    $totalStock = (int) ($stmt->fetch()['total_stock'] ?? 0);

    // STAT 4: Recent Movements (tenant only)
    $recentSql = "
        SELECT
            t.transaction_id,
            t.transaction_type,
            t.quantity,
            t.transaction_date,
            p.product_name,
            p.sku,
            s.company_name AS supplier_name
        FROM Inventory_Transactions t
        INNER JOIN Products p ON t.product_id = p.product_id
        INNER JOIN Suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.user_id = :user_id
        ORDER BY t.transaction_date DESC
        LIMIT 5";
    $stmt = $pdo->prepare($recentSql);
    $stmt->execute([':user_id' => $active_user_id]);
    $recentTransactions = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Dashboard SQL error: ' . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body { background-color: #f4f6f9; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
        .stat-card { transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</head>
<body>

<?php require_once 'navbar.php'; ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark">Dashboard Overview</h2>
            <p class="text-secondary">Welcome to the central command for your inventory operations.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card border-primary border-bottom border-4 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <h6 class="card-title text-muted text-uppercase fw-semibold mb-3">Total Products</h6>
                    <h2 class="display-5 fw-bold text-primary mb-0"><?= htmlspecialchars($productsCount) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card border-success border-bottom border-4 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <h6 class="card-title text-muted text-uppercase fw-semibold mb-3">Suppliers</h6>
                    <h2 class="display-5 fw-bold text-success mb-0"><?= htmlspecialchars($suppliersCount) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card border-danger border-bottom border-4 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <h6 class="card-title text-muted text-uppercase fw-semibold mb-3">Total Stock</h6>
                    <h2 class="display-5 fw-bold text-danger mb-0"><?= htmlspecialchars($totalStock) ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card border-info border-bottom border-4 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <h6 class="card-title text-muted text-uppercase fw-semibold mb-3">Recent Movements</h6>
                    <h2 class="display-5 fw-bold text-info mb-0"><?= htmlspecialchars(count($recentTransactions)) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">5 Most Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php foreach ($recentTransactions as $txn): ?>
                                        <tr>
                                            <td class="text-nowrap"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($txn['transaction_date']))) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($txn['product_name']) ?></strong><br>
                                                <small class="text-muted">SKU: <?= htmlspecialchars($txn['sku']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($txn['supplier_name']) ?></td>
                                            <td>
                                                <?php if ($txn['transaction_type'] === 'IN'): ?>
                                                    <span class="badge bg-success">IN</span>
                                                <?php elseif ($txn['transaction_type'] === 'OUT'): ?>
                                                    <span class="badge bg-danger">OUT</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">ADJUST</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold"><?= htmlspecialchars($txn['quantity']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No transactions yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

