<?php

class DeleteRequest {
    private $id;
    
    public function __construct() {
        // JSON 데이터 파싱
        $jsonData = json_decode(file_get_contents('php://input'), true);
        
        // 필수 필드 검증
        if (!isset($jsonData['id'])) {
            throw new ValidationException('게시글 ID는 필수입니다.', 400, 'REQUIRED_POST_ID');
        }
        
        // ID 유효성 검증
        $this->id = (int)$jsonData['id'];
        if ($this->id <= 0) {
            throw new ValidationException('유효하지 않은 게시글 ID입니다.', 400, 'INVALID_POST_ID');
        }
    }
    
    public function getId() {
        return $this->id;
    }
} 