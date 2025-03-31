<div class="row mb-4">
    <div class="col-md-12">
        <h1><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= getenv('APP_URL') ?>">ໜ້າຫຼັກ</a></li>
                <li class="breadcrumb-item"><a href="<?= getenv('APP_URL') ?>/user">ຜູ້ໃຊ້</a></li>
                <li class="breadcrumb-item active"><?= $user['name'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">ຂໍ້ມູນຜູ້ໃຊ້</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">ID</th>
                        <td><?= $user['id'] ?></td>
                    </tr>
                    <tr>
                        <th>ຊື່</th>
                        <td><?= $user['name'] ?></td>
                    </tr>
                    <tr>
                        <th>ອີເມລ</th>
                        <td><?= $user['email'] ?></td>
                    </tr>
                    <tr>
                        <th>ບົດບາດ</th>
                        <td>
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'info' ?>">
                                <?= $user['role'] === 'admin' ? 'ຜູ້ດູແລລະບົບ' : 'ຜູ້ໃຊ້ທົ່ວໄປ' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>ສະຖານະ</th>
                        <td>
                            <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= $user['status'] === 'active' ? 'ໃຊ້ງານ' : 'ປິດໃຊ້ງານ' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>ວັນທີສ້າງ</th>
                        <td><?= isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>ວັນທີອັບເດດ</th>
                        <td><?= isset($user['updated_at']) ? date('d/m/Y H:i', strtotime($user['updated_at'])) : 'N/A' ?></td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <div class="btn-group">
                    <a href="<?= getenv('APP_URL') ?>/user/edit/<?= $user['id'] ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> ແກ້ໄຂ
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/user/delete/<?= $user['id'] ?>"
                        class="btn btn-danger"
                        onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?')">
                        <i class="bi bi-trash"></i> ລຶບ
                    </a>
                    <a href="<?= getenv('APP_URL') ?>/user" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> ກັບຄືນ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>