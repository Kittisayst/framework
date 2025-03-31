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

// ໂຫຼດການຕັ້ງຄ່າເສັ້ນທາງ
if (file_exists(ROOT_PATH . '/routes/web.php')) {
    require_once ROOT_PATH . '/routes/web.php';
} else {
    die('ກະລຸນາສ້າງໄຟລ໌ routes/web.php');
}

// ເລີ່ມຕົ້ນແອັບພລິເຄຊັນ
$app = new App();
$app->run();