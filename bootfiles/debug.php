<?php

/**
 * ເກັບຂໍ້ມູນດີບັກໄວ້ໃນໄຟລ໌ log
 */

if (getenv('APP_DEBUG') === 'true') {
    // ສ້າງໂຟລເດີ logs ຖ້າບໍ່ມີ
    $log_dir = ROOT_PATH . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    // ເກັບຂໍ້ມູນດີບັກໄວ້ໃນໄຟລ໌ log ແທນທີ່ສົ່ງອອກທາງໜ້າຈໍ
    register_shutdown_function(function () use ($app) {
        $debug_output = ob_get_contents();
        if (!empty($debug_output)) {
            file_put_contents(ROOT_PATH . '/logs/debug_output.log', $debug_output, FILE_APPEND);
        }

        ob_start();
        echo "\n\n==== DEBUG INFO (" . date('Y-m-d H:i:s') . ") ====\n";
        echo "App Instance:\n";
        var_dump($app);
        echo "\nRoutes:\n";
        var_dump($app->getRouter()->getRoutes());
        $debug_data = ob_get_clean();

        file_put_contents(ROOT_PATH . '/logs/debug_app.log', $debug_data, FILE_APPEND);
    });
}
