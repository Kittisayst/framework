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

// ເສັ້ນທາງທີ່ບໍ່ມີ middleware ເພີ່ມເຕີມ
$router->get('test/no-middleware', 'Home', 'index');

// ເສັ້ນທາງທີ່ໃຊ້ AuthMiddleware
$router->get('test/auth', 'Home', 'index')->with('AuthMiddleware');

// ເສັ້ນທາງທີ່ໃຊ້ GuestMiddleware
$router->get('test/guest', 'Home', 'index')->with('GuestMiddleware');

// ເສັ້ນທາງ POST ເພື່ອທົດສອບ CsrfMiddleware
$router->post('test/csrf', 'Home', 'index')->with('CsrfMiddleware');

// ກຳນົດເສັ້ນທາງ URL
// ໜ້າຫຼັກ
$router->get('', 'Home', 'index')->with('LogMiddleware');
$router->get('home', 'Home', 'index');

// ເສັ້ນທາງສຳລັບຜູ້ໃຊ້
$router->get('user', 'User', 'index')->with('AuthMiddleware');
$router->get('user/create', 'User', 'create');
$router->post('user/store', 'User', 'store');
$router->get('user/show/:id', 'User', 'show');
$router->get('user/edit/:id', 'User', 'edit');
$router->post('user/update/:id', 'User', 'update');
$router->get('user/delete/:id', 'User', 'delete');

// ເສັ້ນທາງແບບມີຕົວແປຊື່
$router->get('user/{id}', 'User', 'show');
$router->get('user/edit/{id}', 'User', 'edit');

// ຕົວຢ່າງການກຳນົດເສັ້ນທາງສຳລັບ API
$router->get('api/users', 'Api/User', 'index');
$router->get('api/users/:id', 'Api/User', 'show');
$router->post('api/users', 'Api/User', 'store');
$router->put('api/users/:id', 'Api/User', 'update');
$router->delete('api/users/:id', 'Api/User', 'delete');

// ຕົວຢ່າງການກຳນົດເສັ້ນທາງທີ່ມີຄວາມຊັບຊ້ອນ
$router->get('blog/category/:category/post/:id', 'Blog', 'show');

// ເພີ່ມເສັ້ນທາງສຳລັບການທົດສອບ debug
$router->get('debug/info', 'Debug', 'info');
$router->get('debug/request', 'Debug', 'request');
$router->get('debug/env', 'Debug', 'env');
$router->get('debug/routes', 'Debug', 'routes');
$router->get('debug/query/:table', 'Debug', 'query');
$router->get('debug/resources', 'Debug', 'resources');

// ຈົບການຈັບເວລາການລົງທະບຽນເສັ້ນທາງ
$routeRegTime = debug_timer_stop('routes_registration', false);

// ບັນທຶກຂໍ້ມູນການລົງທະບຽນເສັ້ນທາງສົມບູນ
if ($app->isDebug()) {
    $routes = $router->getRoutes();
    $routeCount = 0;
    
    foreach ($routes as $method => $methodRoutes) {
        $routeCount += count($methodRoutes);
    }
    
    debug_log([
        'time' => $routeRegTime . ' ms',
        'routes_count' => $routeCount,
        'routes_by_method' => [
            'GET' => count($routes['GET']),
            'POST' => count($routes['POST']),
            'PUT' => count($routes['PUT']),
            'DELETE' => count($routes['DELETE'])
        ]
    ], 'Routes Registration Complete');
}

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