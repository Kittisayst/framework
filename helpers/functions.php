<?php

/**
 * ຟັງຊັນຊ່ວຍເຫຼືອຕ່າງໆ
 */

/**
 * ຫຼີກລ່ຽງການໂຈມຕີແບບ XSS
 */
function escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * ຮັບຄ່າຂອງຕົວແປແວດລ້ອມ
 */
function env($key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

/**
 * ຮັບຄ່າຂອງຄຸກກີ້
 */
function getCookie($name, $default = null)
{
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}

/**
 * ຕັ້ງຄ່າຂອງຄຸກກີ້
 */
function setAppCookie($name, $value, $expiry = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true)
{
    return setcookie($name, $value, $expiry, $path, $domain, $secure, $httpOnly);
}

/**
 * ຮັບຄ່າຂອງເຊສຊັນ
 */
function session($key, $default = null)
{
    if (!isset($_SESSION)) {
        session_start();
    }

    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * ຕັ້ງຄ່າຂອງເຊສຊັນ
 */
function setSession($key, $value)
{
    if (!isset($_SESSION)) {
        session_start();
    }

    $_SESSION[$key] = $value;
}

/**
 * ລຶບຄ່າຂອງເຊສຊັນ
 */
function unsetSession($key)
{
    if (!isset($_SESSION)) {
        session_start();
    }

    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * ສ້າງ URL
 */
function url($path = '')
{
    $baseUrl = env('APP_URL', 'http://localhost/framework');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

/**
 * ເຮັດການ redirect
 */
function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

/**
 * ກຳນົດຂໍ້ຄວາມ flash
 */
function setFlash($message, $type = 'success')
{
    setSession('flash', [
        'message' => $message,
        'type' => $type
    ]);
}

/**
 * ຮັບຂໍ້ຄວາມ flash ແລະລຶບອອກຈາກເຊສຊັນ
 */
function getFlash()
{
    $flash = session('flash');
    unsetSession('flash');
    return $flash;
}

/**
 * ຮັບຄ່າປັດຈຸບັນ
 */
function old($key, $default = '')
{
    $oldInput = session('old_input', []);
    return isset($oldInput[$key]) ? $oldInput[$key] : $default;
}

/**
 * ຕັດຂໍ້ຄວາມໃຫ້ສັ້ນລົງ
 */
function truncate($string, $length = 100, $append = '...')
{
    if (strlen($string) > $length) {
        $string = substr($string, 0, $length);
        $string .= $append;
    }

    return $string;
}

/**
 * ຮັບຊື່ຮູບ Gravatar ຂອງເມລ
 */
function getGravatar($email, $size = 80)
{
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/$hash?s=$size&d=mp";
}

/**
 * ປ່ຽນຮູບແບບ datetime ເປັນຄວາມແຕກຕ່າງເປັນມະນຸດອ່ານໄດ້
 */
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $current = time();
    $diff = $current - $time;

    $seconds = $diff;
    $minutes = round($diff / 60);
    $hours = round($diff / 3600);
    $days = round($diff / 86400);
    $weeks = round($diff / 604800);
    $months = round($diff / 2629440);
    $years = round($diff / 31553280);

    if ($seconds <= 60) {
        return "ຫາກໍ່ກີ້ນີ້";
    } else if ($minutes <= 60) {
        return "$minutes ນາທີກ່ອນ";
    } else if ($hours <= 24) {
        return "$hours ຊົ່ວໂມງກ່ອນ";
    } else if ($days <= 7) {
        return "$days ວັນກ່ອນ";
    } else if ($weeks <= 4.3) {
        return "$weeks ອາທິດກ່ອນ";
    } else if ($months <= 12) {
        return "$months ເດືອນກ່ອນ";
    } else {
        return "$years ປີກ່ອນ";
    }
}

/**
 * ປ່ຽນຮູບແບບເລກເປັນສະກຸນເງິນ
 */
function formatMoney($amount, $symbol = '₭', $decimals = 0)
{
    return $symbol . ' ' . number_format($amount, $decimals, ',', '.');
}

/**
 * ຮັບຍອດລວມຂອງໜ້າປັດຈຸບັນແລະຈຳນວນໜ້າທັງໝົດ
 */
function getPaginationInfo($currentPage, $perPage, $total)
{
    $totalPages = ceil($total / $perPage);
    $startItem = (($currentPage - 1) * $perPage) + 1;
    $endItem = min($startItem + $perPage - 1, $total);

    return [
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'start_item' => $startItem,
        'end_item' => $endItem
    ];
}

/**
 * ເຂົ້າລະຫັດ JWT
 */
function encodeJwt($payload, $secret)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $header = base64_encode($header);

    $payload = json_encode($payload);
    $payload = base64_encode($payload);

    $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $signature = base64_encode($signature);

    return "$header.$payload.$signature";
}

/**
 * ຖອດລະຫັດ JWT
 */
function decodeJwt($token, $secret)
{
    $parts = explode('.', $token);

    if (count($parts) !== 3) {
        return null;
    }

    list($header, $payload, $signature) = $parts;

    $validSignature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $validSignature = base64_encode($validSignature);

    if ($signature !== $validSignature) {
        return null;
    }

    $payload = base64_decode($payload);
    return json_decode($payload, true);
}

/**
 * ຟັງຊັນສຳລັບການ debug
 */

/**
 * ສະແດງຂໍ້ມູນແບບມີການຈັດຮູບແບບທີ່ອ່ານງ່າຍ
 * @param mixed $data ຂໍ້ມູນທີ່ຕ້ອງການສະແດງ
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: true)
 * @return void
 */
function debug($data, $exit = true)
{
    echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 14px; color: #333; margin: 20px; line-height: 1.6; overflow: auto;">';
    
    // ສະແດງຂໍ້ມູນປະເພດຕົວແປ
    echo '<div style="background: #555; color: #fff; padding: 5px 10px; margin-bottom: 10px; border-radius: 3px;">';
    echo 'Type: ' . gettype($data);
    echo '</div>';
    
    // ສະແດງຂໍ້ມູນ
    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        var_dump($data);
    }
    
    echo '</pre>';
    
    if ($exit) {
        exit;
    }
}

/**
 * ສະແດງຂໍ້ມູນແບບມີການຈັດຮູບແບບໂດຍບໍ່ໄດ້ຢຸດການປະມວນຜົນ
 * @param mixed $data ຂໍ້ມູນທີ່ຕ້ອງການສະແດງ
 * @return void
 */
function dd($data)
{
    debug($data, false);
}

/**
 * ສະແດງຂໍ້ມູນແລະຢຸດການປະມວນຜົນຫຼັງຈາກນັ້ນ
 * @param mixed $data ຂໍ້ມູນທີ່ຕ້ອງການສະແດງ
 * @return void
 */
function ddd($data)
{
    debug($data, true);
}

/**
 * ສະແດງ backtrace ຂອງໂຄດປັດຈຸບັນ
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: true)
 * @return void
 */
function debug_trace($exit = true)
{
    $trace = debug_backtrace();
    array_shift($trace); // ລຶບແຖວທຳອິດທີ່ເປັນການເອີ້ນໃຊ້ຟັງຊັນນີ້
    
    echo '<pre style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 14px; color: #333; margin: 20px; line-height: 1.6; overflow: auto;">';
    echo '<div style="background: #555; color: #fff; padding: 5px 10px; margin-bottom: 10px; border-radius: 3px;">';
    echo 'Debug Backtrace';
    echo '</div>';
    
    foreach ($trace as $key => $step) {
        $file = isset($step['file']) ? $step['file'] : 'unknown';
        $line = isset($step['line']) ? $step['line'] : 'unknown';
        $function = isset($step['function']) ? $step['function'] : 'unknown';
        $class = isset($step['class']) ? $step['class'] : '';
        $type = isset($step['type']) ? $step['type'] : '';
        
        echo "<div style='margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ddd;'>";
        echo "<strong>#{$key}</strong> ";
        echo "{$file} ({$line})<br>";
        echo "<span style='color: #777;'>{$class}{$type}{$function}()</span>";
        echo "</div>";
    }
    
    echo '</pre>';
    
    if ($exit) {
        exit;
    }
}

/**
 * ບັນທຶກຂໍ້ມູນລົງໃນໄຟລ໌ debug.log
 * @param mixed $data ຂໍ້ມູນທີ່ຕ້ອງການບັນທຶກ
 * @param string $label ປ້າຍຂອງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: ບໍ່ມີ)
 * @return void
 */
function debug_log($data, $label = '')
{
    $logDir = ROOT_PATH . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    
    if ($label) {
        $label = "[{$label}] ";
    }
    
    $logMessage = "[{$timestamp}] {$label}";
    
    if (is_array($data) || is_object($data)) {
        $logMessage .= print_r($data, true);
    } else {
        $logMessage .= var_export($data, true);
    }
    
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * ສະແດງຂໍ້ມູນການຮ້ອງຂໍປັດຈຸບັນ (Request Information)
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: false)
 * @return void
 */
function debug_request($exit = false)
{
    $request = [
        'Method' => $_SERVER['REQUEST_METHOD'],
        'URI' => $_SERVER['REQUEST_URI'],
        'Query String' => $_SERVER['QUERY_STRING'] ?? '',
        'Protocol' => $_SERVER['SERVER_PROTOCOL'],
        'Remote IP' => $_SERVER['REMOTE_ADDR'],
        'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'GET Data' => $_GET,
        'POST Data' => $_POST,
        'FILES Data' => $_FILES,
        'COOKIE Data' => $_COOKIE,
        'SESSION Data' => isset($_SESSION) ? $_SESSION : 'Session not started'
    ];
    
    debug($request, $exit);
}

/**
 * ສະແດງຂໍ້ມູນຕົວແປແວດລ້ອມ (Environment Variables)
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: false)
 * @return void
 */
function debug_env($exit = false)
{
    $env = [];
    foreach ($_ENV as $key => $value) {
        $env[$key] = $value;
    }
    
    // ໃຊ້ getenv() ເພື່ອຮັບຕົວແປແວດລ້ອມທີ່ຕັ້ງຄ່າໂດຍ putenv()
    foreach (array_keys($_SERVER) as $key) {
        $value = getenv($key);
        if ($value !== false && !isset($env[$key])) {
            $env[$key] = $value;
        }
    }
    
    debug($env, $exit);
}

/**
 * ວັດແທກເວລາການປະມວນຜົນ
 */
class DebugTimer
{
    private static $timers = [];
    
    /**
     * ເລີ່ມຕົ້ນບັນທຶກເວລາ
     * @param string $name ຊື່ຂອງຕົວຈັບເວລາ
     * @return void
     */
    public static function start($name = 'default')
    {
        self::$timers[$name] = [
            'start' => microtime(true),
            'end' => null
        ];
    }
    
    /**
     * ຢຸດບັນທຶກເວລາແລະສະແດງຜົນລັບ
     * @param string $name ຊື່ຂອງຕົວຈັບເວລາ
     * @param bool $display ສະແດງຜົນລັບທັນທີຫຼືບໍ່
     * @return float ເວລາທີ່ໃຊ້ (ມິລລິວິນາທີ)
     */
    public static function stop($name = 'default', $display = true)
    {
        if (!isset(self::$timers[$name])) {
            echo "Timer '{$name}' not started!";
            return false;
        }
        
        self::$timers[$name]['end'] = microtime(true);
        $time = self::$timers[$name]['end'] - self::$timers[$name]['start'];
        $timeMs = round($time * 1000, 2);
        
        if ($display) {
            echo "<div style='background: #f5f5f5; padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; font-family: monospace; display: inline-block; margin: 5px;'>";
            echo "Timer <strong>{$name}</strong>: {$timeMs} ms";
            echo "</div>";
        }
        
        return $timeMs;
    }
    
    /**
     * ໄດ້ຮັບຄ່າເວລາທີ່ໄດ້ບັນທຶກໄວ້
     * @param string $name ຊື່ຂອງຕົວຈັບເວລາ
     * @return float ເວລາທີ່ໃຊ້ (ມິລລິວິນາທີ)
     */
    public static function get($name = 'default')
    {
        if (!isset(self::$timers[$name])) {
            return false;
        }
        
        if (self::$timers[$name]['end'] === null) {
            $end = microtime(true);
        } else {
            $end = self::$timers[$name]['end'];
        }
        
        $time = $end - self::$timers[$name]['start'];
        return round($time * 1000, 2);
    }
}

/**
 * ຟັງຊັນຊ່ວຍເຫຼືອສຳລັບເລີ່ມການຈັບເວລາປະຕິບັດໂຄດ
 * @param string $name ຊື່ຂອງຕົວຈັບເວລາ
 * @return void
 */
function debug_timer_start($name = 'default')
{
    DebugTimer::start($name);
}

/**
 * ຟັງຊັນຊ່ວຍເຫຼືອສຳລັບຢຸດການຈັບເວລາປະຕິບັດໂຄດ
 * @param string $name ຊື່ຂອງຕົວຈັບເວລາ
 * @param bool $display ສະແດງຜົນລັບທັນທີຫຼືບໍ່
 * @return float ເວລາທີ່ໃຊ້ (ມິລລິວິນາທີ)
 */
function debug_timer_stop($name = 'default', $display = true)
{
    return DebugTimer::stop($name, $display);
}

/**
 * ກວດສອບຂໍ້ມູນຈາກຖານຂໍ້ມູນປັດຈຸບັນ
 * @param string $query SQL query ທີ່ຕ້ອງການກວດສອບ
 * @param array $params ພາລາມິເຕີສຳລັບ query (ຄ່າເລີ່ມຕົ້ນ: [])
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: false)
 * @return void
 */
function debug_query($query, $params = [], $exit = false)
{
    $db = Database::getInstance();
    
    echo '<div style="background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 14px; color: #333; margin: 20px; line-height: 1.6; overflow: auto;">';
    echo '<div style="background: #555; color: #fff; padding: 5px 10px; margin-bottom: 10px; border-radius: 3px;">SQL Query Debug</div>';
    
    echo '<div style="margin-bottom: 10px;"><strong>Query:</strong><br>';
    echo '<pre style="background: #f9f9f9; padding: 10px; border: 1px solid #eee; border-radius: 3px;">' . $query . '</pre>';
    echo '</div>';
    
    if (!empty($params)) {
        echo '<div style="margin-bottom: 10px;"><strong>Parameters:</strong><br>';
        echo '<pre style="background: #f9f9f9; padding: 10px; border: 1px solid #eee; border-radius: 3px;">';
        print_r($params);
        echo '</pre></div>';
    }
    
    // ປະຕິບັດການກວດສອບ SQL
    debug_timer_start('sql_query');
    try {
        $result = $db->query($query, $params);
        $queryTime = debug_timer_stop('sql_query', false);
        
        echo '<div style="margin-bottom: 10px;"><strong>Execution Time:</strong> ' . $queryTime . ' ms</div>';
        
        echo '<div><strong>Results:</strong><br>';
        echo '<pre style="background: #f9f9f9; padding: 10px; border: 1px solid #eee; border-radius: 3px; max-height: 300px; overflow: auto;">';
        print_r($result);
        echo '</pre></div>';
    } catch (Exception $e) {
        echo '<div style="margin-bottom: 10px; color: #d9534f;"><strong>Error:</strong><br>';
        echo $e->getMessage();
        echo '</div>';
    }
    
    echo '</div>';
    
    if ($exit) {
        exit;
    }
}

/**
 * ສະແດງການໃຊ້ຊັບພະຍາກອນຂອງລະບົບ
 * @param bool $exit ຢຸດການປະມວນຜົນຫຼັງຈາກສະແດງຂໍ້ມູນ (ຄ່າເລີ່ມຕົ້ນ: false)
 * @return void
 */
function debug_resources($exit = false)
{
    $data = [
        'Memory' => [
            'Current Usage' => formatBytes(memory_get_usage()),
            'Peak Usage' => formatBytes(memory_get_peak_usage()),
        ],
        'PHP Info' => [
            'Version' => PHP_VERSION,
            'OS' => PHP_OS,
            'SAPI' => php_sapi_name(),
            'Extensions' => implode(', ', get_loaded_extensions()),
        ],
        'Server Info' => [
            'Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'Server Name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        ],
    ];
    
    debug($data, $exit);
}

/**
 * ການຊ່ວຍເຫຼືອແປງຫົວໜ່ວຍໄບຕ໌ເປັນຮູບແບບທີ່ມະນຸດອ່ານໄດ້
 * @param int $bytes ຂະໜາດໃນຫົວໜ່ວຍໄບຕ໌
 * @param int $precision ຄວາມລະອຽດ (ຄ່າເລີ່ມຕົ້ນ: 2)
 * @return string ຂະໜາດໃນຮູບແບບທີ່ມະນຸດອ່ານໄດ້ (KB, MB, GB, etc.)
 */
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// debug() - ແສດງຂໍ້ມູນທຸກປະເພດແບບມີການຈັດຮູບແບບ
// dd() - "Dump and Die" ແສດງຂໍ້ມູນແລ້ວສືບຕໍ່ການປະມວນຜົນ
// ddd() - "Dump, Die, and Debug" ແສດງຂໍ້ມູນແລ້ວຢຸດການປະມວນຜົນ
// debug_trace() - ແສດງ backtrace ຂອງໂຄດປັດຈຸບັນ
// debug_log() - ບັນທຶກຂໍ້ມູນລົງໃນໄຟລ໌ debug.log
// debug_request() - ແສດງຂໍ້ມູນການຮ້ອງຂໍປັດຈຸບັນ
// debug_env() - ແສດງຂໍ້ມູນຕົວແປແວດລ້ອມ
// DebugTimer ແລະຟັງຊັນທີ່ກ່ຽວຂ້ອງ - ວັດແທກເວລາການປະມວນຜົນ
// debug_query() - ກວດສອບ SQL queries
// debug_resources() - ແສດງການໃຊ້ຊັບພະຍາກອນຂອງລະບົບ
