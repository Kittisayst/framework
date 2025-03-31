<?php

/**
 * Error Handler Middleware
 * Middleware ສຳລັບຈັດການຂໍ້ຜິດພາດໃນລະບົບ
 */
class ErrorHandlerMiddleware extends Middleware
{
    /**
     * ຈັດການຄຳຂໍກ່ອນຈະສົ່ງຕໍ່ໄປຍັງ controller
     */
    public function handle(Request $request)
    {
        // ຕັ້ງຄ່າ error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        return true;
    }

    /**
     * ຈັດການຂໍ້ຜິດພາດ (errors)
     */
    public function handleError($severity, $message, $file, $line)
    {
        // ບັນທຶກຂໍ້ຜິດພາດ
        $this->logError("PHP Error [$severity]: $message in $file on line $line");

        // ສົ່ງຕໍ່ໃຫ້ PHP error handler ເດີມ
        return false;
    }

    /**
     * ຈັດການຂໍ້ຍົກເວັ້ນ (exceptions)
     */
    public function handleException($exception)
    {
        // ບັນທຶກຂໍ້ຍົກເວັ້ນ
        $this->logError("Exception: " . $exception->getMessage() .
            " in " . $exception->getFile() .
            " on line " . $exception->getLine());

        // ສະແດງໜ້າຂໍ້ຜິດພາດທີ່ສວຍງາມ
        $response = new Response();
        $response->setStatusCode(500);

        $errorMessage = $exception->getMessage();
        $errorFile = $exception->getFile();
        $errorLine = $exception->getLine();
        $errorTrace = $exception->getTraceAsString();

        // ຈັດຮູບແບບຂໍ້ຄວາມຂໍ້ຜິດພາດ
        $data = [
            'error' => 'ເກີດຂໍ້ຜິດພາດໃນລະບົບ',
            'message' => $errorMessage,
            'details' => getenv('APP_DEBUG') === 'true' ?
                "File: $errorFile, Line: $errorLine\nTrace:\n$errorTrace" : null
        ];

        $view = new View();
        $content = $view->render('error', $data);

        $response->setContent($content);
        $response->send();
        exit;
    }

    /**
     * ບັນທຶກຂໍ້ຜິດພາດລົງໃນໄຟລ໌
     */
    private function logError($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";

        $logsDir = ROOT_PATH . '/logs';
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        $errorLogFile = $logsDir . '/error.log';
        file_put_contents($errorLogFile, $logMessage, FILE_APPEND);
    }
}
