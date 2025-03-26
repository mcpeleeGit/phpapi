<?php

class ListResponse {
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
            'posts' => array_map(function($post) {
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
            }, $this->result)
        ];
    }
    
    /**
     * 성공 메시지
     */
    private function getMessage() {
        return '블로그 목록을 성공적으로 조회했습니다.';
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