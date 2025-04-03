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
     * ເຮັດການ redirect ໄປຍັງ URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        $response = new Response();
        return $response->redirect($this->generateUrl($url));
    }

    /**
     * ເຮັດການ redirect ໄປຍັງເສັ້ນທາງພາຍໃນແອັບພລິເຄຊັນ
     */
    protected function redirectToRoute($route, $params = [], $statusCode = 302)
    {
        $url = $this->generateUrl($route, $params);
        $this->redirect($url, $statusCode);
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
                }
            }
        }

        return $errors;
    }
}
