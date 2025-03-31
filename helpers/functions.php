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
