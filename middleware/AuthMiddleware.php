<?php

class AuthMiddleware {
    private $excludePaths = [
        '/health-check/hello',
        '/auth/signup',
        '/auth/login',
        '/blog/list',
        '/blog/detail'
    ];
    
    /**
     * 요청 전 처리
     * 
     * @return bool
     */
    public function before() {
        // 현재 요청 경로 확인
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $currentPath = rtrim($currentPath, '/');
        
        // 예외 경로인 경우 인증 스킵
        if ($this->isExcludePath($currentPath)) {
            return true;
        }
        
        // Authorization 헤더 확인
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            $this->sendUnauthorized('No authorization token provided');
            return false;
        }
        
        // Bearer 토큰 추출
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (empty($token)) {
            $this->sendUnauthorized('Invalid authorization token');
            return false;
        }
        
        // TODO: 토큰 검증 로직 구현
        // $this->validateToken($token);
        
        return true;
    }
    
    /**
     * 요청 후 처리
     */
    public function after() {
        // 요청 후 처리 로직 (예: 로깅, 응답 수정 등)
    }
    
    /**
     * 예외 경로인지 확인
     * 
     * @param string $path
     * @return bool
     */
    private function isExcludePath($path) {
        foreach ($this->excludePaths as $excludePath) {
            // 와일드카드(*) 패턴 처리
            if (strpos($excludePath, '*') !== false) {
                $pattern = str_replace('*', '.*', $excludePath);
                $pattern = '#^' . $pattern . '$#';
                if (preg_match($pattern, $path)) {
                    return true;
                }
            } else if ($path === $excludePath) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 401 Unauthorized 응답 전송
     * 
     * @param string $message
     */
    private function sendUnauthorized($message) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message
        ]);
        exit;
    }
    
    /**
     * 토큰 검증
     * 
     * @param string $token
     * @return bool
     */
    private function validateToken($token) {
        // TODO: 실제 토큰 검증 로직 구현
        // 예: JWT 검증, 데이터베이스 조회 등
        return true;
    }
} 