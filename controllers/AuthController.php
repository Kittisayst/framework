<?php
class AuthController extends Controller
{
    public function index()
    {
        $this->view->setLayout('blank');
        $this->view('auth/login');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // ບັນທຶກ old input
            $_SESSION['old'] = [
                'email' => $email
            ];

            if (empty($email) || empty($password)) {
                return $this->redirect('auth/login')->with('error', 'ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບຖ້ວນ');
            }

            // ທົດສອບການເຂົ້າສູ່ລະບົບ
            if ($email === 'admin@gmail.com' && $password === 'admin') {
                $_SESSION['user'] = [
                    'id' => 1,
                    'name' => 'Admin',
                    'email' => $email
                ];
                return $this->redirect('home')->with('success', 'ເຂົ້າສູ່ລະບົບສຳເລັດ');
            }

            return $this->redirect('auth/login')->with('error', 'ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ');
        }

        return $this->view('auth/login');
    }

    public function logout()
    {
        // ລຶບ session
        session_destroy();
        
        // ສະແດງຂໍ້ຄວາມ
        setFlash('ອອກຈາກລະບົບສຳເລັດແລ້ວ', 'info');
        
        // ກັບໄປໜ້າ login
        return $this->redirect('auth/login');
    }
}
