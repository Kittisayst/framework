<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-10 mx-auto">
            <h1 class="display-5 fw-bold mb-4"><?= $pageTitle ?></h1>

            <?php if (isset($flashMessage)): ?>
                <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flashMessage['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-5">
                <div class="col-md-5 order-md-last">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">ຂໍ້ມູນຕິດຕໍ່</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-envelope-fill fs-5 text-primary me-3"></i>
                                    <span><?= $contactInfo['email'] ?></span>
                                </li>
                                <li class="mb-3 d-flex">
                                    <i class="bi bi-telephone-fill fs-5 text-primary me-3"></i>
                                    <span><?= $contactInfo['phone'] ?></span>
                                </li>
                                <li class="d-flex">
                                    <i class="bi bi-geo-alt-fill fs-5 text-primary me-3"></i>
                                    <span><?= $contactInfo['address'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">ຊ່ອງທາງຕິດຕາມ</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="text-decoration-none">
                                    <i class="bi bi-facebook fs-2 text-primary"></i>
                                </a>
                                <a href="#" class="text-decoration-none">
                                    <i class="bi bi-twitter fs-2 text-info"></i>
                                </a>
                                <a href="#" class="text-decoration-none">
                                    <i class="bi bi-github fs-2 text-dark"></i>
                                </a>
                                <a href="#" class="text-decoration-none">
                                    <i class="bi bi-youtube fs-2 text-danger"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">ສົ່ງຂໍ້ຄວາມຫາພວກເຮົາ</h5>
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

                            <form action="<?= getenv('APP_URL') ?>/home/sendMessage" method="post">
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
                                    <label for="subject" class="form-label">ຫົວຂໍ້</label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                        value="<?= isset($formData['subject']) ? $formData['subject'] : '' ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">ຂໍ້ຄວາມ <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?= isset($formData['message']) ? $formData['message'] : '' ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">ສົ່ງຂໍ້ຄວາມ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>