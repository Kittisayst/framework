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

    /**
     * ເພີ່ມເສັ້ນທາງ GET
     */
    public function get($path, $controller, $action)
    {
        $this->addRoute('GET', $path, $controller, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ POST
     */
    public function post($path, $controller, $action)
    {
        $this->addRoute('POST', $path, $controller, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ PUT
     */
    public function put($path, $controller, $action)
    {
        $this->addRoute('PUT', $path, $controller, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງ DELETE
     */
    public function delete($path, $controller, $action)
    {
        $this->addRoute('DELETE', $path, $controller, $action);
        return $this;
    }

    /**
     * ເພີ່ມເສັ້ນທາງໃສ່ໃນ array
     */
    protected function addRoute($method, $path, $controller, $action)
    {
        // ປ່ຽນເສັ້ນທາງໃຫ້ເປັນຮູບແບບປົກກະຕິ (normalize)
        $path = trim($path, '/');

        $this->routes[$method][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
        
        // ບັນທຶກເສັ້ນທາງຫຼ້າສຸດສຳລັບການກຳນົດ middleware
        $this->lastRoute = [
            'method' => $method,
            'path' => $path
        ];
    }
    
    /**
     * ລົງທະບຽນ middleware ທົ່ວໄປ
     */
    public function middleware($middleware)
    {
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
        // ປ່ຽນ URL ໃຫ້ເປັນຮູບແບບປົກກະຕິ (normalize)
        $url = trim($url, '/');

        // ຖ້າ URL ວ່າງເປົ່າ ໃຫ້ໃຊ້ຄ່າເລີ່ມຕົ້ນ
        if (empty($url)) {
            $url = 'home';
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

                // ລຶບຄ່າແມັດທີ່ເປັນຊື່
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        unset($matches[$key]);
                    }
                }

                return [
                    'controller' => $handlers['controller'],
                    'action' => $handlers['action'],
                    'params' => $matches,
                    'path' => $route
                ];
            }
        }

        // ຖ້າບໍ່ພົບເສັ້ນທາງທີ່ກົງກັນ
        $segments = explode('/', $url);

        // ຮູບແບບ: controller/action/params...
        if (count($segments) >= 2) {
            $controller = ucfirst($segments[0]);
            $action = $segments[1];
            $params = array_slice($segments, 2);

            return [
                'controller' => $controller,
                'action' => $action,
                'params' => $params,
                'path' => $url
            ];
        }
        // ຮູບແບບ: controller
        else if (count($segments) == 1) {
            $controller = ucfirst($segments[0]);

            return [
                'controller' => $controller,
                'action' => 'index',
                'params' => [],
                'path' => $url
            ];
        }

        return null;
    }

    /**
     * ແປງເສັ້ນທາງເປັນຕົວປະກອບປົກກະຕິ (regex pattern)
     */
    protected function convertRouteToRegex($route)
    {
        // ແທນທີ່ຕົວແປໃນເສັ້ນທາງດ້ວຍຕົວປະກອບປົກກະຕິ
        if (strpos($route, '{') !== false) {
            $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        }

        // ແທນທີ່ຕົວແປແບບຕົວເລກໃນເສັ້ນທາງດ້ວຍຕົວປະກອບປົກກະຕິ
        if (strpos($route, ':') !== false) {
            $route = preg_replace('/:([a-zA-Z0-9_]+)/', '([^/]+)', $route);
        }

        // ສ້າງຕົວປະກອບປົກກະຕິທີ່ສົມບູນ
        return '/^' . str_replace('/', '\/', $route) . '$/';
    }

    /**
     * ຮັບລາຍການເສັ້ນທາງທັງໝົດ
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}