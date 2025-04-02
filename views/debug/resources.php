<div class="container py-4">
    <h1><?= $pageTitle ?></h1>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                ການນຳໃຊ້ຊັບພະຍາກອນຂອງລະບົບ (ໜ່ວຍຄວາມຈຳ, ໄຟລ໌ທີ່ໃຊ້ ແລະ ການຕັ້ງຄ່າອື່ນໆ).
            </div>
        </div>
    </div>

    <?php foreach ($data as $sectionTitle => $sectionData): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><?= $sectionTitle ?></h5>
            </div>
            <div class="card-body">
                <?php if (is_array($sectionData) && isset($sectionData['Message'])): ?>
                    <p><?= $sectionData['Message'] ?></p>
                <?php elseif (is_array($sectionData) && isset($sectionData['Count']) && isset($sectionData['Files'])): ?>
                    <p>ຈຳນວນໄຟລ໌ທັງໝົດ: <strong><?= $sectionData['Count'] ?></strong></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ໄຟລ໌</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sectionData['Files'] as $index => $file): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $file ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (is_array($sectionData)): ?>
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <?php foreach ($sectionData as $key => $value): ?>
                                <tr>
                                    <th style="width: 200px;"><?= $key ?></th>
                                    <td>
                                        <?php if (is_array($value) || is_object($value)): ?>
                                            <?php if (!empty($value)): ?>
                                                <table class="table table-sm table-bordered">
                                                    <tbody>
                                                        <?php foreach ($value as $subKey => $subValue): ?>
                                                            <tr>
                                                                <th width="30%"><?= $subKey ?></th>
                                                                <td><?= $subValue ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <span class="text-muted">ບໍ່ມີຂໍ້ມູນ</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?= $value ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        <a href="<?= getenv('APP_URL') ?>/debug/request" class="btn btn-info me-2">
            <i class="bi bi-arrow-down-square"></i> ຂໍ້ມູນຄຳຂໍ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/routes" class="btn btn-info me-2">
            <i class="bi bi-signpost-split"></i> ເສັ້ນທາງ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/env" class="btn btn-info me-2">
            <i class="bi bi-gear"></i> ຕົວແປແວດລ້ອມ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/query" class="btn btn-info me-2">
            <i class="bi bi-database"></i> ຖານຂໍ້ມູນ
        </a>
        <a href="<?= getenv('APP_URL') ?>" class="btn btn-secondary">
            <i class="bi bi-house"></i> ກັບໄປໜ້າຫຼັກ
        </a>
    </div>
</div>