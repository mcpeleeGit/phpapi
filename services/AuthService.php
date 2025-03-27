<?php

require_once __DIR__ . '/../common/Logger.php';

class AuthService {
    private $db;
    private $logger;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        
        try {
            $this->db = Database::getInstance();
            $this->logger = Logger::getInstance();
        } catch (Exception $e) {
            // Logger가 초기화되지 않은 상태에서는 error_log를 사용
            error_log("AuthService initialization failed: " . $e->getMessage());
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
            
            // 비밀번호 해시 로깅
            $this->logger->info("Signup password hash", [
                'email' => $request->getEmail(),
                'original_password' => $request->getPassword(),
                'hashed_password' => $hashedPassword
            ]);
            
            // 사용자 등록
            $stmt = $this->db->prepare("
                INSERT INTO users (email, password, name, user_type)
                VALUES (?, ?, ?, 'USER')
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
                'name' => $request->getName(),
                'user_type' => 'USER'
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
                SELECT id, email, password, name, user_type
                FROM users
                WHERE email = ?
            ");
            
            $stmt->execute([$request->getEmail()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new ValidationException('이메일 또는 비밀번호가 올바르지 않습니다.', 401, 'LOGIN_REQUEST_INVALID_CREDENTIALS');
            }
            
            // 비밀번호 검증 로깅
            $this->logger->info("Login password verification", [
                'email' => $request->getEmail(),
                'login_password' => $request->getPassword(),
                'stored_hashed_password' => $user['password'],
                'password_verify_result' => password_verify($request->getPassword(), $user['password']),
                'password_length' => strlen($request->getPassword()),
                'stored_hash_length' => strlen($user['password']),
                'password_info' => password_get_info($user['password'])
            ]);
            
            // 프론트엔드에서 받은 SHA-256 해시를 bcrypt로 다시 해시
            if (!password_verify($request->getPassword(), $user['password'])) {
                throw new ValidationException('이메일 또는 비밀번호가 올바르지 않습니다.', 401, 'LOGIN_REQUEST_INVALID_CREDENTIALS');
            }
            
            // JWT 토큰 생성
            $token = $this->generateToken($user['id']);
            
            return [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'user_type' => $user['user_type']
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
    
    /**
     * JWT 토큰 검증 및 디코딩
     * 
     * @param string $token
     * @return array
     * @throws Exception
     */
    public function validateToken($token) {
        try {
            $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[0])), true);
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[1])), true);
            
            // 토큰 만료 확인
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new Exception('만료된 토큰입니다.', 401);
            }
            
            // 서명 검증
            $headerJson = json_encode($header);
            $payloadJson = json_encode($payload);
            
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($headerJson));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
            
            $signature = hash_hmac('sha256', 
                $base64UrlHeader . "." . $base64UrlPayload, 
                'your-secret-key', 
                true
            );
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            if ($base64UrlSignature !== explode('.', $token)[2]) {
                throw new Exception('유효하지 않은 토큰입니다.', 401);
            }
            
            return $payload;
        } catch (Exception $e) {
            $this->logger->error("Token validation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('토큰 검증에 실패했습니다.', 401);
        }
    }
}