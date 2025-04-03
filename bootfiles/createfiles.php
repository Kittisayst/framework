<?php

/**
 * ສ້າງໄຟລ໌ແລະໂຄງສ້າງທີ່ຈຳເປັນສຳລັບລະບົບ
 */

// ສ້າງໂຄງສ້າງໂຟລເດີທີ່ຈຳເປັນ
$folders = [
    ROOT_PATH . '/public',
    ROOT_PATH . '/public/css',
    ROOT_PATH . '/public/js',
    ROOT_PATH . '/public/images',
    ROOT_PATH . '/logs',
    ROOT_PATH . '/routes',
    VIEWS_PATH . '/layouts',
    VIEWS_PATH . '/home'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}

// ສ້າງໄຟລ໌ CSS ແລະ JS ພື້ນຖານ
if (!file_exists(ROOT_PATH . '/public/css/style.css')) {
    $cssContent = "/* Custom styles */\nbody {\n    font-family: 'Noto Sans Lao', 'Phetsarath OT', sans-serif;\n}\n";
    file_put_contents(ROOT_PATH . '/public/css/style.css', $cssContent);
}

if (!file_exists(ROOT_PATH . '/public/js/script.js')) {
    $jsContent = "// Custom JavaScript code\n";
    file_put_contents(ROOT_PATH . '/public/js/script.js', $jsContent);
}

// ສ້າງ HomeController
if (!file_exists(CONTROLLERS_PATH . '/HomeController.php')) {
    $controllerContent = "<?php\n\n/**\n * Home Controller\n * ຄລາສຄວບຄຸມສຳລັບໜ້າຫຼັກ\n */\n";
    $controllerContent .= "class HomeController extends Controller\n{\n";
    $controllerContent .= "    /**\n     * Constructor\n     */\n";
    $controllerContent .= "    public function __construct()\n    {\n        parent::__construct();\n    }\n\n";
    $controllerContent .= "    /**\n     * ສະແດງໜ້າຫຼັກ\n     */\n";
    $controllerContent .= "    public function index()\n    {\n";
    $controllerContent .= "        \$data = [\n";
    $controllerContent .= "            'pageTitle' => 'ໜ້າຫຼັກ - PHP Framework',\n";
    $controllerContent .= "            'welcomeMessage' => 'ຍິນດີຕ້ອນຮັບເຂົ້າສູ່ PHP Framework'\n";
    $controllerContent .= "        ];\n\n";
    $controllerContent .= "        \$this->view('home/index', \$data);\n";
    $controllerContent .= "    }\n";
    $controllerContent .= "}\n";

    file_put_contents(CONTROLLERS_PATH . '/HomeController.php', $controllerContent);
}

// ສ້າງໜ້າວິວ
if (!file_exists(VIEWS_PATH . '/home/index.php')) {
    $viewContent = "<div class=\"container py-5\">\n";
    $viewContent .= "    <div class=\"row mb-5\">\n";
    $viewContent .= "        <div class=\"col-md-8 mx-auto text-center\">\n";
    $viewContent .= "            <h1 class=\"display-4 fw-bold mb-3\"><?= \$welcomeMessage ?></h1>\n";
    $viewContent .= "            <p class=\"lead mb-4\">PHP Framework ງ່າຍໆ ສຳລັບຜູ້ເລີ່ມຕົ້ນ</p>\n";
    $viewContent .= "        </div>\n";
    $viewContent .= "    </div>\n";
    $viewContent .= "</div>\n";

    file_put_contents(VIEWS_PATH . '/home/index.php', $viewContent);
}

