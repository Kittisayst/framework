<div class="container py-4">
    <h1><?= $pageTitle ?></h1>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                ໝາຍເຫດ: ໜ້ານີ້ສະແດງຂໍ້ມູນທີ່ລະອຽດອ່ອນ. ກະລຸນາເປີດໃຊ້ສະເພາະໃນສະພາບແວດລ້ອມການພັດທະນາເທົ່ານັ້ນ.
            </div>
        </div>
    </div>

    <?php foreach ($data as $sectionTitle => $sectionData): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><?= $sectionTitle ?></h5>
            </div>
            <div class="card-body">
                <?php if (is_array($sectionData) || is_object($sectionData)): ?>
                    <?php if (count((array)$sectionData) > 0): ?>
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <?php foreach ($sectionData as $key => $value): ?>
                                    <tr>
                                        <th style="width: 200px;"><?= $key ?></th>
                                        <td>
                                            <?php if (is_array($value) || is_object($value)): ?>
                                                <pre><?= print_r($value, true) ?></pre>
                                            <?php else: ?>
                                                <?= is_bool($value) ? ($value ? 'true' : 'false') : $value ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">ບໍ່ມີຂໍ້ມູນ</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><?= $sectionData ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- ປຸ່ມເຊື່ອມຕໍ່ debug ອື່ນໆ -->
    <div class="mt-4">
        <a href="<?= getenv('APP_URL') ?>/debug/info" class="btn btn-info me-2">
            <i class="bi bi-info-circle"></i> ຂໍ້ມູນລະບົບ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/routes" class="btn btn-info me-2">
            <i class="bi bi-signpost-split"></i> ເສັ້ນທາງ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/env" class="btn btn-info me-2">
            <i class="bi bi-gear"></i> ຕົວແປແວດລ້ອມ
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