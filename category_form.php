<?php
require_once 'db.php';

$category = [
    'category_id' => '',
    'category_name' => '',
    'description' => ''
];

$action = 'insert';
$page_title = 'Add Category';

if (isset($_GET['id'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM Categories
        WHERE category_id = ?
    ");

    $stmt->execute([$_GET['id']]);

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    $action = 'update';
    $page_title = 'Edit Category';
}
?>

<!DOCTYPE html>
<html>
<head>

    <title><?= $page_title ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card">

        <div class="card-header">
            <h3><?= $page_title ?></h3>
        </div>

        <div class="card-body">

            <form method="POST" action="process_category.php">

                <input
                    type="hidden"
                    name="action"
                    value="<?= $action ?>"
                >

                <input
                    type="hidden"
                    name="category_id"
                    value="<?= $category['category_id'] ?>"
                >

                <div class="mb-3">

                    <label class="form-label">
                        Category Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="category_name"
                        required
                        value="<?= htmlspecialchars($category['category_name']) ?>"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        name="description"
                    ><?= htmlspecialchars($category['description']) ?></textarea>

                </div>

                <button type="submit" class="btn btn-success">
                    Save
                </button>

                <a href="categories.php" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>