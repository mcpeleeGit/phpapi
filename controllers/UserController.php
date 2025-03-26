<?php

class UserController {
    private $userService;
    
    public function __construct() {
        require_once __DIR__ . '/../services/UserService.php';
        $this->userService = new UserService();
    }
    
    /**
     * 사용자 목록 조회 API
     */
    public function getUsers() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $result = $this->userService->getUsers($page, $limit);
        Response::success($result);
    }
    
    /**
     * 사용자 상세 조회 API
     */
    public function getUser() {
        $userId = (int)$_GET['id'];
        
        if ($userId <= 0) {
            throw new ValidationException('유효하지 않은 사용자 ID입니다.', 400, 'INVALID_USER_ID');
        }
        
        $user = $this->userService->getUser($userId);
        Response::success($user);
    }
    
    /**
     * 사용자 정보 수정 API
     */
    public function putUser() {
        $userId = (int)$_GET['id'];
        
        if ($userId <= 0) {
            throw new ValidationException('유효하지 않은 사용자 ID입니다.', 400, 'INVALID_USER_ID');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new ValidationException('요청 데이터가 올바르지 않습니다.', 400, 'INVALID_REQUEST_DATA');
        }
        
        $user = $this->userService->updateUser($userId, $data);
        Response::success($user);
    }
    
    /**
     * 사용자 삭제 API
     */
    public function deleteUser() {
        $userId = (int)$_GET['id'];
        
        if ($userId <= 0) {
            throw new ValidationException('유효하지 않은 사용자 ID입니다.', 400, 'INVALID_USER_ID');
        }
        
        $this->userService->deleteUser($userId);
        Response::success(['message' => '사용자가 삭제되었습니다.']);
    }
} 