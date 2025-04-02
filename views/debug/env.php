<div class="container py-4">
    <h1><?= $pageTitle ?></h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger">
                <i class="bi bi-shield-exclamation"></i> 
                <strong>ຄຳເຕືອນ!</strong> ໜ້ານີ້ສະແດງຂໍ້ມູນຕົວແປແວດລ້ອມທີ່ລະອຽດອ່ອນຫຼາຍ. ກະລຸນາບໍ່ແບ່ງປັນຂໍ້ມູນນີ້ກັບໃຜ.
            </div>
        </div>
    </div>
    
    <?php foreach ($data as $sectionTitle => $sectionData): ?>
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><?= $sectionTitle ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($sectionData)): ?>
                    <p class="text-muted">ບໍ່ມີຂໍ້ມູນ</p>
                <?php else: ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 30%;">ຊື່ຕົວແປ</th>
                                <th>ຄ່າ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sectionData as $key => $value): ?>
                                <tr>
                                    <td><code><?= $key ?></code></td>
                                    <td>
                                        <?php if (is_array($value) || is_object($value)): ?>
                                            <pre><?= print_r($value, true) ?></pre>
                                        <?php elseif (is_bool($value)): ?>
                                            <span class="badge bg-secondary"><?= $value ? 'true' : 'false' ?></span>
                                        <?php elseif (is_null($value)): ?>
                                            <span class="badge bg-light text-dark">null</span>
                                        <?php elseif ($value === ''): ?>
                                            <span class="badge bg-light text-dark">ຄ່າວ່າງ</span>
                                        <?php else: ?>
                                            <?= $value ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- ປຸ່ມເຊື່ອມຕໍ່ debug ອື່ນໆ -->
    <div class="mt-4">
        <a href="<?= getenv('APP_URL') ?>/debug/info" class="btn btn-info me-2">
            <i class="bi bi-info-circle"></i> ຂໍ້ມູນລະບົບ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/request" class="btn btn-info me-2">
            <i class="bi bi-arrow-down-square"></i> ຂໍ້ມູນຄຳຂໍ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/routes" class="btn btn-info me-2">
            <i class="bi bi-signpost-split"></i> ເສັ້ນທາງ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/resources" class="btn btn-info me-2">
            <i class="bi bi-cpu"></i> ຊັບພະຍາກອນ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/query" class="btn btn-info me-2">
            <i class="bi bi-database"></i> ຖານຂໍ້ມູນ
        </a>
        <a href="<?= getenv('APP_URL') ?>" class="btn btn-secondary">
            <i class="bi bi-house"></i> ກັບໄປໜ້າຫຼັກ
        </a>
    </div>
</div>