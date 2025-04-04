<?php

/**
 * Router Class
 * ຄລາສສຳລັບຈັດການເສັ້ນທາງ URL
 */
class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    // ເພີ່ມຕົວແປສຳລັບເກັບ middleware
    protected $middlewares = [];
    protected $routeMiddlewares = [];
    protected $lastRoute = null;

    // ເພີ່ມຕົວແປສຳລັບເກັບຊື່ເສັ້ນທາງ
    protected $namedRoutes = [];

    // ເພີ່ມຕົວແປນັບຈຳນວນເສັ້ນທາງ
    protected $routeCount = 0;

    protected $prefix = '';
    protected $cacheFile = 'bootfiles/cache/routes.cache.php';
    protected $groupStack = [];

    /**
     * ເພີ່ມເສັ້ນທາງ GET
     * 
     * @param string $path ເສັ້ນທາງ URL
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @param string|null $action ຊື່ action (ບໍ່ຈຳເປັນຖ້າ $handler ເປັນ array)
     * @return $this
     */
    public function get($path, $handler, $action = null)
    {
        $this->addRoute('GET', $path, $handler, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ POST
     * 
     * @param string $path ເສັ້ນທາງ URL
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @param string|null $action ຊື່ action (ບໍ່ຈຳເປັນຖ້າ $handler ເປັນ array)
     * @return $this
     */
    public function post($path, $handler, $action = null)
    {
        $this->addRoute('POST', $path, $handler, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ PUT
     * 
     * @param string $path ເສັ້ນທາງ URL
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @param string|null $action ຊື່ action (ບໍ່ຈຳເປັນຖ້າ $handler ເປັນ array)
     * @return $this
     */
    public function put($path, $handler, $action = null)
    {
        $this->addRoute('PUT', $path, $handler, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ DELETE
     * 
     * @param string $path ເສັ້ນທາງ URL
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @param string|null $action ຊື່ action (ບໍ່ຈຳເປັນຖ້າ $handler ເປັນ array)
     * @return $this
     */
    public function delete($path, $handler, $action = null)
    {
        $this->addRoute('DELETE', $path, $handler, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງແບບ Resource (CRUD)
     * 
     * @param string $name ຊື່ resource
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @return $this
     */
    public function resource($name, $handler)
    {
        $name = trim($name, '/');
        $controllerName = is_array($handler) ? $handler[0] : $handler;

        // ສ້າງເສັ້ນທາງ CRUD ມາດຕະຖານ
        // GET /resource - index (ສະແດງລາຍການ)
        $this->get($name, $handler, is_array($handler) ? 'index' : null)
            ->name("{$name}.index");

        // GET /resource/create - create (ສະແດງຟອມສ້າງໃໝ່)
        $this->get("{$name}/create", $handler, is_array($handler) ? 'create' : null)
            ->name("{$name}.create");

        // POST /resource - store (ບັນທຶກຂໍ້ມູນໃໝ່)
        $this->post($name, $handler, is_array($handler) ? 'store' : null)
            ->name("{$name}.store");

        // GET /resource/:id - show (ສະແດງລາຍລະອຽດ)
        $this->get("{$name}/{id}", $handler, is_array($handler) ? 'show' : null)
            ->name("{$name}.show");

        // GET /resource/:id/edit - edit (ສະແດງຟອມແກ້ໄຂ)
        $this->get("{$name}/{id}/edit", $handler, is_array($handler) ? 'edit' : null)
            ->name("{$name}.edit");

        // PUT /resource/:id - update (ອັບເດດຂໍ້ມູນ)
        $this->put("{$name}/{id}", $handler, is_array($handler) ? 'update' : null)
            ->name("{$name}.update");

        // DELETE /resource/:id - destroy (ລຶບຂໍ້ມູນ)
        $this->delete("{$name}/{id}", $handler, is_array($handler) ? 'destroy' : null)
            ->name("{$name}.destroy");

        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງໃສ່ໃນ array
     * 
     * @param string $method ວິທີການ HTTP (GET, POST, ...)
     * @param string $path ເສັ້ນທາງ URL
     * @param mixed $handler ຕົວຈັດການ (string ຫຼື array)
     * @param string|null $action ຊື່ action (ບໍ່ຈຳເປັນຖ້າ $handler ເປັນ array)
     */
    protected function addRoute($method, $path, $handler, $action = null)
    {
        // ປ່ຽນເສັ້ນທາງໃຫ້ເປັນຮູບແບບປົກກະຕິ (normalize)
        $path = $this->prefix
            ? trim($this->prefix . '/' . trim($path, '/'), '/')
            : trim($path, '/');

        if (empty($path)) {
            $path = '/';
        }

        // ຈັດການກັບ controller ແລະ action
        if (is_array($handler)) {
            $controller = $handler[0];
            $action = isset($handler[1]) ? $handler[1] : $action;
        } else {
            $controller = $handler;
        }

        // ກວດສອບວ່າເປັນຄລາສຫຼືບໍ່ (ClassName::class)
        if (strpos($controller, '::class') !== false) {
            $controller = str_replace('::class', '', $controller);
        }

        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action' => $action
        ];

        $this->lastRoute = [
            'method' => $method,
            'path' => $path
        ];

        $this->routeCount++;
    }

    /**
     * ຕັ້ງຊື່ໃຫ້ກັບເສັ້ນທາງຫຼ້າສຸດ
     * 
     * @param string $name ຊື່ທີ່ຕ້ອງການຕັ້ງ
     * @return $this
     */
    public function name($name)
    {
        if ($this->lastRoute) {
            $method = $this->lastRoute['method'];
            $path = $this->lastRoute['path'];
            $this->namedRoutes[$name] = [
                'method' => $method,
                'path' => $path
            ];
        }

        return $this;
    }

    /**
     * ສ້າງ URL ຈາກຊື່ເສັ້ນທາງ
     * 
     * @param string $name ຊື່ເສັ້ນທາງ
     * @param array $params ພາລາມິເຕີທີ່ຈະແທນທີ່ໃນ URL
     * @return string URL ທີ່ສ້າງຂຶ້ນ
     * @throws Exception ເມື່ອບໍ່ພົບເສັ້ນທາງທີ່ກຳນົດຊື່ໄວ້
     */
    public function route($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("ບໍ່ພົບເສັ້ນທາງທີ່ມີຊື່: $name");
        }

        $path = $this->namedRoutes[$name]['path'];

        // ແທນທີ່ພາລາມິເຕີໃນເສັ້ນທາງ
        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
            $path = str_replace(":{$key}", $value, $path);
        }

        $baseUrl = getenv('APP_URL') ?: 'http://localhost/framework';
        return rtrim($baseUrl, '/') . '/' . $path;
    }

    /**
     * ຮັບລາຍການ middleware ທົ່ວໄປ
     */
    public function getGlobalMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * ລົງທະບຽນ middleware ທົ່ວໄປ
     */
    public function middleware($middleware)
    {
        // ກວດສອບວ່າເປັນຄລາສຫຼືບໍ່ (ClassName::class)
        if (strpos($middleware, '::class') !== false) {
            $middleware = str_replace('::class', '', $middleware);
        }

        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * ກຳນົດ middleware ສຳລັບເສັ້ນທາງສະເພາະ
     */
    public function with($middleware)
    {
        if ($this->lastRoute) {
            $method = $this->lastRoute['method'];
            $path = $this->lastRoute['path'];

            // ກວດສອບວ່າເປັນຄລາສຫຼືບໍ່ (ClassName::class)
            if (strpos($middleware, '::class') !== false) {
                $middleware = str_replace('::class', '', $middleware);
            }

            if (!isset($this->routeMiddlewares[$method][$path])) {
                $this->routeMiddlewares[$method][$path] = [];
            }

            $this->routeMiddlewares[$method][$path][] = $middleware;
        }

        return $this;
    }

    /**
     * ຮັບ middlewares ທັງໝົດສຳລັບເສັ້ນທາງທີ່ກຳນົດ
     */
    public function getMiddlewares($method, $path)
    {
        $globalMiddlewares = $this->middlewares;
        $routeMiddlewares = isset($this->routeMiddlewares[$method][$path])
            ? $this->routeMiddlewares[$method][$path]
            : [];

        return array_merge($globalMiddlewares, $routeMiddlewares);
    }

    /**
     * ຊອກຫາເສັ້ນທາງທີ່ກົງກັບ URL
     */
    public function match($url, $method = 'GET')
    {
        // ກວດສອບວ່າມີເສັ້ນທາງລົງທະບຽນຫຼືບໍ່
        if ($this->routeCount === 0) {
            throw new Exception("ບໍ່ມີເສັ້ນທາງລົງທະບຽນໃນລະບົບ. ກະລຸນາກຳນົດເສັ້ນທາງໃນໄຟລ໌ routes/web.php");
        }

        // ປ່ຽນ URL ໃຫ້ເປັນຮູບແບບປົກກະຕິ (normalize)
        $url = trim($url, '/');

        // ຖ້າ URL ວ່າງເປົ່າ ໃຫ້ໃຊ້ຄ່າເລີ່ມຕົ້ນ
        if (empty($url)) {
            $url = '/';
        }

        // ຊອກຫາເສັ້ນທາງທີ່ກົງກັນແບບຊັດເຈນ
        if (isset($this->routes[$method][$url])) {
            $route = $this->routes[$method][$url];
            return [
                'controller' => $route['controller'],
                'action' => $route['action'],
                'params' => [],
                'path' => $url
            ];
        }

        // ຊອກຫາເສັ້ນທາງແບບມີຕົວແປ
        foreach ($this->routes[$method] as $route => $handlers) {
            // ແປງເສັ້ນທາງເປັນຕົວປະກອບປົກກະຕິ (regex pattern)
            $pattern = $this->convertRouteToRegex($route);

            if (preg_match($pattern, $url, $matches)) {
                // ລຶບຄ່າແມັດທັງໝົດ
                array_shift($matches);

                // ຕັ້ງຄ່າ params ໃຫ້ເປັນ associative array
                $params = [];

                // ຊອກຫາຊື່ພາລາມິເຕີ
                if (preg_match_all('/\{([^}]+)\}/', $route, $paramNames)) {
                    $paramNames = $paramNames[1];

                    // ຈັດການກັບພາລາມິເຕີທີ່ມີຊື່
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) {
                            $params[$key] = $value;
                        } elseif (isset($paramNames[$key - 1])) {
                            $params[$paramNames[$key - 1]] = $value;
                        }
                    }
                }

                return [
                    'controller' => $handlers['controller'],
                    'action' => $handlers['action'],
                    'params' => $params,
                    'path' => $route
                ];
            }
        }

        throw new Exception("ບໍ່ພົບເສັ້ນທາງທີ່ກົງກັບ URL: $url");
    }

    /**
     * ແປງເສັ້ນທາງເປັນຕົວປະກອບປົກກະຕິ (regex pattern)
     */
    protected function convertRouteToRegex($route)
    {
        // ແທນທີ່ຕົວແປໃນເສັ້ນທາງດ້ວຍຕົວປະກອບປົກກະຕິ
        $regex = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $route);

        // ສ້າງຕົວປະກອບປົກກະຕິທີ່ສົມບູນ
        return '/^' . str_replace('/', '\/', $regex) . '$/';
    }

    /**
     * ສ້າງ cache ສຳລັບເສັ້ນທາງ
     */
    public function cacheRoutes()
    {
        if (!is_dir(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0777, true);
        }

        $routes = [
            'routes' => $this->routes,
            'namedRoutes' => $this->namedRoutes,
            'routeMiddlewares' => $this->routeMiddlewares
        ];

        file_put_contents($this->cacheFile, '<?php return ' . var_export($routes, true) . ';');
    }

    /**
     * ໂຫຼດເສັ້ນທາງຈາກ cache
     */
    public function loadFromCache()
    {
        if (file_exists($this->cacheFile)) {
            $cached = require $this->cacheFile;
            $this->routes = $cached['routes'];
            $this->namedRoutes = $cached['namedRoutes'];
            $this->routeMiddlewares = $cached['routeMiddlewares'];
            return true;
        }
        return false;
    }

    /**
     * ຈັດກຸ່ມເສັ້ນທາງ
     */
    public function group(array $attributes, callable $callback)
    {
        $this->groupStack[] = $attributes;

        // ເກັບຄ່າເກົ່າ
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middlewares;

        // ນຳໃຊ້ຄ່າໃໝ່
        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix
                ? $previousPrefix . '/' . trim($attributes['prefix'], '/')
                : trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $this->middlewares = array_merge(
                $this->middlewares,
                (array) $attributes['middleware']
            );
        }

        // ເອີ້ນໃຊ້ callback
        $callback($this);

        // ກັບຄືນຄ່າເກົ່າ
        $this->prefix = $previousPrefix;
        $this->middlewares = $previousMiddleware;

        array_pop($this->groupStack);

        return $this;
    }

    /**
     * ກຳນົດ prefix ສຳລັບເສັ້ນທາງ
     */
    public function prefix($prefix)
    {
        $this->prefix = '/' . trim($prefix, '/');
        return $this;
    }

    /**
     * ຮັບລາຍການເສັ້ນທາງທັງໝົດ
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * ຮັບລາຍການເສັ້ນທາງທີ່ຕັ້ງຊື່ແລ້ວ
     */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }

    /**
     * ຮັບຈຳນວນເສັ້ນທາງທັງໝົດທີ່ລົງທະບຽນ
     */
    public function getRouteCount()
    {
        return $this->routeCount;
    }
}
