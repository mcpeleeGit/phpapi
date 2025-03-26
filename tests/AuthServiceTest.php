<?php

use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase {
    private $authService;
    private $db;
    
    protected function setUp(): void {
        // 테스트용 데이터베이스 연결 설정
        require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance();
        
        // 테스트용 테이블 생성
        $this->createTestTables();
        
        // AuthService 인스턴스 생성
        require_once __DIR__ . '/../services/AuthService.php';
        $this->authService = new AuthService();
    }
    
    protected function tearDown(): void {
        // 테스트 후 테이블 삭제
        $this->dropTestTables();
    }
    
    private function createTestTables() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                user_type ENUM('USER', 'ADMIN') NOT NULL DEFAULT 'USER',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
    
    private function dropTestTables() {
        $this->db->exec("DROP TABLE IF EXISTS users");
    }
    
    public function testSignupSuccess() {
        $request = new SignupRequest();
        $request->setEmail('test@example.com');
        $request->setPassword('9301126666cd7eb7bf0c9021aba56352c24f1f6b29675fe4e3aef5a5852d4cbc');
        $request->setName('Test User');
        
        $result = $this->authService->signup($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('user_type', $result);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertEquals('USER', $result['user_type']);
    }
    
    public function testSignupDuplicateEmail() {
        // 먼저 사용자 생성
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, name, user_type)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'Test User',
            'USER'
        ]);
        
        // 중복 이메일로 회원가입 시도
        $request = new SignupRequest();
        $request->setEmail('test@example.com');
        $request->setPassword('9301126666cd7eb7bf0c9021aba56352c24f1f6b29675fe4e3aef5a5852d4cbc');
        $request->setName('Another User');
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('이미 사용 중인 이메일입니다.');
        
        $this->authService->signup($request);
    }
    
    public function testLoginSuccess() {
        // 테스트용 사용자 생성
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, name, user_type)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'test@example.com',
            '9301126666cd7eb7bf0c9021aba56352c24f1f6b29675fe4e3aef5a5852d4cbc',
            'Test User',
            'USER'
        ]);
        
        $request = new LoginRequest();
        $request->setEmail('test@example.com');
        $request->setPassword('9301126666cd7eb7bf0c9021aba56352c24f1f6b29675fe4e3aef5a5852d4cbc');
        
        $result = $this->authService->login($request);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('user_type', $result['user']);
        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertEquals('Test User', $result['user']['name']);
        $this->assertEquals('USER', $result['user']['user_type']);
    }
    
    public function testLoginInvalidCredentials() {
        $request = new LoginRequest();
        $request->setEmail('test@example.com');
        $request->setPassword('wrongpassword');
        
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('이메일 또는 비밀번호가 올바르지 않습니다.');
        
        $this->authService->login($request);
    }
} 