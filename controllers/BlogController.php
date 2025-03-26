<?php

class BlogController {
    private $blogService;
    
    public function __construct() {
        require_once __DIR__ . '/../services/BlogService.php';
        require_once __DIR__ . '/../dto/Blog/WriteRequest.php';
        require_once __DIR__ . '/../dto/Blog/WriteResponse.php';
        require_once __DIR__ . '/../dto/Blog/ListResponse.php';
        require_once __DIR__ . '/../dto/Blog/DetailResponse.php';
        $this->blogService = new BlogService();
    }
    
    /**
     * 블로그 목록 조회
     */
    public function getList() {
        // 게시글 목록 조회
        $result = $this->blogService->getList();
        
        // 응답 DTO 생성 (생성자에서 자동으로 응답 전송)
        new ListResponse($result);
    }
    
    /**
     * 게시글 작성 API
     */
    public function postWrite() {
        // DTO 생성 시 자동으로 검증 수행
        $writeRequest = new WriteRequest();
        
        // 게시글 작성 처리
        $result = $this->blogService->write($writeRequest);
        
        // 응답 DTO 생성 (생성자에서 자동으로 응답 전송)
        new WriteResponse($result);
    }
    
    /**
     * 게시글 상세 조회 API
     */
    public function getDetail() {
        // 게시글 ID 가져오기
        $id = (int)$_GET['id'] ?? 0;
        
        // 게시글 상세 조회
        $result = $this->blogService->getDetail($id);
        
        // 응답 DTO 생성 (생성자에서 자동으로 응답 전송)
        new DetailResponse($result);
    }
} 