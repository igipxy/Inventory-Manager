<?php
require_once 'db.php';
require_once 'navbar.php';

$sql = "SELECT * FROM Categories ORDER BY category_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status = $_GET['status'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Category Directory</h2>

        <a href="category_form.php" class="btn btn-primary">
            Add Category
        </a>
    </div>

    <?php if ($status === 'added'): ?>
        <div class="alert alert-success">Category added successfully.</div>
    <?php endif; ?>

    <?php if ($status === 'updated'): ?>
        <div class="alert alert-success">Category updated successfully.</div>
    <?php endif; ?>

    <?php if ($status === 'deleted'): ?>
        <div class="alert alert-danger">Category deleted successfully.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">

            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        
                        <th>Category Name</th>
                        <th>Description</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($categories as $cat): ?>

                    <tr>


                        <td><?= htmlspecialchars($cat['category_name']) ?></td>

                        <td><?= htmlspecialchars($cat['description']) ?></td>

                        <td>

                            <a href="category_form.php?id=<?= $cat['category_id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>

                            <a href="process_category.php?action=delete&id=<?= $cat['category_id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this category?')">
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </div>
    </div>

</div>

</body>
</html>