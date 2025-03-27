<?php

class BlogService {
    private $db;
    private $authService;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/AuthService.php';
        $this->db = Database::getInstance();
        $this->authService = new AuthService();
    }
    
    /**
     * 블로그 글 작성
     * 
     * @param WriteRequest $request
     * @return array
     * @throws Exception
     */
    public function write(WriteRequest $request) {
        // Authorization 헤더에서 토큰 추출
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception('인증 토큰이 필요합니다.', 401);
        }
        
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (empty($token)) {
            throw new Exception('유효하지 않은 인증 토큰입니다.', 401);
        }
        
        // 토큰 검증 및 user_id 추출
        $payload = $this->authService->validateToken($token);
        $userId = $payload['user_id'];
        
        // 블로그 글 작성
        $stmt = $this->db->prepare('INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)');
        $stmt->execute([
            $userId,
            $request->getTitle(),
            $request->getContent()
        ]);
        
        $postId = $this->db->lastInsertId();
        
        // 작성된 글 조회
        $stmt = $this->db->prepare('SELECT p.*, u.name as author_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'id' => $post['id'],
            'title' => $post['title'],
            'content' => $post['content'],
            'created_at' => $post['created_at'],
            'author' => [
                'id' => $post['user_id'],
                'name' => $post['author_name']
            ]
        ];
    }
    
    /**
     * 블로그 글 목록 조회
     * 
     * @return array
     */
    public function getList() {
        $stmt = $this->db->prepare('
            SELECT p.*, u.name as author_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC
        ');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 블로그 글 상세 조회
     * 
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getDetail($id) {
        $stmt = $this->db->prepare('
            SELECT p.*, u.name as author_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = ?
        ');
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            throw new Exception('게시글을 찾을 수 없습니다.', 404);
        }
        
        return [
            'id' => $post['id'],
            'title' => $post['title'],
            'content' => $post['content'],
            'created_at' => $post['created_at'],
            'author' => [
                'id' => $post['user_id'],
                'name' => $post['author_name']
            ]
        ];
    }
    
    /**
     * 현재 로그인한 사용자 ID 가져오기
     */
    private function getCurrentUserId() {
        // TODO: JWT 토큰에서 사용자 ID 추출
        return 1; // 임시로 1 반환
    }
    
    /**
     * 게시글 수정
     * 
     * @param int $id
     * @param UpdateRequest $request
     * @return array
     * @throws ValidationException
     */
    public function update($id, $request) {
        // Authorization 헤더에서 토큰 추출
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
        
        if (!$token) {
            throw new ValidationException('인증 토큰이 필요합니다.', 401, 'TOKEN_REQUIRED');
        }
        
        // 토큰 검증 및 user_id 추출
        $payload = $this->authService->validateToken($token);
        $userId = $payload['user_id'];
        
        // 게시글 존재 여부 및 권한 확인
        $stmt = $this->db->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            throw new ValidationException('존재하지 않는 게시글입니다.', 404, 'POST_NOT_FOUND');
        }
        
        if ($post['user_id'] != $userId) {
            throw new ValidationException('게시글 수정 권한이 없습니다.', 403, 'NO_PERMISSION');
        }
        
        // 게시글 수정
        $stmt = $this->db->prepare("
            UPDATE posts 
            SET title = ?, content = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $request->getTitle(),
            $request->getContent(),
            $id
        ]);
        
        // 수정된 게시글 조회
        return $this->getDetail($id);
    }
    
    /**
     * 블로그 글 삭제
     * 
     * @param int $postId
     * @return bool
     * @throws Exception
     */
    public function delete($postId) {
        // Authorization 헤더에서 토큰 추출
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception('인증 토큰이 필요합니다.', 401);
        }
        
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (empty($token)) {
            throw new Exception('유효하지 않은 인증 토큰입니다.', 401);
        }
        
        // 토큰 검증 및 user_id 추출
        $payload = $this->authService->validateToken($token);
        $userId = $payload['user_id'];
        
        // 게시물 존재 여부 및 권한 확인
        $stmt = $this->db->prepare('SELECT user_id FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            throw new Exception('게시물을 찾을 수 없습니다.', 404);
        }
        
        if ($post['user_id'] != $userId) {
            throw new Exception('게시물 삭제 권한이 없습니다.', 403);
        }
        
        // 게시물 삭제
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        
        return true;
    }
} 