<div class="row">
    <div class="col-md-8 mx-auto mt-5">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> ເກີດຂໍ້ຜິດພາດ</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="<?= getenv('APP_URL') ?>/public/images/error.png" alt="Error"
                        class="img-fluid" style="max-width: 150px;">
                </div>

                <h5 class="text-danger text-center"><?= isset($error) ? $error : 'ເກີດຂໍ້ຜິດພາດທີ່ບໍ່ຄາດຄິດ' ?></h5>

                <p class="text-center text-muted mt-3">
                    <?= isset($message) ? $message : 'ກະລຸນາລອງໃໝ່ອີກຄັ້ງ ຫຼື ຕິດຕໍ່ຜູ້ດູແລລະບົບ' ?>
                </p>

                <?php if (isset($details) && getenv('APP_DEBUG') === 'true'): ?>
                    <div class="alert alert-secondary mt-3">
                        <h6>ລາຍລະອຽດທາງເຕັກນິກ:</h6>
                        <pre class="mb-0"><?= $details ?></pre>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="text-center">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> ກັບຄືນ
                    </a>
                    <a href="<?= getenv('APP_URL') ?>" class="btn btn-primary">
                        <i class="bi bi-house-fill"></i> ກັບໄປໜ້າຫຼັກ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>