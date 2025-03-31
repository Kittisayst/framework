<?php

/**
 * Auth Middleware
 * ກວດສອບວ່າຜູ້ໃຊ້ເຂົ້າສູ່ລະບົບແລ້ວຫຼືບໍ່
 */
class AuthMiddleware extends Middleware
{
    /**
     * ກວດສອບວ່າຜູ້ໃຊ້ເຂົ້າສູ່ລະບົບແລ້ວຫຼືບໍ່
     */
    public function handle(Request $request)
    {
        // ກວດສອບວ່າມີ session user_id ຫຼືບໍ່
        if (!session('user_id')) {
            // ບັນທຶກ URL ທີ່ຜູ້ໃຊ້ພະຍາຍາມເຂົ້າເຖິງໄວ້ໃນ session
            setSession('redirect_after_login', $request->getUrl());
            
            // ສ້າງຂໍ້ຄວາມແຈ້ງເຕືອນ
            setFlash('ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນເຂົ້າເຖິງໜ້ານີ້', 'warning');
            
            // ສ້າງ response ໃໝ່ແລະ redirect ໄປໜ້າ login
            $response = new Response();
            $response->redirect(url('auth/login'));
            return $response;
        }
        
        // ຖ້າເຂົ້າສູ່ລະບົບແລ້ວ, ອະນຸຍາດໃຫ້ດຳເນີນການຕໍ່
        return true;
    }
}