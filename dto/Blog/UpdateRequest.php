<?php

class UpdateRequest {
    private $id;
    private $title;
    private $content;
    
    public function __construct() {
        // JSON 데이터 파싱
        $jsonData = json_decode(file_get_contents('php://input'), true);
        
        // 필수 필드 검증
        if (!isset($jsonData['id'])) {
            throw new ValidationException('게시글 ID는 필수입니다.', 400, 'REQUIRED_POST_ID');
        }
        if (!isset($jsonData['title'])) {
            throw new ValidationException('제목은 필수입니다.', 400, 'REQUIRED_TITLE');
        }
        if (!isset($jsonData['content'])) {
            throw new ValidationException('내용은 필수입니다.', 400, 'REQUIRED_CONTENT');
        }
        
        // ID 유효성 검증
        $this->id = (int)$jsonData['id'];
        if ($this->id <= 0) {
            throw new ValidationException('유효하지 않은 게시글 ID입니다.', 400, 'INVALID_POST_ID');
        }
        
        // 제목 유효성 검증
        $this->title = trim($jsonData['title']);
        if (empty($this->title)) {
            throw new ValidationException('제목은 필수입니다.', 400, 'REQUIRED_TITLE');
        }
        if (mb_strlen($this->title) > 200) {
            throw new ValidationException('제목은 200자를 초과할 수 없습니다.', 400, 'TITLE_TOO_LONG');
        }
        
        // 내용 유효성 검증
        $this->content = trim($jsonData['content']);
        if (empty($this->content)) {
            throw new ValidationException('내용은 필수입니다.', 400, 'REQUIRED_CONTENT');
        }
        if (mb_strlen($this->content) > 10000) {
            throw new ValidationException('내용은 10000자를 초과할 수 없습니다.', 400, 'CONTENT_TOO_LONG');
        }
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getContent() {
        return $this->content;
    }
} 