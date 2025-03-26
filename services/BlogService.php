<?php

class BlogService {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * 블로그 글 작성
     * 
     * @param WriteRequest $request
     * @return array
     * @throws Exception
     */
    public function write(WriteRequest $request) {
        // 현재 로그인한 사용자 ID 가져오기
        $userId = $this->getCurrentUserId();
        
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
} 