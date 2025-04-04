<?php

/**
 * View Class
 * ຄລາສສຳລັບຈັດການການສະແດງຜົນ
 */
class View
{
    protected $layout = 'main';
    protected $viewPath;
    protected $layoutPath;
    protected $data = [];
    protected $sections = [];
    protected $currentSection = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->viewPath = VIEWS_PATH;
        $this->layoutPath = VIEWS_PATH . '/layouts';
    }

    /**
     * ຕັ້ງຄ່າ layout ທີ່ຈະໃຊ້
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າເສັ້ນທາງສຳລັບໂຟລເດີ views
     */
    public function setViewPath($path)
    {
        $this->viewPath = $path;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າເສັ້ນທາງສຳລັບໂຟລເດີ layouts
     */
    public function setLayoutPath($path)
    {
        $this->layoutPath = $path;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າຂໍ້ມູນສຳລັບການສະແດງຜົນ
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * ກຳນົດຂໍ້ມູນຕົວແປດຽວ
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * ຮັບຄ່າຂອງຂໍ້ມູນ
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * ສະແດງເທມເພລດ
     */
    public function render($template, $data = [])
    {
        try {
            // ລວມຂໍ້ມູນ
            $this->data = array_merge($this->data, $data);

            // ເສັ້ນທາງເຕັມຂອງເທມເພລດ
            $templatePath = $this->resolvePath($template);

            if (!file_exists($templatePath)) {
                throw new Exception("ບໍ່ພົບເທມເພລດ: $template ($templatePath)");
            }

            // ເລີ່ມການບັນທຶກເອົາຜົນລັບ
            ob_start();

            // ແຍກຕົວແປອອກມາໃຫ້ສາມາດໃຊ້ໄດ້ໃນເທມເພລດ
            extract($this->data);

            // ໂຫຼດເທມເພລດແລະປະຕິບັດ
            require $templatePath;

            // ຮັບເນື້ອຫາຂອງເທມເພລດ
            $content = ob_get_clean();

            // ຖ້າມີການກຳນົດ layout ໃຫ້ໂຫຼດ layout ນັ້ນແລະໃສ່ເນື້ອຫາຂອງເທມເພລດເຂົ້າໄປ
            if ($this->layout !== null) {
                $layoutPath = $this->resolveLayoutPath($this->layout);

                if (!file_exists($layoutPath)) {
                    throw new Exception("ບໍ່ພົບໄຟລ໌ layout: {$this->layout} ($layoutPath)");
                }

                ob_start();
                extract($this->data);

                // ກຳນົດຕົວແປ $content ທີ່ຈະໃຊ້ໃນ layout
                $sections = $this->sections;

                require $layoutPath;

                $content = ob_get_clean();
            }

            return $content;
        } catch (Exception $e) {
            // ແຈ້ງເຕືອນຂໍ້ຜິດພາດ
            if (getenv('APP_DEBUG') === 'true') {
                echo '<div style="color: red; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;">';
                echo '<strong>View Error:</strong> ' . $e->getMessage();
                echo '</div>';
            }

            // ບັນທຶກຂໍ້ຜິດພາດ
            error_log('View Error: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * ສະແດງເທມເພລດແລະສົ່ງຜົນລັບໄປຍັງເບຣົວເຊີ
     */
    public function display($template, $data = [])
    {
        echo $this->render($template, $data);
    }

    /**
     * ຊອກຫາເສັ້ນທາງເຕັມຂອງເທມເພລດ
     */
    protected function resolvePath($template)
    {
        // ຖ້າເທມເພລດມີ .php ຕໍ່ທ້າຍແລ້ວ
        if (strpos($template, '.php') !== false) {
            return "{$this->viewPath}/" . $template;
        }

        // ຖ້າບໍ່ມີໃຫ້ເພີ່ມ .php ຕໍ່ທ້າຍ
        return "{$this->viewPath}/{$template}.php";
    }

    /**
     * ຊອກຫາເສັ້ນທາງເຕັມຂອງ layout
     */
    protected function resolveLayoutPath($layout)
    {
        // ຖ້າ layout ມີ .php ຕໍ່ທ້າຍແລ້ວ
        if (strpos($layout, '.php') !== false) {
            return "{$this->layoutPath}/" . $layout;
        }

        // ຖ້າບໍ່ມີໃຫ້ເພີ່ມ .php ຕໍ່ທ້າຍ
        return "{$this->layoutPath}/{$layout}.php";
    }

    /**
     * ສ້າງ URL ໂດຍໃຊ້ route
     * 
     * @param string $route ເສັ້ນທາງ URL
     * @param array $params ພາລາມິເຕີ query string
     * @return string
     */
    public static function url($route = '', $params = [])
    {
        $baseUrl = getenv('APP_URL') ?: 'http://localhost/framework';
        $url = rtrim($baseUrl, '/') . '/' . trim($route, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * ລວມເທມເພລດອື່ນເຂົ້າມາໃນເທມເພລດປັດຈຸບັດ
     */
    public function include($template, $data = [])
    {
        echo $this->render($template, $data);
    }

    /**
     * ເລີ່ມຕົ້ນສ່ວນ (section)
     */
    public function beginSection($name)
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * ຈົບສ່ວນ (section)
     */
    public function endSection()
    {
        if ($this->currentSection !== null) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    /**
     * ສະແດງເນື້ອຫາຂອງສ່ວນ (section)
     */
    public function section($name, $default = '')
    {
        echo isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }

    /**
     * ຂຽນພາບ HTML ໄປໃນຜົນລັບ
     */
    public function escape($html)
    {
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }

    /**
     * ສະແດງ component
     */
    public static function component($name, $data = [])
    {
        $view = new self();
        $view->setLayout(null);
        echo $view->render($name, $data);
    }

    /**
     * ກວດສອບວ່າມີ Flash message ຫຼືບໍ່
     */
    public static function hasFlash($type)
    {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * ເອົາ Flash message
     */
    public static function getFlash($type)
    {
        $message = $_SESSION['flash'][$type] ?? '';
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * ເອົາຄ່າ old input
     */
    public static function useOld($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    /**
     * ສະແດງຂໍ້ຄວາມ Flash
     */
    public static function useFlash()
    {
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;

        if ($flash) {
            echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">';
            echo $flash['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';

            unset($_SESSION['flash']);
        }
    }

    public static function route($name, $params = []) {
        global $router;
        return $router->route($name, $params);
    }
}
