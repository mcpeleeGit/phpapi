<?php

class AuthService {
    private $users = []; // 임시 사용자 저장소
    
    /**
     * 회원가입 처리
     * 
     * @param string $email
     * @param string $password
     * @param string $name
     * @return array
     * @throws Exception
     */
    public function signup($email, $password, $name) {
        // 이메일 형식 검증
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // 비밀번호 복잡도 검증
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        
        // 이메일 중복 체크
        if (isset($this->users[$email])) {
            throw new Exception('Email already exists');
        }
        
        // 사용자 생성
        $user = [
            'id' => uniqid(),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // 임시 저장소에 저장
        $this->users[$email] = $user;
        
        // 비밀번호 제외하고 반환
        unset($user['password']);
        return $user;
    }
    
    /**
     * 로그인 처리
     * 
     * @param string $email
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function login($email, $password) {
        // 사용자 존재 여부 확인
        if (!isset($this->users[$email])) {
            throw new Exception('Invalid email or password');
        }
        
        $user = $this->users[$email];
        
        // 비밀번호 검증
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid email or password');
        }
        
        // TODO: 실제 JWT 토큰 생성 로직 구현
        $token = $this->generateToken($user);
        
        // 사용자 정보에서 비밀번호 제외
        unset($user['password']);
        
        return [
            'token' => $token,
            'user' => $user
        ];
    }
    
    /**
     * 임시 토큰 생성 (실제 구현에서는 JWT 사용)
     * 
     * @param array $user
     * @return string
     */
    private function generateToken($user) {
        // TODO: 실제 JWT 토큰 생성 로직으로 대체
        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24시간
        ];
        
        return base64_encode(json_encode($payload));
    }
}