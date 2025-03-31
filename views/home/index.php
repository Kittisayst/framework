<div class="container py-5">
    <!-- ສ່ວນ Hero -->
    <div class="row mb-5">
        <div class="col-md-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3"><?= $welcomeMessage ?></h1>
            <p class="lead mb-4"><?= $description ?></p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="<?= getenv('APP_URL') ?>/home/about" class="btn btn-primary btn-lg px-4 gap-3">ກ່ຽວກັບ</a>
                <a href="https://github.com/yourusername/framework" class="btn btn-outline-secondary btn-lg px-4">GitHub</a>
            </div>
        </div>
    </div>

    <!-- ຄຸນສົມບັດ -->
    <div class="row g-4 py-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold">ຄຸນສົມບັດຂອງ Framework</h2>
        </div>

        <?php foreach ($features as $feature): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi <?= $feature['icon'] ?> fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title"><?= $feature['title'] ?></h5>
                        <p class="card-text"><?= $feature['description'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ສ່ວນເລີ່ມຕົ້ນໃຊ້ງານ -->
    <div class="row bg-light py-5 rounded-3 mt-4">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="fw-bold mb-3">ເລີ່ມໃຊ້ງານງ່າຍໆ</h2>
            <p class="mb-4">ເລີ່ມຕົ້ນໃຊ້ງານ framework ໄດ້ງ່າຍໆພຽງບໍ່ເທົ່າໃດຂັ້ນຕອນ</p>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    <span class="fw-bold">ການຕິດຕັ້ງ</span>
                </div>
                <div class="card-body text-start">
                    <pre class="mb-0"><code># ຄັດລອກໂຄດ framework ຈາກ GitHub
git clone https://github.com/Kittisayst/framework.git

# ຕັ້ງຄ່າໄຟລ໌ .env
cp .env.example .env

# ເຂົ້າໄປໃນໂຟລເດີ framework
cd framework

# ເລີ່ມຕົ້ນພັດທະນາ
php -S localhost:8000</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>