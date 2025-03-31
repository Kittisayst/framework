<div class="row mb-4">
    <div class="col-md-12">
        <h1><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= getenv('APP_URL') ?>">ໜ້າຫຼັກ</a></li>
                <li class="breadcrumb-item"><a href="<?= getenv('APP_URL') ?>/user">ຜູ້ໃຊ້</a></li>
                <li class="breadcrumb-item active">ສ້າງຜູ້ໃຊ້ໃໝ່</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">ຟອມສ້າງຜູ້ໃຊ້ໃໝ່</h5>
            </div>
            <div class="card-body">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $field => $fieldErrors): ?>
                                <?php foreach ($fieldErrors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= getenv('APP_URL') ?>/user/store" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">ຊື່ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= isset($formData['name']) ? $formData['name'] : '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">ອີເມລ <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?= isset($formData['email']) ? $formData['email'] : '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">ລະຫັດຜ່ານ <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">ບົດບາດ</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user" <?= isset($formData['role']) && $formData['role'] === 'user' ? 'selected' : '' ?>>ຜູ້ໃຊ້ທົ່ວໄປ</option>
                            <option value="admin" <?= isset($formData['role']) && $formData['role'] === 'admin' ? 'selected' : '' ?>>ຜູ້ດູແລລະບົບ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">ສະຖານະ</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?= isset($formData['status']) && $formData['status'] === 'active' ? 'selected' : '' ?>>ໃຊ້ງານ</option>
                            <option value="inactive" <?= isset($formData['status']) && $formData['status'] === 'inactive' ? 'selected' : '' ?>>ປິດໃຊ້ງານ</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                        <a href="<?= getenv('APP_URL') ?>/user" class="btn btn-secondary">ຍົກເລີກ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">ຄຳແນະນຳ</h5>
            </div>
            <div class="card-body">
                <p>ຂໍ້ມູນທີ່ຕ້ອງປ້ອນ:</p>
                <ul>
                    <li><strong>ຊື່:</strong> ຊື່ຜູ້ໃຊ້ (ຢ່າງໜ້ອຍ 3 ຕົວອັກສອນ)</li>
                    <li><strong>ອີເມລ:</strong> ອີເມລທີ່ຖືກຕ້ອງ (ຕ້ອງບໍ່ຊ້ຳກັບຜູ້ໃຊ້ອື່ນ)</li>
                    <li><strong>ລະຫັດຜ່ານ:</strong> ຢ່າງໜ້ອຍ 6 ຕົວອັກສອນ</li>
                    <li><strong>ບົດບາດ:</strong> ກຳນົດສິດການໃຊ້ງານຂອງຜູ້ໃຊ້</li>
                    <li><strong>ສະຖານະ:</strong> ກຳນົດວ່າຜູ້ໃຊ້ສາມາດເຂົ້າສູ່ລະບົບໄດ້ຫຼືບໍ່</li>
                </ul>
            </div>
        </div>
    </div>
</div>