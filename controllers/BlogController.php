<?php

class BlogController {
    private $request;
    
    public function __construct() {
        $this->request = new Request();
    }
    
    /**
     * 블로그 목록 조회
     */
    public function getList() {
        // TODO: 실제 데이터베이스에서 블로그 목록 조회
        $blogs = [
            [
                'id' => 1,
                'title' => '첫 번째 블로그 포스트',
                'content' => '블로그 내용입니다...',
                'author' => '홍길동',
                'created_at' => '2024-03-21 10:00:00'
            ],
            [
                'id' => 2,
                'title' => '두 번째 블로그 포스트',
                'content' => '다른 블로그 내용입니다...',
                'author' => '김철수',
                'created_at' => '2024-03-21 11:00:00'
            ]
        ];
        
        Response::success([
            'blogs' => $blogs
        ], '블로그 목록을 성공적으로 조회했습니다.');
    }
    
    /**
     * 블로그 글쓰기
     */
    public function postWrite() {
        try {
            // 필수 필드 검증
            $this->request->validateRequired(['title', 'content']);
            
            // 문자열 길이 검증
            $this->request->validateLength('title', 2, 100);
            $this->request->validateLength('content', 10, 10000);
            
            // XSS 방지를 위한 데이터 필터링
            $data = $this->request->sanitize(['title', 'content']);
            
            // TODO: 실제 데이터베이스에 블로그 포스트 저장
            $blogPost = [
                'id' => 3, // 임시 ID
                'title' => $data['title'],
                'content' => $data['content'],
                'author' => '작성자', // TODO: 실제 로그인한 사용자 정보 사용
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            Response::success(
                ['blog' => $blogPost],
                '블로그 포스트가 성공적으로 작성되었습니다.'
            );
        } catch (Exception $e) {
            Response::error(
                $e->getMessage(),
                400,
                ['error_code' => 'VALIDATION_ERROR']
            );
        }
    }
} 