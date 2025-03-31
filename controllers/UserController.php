<?php

/**
 * User Controller
 * ຄລາສຄວບຄຸມສຳລັບຈັດການຜູ້ໃຊ້
 */
class UserController extends Controller
{
    protected $userModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->loadModel('User');
    }

    /**
     * ສະແດງລາຍການຜູ້ໃຊ້
     */
    public function index()
    {
        // ຮັບພາລາມິເຕີຄົ້ນຫາຖ້າມີ
        $search = $this->request->get('search');

        // ຮັບຂໍ້ມູນຜູ້ໃຊ້
        if ($search) {
            $users = $this->userModel->search($search);
            $pageTitle = "ຄົ້ນຫາຜູ້ໃຊ້: $search";
        } else {
            $users = $this->userModel->all();
            $pageTitle = "ລາຍຊື່ຜູ້ໃຊ້ທັງໝົດ";
        }

        // ສົ່ງຂໍ້ມູນໄປຍັງ view
        $this->view('user/index', [
            'users' => $users,
            'pageTitle' => $pageTitle,
            'search' => $search
        ]);
    }

    /**
     * ສະແດງລາຍລະອຽດຜູ້ໃຊ້
     */
    public function show($id)
    {
        // ຮັບຂໍ້ມູນຜູ້ໃຊ້ຕາມ ID
        $user = $this->userModel->find($id);

        // ຖ້າບໍ່ພົບຜູ້ໃຊ້
        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->view('error', [
                'message' => "ບໍ່ພົບຜູ້ໃຊ້ ID: $id"
            ]);
        }

        // ສົ່ງຂໍ້ມູນໄປຍັງ view
        $this->view('user/show', [
            'user' => $user,
            'pageTitle' => "ລາຍລະອຽດຜູ້ໃຊ້: {$user['name']}"
        ]);
    }

    /**
     * ສະແດງຟອມສ້າງຜູ້ໃຊ້ໃໝ່
     */
    public function create()
    {
        $this->view('user/create', [
            'pageTitle' => 'ສ້າງຜູ້ໃຊ້ໃໝ່'
        ]);
    }

    /**
     * ບັນທຶກຂໍ້ມູນຜູ້ໃຊ້ໃໝ່
     */
    public function store()
    {
        // ຮັບຂໍ້ມູນຈາກຟອມ
        $data = $this->getFormData();

        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ
        $errors = $this->validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // ຖ້າພົບຂໍ້ຜິດພາດ
        if (!empty($errors)) {
            return $this->view('user/create', [
                'pageTitle' => 'ສ້າງຜູ້ໃຊ້ໃໝ່',
                'errors' => $errors,
                'formData' => $data
            ]);
        }

        // ເຂົ້າລະຫັດລັບຜ່ານ
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // ບັນທຶກຂໍ້ມູນ
        $userId = $this->userModel->create($data);

        // ກວດສອບວ່າບັນທຶກສຳເລັດຫຼືບໍ່
        if ($userId) {
            // Redirect ໄປຍັງໜ້າລາຍລະອຽດຜູ້ໃຊ້
            $this->redirect($this->generateUrl("user/show/$userId"));
        } else {
            // ຖ້າບັນທຶກບໍ່ສຳເລັດ
            return $this->view('user/create', [
                'pageTitle' => 'ສ້າງຜູ້ໃຊ້ໃໝ່',
                'errors' => ['ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກຂໍ້ມູນ'],
                'formData' => $data
            ]);
        }
    }

    /**
     * ສະແດງຟອມແກ້ໄຂຜູ້ໃຊ້
     */
    public function edit($id)
    {
        // ຮັບຂໍ້ມູນຜູ້ໃຊ້ຕາມ ID
        $user = $this->userModel->find($id);

        // ຖ້າບໍ່ພົບຜູ້ໃຊ້
        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->view('error', [
                'message' => "ບໍ່ພົບຜູ້ໃຊ້ ID: $id"
            ]);
        }

        // ສົ່ງຂໍ້ມູນໄປຍັງ view
        $this->view('user/edit', [
            'user' => $user,
            'pageTitle' => "ແກ້ໄຂຜູ້ໃຊ້: {$user['name']}"
        ]);
    }

    /**
     * ອັບເດດຂໍ້ມູນຜູ້ໃຊ້
     */
    public function update($id)
    {
        // ຮັບຂໍ້ມູນຜູ້ໃຊ້ເດີມ
        $user = $this->userModel->find($id);

        // ຖ້າບໍ່ພົບຜູ້ໃຊ້
        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->view('error', [
                'message' => "ບໍ່ພົບຜູ້ໃຊ້ ID: $id"
            ]);
        }

        // ຮັບຂໍ້ມູນຈາກຟອມ
        $data = $this->getFormData();

        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email'
        ];

        // ຖ້າມີການອັບເດດລະຫັດຜ່ານ
        if (!empty($data['password'])) {
            $rules['password'] = 'min:6';
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // ຖ້າບໍ່ມີການອັບເດດລະຫັດຜ່ານ ໃຫ້ລຶບອອກຈາກຂໍ້ມູນທີ່ຈະອັບເດດ
            unset($data['password']);
        }

        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ
        $errors = $this->validate($data, $rules);

        // ຖ້າພົບຂໍ້ຜິດພາດ
        if (!empty($errors)) {
            return $this->view('user/edit', [
                'user' => array_merge($user, $data),
                'pageTitle' => "ແກ້ໄຂຜູ້ໃຊ້: {$user['name']}",
                'errors' => $errors
            ]);
        }

        // ອັບເດດຂໍ້ມູນ
        $result = $this->userModel->update($id, $data);

        // ກວດສອບວ່າອັບເດດສຳເລັດຫຼືບໍ່
        if ($result) {
            // Redirect ໄປຍັງໜ້າລາຍລະອຽດຜູ້ໃຊ້
            $this->redirect($this->generateUrl("user/show/$id"));
        } else {
            // ຖ້າອັບເດດບໍ່ສຳເລັດ
            return $this->view('user/edit', [
                'user' => array_merge($user, $data),
                'pageTitle' => "ແກ້ໄຂຜູ້ໃຊ້: {$user['name']}",
                'errors' => ['ເກີດຂໍ້ຜິດພາດໃນການອັບເດດຂໍ້ມູນ']
            ]);
        }
    }

    /**
     * ລຶບຜູ້ໃຊ້
     */
    public function delete($id)
    {
        // ກວດສອບວ່າມີຜູ້ໃຊ້ນີ້ຢູ່ຫຼືບໍ່
        $user = $this->userModel->find($id);

        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->view('error', [
                'message' => "ບໍ່ພົບຜູ້ໃຊ້ ID: $id"
            ]);
        }

        // ລຶບຜູ້ໃຊ້
        $result = $this->userModel->delete($id);

        // Redirect ກັບໄປໜ້າລາຍການຜູ້ໃຊ້
        $this->redirect($this->generateUrl('user'));
    }
}
