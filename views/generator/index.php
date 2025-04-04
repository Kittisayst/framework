<?php
$title = 'MVC Generator';
require_once VIEWS_PATH . '/layouts/header.php';
?>

<div class="container mt-4">
    <h1>MVC Generator</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Select Table</h5>
            <div class="list-group">
                <?php foreach ($tables as $table): ?>
                    <a href="/generator/configure/<?= $table ?>" class="list-group-item list-group-item-action">
                        <?= ucfirst($table) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layouts/footer.php'; ?> 