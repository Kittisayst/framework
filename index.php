<?php
/**
 * My PHP Framework
 * ຈຸດເລີ່ມຕົ້ນຂອງແອັບພລິເຄຊັນ
 */

// ກຳນົດຄ່າຄົງທີ່ສຳລັບເສັ້ນທາງຂອງລະບົບ
define('ROOT_PATH', dirname(__FILE__));
define('CORE_PATH', ROOT_PATH . '/core');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('HELPERS_PATH', ROOT_PATH . '/helpers');
define('MIDDLEWARES_PATH', ROOT_PATH . '/middlewares');

// ໂຫຼດໄຟລ໌ຕັ້ງຄ່າສະພາບແວດລ້ອມ
$env = [];
if (file_exists(ROOT_PATH . '/.env')) {
    $envLines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        // ຂ້າມບັນທັດທີ່ເປັນຄຳອະທິບາຍ
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
        
        // ຕັ້ງຄ່າເປັນຕົວແປແວດລ້ອມ
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// ຕັ້ງຄ່າ error reporting ຕາມໂໝດ debug
if (isset($env['APP_DEBUG']) && $env['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ຕັ້ງຄ່າ timezone
if (isset($env['TIMEZONE'])) {
    date_default_timezone_set($env['TIMEZONE']);
} else {
    date_default_timezone_set('UTC');
}

// ເລີ່ມ session
session_start();

// ໂຫຼດຟັງຊັນຊ່ວຍເຫຼືອ
if (file_exists(HELPERS_PATH . '/functions.php')) {
    require_once HELPERS_PATH . '/functions.php';
}

// ໂຫຼດຄລາສຫຼັກຂອງ framework
require_once CORE_PATH . '/App.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/Request.php';
require_once CORE_PATH . '/Response.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/View.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Middleware.php';

// ເລີ່ມຕົ້ນແອັບພລິເຄຊັນ - ຕ້ອງສ້າງກ່ອນໂຫຼດເສັ້ນທາງ
$app = App::getInstance();

// ກວດສອບຄວາມຖືກຕ້ອງຂອງເສັ້ນທາງ
if (file_exists(ROOT_PATH . '/routes/web.php')) {
    require_once ROOT_PATH . '/routes/web.php';
} else {
    // ສ້າງໄຟລ໌ເສັ້ນທາງຖ້າຍັງບໍ່ມີ
    $routesDir = ROOT_PATH . '/routes';
    if (!is_dir($routesDir)) {
        mkdir($routesDir, 0755, true);
    }
    
    $routeContent = "<?php\n\n/**\n * Web Routes\n * ການກຳນົດເສັ້ນທາງ URL ຂອງແອັບພລິເຄຊັນ\n */\n\n";
    $routeContent .= "\$router = \$app->getRouter();\n\n";
    $routeContent .= "// ເພີ່ມເສັ້ນທາງ URL ຂອງທ່ານຢູ່ນີ້\n\n";
    $routeContent .= "// ໜ້າຫຼັກ\n";
    $routeContent .= "\$router->get('', 'Home', 'index');\n";
    $routeContent .= "\$router->get('home', 'Home', 'index');\n\n";
    $routeContent .= "// ເສັ້ນທາງສຳລັບຜູ້ໃຊ້\n";
    $routeContent .= "\$router->get('user', 'User', 'index');\n";
    $routeContent .= "\$router->get('user/:id', 'User', 'show');\n";
    $routeContent .= "\$router->get('user/edit/:id', 'User', 'edit');\n";
    $routeContent .= "\$router->get('user/create', 'User', 'create');\n";
    $routeContent .= "\$router->post('user/store', 'User', 'store');\n";
    $routeContent .= "\$router->post('user/update/:id', 'User', 'update');\n";
    $routeContent .= "\$router->get('user/delete/:id', 'User', 'delete');\n";
    
    file_put_contents($routesDir . '/web.php', $routeContent);
    require_once $routesDir . '/web.php';
}

// ກວດສອບຄວາມພ້ອມຂອງ HomeController
if (!file_exists(CONTROLLERS_PATH . '/HomeController.php')) {
    // ສ້າງ HomeController ຖ້າຍັງບໍ່ມີ
    $controllerDir = CONTROLLERS_PATH;
    if (!is_dir($controllerDir)) {
        mkdir($controllerDir, 0755, true);
    }
    
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
    
    file_put_contents($controllerDir . '/HomeController.php', $controllerContent);
}

// ກວດສອບຄວາມພ້ອມຂອງໜ້າຫຼັກໃນ views
if (!file_exists(VIEWS_PATH . '/home/index.php')) {
    $viewDir = VIEWS_PATH . '/home';
    if (!is_dir($viewDir)) {
        mkdir($viewDir, 0755, true);
    }
    
    $viewContent = "<div class=\"container py-5\">\n";
    $viewContent .= "    <div class=\"row mb-5\">\n";
    $viewContent .= "        <div class=\"col-md-8 mx-auto text-center\">\n";
    $viewContent .= "            <h1 class=\"display-4 fw-bold mb-3\"><?= \$welcomeMessage ?></h1>\n";
    $viewContent .= "            <p class=\"lead mb-4\">PHP Framework ງ່າຍໆ ສຳລັບຜູ້ເລີ່ມຕົ້ນ</p>\n";
    $viewContent .= "        </div>\n";
    $viewContent .= "    </div>\n";
    $viewContent .= "</div>\n";
    
    file_put_contents($viewDir . '/index.php', $viewContent);
}

// ກວດສອບຄວາມພ້ອມຂອງ layout ຫຼັກ
if (!file_exists(VIEWS_PATH . '/layouts/main.php')) {
    $layoutDir = VIEWS_PATH . '/layouts';
    if (!is_dir($layoutDir)) {
        mkdir($layoutDir, 0755, true);
    }
    
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
    
    file_put_contents($layoutDir . '/main.php', $layoutContent);
}

// ກວດສອບຄວາມພ້ອມຂອງໂຟລເດີສາທາລະນະ
if (!is_dir(ROOT_PATH . '/public')) {
    mkdir(ROOT_PATH . '/public', 0755, true);
    mkdir(ROOT_PATH . '/public/css', 0755, true);
    mkdir(ROOT_PATH . '/public/js', 0755, true);
    mkdir(ROOT_PATH . '/public/images', 0755, true);
    
    // ສ້າງໄຟລ໌ CSS ພື້ນຖານ
    $cssContent = "/* Custom styles */\nbody {\n    font-family: 'Noto Sans Lao', 'Phetsarath OT', sans-serif;\n}\n";
    file_put_contents(ROOT_PATH . '/public/css/style.css', $cssContent);
    
    // ສ້າງໄຟລ໌ JavaScript ພື້ນຖານ
    $jsContent = "// Custom JavaScript code\n";
    file_put_contents(ROOT_PATH . '/public/js/script.js', $jsContent);
}

// ກວດສອບແລະສ້າງໄຟລ໌ .env ຖ້າຍັງບໍ່ມີ
if (!file_exists(ROOT_PATH . '/.env')) {
    $envContent = "# ການຕັ້ງຄ່າແອັບພລິເຄຊັນ\n";
    $envContent .= "APP_NAME=MyFramework\n";
    $envContent .= "APP_URL=http://localhost/framework\n";
    $envContent .= "APP_DEBUG=true\n\n";
    $envContent .= "# ການຕັ້ງຄ່າຖານຂໍ້ມູນ\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST=localhost\n";
    $envContent .= "DB_PORT=3306\n";
    $envContent .= "DB_DATABASE=framework\n";
    $envContent .= "DB_USERNAME=root\n";
    $envContent .= "DB_PASSWORD=\n\n";
    $envContent .= "# ການຕັ້ງຄ່າ session\n";
    $envContent .= "SESSION_LIFETIME=120\n";
    $envContent .= "SESSION_SECURE=false\n\n";
    $envContent .= "# ການຕັ້ງຄ່າ timezone\n";
    $envContent .= "TIMEZONE=Asia/Bangkok\n";
    
    file_put_contents(ROOT_PATH . '/.env', $envContent);
}

// ກວດສອບຄວາມພ້ອມຂອງໄຟລ໌ .htaccess
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

// ສະແດງຂໍ້ມູນໂຄງສ້າງ ເພື່ອຈຸດປະສົງການດີບັກ
if (getenv('APP_DEBUG') === 'true') {
    echo '<h1>PHP Framework Debugging Information</h1>';
    echo '<h2>Application Structure:</h2>';
    echo '<pre>';
    var_dump($app);
    echo '</pre>';

    echo '<h2>Routes:</h2>';
    echo '<pre>';
    var_dump($app->getRouter()->getRoutes());
    echo '</pre>';
}

// ເລີ່ມຕົ້ນແອັບພລິເຄຊັນ
$app->run();