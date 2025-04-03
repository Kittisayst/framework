<?php

/**
 * ສ້າງໄຟລ໌ເສັ້ນທາງ URL ພື້ນຖານ
 */

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

file_put_contents(ROOT_PATH . '/routes/web.php', $routeContent);
require_once ROOT_PATH . '/routes/web.php';
