<?php

class LoginResponse {
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
            'token' => $this->result['token'],
            'user' => [
                'id' => $this->result['user']['id'],
                'email' => $this->result['user']['email'],
                'name' => $this->result['user']['name']
            ]
        ];
    }
    
    /**
     * 성공 메시지
     */
    private function getMessage() {
        return '로그인이 성공적으로 완료되었습니다.';
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