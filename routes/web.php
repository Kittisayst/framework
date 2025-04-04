<?php

/**
 * Web Routes
 * ການກຳນົດເສັ້ນທາງ URL ຂອງແອັບພລິເຄຊັນ
 */


// ຮັບອອບເຈັກຂອງ Router ຈາກ App
$app = App::getInstance();
$router = $app->getRouter();

$router->middleware('LogMiddleware');

$router->get('/', [UserController::class, 'index']);
$router->get('/data', [UserController::class, 'create'])->name('user.create');
$router->resource('users', [UserController::class]);
$router->resource('/login', [AuthController::class, 'index']);

$router->group(['prefix' => 'debug'], function ($router) {
    $router->get('/', [DebugController::class, 'info'])->name('debug.info');
    $router->get('/request', [DebugController::class, 'request']);
    $router->get('/env', [DebugController::class, 'env']);
    $router->get('/routes', [DebugController::class, 'routes']);
    $router->get('/query', [DebugController::class, 'query']);
    $router->get('/resources', [DebugController::class, 'resources']);
});

// $router->group(['prefix' => 'auth'], function ($router) {
//     $router->get('/', [AuthController::class, 'index']);
//     $router->post('/login', [AuthController::class, 'login']);
// });

$router->group(['prefix' => 'generator'], function ($router) {
    $router->get('/', [GeneratorController::class, 'index']);
    $router->get('/configure/{table}', [GeneratorController::class, 'configure']);
    $router->post('/generate', [GeneratorController::class, 'generate']);
});

