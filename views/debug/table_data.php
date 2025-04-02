<div class="container py-4">
    <h1><?= $pageTitle ?></h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                ສະແດງຂໍ້ມູນຈາກຕາຕະລາງ <strong><?= $table ?></strong>. 
                ຄຳສັ່ງ: <code><?= $query ?></code>
                <br>ເວລາປະຕິບັດ: <strong><?= $queryTime ?> ms</strong> / ຈຳນວນທັງໝົດ: <strong><?= $totalRows ?> ແຖວ</strong>
            </div>
        </div>
    </div>
    
    <!-- ສະແດງໂຄງສ້າງຕາຕະລາງ -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">ໂຄງສ້າງຕາຕະລາງ</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($structure as $column): ?>
                            <tr>
                                <td><?= $column['Field'] ?></td>
                                <td><code><?= $column['Type'] ?></code></td>
                                <td><?= $column['Null'] ?></td>
                                <td>
                                    <?php if ($column['Key'] === 'PRI'): ?>
                                        <span class="badge bg-danger">Primary</span>
                                    <?php elseif ($column['Key'] === 'UNI'): ?>
                                        <span class="badge bg-success">Unique</span>
                                    <?php elseif ($column['Key'] === 'MUL'): ?>
                                        <span class="badge bg-info">Index</span>
                                    <?php else: ?>
                                        <?= $column['Key'] ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $column['Default'] ?? '<span class="text-muted">NULL</span>' ?></td>
                                <td><?= $column['Extra'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- ສະແດງຂໍ້ມູນຕາຕະລາງ -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">ຂໍ້ມູນຕາຕະລາງ (<?= count($data) ?> ຈາກ <?= $totalRows ?> ແຖວ)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($data)): ?>
                <div class="p-4">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> ບໍ່ມີຂໍ້ມູນໃນຕາຕະລາງນີ້.
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php foreach (array_keys($data[0]) as $columnName): ?>
                                    <th><?= $columnName ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td>
                                            <?php if (is_null($value)): ?>
                                                <span class="text-muted">NULL</span>
                                            <?php elseif (is_bool($value)): ?>
                                                <?= $value ? 'true' : 'false' ?>
                                            <?php elseif (is_array($value) || is_object($value)): ?>
                                                <pre><?= print_r($value, true) ?></pre>
                                            <?php elseif (strlen($value) > 100): ?>
                                                <?= substr($value, 0, 100) ?>... <span class="badge bg-secondary">ຂໍ້ຄວາມຍາວ</span>
                                            <?php else: ?>
                                                <?= $value ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ປຸ່ມເຊື່ອມຕໍ່ debug ອື່ນໆ -->
    <div class="mt-4">
        <a href="<?= getenv('APP_URL') ?>/debug/query" class="btn btn-primary me-2">
            <i class="bi bi-arrow-left"></i> ກັບໄປລາຍການຕາຕະລາງ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/info" class="btn btn-info me-2">
            <i class="bi bi-info-circle"></i> ຂໍ້ມູນລະບົບ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/request" class="btn btn-info me-2">
            <i class="bi bi-arrow-down-square"></i> ຂໍ້ມູນຄຳຂໍ
        </a>
        <a href="<?= getenv('APP_URL') ?>/debug/routes" class="btn btn-info me-2">
            <i class="bi bi-signpost-split"></i> ເສັ້ນທາງ
        </a>
        <a href="<?= getenv('APP_URL') ?>" class="btn btn-secondary">
            <i class="bi bi-house"></i> ກັບໄປໜ້າຫຼັກ
        </a>
    </div>
</div>