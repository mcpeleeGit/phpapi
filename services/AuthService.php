<?php

class AuthService {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * 회원가입 처리
     * 
     * @param SignupRequest $request
     * @return array
     * @throws Exception
     */
    public function signup(SignupRequest $request) {
        // 이메일 중복 검사
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$request->getEmail()]);
        if ($stmt->fetch()) {
            throw new Exception('이미 사용 중인 이메일입니다.', 400);
        }
        
        // 비밀번호 해시화
        $hashedPassword = password_hash($request->getPassword(), PASSWORD_DEFAULT);
        
        // 사용자 생성
        $stmt = $this->db->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
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
    }
    
    /**
     * 로그인 처리
     * 
     * @param LoginRequest $request
     * @return array
     * @throws Exception
     */
    public function login(LoginRequest $request) {
        // 사용자 조회
        $stmt = $this->db->prepare('SELECT id, email, password, name FROM users WHERE email = ?');
        $stmt->execute([$request->getEmail()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($request->getPassword(), $user['password'])) {
            throw new Exception('이메일 또는 비밀번호가 일치하지 않습니다.', 401);
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
    }
    
    /**
     * JWT 토큰 생성
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