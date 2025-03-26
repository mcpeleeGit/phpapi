<?php

class DetailResponse {
    private $result;
    
    public function __construct(array $result) {
        $this->result = $result;
        
        // 생성자에서 바로 응답 전송
        $this->send();
    }
    
    /**
     * 응답 데이터 생성
     */
    private function toArray() {
        return [
            'id' => $this->result['id'],
            'title' => $this->result['title'],
            'content' => $this->result['content'],
            'created_at' => $this->result['created_at'],
            'author' => [
                'id' => $this->result['author']['id'],
                'name' => $this->result['author']['name']
            ]
        ];
    }
    
    /**
     * 성공 메시지
     */
    private function getMessage() {
        return '블로그 글을 성공적으로 조회했습니다.';
    }
    
    /**
     * 응답 전송
     */
    private function send() {
        Response::success(
            $this->toArray(),
            $this->getMessage()
        );
    }
} 