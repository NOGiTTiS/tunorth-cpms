<?php
class ErrorPage extends Controller {
    public function index() {
        http_response_code(404);
        $this->view('errors/404');
    }

    public function server_error() {
        http_response_code(500);
        $this->view('errors/500');
    }
}
