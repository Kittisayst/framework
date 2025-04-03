<?php

/**
 * Auth Controller
 * ຄລາສຄວບຄຸມສຳລັບການຈັດການເຂົ້າສູ່ລະບົບແລະຢືນຢັນຕົວຕົນ
 */
class AuthController extends Controller
{
    /**
     * ສະແດງຟອມເຂົ້າສູ່ລະບົບ
     */
    public function index()
    {
        // ກວດສອບວ່າໄດ້ເຂົ້າສູ່ລະບົບແລ້ວຫຼືບໍ່
        if (session('user_id')) {
            return $this->redirect('home');
        }

        // ຕັ້ງຄ່າ layout ເປັນ blank
        $this->view->setLayout('blank');

        // ສະແດງໜ້າ login
        $this->view('auth/login', [
            'pageTitle' => 'ເຂົ້າສູ່ລະບົບ'
        ]);
    }

    /**
     * ຮັບຂໍ້ມູນຈາກຟອມເຂົ້າສູ່ລະບົບແລະກວດສອບ
     */
    public function login()
    {
        // ຖ້າບໍ່ແມ່ນ POST request, ໃຫ້ກັບຄືນໄປໜ້າ login
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('auth');
        }

        // ຮັບຂໍ້ມູນຈາກຟອມ
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        // ກວດສອບວ່າປ້ອນຂໍ້ມູນຄົບຖ້ວນຫຼືບໍ່
        if (empty($email) || empty($password)) {
            return $this->redirectWithError('auth', 'ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບຖ້ວນ');
        }

        // ກວດສອບການເຂົ້າສູ່ລະບົບ
        if ($email === 'admin@example.com' && $password === 'admin') {
            setSession('user_id', 1);
            return $this->redirectWithSuccess('home', 'ເຂົ້າສູ່ລະບົບສຳເລັດ');
        }

        // ຖ້າຂໍ້ມູນບໍ່ຖືກຕ້ອງ
        return $this->redirectWithError('auth', 'ອີເມວຫຼືລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ');
    }

    /**
     * ອອກຈາກລະບົບ
     */
    public function logout()
    {
        // ລຶບຄຸກກີ້ຈື່ຈຳ
        setAppCookie('remember_token', '', time() - 3600, '/');

        // ລຶບຂໍ້ມູນ session ຂອງຜູ້ໃຊ້
        unsetSession('user_id');
        unsetSession('user_name');
        unsetSession('user_email');
        unsetSession('user_role');
        unsetSession('remember_token');

        // ຕັ້ງຄ່າຂໍ້ຄວາມແຈ້ງເຕືອນ
        setFlash('ອອກຈາກລະບົບສຳເລັດແລ້ວ', 'info');

        // ກັບໄປໜ້າ login
        return $this->redirect('auth');
    }

    /**
     * ລືມລະຫັດຜ່ານ
     */
    public function forgotPassword()
    {
        // ຕັ້ງຄ່າ layout ເປັນ blank
        $this->view->setLayout('blank');

        // ສະແດງໜ້າລືມລະຫັດຜ່ານ
        $this->view('auth/forgot_password', [
            'pageTitle' => 'ລືມລະຫັດຜ່ານ'
        ]);
    }

    /**
     * ຮັບຄຳຂໍລືມລະຫັດຜ່ານ
     */
    public function resetPassword()
    {
        // ກວດສອບວ່າເປັນຄຳຂໍແບບ POST ຫຼືບໍ່
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('auth/forgot-password');
        }

        // ຮັບອີເມວຈາກຟອມ
        $email = $this->request->post('email');

        // ບັນທຶກຂໍ້ມູນເກົ່າເພື່ອສະແດງຄືນຖ້າມີຂໍ້ຜິດພາດ
        setSession('old_input', [
            'email' => $email
        ]);

        // ກວດສອບວ່າປ້ອນອີເມວຫຼືບໍ່
        if (empty($email)) {
            setFlash('ກະລຸນາປ້ອນອີເມວຂອງທ່ານ', 'danger');
            return $this->redirect('auth/forgot-password');
        }

        // ກວດສອບວ່າອີເມວຖືກຕ້ອງຫຼືບໍ່
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('ກະລຸນາປ້ອນອີເມວທີ່ຖືກຕ້ອງ', 'danger');
            return $this->redirect('auth/forgot-password');
        }

        // ໃນລະບົບຈິງຄວນກວດສອບວ່າມີຜູ້ໃຊ້ນີ້ໃນລະບົບຫຼືບໍ່ ແລະສົ່ງອີເມວໄປໃຫ້ຜູ້ໃຊ້

        // ຕັ້ງຄ່າຂໍ້ຄວາມແຈ້ງເຕືອນ
        setFlash('ຄຳແນະນຳການຣີເຊັດລະຫັດຜ່ານໄດ້ຖືກສົ່ງໄປຍັງອີເມວຂອງທ່ານແລ້ວ', 'success');
        return $this->redirect('auth');
    }

    /**
     * ສະແດງໜ້າລົງທະບຽນ
     */
    public function register()
    {
        // ຕັ້ງຄ່າ layout ເປັນ blank
        $this->view->setLayout('blank');

        // ສະແດງໜ້າລົງທະບຽນ
        $this->view('auth/register', [
            'pageTitle' => 'ລົງທະບຽນ'
        ]);
    }

    /**
     * ຮັບຂໍ້ມູນຈາກຟອມລົງທະບຽນ
     */
    public function store()
    {
        // ກວດສອບວ່າເປັນຄຳຂໍແບບ POST ຫຼືບໍ່
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('auth/register');
        }

        // ຮັບຂໍ້ມູນຈາກຟອມ
        $data = $this->getFormData();

        // ບັນທຶກຂໍ້ມູນເກົ່າເພື່ອສະແດງຄືນຖ້າມີຂໍ້ຜິດພາດ
        setSession('old_input', $data);

        // ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required'
        ];

        $errors = $this->validate($data, $rules);

        // ກວດສອບວ່າລະຫັດຜ່ານກົງກັນຫຼືບໍ່
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'][] = 'ລະຫັດຜ່ານບໍ່ກົງກັນ';
        }

        // ຖ້າມີຂໍ້ຜິດພາດ
        if (!empty($errors)) {
            setSession('errors', $errors);
            return $this->redirect('auth/register');
        }

        // ໃນລະບົບຈິງຄວນປະຕິບັດການລົງທະບຽນຜູ້ໃຊ້ໃໝ່

        // ຕັ້ງຄ່າຂໍ້ຄວາມແຈ້ງເຕືອນ
        setFlash('ລົງທະບຽນສຳເລັດ. ກະລຸນາເຂົ້າສູ່ລະບົບ', 'success');
        return $this->redirect('auth');
    }
}
