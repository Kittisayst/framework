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
define('BOOTFILES_PATH', ROOT_PATH . '/bootfiles');

// ສ້າງໂຟລເດີ bootfiles ຖ້າຍັງບໍ່ມີ
if (!is_dir(BOOTFILES_PATH)) {
    mkdir(BOOTFILES_PATH, 0755, true);
}

// ໂຫຼດໄຟລ໌ຕັ້ງຄ່າສະພາບແວດລ້ອມ
require_once BOOTFILES_PATH . '/environment.php';

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

// ສ້າງໄຟລ໌ທີ່ຈຳເປັນຕ່າງໆຂອງລະບົບ
require_once BOOTFILES_PATH . '/createfiles.php';

// ສ້າງ App
$app = App::getInstance();

// ຮັບເສັ້ນທາງ URL
if (file_exists(ROOT_PATH . '/routes/web.php')) {
    require_once ROOT_PATH . '/routes/web.php';
} else {
    require_once BOOTFILES_PATH . '/createroutes.php';
}

// ເລີ່ມຕົ້ນແອັບພລິເຄຊັນ
$app->run();