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

$router->get('/', [UserController::class, 'index']);
$router->get('/data', [UserController::class, 'create'])->name('user.create');
