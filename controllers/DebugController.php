<?php

/**
 * Debug Controller
 * ຄລາສຄວບຄຸມສຳລັບການທົດສອບແລະ debug
 */
class DebugController extends Controller
{
    protected $middlewares = [];
    protected $routeMiddlewares = [];
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // ກວດສອບວ່າ debug ເປີດໃຊ້ງານຫຼືບໍ່
        if (getenv('APP_DEBUG') !== 'true') {
            $this->response->setStatusCode(403);
            $this->view('error', [
                'message' => 'Debug mode is not enabled. Enable APP_DEBUG=true in .env file.'
            ]);
            exit;
        }
    }

    /**
     * ສະແດງຂໍ້ມູນພື້ນຖານຂອງລະບົບ
     */
    public function info()
    {
        $app = App::getInstance();

        $data = [
            'Framework Info' => [
                'App Debug' => $app->isDebug() ? 'Enabled' : 'Disabled',
                'Environment' => getenv('APP_ENV') ?: 'Production',
                'Base URL' => getenv('APP_URL'),
                'Timezone' => getenv('TIMEZONE') ?: date_default_timezone_get(),
            ],
            'PHP Info' => [
                'Version' => PHP_VERSION,
                'OS' => PHP_OS,
                'SAPI' => php_sapi_name(),
                'Memory Limit' => ini_get('memory_limit'),
                'Max Execution Time' => ini_get('max_execution_time') . ' seconds',
            ],
            'Database Info' => [
                'Host' => getenv('DB_HOST'),
                'Database' => getenv('DB_DATABASE'),
                'Username' => getenv('DB_USERNAME'),
                'Port' => getenv('DB_PORT'),
            ],
            'Path Info' => [
                'Root Path' => ROOT_PATH,
                'Core Path' => CORE_PATH,
                'Controllers Path' => CONTROLLERS_PATH,
                'Models Path' => MODELS_PATH,
                'Views Path' => VIEWS_PATH,
            ],
        ];

        // ສະແດງຂໍ້ມູນ
        $this->view('debug/info', [
            'pageTitle' => 'Debug Information',
            'data' => $data
        ]);
    }

    /**
     * ສະແດງຂໍ້ມູນການຮ້ອງຂໍປັດຈຸບັນ
     */
    public function request()
    {
        // ຮັບຂໍ້ມູນການຮ້ອງຂໍປັດຈຸບັນ
        $request = $this->request;

        $data = [
            'Request Info' => [
                'Method' => $request->getMethod(),
                'URL' => $request->getUrl(),
                'URI' => $_SERVER['REQUEST_URI'],
                'Query String' => $_SERVER['QUERY_STRING'] ?? '',
                'IP Address' => $_SERVER['REMOTE_ADDR'],
                'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ],
            'GET Data' => $request->get(),
            'POST Data' => $request->post(),
            'Cookies' => $request->cookie(),
            'Session Data' => isset($_SESSION) ? $_SESSION : 'Session not started',
            'Server Variables' => $_SERVER,
        ];

        // ສະແດງຂໍ້ມູນ
        $this->view('debug/request', [
            'pageTitle' => 'Request Debug Information',
            'data' => $data
        ]);
    }

    /**
     * ສະແດງຂໍ້ມູນຕົວແປແວດລ້ອມ
     */
    public function env()
    {
        $env = [];

        // ອ່ານໄຟລ໌ .env
        if (file_exists(ROOT_PATH . '/.env')) {
            $envContent = file_get_contents(ROOT_PATH . '/.env');
            $envLines = explode("\n", $envContent);

            foreach ($envLines as $line) {
                $line = trim($line);

                // ຂ້າມແຖວເປົ່າແລະຄຳອະທິບາຍ
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }

                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $env[$key] = $value;
                }
            }
        }

        // ຮັບຄ່າຈາກ getenv()
        $envVars = [];
        foreach (array_keys($_SERVER) as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $envVars[$key] = $value;
            }
        }

        $data = [
            '.env File Variables' => $env,
            'Environment Variables' => $envVars,
        ];

        // ສະແດງຂໍ້ມູນ
        $this->view('debug/env', [
            'pageTitle' => 'Environment Variables',
            'data' => $data
        ]);
    }

    /**
     * ສະແດງລາຍຊື່ເສັ້ນທາງທັງໝົດ
     */
    public function routes()
    {
        $router = App::getInstance()->getRouter();
        var_dump($router);
        $routes = $router->getRoutes();

        // ແປງຂໍ້ມູນເສັ້ນທາງໃຫ້ເປັນຮູບແບບທີ່ເໝາະກັບ view
        $formattedRoutes = [];

        // ໄລ່ລຽງຕາມວິທີການ HTTP (GET, POST, ອື່ນໆ)
        foreach ($routes as $method => $methodRoutes) {
            // ໄລ່ລຽງຕາມເສັ້ນທາງພາຍໃນວິທີການແຕ່ລະຢ່າງ
            foreach ($methodRoutes as $path => $routeInfo) {
                // ຮັບ middlewares ສຳລັບເສັ້ນທາງນີ້
                $middlewareList = $router->getMiddlewares($method, $path);

                // ເພີ່ມເສັ້ນທາງນີ້ເຂົ້າໃນລາຍການທີ່ຈັດຮູບແບບແລ້ວ
                $formattedRoutes[] = [
                    'method' => $method,
                    'path' => $path,
                    'controller' => $routeInfo['controller'],
                    'action' => $routeInfo['action'],
                    'middlewares' => $middlewareList
                ];
            }
        }

        // ສະແດງຂໍ້ມູນ
        $this->view('debug/routes', [
            'pageTitle' => 'Registered Routes',
            'routes' => $formattedRoutes
        ]);
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
     * ທົດສອບ SQL query
     */
    public function query($table = null)
    {
        $db = Database::getInstance();

        if ($table === null) {
            // ຮັບລາຍຊື່ຕາຕະລາງທັງໝົດໃນຖານຂໍ້ມູນ
            $query = "SHOW TABLES";
            $tables = $db->query($query);

            // ສະແດງຂໍ້ມູນ
            $this->view('debug/tables', [
                'pageTitle' => 'Database Tables',
                'tables' => $tables
            ]);
        } else {
            // ອ່ານໂຄງສ້າງຕາຕະລາງ
            $structureQuery = "DESCRIBE `$table`";
            $structure = $db->query($structureQuery);

            // ອ່ານຂໍ້ມູນຈາກຕາຕະລາງ
            $dataQuery = "SELECT * FROM `$table` LIMIT 100";

            debug_timer_start('table_query');
            $tableData = $db->query($dataQuery);
            $queryTime = debug_timer_stop('table_query', false);

            // ນັບຈຳນວນແຖວທັງໝົດ
            $countQuery = "SELECT COUNT(*) as total FROM `$table`";
            $countResult = $db->query($countQuery);
            $totalRows = $countResult[0]['total'];

            // ສະແດງຂໍ້ມູນ
            $this->view('debug/table_data', [
                'pageTitle' => "Table: $table",
                'table' => $table,
                'structure' => $structure,
                'data' => $tableData,
                'totalRows' => $totalRows,
                'queryTime' => $queryTime,
                'query' => $dataQuery
            ]);
        }
    }

    /**
     * ສະແດງການໃຊ້ຊັບພະຍາກອນ
     */
    public function resources()
    {
        $data = [
            'Memory Usage' => [
                'Current' => formatBytes(memory_get_usage()),
                'Peak' => formatBytes(memory_get_peak_usage()),
            ],
            'Timers' => $this->getActiveTimers(),
            'Included Files' => [
                'Count' => count(get_included_files()),
                'Files' => get_included_files(),
            ],
            'PHP Settings' => [
                'display_errors' => ini_get('display_errors'),
                'error_reporting' => ini_get('error_reporting'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ];

        // ສະແດງຂໍ້ມູນ
        $this->view('debug/resources', [
            'pageTitle' => 'Resource Usage',
            'data' => $data
        ]);
    }

    /**
     * ຮັບລາຍຊື່ debug timers ທີ່ກຳລັງໃຊ້ງານ
     */
    /**
     * ຮັບລາຍຊື່ debug timers ທີ່ກຳລັງໃຊ້ງານ
     */
    private function getActiveTimers()
    {
        // ໃນກໍລະນີທີ່ບໍ່ສາມາດເຂົ້າເຖິງຕົວແປ timers ໂດຍກົງ, ສ້າງຂໍ້ມູນລາຍງານສະເພາະ
        $timers = [];

        // ຖ້າມີເວລາທີ່ບັນທຶກໄວ້ໃນບັນທຶກ, ອາດຈະອ່ານຈາກບັນທຶກ debug
        $debugLogFile = ROOT_PATH . '/logs/debug.log';
        if (file_exists($debugLogFile)) {
            $logContent = file_get_contents($debugLogFile);

            // ຄົ້ນຫາລາຍການເວລາໃນບັນທຶກ
            preg_match_all('/Timer ([^:]+): ([0-9.]+) ms/', $logContent, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $timers[$match[1]] = $match[2] . ' ms';
            }
        }

        return [
            'Active Timers' => $timers,
            'Note' => 'ຕ້ອງເປີດໃຊ້ debug_timer_start() ແລະ debug_timer_stop() ກ່ອນຈຶ່ງຈະເຫັນຂໍ້ມູນທີ່ນີ້'
        ];
    }
}
