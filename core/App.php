<?php

/**
 * App Class
 * ຄລາສຫຼັກສຳລັບຈັດການແອັບພລິເຄຊັນ
 */
class App
{
    protected $router;
    protected $request;
    protected $response;
    protected $controller;
    protected $action;
    protected $params = [];
    protected static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$instance = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
    }

    /**
     * ຮັບອິນສະຕັນຂອງ App
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new App();
        }
        return self::$instance;
    }

    /**
     * ຮັບຄ່າຂອງຕົວແປແວດລ້ອມ
     */
    public function getEnv($key, $default = null)
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * ເລີ່ມຕົ້ນແອັບພລິເຄຊັນ
     */
    public function run()
    {
        try {
            // ປະມວນຜົນຄຳຂໍ URL
            $url = $this->request->getUrl();
            $method = $this->request->getMethod();

            // ຮັບເສັ້ນທາງທີ່ກົງກັບ URL
            $route = $this->router->match($url, $method);

            if ($route) {
                $this->controller = $route['controller'];
                $this->action = $route['action'];
                $this->params = $route['params'];

                // ຮຽກໃຊ້ middlewares
                $middlewares = $this->router->getMiddlewares($method, $route['path']);

                foreach ($middlewares as $middlewareName) {
                    $middlewareFile = MIDDLEWARES_PATH . '/' . $middlewareName . '.php';

                    if (file_exists($middlewareFile)) {
                        require_once $middlewareFile;

                        $middlewareClass = $middlewareName;
                        $middleware = new $middlewareClass();

                        $result = $middleware->handle($this->request);

                        // ຖ້າ middleware ສົ່ງຄືນ Response, ໃຫ້ສົ່ງ response ນັ້ນເລີຍ
                        if ($result instanceof Response) {
                            $result->send();
                            return;
                        }

                        // ຖ້າ middleware ສົ່ງຄືນ false, ຢຸດການປະມວນຜົນ
                        if ($result === false) {
                            return;
                        }
                    }
                }

                // ກວດສອບວ່າຄລາສ controller ມີຢູ່ຫຼືບໍ່
                $controllerFile = CONTROLLERS_PATH . '/' . $this->controller . 'Controller.php';
                $controllerClass = $this->controller . 'Controller';

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;

                    if (class_exists($controllerClass)) {
                        $controllerObj = new $controllerClass();

                        // ກວດສອບວ່າມີເມທອດທີ່ຕ້ອງການຫຼືບໍ່
                        if (method_exists($controllerObj, $this->action)) {
                            // ເອີ້ນໃຊ້ເມທອດກັບພາລາມິເຕີທີ່ຕ້ອງການ
                            $response = call_user_func_array([$controllerObj, $this->action], $this->params);

                            // ຖ້າ controller ສົ່ງຄືນ Response, ໃຫ້ນຳໃຊ້ response ນັ້ນ
                            if ($response instanceof Response) {
                                // ຈັດການ middlewares ຫຼັງຈາກ controller
                                foreach ($middlewares as $middlewareName) {
                                    $middlewareFile = MIDDLEWARES_PATH . '/' . $middlewareName . '.php';

                                    if (file_exists($middlewareFile)) {
                                        require_once $middlewareFile;

                                        $middlewareClass = $middlewareName;
                                        $middleware = new $middlewareClass();

                                        if (method_exists($middleware, 'afterController')) {
                                            $response = $middleware->afterController($this->request, $response);
                                        }
                                    }
                                }

                                $response->send();
                            }
                        } else {
                            $this->response->setStatusCode(404);
                            $this->renderError('ບໍ່ພົບເມທອດ: ' . $this->action);
                        }
                    } else {
                        $this->response->setStatusCode(404);
                        $this->renderError('ບໍ່ພົບຄລາສ controller: ' . $controllerClass);
                    }
                } else {
                    $this->response->setStatusCode(404);
                    $this->renderError('ບໍ່ພົບໄຟລ໌ controller: ' . $controllerFile);
                }
            } else {
                $this->response->setStatusCode(404);
                $this->renderError('ບໍ່ພົບເສັ້ນທາງສຳລັບ: ' . $url);
            }
        } catch (Exception $e) {
            // ຈັດການຂໍ້ຜິດພາດທົ່ວໄປ
            $this->response->setStatusCode(500);
            $this->renderError($e->getMessage(), $e);
        }
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດ
     */
    protected function renderError($message, $exception = null)
    {
        if (file_exists(VIEWS_PATH . '/error.php')) {
            $error = $message;
            
            // ເພີ່ມລາຍລະອຽດຂໍ້ຜິດພາດຖ້າຢູ່ໃນໂໝດ debug ແລະ ມີ exception
            $details = null;
            if (getenv('APP_DEBUG') === 'true' && $exception instanceof Exception) {
                $details = "File: " . $exception->getFile() . 
                          ", Line: " . $exception->getLine() . 
                          "\nTrace:\n" . $exception->getTraceAsString();
            }
            
            include VIEWS_PATH . '/error.php';
        } else {
            echo '<h1>ຂໍ້ຜິດພາດ</h1>';
            echo '<p>' . $message . '</p>';
            
            if (getenv('APP_DEBUG') === 'true' && $exception instanceof Exception) {
                echo '<pre>File: ' . $exception->getFile() . 
                     ', Line: ' . $exception->getLine() . 
                     "\nTrace:\n" . $exception->getTraceAsString() . '</pre>';
            }
        }
    }

    /**
     * ຮັບອອບເຈັກ request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * ຮັບອອບເຈັກ response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * ຮັບອອບເຈັກ router
     */
    public function getRouter()
    {
        return $this->router;
    }
}