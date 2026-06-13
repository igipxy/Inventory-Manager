<?php
// Shared navigation bar without the Categories link
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-boxes me-2"></i>InventoryManager</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="supplier.php">Suppliers</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="transaction.php">Transactions</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-outline-light btn-sm px-3" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
