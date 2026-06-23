<?php
// Include the database connection
require_once 'db.php';

// Shared navbar without the Categories link
require_once 'navbar.php';

session_start();
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$active_user_id = (int) $_SESSION['user_id'];

// Fetch suppliers (optional search) - tenant-isolated
$searchTerm = trim($_GET['search'] ?? '');

// Dynamic SQL: base query MUST ALWAYS enforce WHERE user_id = ?
$sql = "SELECT supplier_id, company_name, contact_email, contact_phone
        FROM Suppliers
        WHERE user_id = ?";

$params = [$active_user_id];

if (isset($_GET['search'])) {
    // Requirement: append AND company_name LIKE ? ONLY if a search is submitted.
    if ($searchTerm !== '') {
        $sql .= " AND company_name LIKE ?";
        $params[] = '%' . $searchTerm . '%';
    }
}

$sql .= " ORDER BY supplier_id ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching suppliers: " . $e->getMessage());
}

$status = $_GET['status'] ?? '';
$alert = null;
if ($status === 'added') {
    $alert = ['type' => 'success', 'msg' => 'Supplier added successfully.'];
} elseif ($status === 'updated') {
    $alert = ['type' => 'success', 'msg' => 'Supplier updated successfully.'];
} elseif ($status === 'deleted') {
    $alert = ['type' => 'warning', 'msg' => 'Supplier deleted successfully.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php /* navbar is rendered via navbar.php */ ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-dark">Supplier Directory</h2>
            <a href="supplier_form.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Add Supplier</a>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> shadow-sm" role="alert">
                <?= htmlspecialchars($alert['msg']) ?>
            </div>
        <?php endif; ?>

        <form class="mb-3" method="GET" action="supplier.php">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
                    name="search"
                    placeholder="Search suppliers..."
                    value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
                <button class="btn btn-outline-dark" type="submit">Search</button>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<<<<<<< HEAD
                            <?php if (!empty($suppliers)): ?>
                                <?php foreach ($suppliers as $sup): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($sup['company_name']) ?></td>
                                        <td><?= htmlspecialchars($sup['contact_email'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($sup['contact_phone'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="supplier_form.php?id=<?= urlencode($sup['supplier_id']) ?>">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <a class="btn btn-sm btn-outline-danger" 
                                                   href="process_supplier.php?action=delete&id=<?= urlencode($sup['supplier_id']) ?>" 
                                                   onclick="return confirm('Delete supplier (ID: <?= htmlspecialchars($sup['supplier_id']) ?>)? This action cannot be undone.');">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No suppliers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
=======
    <?php if (!empty($suppliers)): ?>
        <?php $no = 1; foreach ($suppliers as $sup): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($sup['company_name']) ?></td>
                <td><?= htmlspecialchars($sup['contact_email'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($sup['contact_phone'] ?? 'N/A') ?></td>
                <td>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="supplier_form.php?id=<?= urlencode($sup['supplier_id']) ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a class="btn btn-sm btn-outline-danger" 
                           href="process_supplier.php?action=delete&id=<?= urlencode($sup['supplier_id']) ?>" 
                           onclick="return confirm('Delete supplier (ID: <?= htmlspecialchars($sup['supplier_id']) ?>)? This action cannot be undone.');">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-muted">No suppliers found.</td>
        </tr>
    <?php endif; ?>
</tbody>

>>>>>>> 9056e1b889a0821c487c86b9197cf60bd5b12c0e
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
