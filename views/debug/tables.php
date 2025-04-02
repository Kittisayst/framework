<div class="container py-4">
    <h1><?= $pageTitle ?></h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                ຄລິກທີ່ຊື່ຕາຕະລາງເພື່ອເບິ່ງໂຄງສ້າງແລະຂໍ້ມູນ.
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">ຕາຕະລາງໃນຖານຂໍ້ມູນ <?= getenv('DB_DATABASE') ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($tables)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i> ບໍ່ພົບຕາຕະລາງໃນຖານຂໍ້ມູນ.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($tables as $table): ?>
                        <?php 
                            // ດຶງຊື່ຕາຕະລາງຈາກຜົນລັບ (ອາດຈະແຕກຕ່າງກັນຂຶ້ນກັບຖານຂໍ້ມູນ)
                            $tableName = current($table);
                        ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $tableName ?></h5>
                                    <a href="<?= getenv('APP_URL') ?>/debug/query/<?= $tableName ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-table"></i> ເບິ່ງຂໍ້ມູນ
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
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
        <a href="<?= getenv('APP_URL') ?>/debug/resources" class="btn btn-info me-2">
            <i class="bi bi-cpu"></i> ຊັບພະຍາກອນ
        </a>
        <a href="<?= getenv('APP_URL') ?>" class="btn btn-secondary">
            <i class="bi bi-house"></i> ກັບໄປໜ້າຫຼັກ
        </a>
    </div>
</div>