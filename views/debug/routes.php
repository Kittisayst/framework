<div class="container py-4">
    <h1><?= $pageTitle ?></h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                ສະແດງລາຍຊື່ເສັ້ນທາງທັງໝົດທີ່ລົງທະບຽນໃນລະບົບ, ຄລາສ controller ທີ່ຈັດການກັບພວກມັນ ແລະ middlewares ທີ່ໃຊ້.
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">ເສັ້ນທາງທີ່ລົງທະບຽນທັງໝົດ (<?= count($routes) ?>)</h5>
                </div>
                <div class="col-auto">
                    <?php
                        $getCount = 0;
                        $postCount = 0;
                        $putCount = 0;
                        $deleteCount = 0;
                        
                        foreach ($routes as $route) {
                            if ($route['method'] === 'GET') $getCount++;
                            else if ($route['method'] === 'POST') $postCount++;
                            else if ($route['method'] === 'PUT') $putCount++;
                            else if ($route['method'] === 'DELETE') $deleteCount++;
                        }
                    ?>
                    <span class="badge bg-light text-primary">GET: <?= $getCount ?></span>
                    <span class="badge bg-light text-primary">POST: <?= $postCount ?></span>
                    <span class="badge bg-light text-primary">PUT: <?= $putCount ?></span>
                    <span class="badge bg-light text-primary">DELETE: <?= $deleteCount ?></span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($routes)): ?>
                <div class="p-4">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> ບໍ່ພົບເສັ້ນທາງທີ່ລົງທະບຽນ.
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Method</th>
                                <th>PATH</th>
                                <th>Name</th>
                                <th>Controller</th>
                                <th>Action</th>
                                <th>Middlewares</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($routes as $route): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php
                                            switch ($route['method']) {
                                                case 'GET': echo 'success'; break;
                                                case 'POST': echo 'primary'; break;
                                                case 'PUT': echo 'warning'; break;
                                                case 'DELETE': echo 'danger'; break;
                                                default: echo 'secondary'; break;
                                            }
                                        ?>"><?= $route['method'] ?></span>
                                    </td>
                                    <td><code><?= $route['path'] ?></code></td>
                                    <td>
                                        <?php if ($route['name']): ?>
                                            <span class="badge bg-info"><?= $route['name'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">ບໍ່ມີ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $route['controller'] ?></td>
                                    <td><?= $route['action'] ?></td>
                                    <td>
                                        <?php if (!empty($route['middlewares'])): ?>
                                            <?php foreach ($route['middlewares'] as $middleware): ?>
                                                <span class="badge bg-secondary me-1"><?= $middleware ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">ບໍ່ມີ</span>
                                        <?php endif; ?>
                                    </td>
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
        <a href="<?= getenv('APP_URL') ?>/debug/info" class="btn btn-info me-2">
            <i class="bi bi-info-circle"></i> ຂໍ້ມູນລະບົບ
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