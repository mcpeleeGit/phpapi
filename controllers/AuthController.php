<?php

class AuthController {
    private $authService;
    private $request;
    
    public function __construct() {
        require_once __DIR__ . '/../services/AuthService.php';
        $this->authService = new AuthService();
        $this->request = new Request();
    }
    
    /**
     * 회원가입 API
     */
    public function postSignup() {
        try {
            // 필수 필드 검증
            $this->request->validateRequired(['email', 'password', 'name']);
            
            // 이메일 형식 검증
            $this->request->validateEmail('email');
            
            // 비밀번호 길이 검증
            $this->request->validateLength('password', 8, 20);
            
            // 이름 길이 검증
            $this->request->validateLength('name', 2, 50);
            
            // XSS 방지를 위한 데이터 필터링
            $data = $this->request->sanitize(['email', 'name']);
            
            // 회원가입 처리
            $result = $this->authService->signup(
                $data['email'],
                $this->request->get('password'), // 비밀번호는 필터링하지 않음
                $data['name']
            );
            
            Response::success(
                [
                    'id' => $result['id'],
                    'email' => $result['email'],
                    'name' => $result['name']
                ],
                '회원가입이 성공적으로 완료되었습니다.'
            );
        } catch (Exception $e) {
            Response::error(
                $e->getMessage(),
                400,
                ['error_code' => 'SIGNUP_ERROR']
            );
        }
    }
    
    /**
     * 로그인 API
     */
    public function postLogin() {
        try {
            // 필수 필드 검증
            $this->request->validateRequired(['email', 'password']);
            
            // 이메일 형식 검증
            $this->request->validateEmail('email');
            
            // 비밀번호 길이 검증
            $this->request->validateLength('password', 8, 20);
            
            // XSS 방지를 위한 데이터 필터링
            $data = $this->request->sanitize(['email']);
            
            // 로그인 처리
            $result = $this->authService->login(
                $data['email'],
                $this->request->get('password') // 비밀번호는 필터링하지 않음
            );
            
            Response::success(
                [
                    'token' => $result['token'],
                    'user' => [
                        'id' => $result['user']['id'],
                        'email' => $result['user']['email'],
                        'name' => $result['user']['name']
                    ]
                ],
                '로그인이 성공적으로 완료되었습니다.'
            );
        } catch (Exception $e) {
            Response::error(
                $e->getMessage(),
                401,
                ['error_code' => 'LOGIN_ERROR']
            );
        }
    }
} 