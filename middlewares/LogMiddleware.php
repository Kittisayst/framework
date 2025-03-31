<?php

/**
 * Log Middleware
 * Middleware ສຳລັບບັນທຶກຂໍ້ມູນການໃຊ້ງານ
 */
class LogMiddleware extends Middleware
{
    /**
     * ຈັດການຄຳຂໍກ່ອນຈະສົ່ງຕໍ່ໄປຍັງ controller
     * 
     * @param Request $request ຄຳຂໍປັດຈຸບັນ
     * @return bool|Response 
     */
    public function handle(Request $request)
    {
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

        // ສືບຕໍ່ການປະມວນຜົນຄຳຂໍ
        return true;
    }

    /**
     * ຈັດການຫຼັງຈາກ controller ສົ່ງຄຳຕອບກັບ
     * 
     * @param Request $request ຄຳຂໍປັດຈຸບັນ
     * @param Response $response ການຕອບກັບປັດຈຸບັນ
     * @return Response
     */
    public function afterController(Request $request, Response $response)
    {
        // ເພີ່ມ header ເພື່ອບອກວ່າຄຳຂໍຖືກບັນທຶກແລ້ວ
        $response->setHeader('X-Logged', 'true');

        return $response;
    }
}
