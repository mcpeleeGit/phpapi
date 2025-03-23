<?php
require_once 'common/Controller.php';
require_once 'service/signService.php';

class SignupController extends Controller {
    private $signupService;
    
    public function __construct() {
        parent::__construct();
        $this->signupService = new SignupService();
    }
    
    public function default() {
        $this->signup();
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // POST 요청 처리
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $this->signupService->signup($data);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            // GET 요청 처리
            echo json_encode([
                'message' => '회원가입 페이지입니다.',
                'method' => 'GET'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 