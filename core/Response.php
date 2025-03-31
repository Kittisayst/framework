<?php

/**
 * Response Class
 * ຄລາສສຳລັບຈັດການການຕອບກັບ HTTP
 */
class Response
{
    protected $headers = [];
    protected $content = '';
    protected $statusCode = 200;
    protected $statusText = [
        200 => 'OK',
        201 => 'Created',
        301 => 'Moved Permanently',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
    }

    /**
     * ຕັ້ງຄ່າເນື້ອຫາຂອງການຕອບກັບ
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າລະຫັດສະຖານະ HTTP
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າ header
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * ຕັ້ງຄ່າຫຼາຍ headers ພ້ອມກັນ
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * ຕັ້ງຄ່າ Content-Type ເປັນ application/json
     */
    public function json()
    {
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        return $this;
    }

    /**
     * ຕັ້ງຄ່າ Content-Type ເປັນ text/plain
     */
    public function text()
    {
        $this->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        return $this;
    }

    /**
     * ສົ່ງການຕອບກັບໄປຍັງໄຄລເອັນ
     */
    public function send()
    {
        // ສົ່ງລະຫັດສະຖານະ
        $statusText = isset($this->statusText[$this->statusCode]) ? $this->statusText[$this->statusCode] : '';
        header("HTTP/1.1 {$this->statusCode} {$statusText}");

        // ສົ່ງ headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // ສົ່ງເນື້ອຫາ
        echo $this->content;
        exit;
    }

    /**
     * ສົ່ງຂໍ້ມູນ JSON
     */
    public function sendJson($data, $statusCode = null)
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }

        $this->json();
        $this->setContent(json_encode($data));
        $this->send();
    }

    /**
     * ເຮັດການ redirect ໄປຍັງ URL ໃໝ່
     */
    public function redirect($url, $statusCode = 302)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
    }

    /**
     * ໂຫຼດເທມເພລດແລະສົ່ງເປັນການຕອບກັບ
     */
    public function view($template, $data = [], $statusCode = 200)
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }

        $view = new View();
        $content = $view->render($template, $data);

        $this->setContent($content);
        $this->send();
    }

    /**
     * ສົ່ງໄຟລ໌ໃຫ້ດາວໂຫຼດ
     */
    public function download($filePath, $filename = null, $contentType = null)
    {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            $this->setContent('File not found');
            $this->send();
        }

        if ($filename === null) {
            $filename = basename($filePath);
        }

        if ($contentType === null) {
            $contentType = mime_content_type($filePath);
        }

        $filesize = filesize($filePath);

        $this->setHeader('Content-Type', $contentType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', $filesize);
        $this->setHeader('Pragma', 'public');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeader('Expires', '0');

        readfile($filePath);
        exit;
    }
}
