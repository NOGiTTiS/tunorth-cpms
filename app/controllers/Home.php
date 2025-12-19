<?php
class Home extends Controller {
    public function index() {
        // ถ้าล็อกอินแล้วไป Dashboard ถ้าไม่ล็อกอินไป Auth
        if(isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
        } else {
            header('Location: /auth');
        }
    }
}