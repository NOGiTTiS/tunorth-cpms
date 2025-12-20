<?php
class App {
    protected $controller = 'Home'; // หน้าแรกเริ่มต้น
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. ตรวจสอบ Controller
        if (isset($url[0])) {
            if (file_exists(__DIR__ . '/../controllers/' . ucfirst($url[0]) . '.php')) {
                $this->controller = ucfirst($url[0]);
                unset($url[0]);
            } else {
                // ถ้าใส่ URL มาแต่ไม่เจอไฟล์ Controller -> 404
                $this->controller = 'ErrorPage';
            }
        }

        require_once __DIR__ . '/../controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // 2. ตรวจสอบ Method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // ถ้ามี method แต่ใน class ไม่มี function นั้น -> 404 (หรือจะ redirect ก็ได้)
                // กรณีนี้เราจะเรียก index แทน หรือจะเด้งไป ErrorPage ก็ได้
                // แต่เพื่อความ clean ถ้า method ผิด ให้ถือว่าเป็น 404 ดีกว่า
                // อย่างไรก็ตาม logic เดิมคือ ignore แล้วเรียก index
                if ($this->controller instanceof ErrorPage) {
                   // ถ้าเป็น ErrorPage อยู่แล้วก็ปล่อย
                } else {
                    // ถ้าหา method ไม่เจอ ให้ redirect ไปหน้า 404
                    require_once __DIR__ . '/../controllers/ErrorPage.php';
                    $this->controller = new ErrorPage();
                    $this->method = 'index';
                }
            }
        }

        $this->params = $url ? array_values($url) : [];
        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (Throwable $e) {
            // Log Error (Optional)
            error_log($e->getMessage());
            
            // Show 500 Page
            http_response_code(500);
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}