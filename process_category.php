<?php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {

        $sql = "
            INSERT INTO Categories
            (
                category_name,
                description
            )
            VALUES
            (
                :category_name,
                :description
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':category_name' => trim($_POST['category_name']),
            ':description' => trim($_POST['description'])
        ]);

        header("Location: categories.php?status=added");
        exit;
    }

    if ($action === 'update') {

        $sql = "
            UPDATE Categories
            SET
                category_name = :category_name,
                description = :description
            WHERE category_id = :category_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':category_name' => trim($_POST['category_name']),
            ':description' => trim($_POST['description']),
            ':category_id' => (int)$_POST['category_id']
        ]);

        header("Location: categories.php?status=updated");
        exit;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($_GET['action'])
    && $_GET['action'] === 'delete'
    && isset($_GET['id'])
) {

    $stmt = $pdo->prepare("
        DELETE FROM Categories
        WHERE category_id = ?
    ");

    $stmt->execute([
        (int)$_GET['id']
    ]);

    header("Location: categories.php?status=deleted");
    exit;
}

header("Location: categories.php");
exit;