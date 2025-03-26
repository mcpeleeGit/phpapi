<?php

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                'mysql:host=localhost:3306;dbname=giun;charset=utf8mb4',
                'root',
                'test123',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception('데이터베이스 연결에 실패했습니다: ' . $e->getMessage());
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