<?php

/**
 * ໂຫຼດຄ່າຕັ້ງຈາກໄຟລ໌ .env ແລະຕັ້ງຄ່າເບື້ອງຕົ້ນ
 */

// ໂຫຼດແລະກຳນົດຄ່າຕັ້ງຈາກໄຟລ໌ .env
$env = [];
if (file_exists(ROOT_PATH . '/.env')) {
    $envLines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        // ຂ້າມບັນທັດທີ່ເປັນຄຳອະທິບາຍ
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);

        // ຕັ້ງຄ່າເປັນຕົວແປແວດລ້ອມ
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// ສ້າງໄຟລ໌ .env ຖ້າຍັງບໍ່ມີ
if (!file_exists(ROOT_PATH . '/.env')) {
    $envContent = "# ການຕັ້ງຄ່າແອັບພລິເຄຊັນ\n";
    $envContent .= "APP_NAME=MyFramework\n";
    $envContent .= "APP_URL=http://localhost/framework\n";
    $envContent .= "APP_DEBUG=true\n\n";
    $envContent .= "# ການຕັ້ງຄ່າຖານຂໍ້ມູນ\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST=localhost\n";
    $envContent .= "DB_PORT=3306\n";
    $envContent .= "DB_DATABASE=framework\n";
    $envContent .= "DB_USERNAME=root\n";
    $envContent .= "DB_PASSWORD=\n\n";
    $envContent .= "# ການຕັ້ງຄ່າ session\n";
    $envContent .= "SESSION_LIFETIME=120\n";
    $envContent .= "SESSION_SECURE=false\n\n";
    $envContent .= "# ການຕັ້ງຄ່າ timezone\n";
    $envContent .= "TIMEZONE=Asia/Bangkok\n";

    file_put_contents(ROOT_PATH . '/.env', $envContent);
}

// ຕັ້ງຄ່າ error reporting ຕາມໂໝດ debug
if (getenv('APP_DEBUG') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ຕັ້ງຄ່າ timezone
if (getenv('TIMEZONE')) {
    date_default_timezone_set(getenv('TIMEZONE'));
} else {
    date_default_timezone_set('UTC');
}

// ເລີ່ມ session
session_start();
