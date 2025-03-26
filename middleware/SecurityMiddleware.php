<?php

class SecurityMiddleware {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/security.php';
        
        // 로그 디렉토리 생성
        $logDir = dirname($this->config['log_file']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * 요청 전 처리
     * 
     * @return bool
     */
    public function before() {
        // CORS 헤더 설정
        header('Access-Control-Allow-Origin: http://localhost:3000');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, x-security-token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // 24시간 동안 preflight 요청 캐시
        
        // OPTIONS 요청 처리 (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        // Content-Type 헤더 검증
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (!str_contains($contentType, 'application/json')) {
                Response::error('Content-Type must be application/json', 415);
                return false;
            }
        }
        
        // 현재 요청 경로 확인
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $currentPath = rtrim($currentPath, '/');
        
        // 예외 경로인 경우 보안 검사 스킵
        if ($this->isExcludePath($currentPath)) {
            return true;
        }
        
        // IP 주소 검증
        if (!$this->validateIp()) {
            $this->logSecurityEvent('IP', $this->getClientIp());
            $this->sendForbidden('Access denied: IP not allowed');
            return false;
        }
        
        // User-Agent 검증
        if (!$this->validateUserAgent()) {
            $this->logSecurityEvent('User-Agent', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
            $this->sendForbidden('Access denied: User-Agent not allowed');
            return false;
        }
        
        return true;
    }
    
    /**
     * 요청 후 처리
     */
    public function after() {
        // 요청 후 처리 로직 (예: 로깅, 응답 수정 등)
    }
    
    /**
     * IP 주소 검증
     * 
     * @return bool
     */
    private function validateIp() {
        $clientIp = $this->getClientIp();
        return in_array($clientIp, $this->config['allowed_ips']);
    }
    
    /**
     * User-Agent 검증
     * 
     * @return bool
     */
    private function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return in_array($userAgent, $this->config['allowed_user_agents']);
    }
    
    /**
     * 클라이언트 IP 주소 가져오기
     * 
     * @return string
     */
    private function getClientIp() {
        $ipaddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
            
        return $ipaddress;
    }
    
    /**
     * 예외 경로인지 확인
     * 
     * @param string $path
     * @return bool
     */
    private function isExcludePath($path) {
        return in_array($path, $this->config['exclude_paths']);
    }
    
    /**
     * 403 Forbidden 응답 전송
     * 
     * @param string $message
     */
    private function sendForbidden($message) {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Forbidden',
            'message' => $message
        ]);
        exit;
    }
    
    /**
     * 보안 이벤트 로깅
     * 
     * @param string $type 이벤트 타입
     * @param string $value 이벤트 값
     * @param string|null $message 추가 메시지
     */
    private function logSecurityEvent($type, $value, $message = null) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getClientIp();
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        $logMessage = sprintf(
            "[%s] %s: %s | IP: %s | Path: %s | Method: %s%s\n",
            $timestamp,
            $type,
            $value,
            $ip,
            $path,
            $method,
            $message ? " | Message: {$message}" : ""
        );
        
        file_put_contents(
            $this->config['log_file'],
            $logMessage,
            FILE_APPEND
        );
    }
} 