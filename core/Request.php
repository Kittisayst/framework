<?php

/**
 * Request Class
 * ຄລາສສຳລັບຈັດການຄຳຂໍ HTTP
 */
class Request
{
    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $server = [];
    protected $cookies = [];
    protected $method = 'GET';
    protected $uri = '';
    protected $url = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;

        // ຮັບເອົາວິທີການ HTTP
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        // ຮັບເອົາ URI ຂອງຄຳຂໍ
        $this->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        // ແຍກ URL ອອກຈາກ URI
        $this->parseUrl();
    }

    /**
     * ແຍກ URL ອອກຈາກ URI
     */
    protected function parseUrl()
    {
        // ກວດສອບສຳລັບ URL ທີ່ສົ່ງຕໍ່ຈາກ mod_rewrite
        if (isset($_GET['url'])) {
            $this->url = $_GET['url'];
            unset($_GET['url']);
            return;
        }

        // ແຍກ URL ຈາກ REQUEST_URI
        $uri = $this->uri;

        // ລຶບ query string ຖ້າມີ
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // ລຶບພາດເບສຂອງແອັບພລິເຄຊັນ
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\') {
            $uri = str_replace($basePath, '', $uri);
        }

        // ລຶບເຄື່ອງໝາຍ / ບ່ອນທີ່ບໍ່ຕ້ອງການ
        $this->url = trim($uri, '/');
    }

    /**
     * ຮັບເອົາວິທີການ HTTP
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * ຮັບເອົາ URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * ຮັບເອົາຄ່າຈາກ GET
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }

        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    /**
     * ຮັບເອົາຄ່າຈາກ POST
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }

        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    /**
     * ຮັບເອົາຄ່າຈາກ GET ຫຼື POST
     */
    public function input($key, $default = null)
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $this->post($key);
        if ($value !== null) {
            return $value;
        }

        return $default;
    }

    /**
     * ຮັບເອົາທຸກຂໍ້ມູນທີ່ສົ່ງເຂົ້າມາ
     */
    public function all()
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * ກວດສອບວ່າມີຕົວແປນີ້ຫຼືບໍ່
     */
    public function has($key)
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    /**
     * ຮັບເອົາໄຟລ໌ທີ່ອັບໂຫຼດ
     */
    public function file($key = null)
    {
        if ($key === null) {
            return $this->files;
        }

        return isset($this->files[$key]) ? $this->files[$key] : null;
    }

    /**
     * ຮັບເອົາຄຸກກີ້
     */
    public function cookie($key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookies;
        }

        return isset($this->cookies[$key]) ? $this->cookies[$key] : $default;
    }

    /**
     * ກວດສອບວ່າເປັນການຮ້ອງຂໍແບບ AJAX ຫຼືບໍ່
     */
    public function isAjax()
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * ຮັບຄ່າ header
     */
    public function header($key = null)
    {
        $headers = getallheaders();

        if ($key === null) {
            return $headers;
        }

        foreach ($headers as $name => $value) {
            if (strtolower($name) === strtolower($key)) {
                return $value;
            }
        }

        return null;
    }
}
