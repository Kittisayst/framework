<?php

/**
 * Home Controller
 * ຄລາສຄວບຄຸມສຳລັບໜ້າຫຼັກ
 */
class HomeController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ສະແດງໜ້າຫຼັກ
     */
    public function index()
    {
        // ຕົວຢ່າງຂໍ້ມູນສຳລັບໜ້າຫຼັກ
        $data = [
            'pageTitle' => 'ໜ້າຫຼັກ - PHP Framework',
            'welcomeMessage' => 'ຍິນດີຕ້ອນຮັບເຂົ້າສູ່ PHP Framework',
            'description' => 'ນີ້ແມ່ນ Framework PHP ທີ່ໃຊ້ງານງ່າຍ ເໝາະສຳລັບຜູ້ເລີ່ມຕົ້ນ ແລະ ໂຄງການຂະໜາດນ້ອຍເຖິງກາງ.',
            'features' => [
                [
                    'title' => 'ໃຊ້ງານງ່າຍ',
                    'description' => 'ອອກແບບມາເພື່ອໃຫ້ໃຊ້ງານໄດ້ງ່າຍ ເໝາະສຳລັບຜູ້ທີ່ເລີ່ມຕົ້ນຮຽນ PHP.',
                    'icon' => 'bi-stars'
                ],
                [
                    'title' => 'ແບບ MVC',
                    'description' => 'ໃຊ້ຮູບແບບ Model-View-Controller ເພື່ອແຍກການຈັດການຂໍ້ມູນ, ການສະແດງຜົນ, ແລະ ການຄວບຄຸມ.',
                    'icon' => 'bi-diagram-3'
                ],
                [
                    'title' => 'ປັບແຕ່ງໄດ້',
                    'description' => 'ສາມາດປັບແຕ່ງໄດ້ຕາມຄວາມຕ້ອງການ ແລະ ຂະຫຍາຍຄວາມສາມາດໄດ້ງ່າຍ.',
                    'icon' => 'bi-gear'
                ],
                [
                    'title' => 'ປະສິດທິພາບສູງ',
                    'description' => 'ອອກແບບມາໃຫ້ມີປະສິດທິພາບສູງ ແລະ ກະທັດຮັດ.',
                    'icon' => 'bi-speedometer'
                ]
            ]
        ];

        // ສົ່ງຂໍ້ມູນໄປຍັງ view
        $this->view('home/index', $data);
    }

    /**
     * ສະແດງໜ້າກ່ຽວກັບ
     */
    public function about()
    {
        $data = [
            'pageTitle' => 'ກ່ຽວກັບ - PHP Framework',
            'content' => 'PHP Framework ແມ່ນ framework ທີ່ອອກແບບມາເພື່ອໃຫ້ໃຊ້ງານງ່າຍ ແລະ ເໝາະສຳລັບການຮຽນຮູ້ການພັດທະນາເວັບໄຊດ້ວຍ PHP.'
        ];

        $this->view('home/about', $data);
    }

    /**
     * ສະແດງໜ້າຕິດຕໍ່
     */
    public function contact()
    {
        $data = [
            'pageTitle' => 'ຕິດຕໍ່ - PHP Framework',
            'contactInfo' => [
                'email' => 'contact@example.com',
                'phone' => '+856 20 1234 5678',
                'address' => 'ນະຄອນຫຼວງວຽງຈັນ, ສປປ ລາວ'
            ]
        ];

        $this->view('home/contact', $data);
    }

    /**
     * ສົ່ງຂໍ້ຄວາມຕິດຕໍ່
     */
    public function sendMessage()
    {
        // ຮັບຂໍ້ມູນຈາກຟອມ
        $data = $this->getFormData();

        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ
        $errors = $this->validate($data, [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);

        // ຖ້າພົບຂໍ້ຜິດພາດ
        if (!empty($errors)) {
            return $this->view('home/contact', [
                'pageTitle' => 'ຕິດຕໍ່ - PHP Framework',
                'errors' => $errors,
                'formData' => $data,
                'contactInfo' => [
                    'email' => 'contact@example.com',
                    'phone' => '+856 20 1234 5678',
                    'address' => 'ນະຄອນຫຼວງວຽງງຈັນ, ສປປ ລາວ'
                ]
            ]);
        }

        // ສົມມຸດວ່າສົ່ງຂໍ້ຄວາມສຳເລັດ
        // ໃນຕົວຈິງຄວນມີໂຄດສຳລັບສົ່ງອີເມລຫຼືບັນທຶກລົງຖານຂໍ້ມູນ

        // ຕັ້ງຄ່າຂໍ້ຄວາມແຈ້ງເຕືອນ
        setSession('flash', [
            'message' => 'ຂໍຂອບໃຈ! ພວກເຮົາໄດ້ຮັບຂໍ້ຄວາມຂອງທ່ານແລ້ວ.',
            'type' => 'success'
        ]);

        // Redirect ກັບໄປໜ້າຕິດຕໍ່
        $this->redirect('home/contact');
    }

    /**
     * ສະແດງຂໍ້ຜິດພາດ 404
     */
    public function notFound()
    {
        $this->response->setStatusCode(404);

        $this->view('error', [
            'error' => 'ບໍ່ພົບໜ້າທີ່ຕ້ອງການ',
            'message' => 'ໜ້າທີ່ທ່ານກຳລັງຊອກຫາອາດຈະຖືກຍ້າຍຫຼືລຶບອອກແລ້ວ.'
        ]);
    }
}
