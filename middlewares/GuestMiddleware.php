<?php

/**
 * Guest Middleware
 * ກວດສອບວ່າຜູ້ໃຊ້ຍັງບໍ່ໄດ້ເຂົ້າສູ່ລະບົບ
 */
class GuestMiddleware extends Middleware
{
    /**
     * ກວດສອບວ່າຜູ້ໃຊ້ຍັງບໍ່ໄດ້ເຂົ້າສູ່ລະບົບ
     */
    public function handle(Request $request)
    {
        // ຖ້າມີ session user_id, ໝາຍຄວາມວ່າຜູ້ໃຊ້ເຂົ້າສູ່ລະບົບແລ້ວ
        if (session('user_id')) {
            // ສົ່ງຕໍ່ໄປຍັງໜ້າຫຼັກຫຼືໜ້າ dashboard
            $response = new Response();
            $response->redirect(url('dashboard'));
            return $response;
        }

        // ຖ້າຍັງບໍ່ໄດ້ເຂົ້າສູ່ລະບົບ, ອະນຸຍາດໃຫ້ດຳເນີນການຕໍ່
        return true;
    }
}
