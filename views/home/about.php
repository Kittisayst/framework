<div class="container py-5">
    <div class="row mb-5">
        <div class="col-md-10 mx-auto">
            <h1 class="display-5 fw-bold mb-4"><?= $pageTitle ?></h1>

            <div class="card mb-5">
                <div class="card-body">
                    <h5 class="card-title">ກ່ຽວກັບ Framework ນີ້</h5>
                    <p class="card-text"><?= $content ?></p>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-12">
                    <h2 class="fw-bold mb-4">ເປັນຫຍັງຕ້ອງໃຊ້ Framework ນີ້?</h2>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-rocket-takeoff me-2 text-primary"></i> ງ່າຍສຳລັບຜູ້ເລີ່ມຕົ້ນ</h5>
                            <p class="card-text">ອອກແບບມາສະເພາະເພື່ອຜູ້ທີ່ເລີ່ມຕົ້ນຮຽນ PHP ແລະ ຕ້ອງການເຂົ້າໃຈຫຼັກການຂອງ MVC framework.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-lightning-charge me-2 text-primary"></i> ໄວແລະເບົາ</h5>
                            <p class="card-text">ບໍ່ມີໂຄ້ດທີ່ຊັບຊ້ອນຫຼືບໍ່ຈຳເປັນ, ໃຫ້ປະສິດທິພາບດີໃນຂະນະທີ່ຍັງເຂົ້າໃຈງ່າຍ.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-book me-2 text-primary"></i> ຮຽນຮູ້ງ່າຍ</h5>
                            <p class="card-text">ໂຄດຖືກຈັດໃຫ້ເປັນລະບຽບແລະມີຄຳອະທິບາຍຄົບຖ້ວນ, ເຮັດໃຫ້ງ່າຍຕໍ່ການຮຽນຮູ້ແລະເຂົ້າໃຈ.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-diagram-3 me-2 text-primary"></i> ຮູບແບບ MVC</h5>
                            <p class="card-text">ປະຕິບັດຕາມຮູບແບບ Model-View-Controller ເພື່ອແຍກສ່ວນຂອງແອັບພລິເຄຊັນຢ່າງຈະແຈ້ງ.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h2 class="fw-bold mb-4">ເຕັກໂນໂລຢີທີ່ໃຊ້</h2>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded me-3">
                                <i class="bi bi-filetype-php fs-2 text-primary"></i>
                            </div>
                            <span class="fw-bold">PHP</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded me-3">
                                <i class="bi bi-database fs-2 text-primary"></i>
                            </div>
                            <span class="fw-bold">MySQL</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded me-3">
                                <i class="bi bi-bootstrap fs-2 text-primary"></i>
                            </div>
                            <span class="fw-bold">Bootstrap</span>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-3 rounded me-3">
                                <i class="bi bi-code-slash fs-2 text-primary"></i>
                            </div>
                            <span class="fw-bold">HTML/CSS</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="<?= getenv('APP_URL') ?>/home/contact" class="btn btn-primary btn-lg">ຕິດຕໍ່ພວກເຮົາ</a>
                <a href="<?= getenv('APP_URL') ?>" class="btn btn-secondary btn-lg ms-2">ກັບໄປໜ້າຫຼັກ</a>
            </div>
        </div>
    </div>
</div>