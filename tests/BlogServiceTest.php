<?php

use PHPUnit\Framework\TestCase;

class BlogServiceTest extends TestCase {
    private $blogService;
    private $db;
    
    protected function setUp(): void {
        // 테스트용 데이터베이스 연결 설정
        require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance();
        
        // 테스트용 테이블 생성
        $this->createTestTables();
        
        // BlogService 인스턴스 생성
        require_once __DIR__ . '/../services/BlogService.php';
        $this->blogService = new BlogService();
    }
    
    protected function tearDown(): void {
        // 테스트 후 테이블 삭제
        $this->dropTestTables();
    }
    
    private function createTestTables() {
        // users 테이블 생성
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
        
        // posts 테이블 생성
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
    }
    
    private function dropTestTables() {
        $this->db->exec("DROP TABLE IF EXISTS posts");
        $this->db->exec("DROP TABLE IF EXISTS users");
    }
    
    private function createTestUser() {
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
        
        return $this->db->lastInsertId();
    }
    
    public function testWriteSuccess() {
        $userId = $this->createTestUser();
        
        $request = new WriteRequest();
        $request->setTitle('Test Post');
        $request->setContent('This is a test post content.');
        
        $result = $this->blogService->write($request, $userId);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertEquals('Test Post', $result['title']);
        $this->assertEquals('This is a test post content.', $result['content']);
        $this->assertEquals($userId, $result['author']['id']);
    }
    
    public function testListSuccess() {
        $userId = $this->createTestUser();
        
        // 테스트용 게시글 생성
        $stmt = $this->db->prepare("
            INSERT INTO posts (user_id, title, content)
            VALUES (?, ?, ?)
        ");
        
        for ($i = 1; $i <= 5; $i++) {
            $stmt->execute([
                $userId,
                "Test Post {$i}",
                "This is test post content {$i}."
            ]);
        }
        
        $result = $this->blogService->list();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('posts', $result);
        $this->assertCount(5, $result['posts']);
        
        foreach ($result['posts'] as $post) {
            $this->assertArrayHasKey('id', $post);
            $this->assertArrayHasKey('title', $post);
            $this->assertArrayHasKey('content', $post);
            $this->assertArrayHasKey('created_at', $post);
            $this->assertArrayHasKey('author', $post);
        }
    }
    
    public function testDetailSuccess() {
        $userId = $this->createTestUser();
        
        // 테스트용 게시글 생성
        $stmt = $this->db->prepare("
            INSERT INTO posts (user_id, title, content)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            'Test Post',
            'This is test post content.'
        ]);
        
        $postId = $this->db->lastInsertId();
        
        $result = $this->blogService->detail($postId);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertEquals($postId, $result['id']);
        $this->assertEquals('Test Post', $result['title']);
        $this->assertEquals('This is test post content.', $result['content']);
        $this->assertEquals($userId, $result['author']['id']);
    }
    
    public function testDetailNotFound() {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('게시글을 찾을 수 없습니다.');
        
        $this->blogService->detail(999);
    }
} 