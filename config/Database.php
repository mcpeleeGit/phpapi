<?php

require_once dirname(__DIR__) . '/config/Env.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $env = Env::getInstance();
            $dbConfig = $env->getDbConfig();
            
            // 디버그 로깅
            error_log("Database connection attempt with config: " . json_encode($dbConfig));
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['name']
            );
            
            $this->connection = new PDO(
                $dsn,
                $dbConfig['user'],
                $dbConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // 연결 성공 로깅
            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception('데이터베이스 연결에 실패했습니다.', 500);
        }
    }
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
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