// ສ້າງໄຟລ໌ layout
if (!file_exists(VIEWS_PATH . '/layouts/main.php')) {
    $layoutContent = "<!DOCTYPE html>\n<html lang=\"lo\">\n\n<head>\n";
    $layoutContent .= "    <meta charset=\"UTF-8\">\n";
    $layoutContent .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $layoutContent .= "    <title><?= isset(\$pageTitle) ? \$pageTitle : 'My PHP Framework' ?></title>\n\n";
    $layoutContent .= "    <!-- Bootstrap CSS -->\n";
    $layoutContent .= "    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n\n";
    $layoutContent .= "    <!-- Custom CSS -->\n";
    $layoutContent .= "    <link href=\"<?= getenv('APP_URL') ?>/public/css/style.css\" rel=\"stylesheet\">\n";
    $layoutContent .= "</head>\n\n<body>\n";
    $layoutContent .= "    <!-- Navbar -->\n";
    $layoutContent .= "    <nav class=\"navbar navbar-expand-lg navbar-dark bg-primary mb-4\">\n";
    $layoutContent .= "        <div class=\"container\">\n";
    $layoutContent .= "            <a class=\"navbar-brand\" href=\"<?= getenv('APP_URL') ?>\">PHP Framework</a>\n";
    $layoutContent .= "            <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">\n";
    $layoutContent .= "                <span class=\"navbar-toggler-icon\"></span>\n";
    $layoutContent .= "            </button>\n";
    $layoutContent .= "            <div class=\"collapse navbar-collapse\" id=\"navbarNav\">\n";
    $layoutContent .= "                <ul class=\"navbar-nav\">\n";
    $layoutContent .= "                    <li class=\"nav-item\">\n";
    $layoutContent .= "                        <a class=\"nav-link\" href=\"<?= getenv('APP_URL') ?>\">ໜ້າຫຼັກ</a>\n";
    $layoutContent .= "                    </li>\n";
    $layoutContent .= "                    <li class=\"nav-item\">\n";
    $layoutContent .= "                        <a class=\"nav-link\" href=\"<?= getenv('APP_URL') ?>/user\">ຜູ້ໃຊ້</a>\n";
    $layoutContent .= "                    </li>\n";
    $layoutContent .= "                </ul>\n";
    $layoutContent .= "            </div>\n";
    $layoutContent .= "        </div>\n";
    $layoutContent .= "    </nav>\n\n";
    $layoutContent .= "    <!-- Main Content -->\n";
    $layoutContent .= "    <div class=\"container\">\n";
    $layoutContent .= "        <?php if (isset(\$flashMessage)): ?>\n";
    $layoutContent .= "            <div class=\"alert alert-<?= \$flashMessage['type'] ?> alert-dismissible fade show\" role=\"alert\">\n";
    $layoutContent .= "                <?= \$flashMessage['message'] ?>\n";
    $layoutContent .= "                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>\n";
    $layoutContent .= "            </div>\n";
    $layoutContent .= "        <?php endif; ?>\n\n";
    $layoutContent .= "        <?= \$content ?>\n";
    $layoutContent .= "    </div>\n\n";
    $layoutContent .= "    <!-- Footer -->\n";
    $layoutContent .= "    <footer class=\"mt-5 py-3 bg-light\">\n";
    $layoutContent .= "        <div class=\"container text-center\">\n";
    $layoutContent .= "            <p>&copy; <?= date('Y') ?> My PHP Framework</p>\n";
    $layoutContent .= "        </div>\n";
    $layoutContent .= "    </footer>\n\n";
    $layoutContent .= "    <!-- Bootstrap JS -->\n";
    $layoutContent .= "    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>\n\n";
    $layoutContent .= "    <!-- Custom JavaScript -->\n";
    $layoutContent .= "    <script src=\"<?= getenv('APP_URL') ?>/public/js/script.js\"></script>\n";
    $layoutContent .= "</body>\n\n</html>";

    file_put_contents(VIEWS_PATH . '/layouts/main.php', $layoutContent);
}

// ສ້າງໄຟລ໌ .htaccess
if (!file_exists(ROOT_PATH . '/.htaccess')) {
    $htaccessContent = "# ເປີດໃຊ້ງານ mod_rewrite\n";
    $htaccessContent .= "RewriteEngine On\n\n";
    $htaccessContent .= "# ຖ້າຄຳຂໍບໍ່ແມ່ນໄຟລ໌ຫຼືໂຟລເດີທີ່ມີຢູ່ແລ້ວ\n";
    $htaccessContent .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $htaccessContent .= "RewriteCond %{REQUEST_FILENAME} !-d\n\n";
    $htaccessContent .= "# ສົ່ງທຸກຄຳຂໍໄປຍັງ index.php\n";
    $htaccessContent .= "RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]\n\n";
    $htaccessContent .= "# ປ້ອງກັນການເຂົ້າເຖິງໄຟລ໌ .env\n";
    $htaccessContent .= "<Files .env>\n";
    $htaccessContent .= "    Order Allow,Deny\n";
    $htaccessContent .= "    Deny from all\n";
    $htaccessContent .= "</Files>\n\n";
    $htaccessContent .= "# ປ້ອງກັນການເຂົ້າເຖິງໂຟລເດີ\n";
    $htaccessContent .= "<IfModule mod_autoindex.c>\n";
    $htaccessContent .= "    Options -Indexes\n";
    $htaccessContent .= "</IfModule>\n\n";
    $htaccessContent .= "# ຕັ້ງຄ່າຄວາມປອດໄພເພີ່ມເຕີມ\n";
    $htaccessContent .= "<IfModule mod_headers.c>\n";
    $htaccessContent .= "    Header set X-Content-Type-Options \"nosniff\"\n";
    $htaccessContent .= "    Header set X-XSS-Protection \"1; mode=block\"\n";
    $htaccessContent .= "    Header set X-Frame-Options \"SAMEORIGIN\"\n";
    $htaccessContent .= "</IfModule>";

    file_put_contents(ROOT_PATH . '/.htaccess', $htaccessContent);
}

// ສ້າງໂຟລເດີເກັບ logs
if (!is_dir(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}
