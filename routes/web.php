<?php

/**
 * Web Routes
 * ການກຳນົດເສັ້ນທາງ URL ຂອງແອັບພລິເຄຊັນ
 */

// ຮັບອອບເຈັກຂອງ Router ຈາກ App
$router = App::getInstance()->getRouter();

// ກຳນົດເສັ້ນທາງ URL
// ໜ້າຫຼັກ
$router->get('', 'Home', 'index')->with('AuthMiddleware');;
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

/**
 * ຕົວຢ່າງການກຳນົດເສັ້ນທາງເພີ່ມເຕີມ:
 *
 * $router->get('products', 'Product', 'index');
 * $router->get('products/:id', 'Product', 'show');
 * $router->get('products/category/:category', 'Product', 'category');
 * 
 * $router->get('cart', 'Cart', 'index');
 * $router->post('cart/add/:id', 'Cart', 'add');
 * $router->get('cart/remove/:id', 'Cart', 'remove');
 * 
 * $router->get('checkout', 'Checkout', 'index');
 * $router->post('checkout/process', 'Checkout', 'process');
 * 
 * $router->get('login', 'Auth', 'login');
 * $router->post('login', 'Auth', 'authenticate');
 * $router->get('logout', 'Auth', 'logout');
 * $router->get('register', 'Auth', 'register');
 * $router->post('register', 'Auth', 'store');
 */

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
