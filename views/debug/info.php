<div class="container py-4">
    <h1><?= $pageTitle ?></h1>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                ໝາຍເຫດ: ໜ້ານີ້ສະແດງສະເພາະເມື່ອ APP_DEBUG=true ໃນໄຟລ໌ .env ເທົ່ານັ້ນ.
            </div>
        </div>
    </div>

    <?php foreach ($data as $sectionTitle => $sectionData): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><?= $sectionTitle ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <?php foreach ($sectionData as $key => $value): ?>
                            <tr>
                                <th style="width: 200px;"><?= $key ?></th>
                                <td>
                                    <?php if (is_array($value) || is_object($value)): ?>
                                        <pre><?= print_r($value, true) ?></pre>
                                    <?php else: ?>
                                        <?= $value ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="mt-4">
        <a href="<?= getenv('APP_URL') ?>/debug/routes" class="btn btn-info me-2">
            <i class="bi bi-signpost-split"></i> ເສັ້ນທາງທັງໝົດ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/request" class="btn btn-info me-2">
            <i class="bi bi-arrow-down-square"></i> ຂໍ້ມູນຄຳຂໍ
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