<?php

class SignupRequest {
    private $request;
    private $email;
    private $password;
    private $name;
    
    /**
     * 생성자에서 바로 검증 수행
     * 
     * @throws ValidationException
     */
    public function __construct() {
        $this->request = new Request();
        $this->validate();
    }
    
    /**
     * 요청 데이터 검증
     * 
     * @throws ValidationException
     */
    private function validate() {
        // 필수 필드 검증
        if (!$this->request->has('email') || !$this->request->has('password') || !$this->request->has('name')) {
            throw new ValidationException('필수 필드가 누락되었습니다.', 400, 'SIGNUP_REQUEST_MISSING_FIELDS');
        }
        
        // 이메일 형식 검증
        if (!filter_var($this->request->get('email'), FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('올바른 이메일 형식이 아닙니다.', 400, 'SIGNUP_REQUEST_INVALID_EMAIL');
        }
        
        // 비밀번호 길이 검증
        if (strlen($this->request->get('password')) < 8) {
            throw new ValidationException('비밀번호는 8자 이상이어야 합니다.', 400, 'SIGNUP_REQUEST_INVALID_PASSWORD');
        }
        
        // XSS 방지를 위한 데이터 필터링
        $this->email = htmlspecialchars($this->request->get('email'));
        $this->password = $this->request->get('password'); // 비밀번호는 필터링하지 않음
        $this->name = htmlspecialchars($this->request->get('name'));
    }
    
    /**
     * 이메일 가져오기
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * 비밀번호 가져오기
     */
    public function getPassword() {
        return $this->password;
    }
    
    /**
     * 이름 가져오기
     */
    public function getName() {
        return $this->name;
    }
} 