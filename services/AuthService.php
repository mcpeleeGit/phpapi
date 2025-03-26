<?php

class AuthService {
    private $db;
    private $logger;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        require_once __DIR__ . '/../common/Logger.php';
        
        try {
            $this->db = Database::getInstance();
            $this->logger = Logger::getInstance();
        } catch (Exception $e) {
            $this->logger->error("AuthService initialization failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('서비스 초기화 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 회원가입 처리
     * 
     * @param SignupRequest $request
     * @return array
     * @throws Exception
     */
    public function signup(SignupRequest $request) {
        try {
            // 이메일 중복 체크
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$request->getEmail()]);
            if ($stmt->fetch()) {
                throw new ValidationException('이미 사용 중인 이메일입니다.', 400, 'SIGNUP_REQUEST_DUPLICATE_EMAIL');
            }
            
            // 비밀번호 해시화
            $hashedPassword = password_hash($request->getPassword(), PASSWORD_DEFAULT);
            
            // 사용자 등록
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, name)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $request->getEmail(),
                $hashedPassword,
                $request->getName()
            ]);
            
            $userId = $this->db->lastInsertId();
            
            return [
                'id' => $userId,
                'email' => $request->getEmail(),
                'name' => $request->getName()
            ];
        } catch (PDOException $e) {
            $this->logger->error("Signup failed", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0],
                'sql_message' => $e->errorInfo[2],
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('회원가입 처리 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * 로그인 처리
     * 
     * @param LoginRequest $request
     * @return array
     * @throws Exception
     */
    public function login(LoginRequest $request) {
        try {
            // 사용자 조회
            $stmt = $this->db->prepare("
                SELECT id, email, password, name
                FROM users
                WHERE email = ?
            ");
            
            $stmt->execute([$request->getEmail()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($request->getPassword(), $user['password'])) {
                throw new ValidationException('이메일 또는 비밀번호가 올바르지 않습니다.', 401, 'LOGIN_REQUEST_INVALID_CREDENTIALS');
            }
            
            // JWT 토큰 생성
            $token = $this->generateToken($user['id']);
            
            return [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ];
        } catch (PDOException $e) {
            $this->logger->error("Login failed", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0],
                'sql_message' => $e->errorInfo[2],
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('로그인 처리 중 오류가 발생했습니다.', 500);
        }
    }
    
    /**
     * JWT 토큰 생성
     * 
     * @param int $userId
     * @return string
     */
    private function generateToken($userId) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'exp' => time() + (60 * 60 * 24) // 24시간
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'your-secret-key', true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}