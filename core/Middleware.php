<?php

/**
 * Base Middleware Class
 * ຄລາສພື້ນຖານສຳລັບ Middleware ທຸກຕົວ
 */
abstract class Middleware
{
    /**
     * ດຳເນີນການກ່ອນທີ່ request ຈະໄປເຖິງ controller
     * 
     * @param Request $request ຄຳຂໍປັດຈຸບັນ
     * @return bool|Response ສົ່ງຄືນ true ຖ້າຜ່ານການກວດສອບ, ຫຼື Response ຖ້າຕ້ອງການ redirect
     */
    abstract public function handle(Request $request);

    /**
     * ຈັດການຫຼັງຈາກ controller ສົ່ງຄຳຕອບກັບ 
     * (ເປັນຕົວເລືອກ, ບໍ່ໄດ້ນຳໃຊ້ຢູ່ທຸກ Middleware)
     * 
     * @param Request $request ຄຳຂໍປັດຈຸບັນ
     * @param Response $response ການຕອບກັບປັດຈຸບັນ
     * @return Response ການຕອບກັບທີ່ອາດຈະຖືກປັບປຸງ
     */
    public function afterController(Request $request, Response $response)
    {
        return $response;
    }
}
