<?php

class CsrfMiddleware extends Middleware
{
    public function handle(Request $request)
    {
        // ຖ້າເປັນຄຳຂໍປະເພດ POST, PUT, DELETE ໃຫ້ກວດສອບ CSRF token
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $token = $request->post('csrf_token');
            $sessionToken = session('csrf_token');

            if (!$token || $token !== $sessionToken) {
                $response = new Response();
                $response->setStatusCode(403);
                $response->setContent('CSRF token ບໍ່ຖືກຕ້ອງ');
                return $response;
            }
        }

        // ສ້າງ CSRF token ໃໝ່ສຳລັບການຮ້ອງຂໍຕໍ່ໄປ
        $newToken = bin2hex(random_bytes(32));
        setSession('csrf_token', $newToken);

        return true;
    }
}
