<div class="row mb-4">
    <div class="col-md-6">
        <h1><?= $pageTitle ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= getenv('APP_URL') ?>/user/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> ເພີ່ມຜູ້ໃຊ້ໃໝ່
        </a>
    </div>
</div>

<!-- ຟອມຄົ້ນຫາ -->
<div class="row mb-4">
    <div class="col-md-12">
        <form action="<?= getenv('APP_URL') ?>/user" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2"
                placeholder="ຄົ້ນຫາຕາມຊື່ຫຼືອີເມລ"
                value="<?= isset($search) ? $search : '' ?>">
            <button type="submit" class="btn btn-outline-primary">ຄົ້ນຫາ</button>
            <?php if (isset($search) && !empty($search)): ?>
                <a href="<?= getenv('APP_URL') ?>/user" class="btn btn-outline-secondary ms-2">ລ້າງ</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (empty($users)): ?>
    <div class="alert alert-info">
        <?= isset($search) && !empty($search) ? 'ບໍ່ພົບຜົນການຄົ້ນຫາ.' : 'ຍັງບໍ່ມີຜູ້ໃຊ້ໃນລະບົບ.' ?>
    </div>
<?php else: ?>
    <!-- ຕາຕະລາງຜູ້ໃຊ້ -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>ຊື່</th>
                    <th>ອີເມລ</th>
                    <th>ບົດບາດ</th>
                    <th>ສະຖານະ</th>
                    <th>ການດຳເນີນການ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                <?= $user['role'] === 'admin' ? 'ຜູ້ດູແລລະບົບ' : 'ຜູ້ໃຊ້ທົ່ວໄປ' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= $user['status'] === 'active' ? 'ໃຊ້ງານ' : 'ປິດໃຊ້ງານ' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= getenv('APP_URL') ?>/user/show/<?= $user['id'] ?>"
                                    class="btn btn-info">
                                    ເບິ່ງ
                                </a>
                                <a href="<?= getenv('APP_URL') ?>/user/edit/<?= $user['id'] ?>"
                                    class="btn btn-warning">
                                    ແກ້ໄຂ
                                </a>
                                <a href="<?= getenv('APP_URL') ?>/user/delete/<?= $user['id'] ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?')">
                                    ລຶບ
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>