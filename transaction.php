<?php
// Include the database connection
require_once 'db.php';

// Shared navbar without the Categories link
require_once 'navbar.php';

// ------------------------------
// INSERT a new transaction (DML)
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {
        // NOTE: some schemas may not have supplier_id on Inventory_Transactions.
        // We insert into product_id + transaction_type + quantity + transaction_date (+ notes).
        $sql = "INSERT INTO Inventory_Transactions (product_id, transaction_type, quantity, transaction_date, notes)
                VALUES (:product_id, :transaction_type, :quantity, :transaction_date, :notes)";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':product_id' => $_POST['product_id'],
                ':transaction_type' => $_POST['transaction_type'],
                ':quantity' => $_POST['quantity'],
                ':transaction_date' => $_POST['transaction_date'],
                ':notes' => !empty($_POST['notes']) ? $_POST['notes'] : null,
            ]);

            header('Location: transaction.php?status=inserted');
            exit;
        } catch (PDOException $e) {
            die('Insert transaction failed: ' . $e->getMessage());
        }
    }
}

$status = $_GET['status'] ?? '';
$alert = null;
if ($status === 'inserted') {
    $alert = ['type' => 'success', 'msg' => 'Transaction recorded successfully.'];
}

// ------------------------------
// For INSERT form dropdowns
// ------------------------------
$products = [];

try {
    $products = $pdo->query(
        "SELECT p.product_id, p.sku, p.product_name, s.company_name AS supplier_name FROM Products p  
        LEFT JOIN Suppliers s
        ON p.supplier_id = s.supplier_id 
        ORDER BY p.product_name ASC")->fetchAll();
} catch (PDOException $e) {
    die('Error loading dropdown data: ' . $e->getMessage());
}

// ------------------------------
// SELECT with INNER JOINs
// (Products + Suppliers)
// ------------------------------
// We join suppliers via Products.supplier_id (so we don't require Inventory_Transactions.supplier_id).
$sql = "
    SELECT
        t.transaction_id,
        t.transaction_type,
        t.quantity,
        t.transaction_date,
        t.notes,
        p.product_name,
        p.sku,
        s.company_name AS supplier_name
    FROM Inventory_Transactions t
    INNER JOIN Products p ON t.product_id = p.product_id
    INNER JOIN Suppliers s ON p.supplier_id = s.supplier_id
    ORDER BY t.transaction_date DESC
";

try {
    $stmt = $pdo->query($sql);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error fetching transactions: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php /* navbar is rendered via navbar.php */ ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark">Transaction Ledger</h2>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> shadow-sm" role="alert">
                <?= htmlspecialchars($alert['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- INSERT form -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">Log Movement</h4>
            </div>
            <div class="card-body">
                <form action="transaction.php" method="POST" class="row g-3">
                    <input type="hidden" name="action" value="insert">

                    <div class="col-md-6">
                        <label class="form-label">Product</label>
                        <select class="form-select" name="product_id" id="productSelect" required>
                            <option value="">Select a product...</option>
                            <?php foreach ($products as $p): ?>
                                <option
                                    value="<?= htmlspecialchars($p['product_id']) ?>"
                                    data-supplier="<?= htmlspecialchars($p['supplier_name'] ?? 'No Supplier') ?>"
                                >
                                    <?= htmlspecialchars($p['product_name']) ?> (<?= htmlspecialchars($p['sku']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier (for reference)</label>
                        <div
                            id="supplierDisplay"
                            class="form-control bg-light"
                            style="min-height:38px;"
                        >
                            Select a product...
                        </div>
                        <div class="form-text">Supplier is derived from the selected product.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Transaction Type</label>
                        <select class="form-select" name="transaction_type" required>
                            <option value="IN">IN</option>
                            <option value="OUT">OUT</option>
                            <option value="ADJUST">ADJUST</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Quantity</label>
                        <input class="form-control" type="number" name="quantity" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Date & Time</label>
                        <input class="form-control" type="datetime-local" name="transaction_date" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" maxlength="500"></textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-box-arrow-in-down me-1"></i>
                            Record Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Joined table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Date & Time</th>
                                <th>Product</th>
                                <th>Supplier</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions)): ?>
                                <?php foreach ($transactions as $txn): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <?= htmlspecialchars(date('M j, Y g:i A', strtotime($txn['transaction_date']))) ?>
                                        </td>
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
                                        <td class="fw-bold"><?= htmlspecialchars($txn['quantity']) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($txn['notes'] ?? '--') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No transactions recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const productSelect = document.getElementById('productSelect');
    const supplierDisplay = document.getElementById('supplierDisplay');

    function updateSupplier() {
        const selected =
            productSelect.options[productSelect.selectedIndex];

        supplierDisplay.textContent = selected.dataset.supplier || 'Select a product...';
    }

    productSelect.addEventListener('change', updateSupplier);

    updateSupplier();
});
</script>
</html>

