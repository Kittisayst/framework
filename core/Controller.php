<?php

/**
 * Base Controller Class
 * ຄລາສພື້ນຖານສຳລັບຄວບຄຸມທຸກຕົວ
 */
class Controller
{
    protected $app;
    protected $request;
    protected $response;
    protected $view;
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = App::getInstance();
        $this->request = $this->app->getRequest();
        $this->response = $this->app->getResponse();
        $this->view = new View();
        $this->db = Database::getInstance();
    }

    /**
     * ສະແດງເທມເພລດ
     */
    protected function render($template, $data = [])
    {
        return $this->view->render($template, $data);
    }

    /**
     * ສະແດງເທມເພລດແລະສົ່ງຜົນລັບໄປຍັງເບຣົວເຊີ
     */
    protected function view($template, $data = [], $statusCode = 200)
    {
        $this->response->setStatusCode($statusCode);
        $this->response->setContent($this->render($template, $data));
        $this->response->send();
    }

    /**
     * ສົ່ງຂໍ້ມູນ JSON
     */
    protected function json($data, $statusCode = 200)
    {
        $this->response->setStatusCode($statusCode);
        $this->response->json();
        $this->response->setContent(json_encode($data));
        $this->response->send();
    }

    /**
     * ສ້າງ URL ຈາກເສັ້ນທາງພາຍໃນແອັບພລິເຄຊັນ
     */
    protected function generateUrl($route, $params = [])
    {
        $baseUrl = getenv('APP_URL') ?: 'http://localhost/framework';
        $url = rtrim($baseUrl, '/') . '/' . trim($route, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * ເຮັດການ redirect ໄປຍັງ URL
     */
    protected function redirect($route, $statusCode = 302)
    {
        return $this->response->redirect($this->generateUrl($route), $statusCode);
    }

    /**
     * ເຮັດການ redirect ພ້ອມຂໍ້ຄວາມ flash
     */
    protected function redirectWith($route, $type, $message, $statusCode = 302)
    {
        return $this->response->with($type, $message)
                             ->redirect($this->generateUrl($route), $statusCode);
    }

    /**
     * ເຮັດການ redirect ພ້ອມຂໍ້ຄວາມ flash ສຳເລັດ
     */
    protected function redirectWithSuccess($route, $message, $statusCode = 302)
    {
        return $this->redirectWith($route, 'success', $message, $statusCode);
    }

    /**
     * ເຮັດການ redirect ພ້ອມຂໍ້ຄວາມ flash ຂໍ້ມູນ
     */
    protected function redirectWithInfo($route, $message, $statusCode = 302)
    {
        return $this->redirectWith($route, 'info', $message, $statusCode);
    }

    /**
     * ເຮັດການ redirect ພ້ອມຂໍ້ຄວາມ flash ເຕືອນ
     */
    protected function redirectWithWarning($route, $message, $statusCode = 302)
    {
        return $this->redirectWith($route, 'warning', $message, $statusCode);
    }

    /**
     * ເຮັດການ redirect ພ້ອມຂໍ້ຄວາມ flash ຜິດພາດ
     */
    protected function redirectWithError($route, $message, $statusCode = 302)
    {
        return $this->redirectWith($route, 'danger', $message, $statusCode);
    }

    /**
     * ໂຫຼດໂມເດລທີ່ຕ້ອງການ
     */
    protected function loadModel($modelName)
    {
        $modelClass = ucfirst($modelName) . 'Model';
        $modelFile = MODELS_PATH . '/' . $modelClass . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;

            if (class_exists($modelClass)) {
                return new $modelClass();
            }
        }

        throw new Exception("ບໍ່ພົບໂມເດລ: $modelName");
    }

    /**
     * ຮັບຂໍ້ມູນຈາກແບບຟອມທີ່ສົ່ງເຂົ້າມາ
     */
    protected function getFormData()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->request->post();
        }

        return [];
    }

    /**
     * ກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນແບບຟອມ
     */
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            // ແຍກຄວາມຕ້ອງການຕ່າງໆອອກເປັນຊຸດ
            $requirements = explode('|', $rule);

            foreach ($requirements as $requirement) {
                // ແຍກຄວາມຕ້ອງການແລະຄ່າພາລາມິເຕີ
                $parts = explode(':', $requirement);
                $ruleName = $parts[0];
                $ruleParam = isset($parts[1]) ? $parts[1] : null;

                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || trim($data[$field]) === '') {
                            $errors[$field][] = "$field ຕ້ອງຕື່ມຂໍ້ມູນ";
                        }
                        break;

                    case 'min':
                        if (isset($data[$field]) && strlen($data[$field]) < $ruleParam) {
                            $errors[$field][] = "$field ຕ້ອງມີຄວາມຍາວຢ່າງໜ້ອຍ $ruleParam ຕົວອັກສອນ";
                        }
                        break;

                    case 'max':
                        if (isset($data[$field]) && strlen($data[$field]) > $ruleParam) {
                            $errors[$field][] = "$field ຕ້ອງມີຄວາມຍາວບໍ່ເກີນ $ruleParam ຕົວອັກສອນ";
                        }
                        break;

                    case 'email':
                        if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "$field ຕ້ອງເປັນອີເມລທີ່ຖືກຕ້ອງ";
                        }
                        break;

                    case 'numeric':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field][] = "$field ຕ້ອງເປັນຕົວເລກເທົ່ານັ້ນ";
                        }
                        break;

                    case 'alpha':
                        if (isset($data[$field]) && !ctype_alpha($data[$field])) {
                            $errors[$field][] = "$field ຕ້ອງເປັນຕົວອັກສອນເທົ່ານັ້ນ";
                        }
                        break;

                    case 'alphanumeric':
                        if (isset($data[$field]) && !ctype_alnum($data[$field])) {
                            $errors[$field][] = "$field ຕ້ອງເປັນຕົວອັກສອນຫຼືຕົວເລກເທົ່ານັ້ນ";
                        }
                        break;

                    case 'match':
                        if (isset($data[$field]) && isset($data[$ruleParam]) && $data[$field] !== $data[$ruleParam]) {
                            $errors[$field][] = "$field ຕ້ອງກົງກັບ $ruleParam";
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * ເຮັດການ validate ຂໍ້ມູນຟອມ ແລະ ສົ່ງຄືນຄ່າຂໍ້ຜິດພາດຖ້າມີ
     * ຫຼືຂໍ້ມູນທີ່ຜ່ານການກວດສອບແລ້ວຖ້າບໍ່ມີຂໍ້ຜິດພາດ
     */
    protected function validateForm($rules, $redirectRoute = null)
    {
        // ຮັບຂໍ້ມູນຈາກຟອມ
        $data = $this->getFormData();
        
        // ບັນທຶກຂໍ້ມູນເຂົ້າ session ເພື່ອສະແດງຄືນໃນກໍລະນີທີ່ມີຂໍ້ຜິດພາດ
        setSession('old_input', $data);
        
        // ກວດສອບຂໍ້ມູນຕາມກົດທີ່ກຳນົດ
        $errors = $this->validate($data, $rules);
        
        // ຖ້າມີຂໍ້ຜິດພາດ ແລະ ມີການລະບຸ route ສຳລັບ redirect
        if (!empty($errors) && $redirectRoute !== null) {
            // ບັນທຶກຂໍ້ຜິດພາດເຂົ້າ session
            setSession('errors', $errors);
            
            // redirect ກັບໄປໜ້າຟອມພ້ອມກັບຂໍ້ຄວາມຜິດພາດ
            return $this->redirectWithError($redirectRoute, 'ກະລຸນາກວດສອບຂໍ້ມູນທີ່ປ້ອນ');
        }
        
        // ສົ່ງຄືນຂໍ້ຜິດພາດ ຫຼື ຂໍ້ມູນທີ່ຜ່ານການກວດສອບແລ້ວ
        return [
            'data' => $data,
            'errors' => $errors,
            'isValid' => empty($errors)
        ];
    }

    /**
     * ສົ່ງຄືນຂໍ້ຄວາມຕອບກັບແບບສຳເລັດ
     */
    protected function success($message, $data = [], $statusCode = 200)
    {
        return $this->response->sendJson([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * ສົ່ງຄືນຂໍ້ຄວາມຕອບກັບແບບຜິດພາດ
     */
    protected function error($message, $errors = [], $statusCode = 400)
    {
        return $this->response->sendJson([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}