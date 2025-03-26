<?php

class WriteRequest {
    private $request;
    private $title;
    private $content;
    
    /**
     * 생성자에서 바로 검증 수행
     * 
     * @throws ValidationException
     */
    public function __construct() {
        $this->request = new Request();
        $this->validate();
    }
    
    /**
     * 요청 데이터 검증
     * 
     * @throws ValidationException
     */
    private function validate() {
        // 필수 필드 검증
        if (!$this->request->has('title') || !$this->request->has('content')) {
            throw new ValidationException('필수 필드가 누락되었습니다.', 400, 'WRITE_REQUEST_MISSING_FIELDS');
        }
        
        // 제목 길이 검증
        if (strlen($this->request->get('title')) > 200) {
            throw new ValidationException('제목은 200자를 초과할 수 없습니다.', 400, 'WRITE_REQUEST_INVALID_TITLE_LENGTH');
        }
        
        // 내용 길이 검증
        if (strlen($this->request->get('content')) > 10000) {
            throw new ValidationException('내용은 10000자를 초과할 수 없습니다.', 400, 'WRITE_REQUEST_INVALID_CONTENT_LENGTH');
        }
        
        // XSS 방지를 위한 데이터 필터링
        $this->title = htmlspecialchars($this->request->get('title'));
        $this->content = htmlspecialchars($this->request->get('content'));
    }
    
    /**
     * 제목 가져오기
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * 내용 가져오기
     */
    public function getContent() {
        return $this->content;
    }
} 