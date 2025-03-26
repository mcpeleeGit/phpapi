<?php

class UserService {
    private $db;
    private $logger;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../common/Logger.php';
        
        try {
            $this->db = Database::getInstance();
            $this->logger = Logger::getInstance();
        } catch (Exception $e) {
            $this->logger->error("UserService initialization failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('서비스 초기화 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 사용자 목록 조회
     * 
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUsers($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            // 전체 사용자 수 조회
            $countStmt = $this->db->query("SELECT COUNT(*) FROM users");
            $total = $countStmt->fetchColumn();
            
            // 사용자 목록 조회
            $stmt = $this->db->prepare("
                SELECT id, email, name, user_type, created_at, updated_at
                FROM users
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->execute([$limit, $offset]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'users' => $users
            ];
        } catch (PDOException $e) {
            $this->logger->error("Failed to get users", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('사용자 목록 조회 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 사용자 상세 조회
     * 
     * @param int $userId
     * @return array
     */
    public function getUser($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, user_type, created_at, updated_at
                FROM users
                WHERE id = ?
            ");
            
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new ValidationException('사용자를 찾을 수 없습니다.', 404, 'USER_NOT_FOUND');
            }
            
            return $user;
        } catch (PDOException $e) {
            $this->logger->error("Failed to get user", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('사용자 조회 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 사용자 정보 수정
     * 
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateUser($userId, $data) {
        try {
            // 사용자 존재 여부 확인
            $user = $this->getUser($userId);
            
            // 수정 가능한 필드
            $allowedFields = ['name', 'user_type'];
            $updateFields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updateFields[] = "{$key} = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($updateFields)) {
                throw new ValidationException('수정할 수 있는 필드가 없습니다.', 400, 'INVALID_UPDATE_FIELDS');
            }
            
            // updated_at 추가
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            // 사용자 정보 수정
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // 수정된 사용자 정보 조회
            return $this->getUser($userId);
        } catch (PDOException $e) {
            $this->logger->error("Failed to update user", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('사용자 정보 수정 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 사용자 삭제
     * 
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId) {
        try {
            // 사용자 존재 여부 확인
            $user = $this->getUser($userId);
            
            // 사용자 삭제
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            return true;
        } catch (PDOException $e) {
            $this->logger->error("Failed to delete user", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('사용자 삭제 중 오류가 발생했습니다.', 500);
        }
    }
} 