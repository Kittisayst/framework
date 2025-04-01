<?php

class LogMiddleware extends Middleware
{
    public function handle(Request $request)
    {
        var_dump($request);
        // ຮັບຂໍ້ມູນທີ່ຕ້ອງການບັນທຶກ
        $method = $request->getMethod();
        $url = $request->getUrl();
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
        $timestamp = date('Y-m-d H:i:s');
        
        // ສ້າງຂໍ້ຄວາມ log
        $logMessage = "[{$timestamp}] {$ip} {$method} {$url} {$userAgent}\n";
        
        // ກຳນົດເສັ້ນທາງໄຟລ໌ log
        $logFile = ROOT_PATH . '/logs/access.log';
        
        // ສ້າງໂຟລເດີ logs ຖ້າຍັງບໍ່ມີ
        $logsDir = ROOT_PATH . '/logs';
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        // ບັນທຶກຂໍ້ມູນລົງໃນໄຟລ໌
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        return true;
    }
}