<!DOCTYPE html>
<html lang="lo" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'My PHP Framework' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= getenv('APP_URL') ?>/public/css/style.css" rel="stylesheet">


</head>

<body>
    <!-- Main Content -->
    <main>
        <div class="container">
            <?php if (isset($flashMessage)): ?>
                <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flashMessage['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="<?= getenv('APP_URL') ?>/public/js/script.js"></script>
</body>

</html>