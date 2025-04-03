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
    protected $debug = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        self::$instance = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();

        // ກຳນົດໂໝດ debug ຈາກຄ່າໃນ .env
        $this->debug = (getenv('APP_DEBUG') === 'true');

        // ຖ້າເປີດໃຊ້ debug mode, ໃຫ້ເລີ່ມເກັບເວລາປະຕິບັດການທັງໝົດ
        if ($this->debug) {
            debug_timer_start('app_execution');
        }
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
            // ບັນທຶກຂໍ້ມູນການຮ້ອງຂໍເຂົ້າໃນບັນທຶກ debug ຖ້າອະນຸຍາດໃຫ້ debug
            if ($this->debug) {
                debug_log($this->request, 'Incoming Request');
            }

            // ປະມວນຜົນຄຳຂໍ URL
            $url = $this->request->getUrl();
            $method = $this->request->getMethod();

            // ກວດສອບວ່າໄດ້ມີການລົງທະບຽນເສັ້ນທາງໃດໆບໍ່
            if ($this->router->getRouteCount() === 0) {
                throw new Exception("ບໍ່ພົບການລົງທະບຽນເສັ້ນທາງໃດໆໃນ routes/web.php. ກະລຸນາກຳນົດເສັ້ນທາງໃຫ້ຖືກຕ້ອງ.");
            }

            // ຮັບເສັ້ນທາງທີ່ກົງກັບ URL
            $route = $this->router->match($url, $method);

            // ບັນທຶກຂໍ້ມູນເສັ້ນທາງທີ່ຄົ້ນພົບ
            if ($this->debug) {
                debug_log($route, 'Matched Route');
            }

            if ($route) {
                $this->controller = $route['controller'];
                $this->action = $route['action'];
                $this->params = $route['params'];

                // ຮຽກໃຊ້ middlewares
                $middlewares = $this->router->getMiddlewares($method, $route['path']);

                // ບັນທຶກຂໍ້ມູນ middlewares ທີ່ຈະໃຊ້
                if ($this->debug) {
                    debug_log($middlewares, 'Middlewares');
                }

                // ເລີ່ມຈັບເວລາປະຕິບັດງານຂອງ middlewares
                if ($this->debug) {
                    debug_timer_start('middlewares_execution');
                }

                foreach ($middlewares as $middlewareName) {
                    $middlewareFile = MIDDLEWARES_PATH . '/' . $middlewareName . '.php';

                    if (file_exists($middlewareFile)) {
                        require_once $middlewareFile;

                        $middlewareClass = $middlewareName;
                        $middleware = new $middlewareClass();

                        // ເລີ່ມຈັບເວລາສຳລັບແຕ່ລະ middleware
                        if ($this->debug) {
                            debug_timer_start('middleware_' . $middlewareName);
                        }

                        $result = $middleware->handle($this->request);

                        // ຈົບເວລາສຳລັບແຕ່ລະ middleware
                        if ($this->debug) {
                            $time = debug_timer_stop('middleware_' . $middlewareName, false);
                            debug_log("Middleware $middlewareName executed in $time ms", 'Middleware Timing');
                        }

                        // ຖ້າ middleware ສົ່ງຄືນ Response, ໃຫ້ສົ່ງ response ນັ້ນເລີຍ
                        if ($result instanceof Response) {
                            if ($this->debug) {
                                debug_log("Response returned from middleware: $middlewareName", 'Middleware Response');
                                $this->logExecutionTime();
                            }
                            $result->send();
                            return;
                        }

                        // ຖ້າ middleware ສົ່ງຄືນ false, ຢຸດການປະມວນຜົນ
                        if ($result === false) {
                            if ($this->debug) {
                                debug_log("Execution stopped by middleware: $middlewareName", 'Middleware Halt');
                                $this->logExecutionTime();
                            }
                            return;
                        }
                    }
                }

                // ຈົບເວລາການປະຕິບັດງານຂອງ middlewares
                if ($this->debug) {
                    $time = debug_timer_stop('middlewares_execution', false);
                    debug_log("All middlewares executed in $time ms", 'Middlewares Complete');
                }

                // ເພີ່ມ 'Controller' ຕໍ່ທ້າຍຊື່ controller ຖ້າຍັງບໍ່ມີ
                if (strpos($this->controller, 'Controller') === false) {
                    $this->controller = $this->controller . 'Controller';
                }

                // ກວດສອບວ່າຄລາສ controller ມີຢູ່ຫຼືບໍ່
                $controllerFile = CONTROLLERS_PATH . '/' . $this->controller . '.php';
                $controllerClass = $this->controller;

                if ($this->debug) {
                    debug_log("Loading controller: $controllerClass from $controllerFile", 'Controller Load');
                }

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;

                    if (class_exists($controllerClass)) {
                        // ເລີ່ມຈັບເວລາການສ້າງອອບເຈັກ controller
                        if ($this->debug) {
                            debug_timer_start('controller_instantiation');
                        }

                        $controllerObj = new $controllerClass();

                        if ($this->debug) {
                            $time = debug_timer_stop('controller_instantiation', false);
                            debug_log("Controller instantiated in $time ms", 'Controller Creation');
                        }

                        // ກວດສອບວ່າມີເມທອດທີ່ຕ້ອງການຫຼືບໍ່
                        if (method_exists($controllerObj, $this->action)) {
                            // ເລີ່ມຈັບເວລາການປະຕິບັດງານຂອງ controller action
                            if ($this->debug) {
                                debug_timer_start('controller_action');
                                debug_log("Executing action: {$this->action} with params: " . json_encode($this->params), 'Controller Action');
                            }

                            // ປັບຮູບແບບຂອງ params ໃຫ້ເປັນ array ຕາມລຳດັບ ສຳລັບ call_user_func_array
                            $paramValues = [];
                            if (is_array($this->params)) {
                                // ກໍລະນີເປັນ associative array
                                if (count($this->params) > 0 && array_keys($this->params) !== range(0, count($this->params) - 1)) {
                                    // ໃຊ້ reflection ເພື່ອຮັບຊື່ພາລາມິເຕີຂອງເມທອດ
                                    $reflection = new ReflectionMethod($controllerObj, $this->action);
                                    $parameters = $reflection->getParameters();

                                    foreach ($parameters as $parameter) {
                                        $paramName = $parameter->getName();

                                        if (isset($this->params[$paramName])) {
                                            $paramValues[] = $this->params[$paramName];
                                        } elseif ($parameter->isDefaultValueAvailable()) {
                                            $paramValues[] = $parameter->getDefaultValue();
                                        } else {
                                            $paramValues[] = null;
                                        }
                                    }
                                } else {
                                    // ກໍລະນີເປັນ indexed array
                                    $paramValues = array_values($this->params);
                                }
                            }

                            // ເອີ້ນໃຊ້ເມທອດກັບພາລາມິເຕີທີ່ຕ້ອງການ
                            $response = call_user_func_array([$controllerObj, $this->action], $paramValues);

                            if ($this->debug) {
                                $time = debug_timer_stop('controller_action', false);
                                debug_log("Action executed in $time ms", 'Controller Action Complete');
                            }

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
                                            if ($this->debug) {
                                                debug_timer_start('after_middleware_' . $middlewareName);
                                            }

                                            $response = $middleware->afterController($this->request, $response);

                                            if ($this->debug) {
                                                $time = debug_timer_stop('after_middleware_' . $middlewareName, false);
                                                debug_log("After-controller middleware $middlewareName executed in $time ms", 'After Middleware');
                                            }
                                        }
                                    }
                                }

                                if ($this->debug) {
                                    debug_log("Sending response", 'Response');
                                    $this->logExecutionTime();
                                }

                                $response->send();
                            } else if ($this->debug) {
                                debug_log("Warning: Action did not return a Response object", 'Controller Action Warning');
                                $this->logExecutionTime();
                            }
                        } else {
                            $this->response->setStatusCode(404);
                            $errorMsg = 'ບໍ່ພົບເມທອດ: ' . $this->action . ' ໃນ controller ' . $controllerClass;

                            if ($this->debug) {
                                debug_log($errorMsg, 'Error');
                            }

                            $this->renderError($errorMsg);
                        }
                    } else {
                        $this->response->setStatusCode(404);
                        $errorMsg = 'ບໍ່ພົບຄລາສ controller: ' . $controllerClass;

                        if ($this->debug) {
                            debug_log($errorMsg, 'Error');
                        }

                        $this->renderError($errorMsg);
                    }
                } else {
                    $this->response->setStatusCode(404);
                    $errorMsg = 'ບໍ່ພົບໄຟລ໌ controller: ' . $controllerFile;

                    if ($this->debug) {
                        debug_log($errorMsg, 'Error');
                    }

                    $this->renderError($errorMsg);
                }
            } else {
                $this->response->setStatusCode(404);
                $errorMsg = 'ບໍ່ພົບເສັ້ນທາງສຳລັບ: ' . $url;

                if ($this->debug) {
                    debug_log($errorMsg, 'Error');
                }

                $this->renderError($errorMsg);
            }
        } catch (Exception $e) {
            // ຈັດການຂໍ້ຜິດພາດທົ່ວໄປ
            $this->response->setStatusCode(500);

            if ($this->debug) {
                debug_log($e, 'Exception');
            }

            $this->renderError($e->getMessage(), $e);
        }

        // ບັນທຶກເວລາການປະຕິບັດງານທັງໝົດ (ຈະຖືກປະຕິບັດຖ້າບໍ່ມີການເອີ້ນໃຊ້ $response->send())
        if ($this->debug) {
            $this->logExecutionTime();
        }
    }

    /**
     * ບັນທຶກເວລາປະຕິບັດການຂອງ application ທັງໝົດ
     */
    protected function logExecutionTime()
    {
        $time = debug_timer_stop('app_execution', false);
        debug_log("Total application execution time: $time ms", 'Application Timing');

        // ບັນທຶກການໃຊ້ຊັບພະຍາກອນ
        debug_log([
            'memory_usage' => formatBytes(memory_get_usage()),
            'memory_peak' => formatBytes(memory_get_peak_usage()),
        ], 'Resource Usage');
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດ
     */
    protected function renderError($message, $exception = null)
    {
        if ($this->debug) {
            debug_log("Rendering error: $message", 'Error Rendering');
        }

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

        if ($this->debug) {
            $this->logExecutionTime();
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

    /**
     * ຕັ້ງຄ່າໂໝດ debug
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
        return $this;
    }

    /**
     * ກວດສອບວ່າກຳລັງຢູ່ໃນໂໝດ debug ຫຼືບໍ່
     */
    public function isDebug()
    {
        return $this->debug;
    }
}
