<?php

class AuthController {
    private $authService;
    
    public function __construct() {
        require_once __DIR__ . '/../services/AuthService.php';
        require_once __DIR__ . '/../dto/Auth/SignupRequest.php';
        require_once __DIR__ . '/../dto/Auth/LoginRequest.php';
        require_once __DIR__ . '/../dto/Auth/SignupResponse.php';
        require_once __DIR__ . '/../dto/Auth/LoginResponse.php';
        $this->authService = new AuthService();
    }
    
    /**
     * 회원가입 API
     */
    public function postSignup() {
        // DTO 생성 시 자동으로 검증 수행
        $signupRequest = new SignupRequest();
        
        // 회원가입 처리
        $result = $this->authService->signup($signupRequest);
        
        // 응답 DTO 생성 (생성자에서 자동으로 응답 전송)
        new SignupResponse($result);
    }
    
    /**
     * 로그인 API
     */
    public function postLogin() {
        // DTO 생성 시 자동으로 검증 수행
        $loginRequest = new LoginRequest();
        
        // 로그인 처리
        $result = $this->authService->login($loginRequest);
        
        // 응답 DTO 생성 (생성자에서 자동으로 응답 전송)
        new LoginResponse($result);
    }
} 