<?php

class Logger {
    private static $instance = null;
    private $logFile;
    private $isLocalhost;
    
    private function __construct() {
        $this->isLocalhost = $_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1';
        
        if (!$this->isLocalhost) {
            $logDir = __DIR__ . '/../logs';
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $this->logFile = $logDir . '/' . date('Y-m-d') . '.log';
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 로그 파일에 메시지 기록
     * 
     * @param string $type 로그 타입 (error, info, debug 등)
     * @param string $message 로그 메시지
     * @param array $context 추가 컨텍스트 정보
     */
    public function log($type, $message, $context = []) {
        $output = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($type),
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        
        if ($this->isLocalhost) {
            // 로컬 환경에서는 error_log를 사용하여 로그 출력
            error_log($output);
        } else {
            // 로컬이 아닌 환경에서는 파일에 로그 기록
            file_put_contents($this->logFile, $output, FILE_APPEND);
        }
    }
    
    /**
     * 에러 로그 기록
     */
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    /**
     * 정보 로그 기록
     */
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    /**
     * 디버그 로그 기록
     */
    public function debug($message, $context = []) {
        $this->log('debug', $message, $context);
    }
    
    /**
     * 복제 방지
     */
    private function __clone() {}
    
    /**
     * 역직렬화 방지
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
} 