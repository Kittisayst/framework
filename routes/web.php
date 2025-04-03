<?php

/**
 * Web Routes
 * ການກຳນົດເສັ້ນທາງ URL ຂອງແອັບພລິເຄຊັນ
 */

// ເລີ່ມຈັບເວລາການລົງທະບຽນເສັ້ນທາງ
debug_timer_start('routes_registration');

// ຮັບອອບເຈັກຂອງ Router ຈາກ App
$app = App::getInstance();
$router = $app->getRouter();

// ບັນທຶກຂໍ້ມູນການລົງທະບຽນເສັ້ນທາງເລີ່ມຕົ້ນ
if ($app->isDebug()) {
    debug_log('Starting routes registration', 'Routes');
}

// ລົງທະບຽນ middleware ທົ່ວໄປ
$router->middleware('LogMiddleware');
$router->middleware('ErrorHandlerMiddleware');
$router->middleware('CsrfMiddleware');

// ກຳນົດເສັ້ນທາງ URL
// ໜ້າຫຼັກ
$router->get('', 'Home', 'index')->with('LogMiddleware');
$router->get('home', 'Home', 'index');

// ເສັ້ນທາງສຳລັບຜູ້ໃຊ້
$router->post('auth/login', 'Auth', 'login');
// ເສັ້ນທາງສຳລັບຜູ້ໃຊ້
$router->get('user', 'User', 'index')->with('AuthMiddleware');
$router->get('user/create', 'User', 'create');
$router->post('user/store', 'User', 'store');
$router->get('user/show/:id', 'User', 'show');
$router->get('user/edit/:id', 'User', 'edit');
$router->post('user/update/:id', 'User', 'update');
$router->get('user/delete/:id', 'User', 'delete');


// ເພີ່ມເສັ້ນທາງສຳລັບການທົດສອບ debug
$router->get('debug/info', 'Debug', 'info');
$router->get('debug/request', 'Debug', 'request');
$router->get('debug/env', 'Debug', 'env');
$router->get('debug/routes', 'Debug', 'routes');
$router->get('debug/query/:table', 'Debug', 'query');
$router->get('debug/resources', 'Debug', 'resources');


/**
 * ໝາຍເຫດ:
 * 
 * 1. ຮູບແບບຂອງການກຳນົດເສັ້ນທາງແມ່ນ:
 *    $router->METHOD('PATH', 'CONTROLLER', 'ACTION');
 * 
 * 2. ຕົວແປໃນເສັ້ນທາງສາມາດກຳນົດໂດຍໃຊ້ຮູບແບບຕໍ່ໄປນີ້:
 *    - ໃຊ້ເຄື່ອງໝາຍ ":" ກ່ອນຊື່ຕົວແປ - ເຊັ່ນ: :id, :category
 *    - ໃຊ້ວົງປີກກາ {} ລ້ອມຮອບຊື່ຕົວແປ - ເຊັ່ນ: {id}, {category}
 * 
 * 3. ວິທີການ HTTP ທີ່ສາມາດກຳນົດໄດ້:
 *    - get(): ສຳລັບຄຳຂໍ GET
 *    - post(): ສຳລັບຄຳຂໍ POST
 *    - put(): ສຳລັບຄຳຂໍ PUT
 *    - delete(): ສຳລັບຄຳຂໍ DELETE
 */