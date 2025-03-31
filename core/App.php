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
        // ປະມວນຜົນຄຳຂໍ URL
        $url = $this->request->getUrl();

        // ຮັບເສັ້ນທາງທີ່ກົງກັບ URL
        $route = $this->router->match($url, $this->request->getMethod());

        if ($route) {
            $this->controller = $route['controller'];
            $this->action = $route['action'];
            $this->params = $route['params'];

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
                        call_user_func_array([$controllerObj, $this->action], $this->params);
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
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດ
     */
    protected function renderError($message)
    {
        if (file_exists(VIEWS_PATH . '/error.php')) {
            $error = $message;
            include VIEWS_PATH . '/error.php';
        } else {
            echo '<h1>ຂໍ້ຜິດພາດ</h1>';
            echo '<p>' . $message . '</p>';
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
