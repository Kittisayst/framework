<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? $pageTitle : 'My PHP Framework' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white py-3">
                        <h4 class="mb-0 d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span><?= isset($error) ? $error : 'ເກີດຂໍ້ຜິດພາດໃນລະບົບ' ?></span>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="<?= getenv('APP_URL') ?>/public/images/error.png"
                                alt="Error" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.8;">

                            <h5 class="text-danger mb-3"><?= isset($message) ? $message : 'ກະລຸນາລອງໃໝ່ອີກຄັ້ງ ຫຼື ຕິດຕໍ່ຜູ້ດູແລລະບົບ' ?></h5>

                            <div class="d-flex justify-content-center mt-4">
                                <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-left"></i> ກັບຄືນ
                                </a>
                                <a href="<?= getenv('APP_URL') ?>" class="btn btn-primary">
                                    <i class="bi bi-house-fill"></i> ກັບໄປໜ້າຫຼັກ
                                </a>
                            </div>
                        </div>

                        <?php if (isset($details) && getenv('APP_DEBUG') === 'true'): ?>
                            <div class="mt-4">
                                <div class="alert alert-secondary">
                                    <h6 class="d-flex align-items-center mb-3">
                                        <i class="bi bi-code-slash me-2"></i> ລາຍລະອຽດທາງເຕັກນິກ:
                                    </h6>
                                    <div class="bg-light p-3 rounded">
                                        <pre class="mb-0 text-danger" style="white-space: pre-wrap; word-break: break-word; font-size: 0.875rem;"><?= $details ?></pre>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (getenv('APP_DEBUG') !== 'true'): ?>
                            <div class="text-center text-muted small mt-4">
                                <p>ຫາກທ່ານພົບເຫັນຂໍ້ຜິດພາດນີ້ຫຼາຍຄັ້ງ, ກະລຸນາຕິດຕໍ່ຜູ້ບໍລິຫານລະບົບ.</p>
                                <p>ລະຫັດອ້າງອີງ: <?= uniqid() ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light py-3 text-center">
                        <div class="row align-items-center">
                            <div class="col">
                                <i class="bi bi-clock"></i> <?= date('d/m/Y H:i:s') ?>
                            </div>
                            <div class="col">
                                <i class="bi bi-globe"></i> <?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>