<?php

class Router {
    private $authMiddleware;
    private $securityMiddleware;
    
    public function __construct() {
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        require_once __DIR__ . '/../middleware/SecurityMiddleware.php';
        require_once __DIR__ . '/Response.php';
        require_once __DIR__ . '/Request.php';
        require_once __DIR__ . '/ValidationException.php';
        
        $this->authMiddleware = new AuthMiddleware();
        $this->securityMiddleware = new SecurityMiddleware();
    }
    
    /**
     * 라우팅 처리
     */
    public function handle() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/');
        
        // 보안 미들웨어 실행
        if (!$this->securityMiddleware->before()) {
            return;
        }
        
        // 인증 미들웨어 실행
        if (!$this->authMiddleware->before()) {
            return;
        }
        
        // URL 패턴에서 컨트롤러와 메소드 추출
        $segments = explode('/', trim($path, '/'));
        if (count($segments) < 2) {
            $this->sendNotFound('Invalid URL pattern');
            return;
        }
        
        $controllerName = ucfirst($segments[0]) . 'Controller';
        $methodName = strtolower($method) . ucfirst($segments[1]);
        
        // 컨트롤러 파일 자동 로드
        $controllerFile = $this->getControllerFile($controllerName);
        if (!file_exists($controllerFile)) {
            $this->sendNotFound("Controller file not found: {$controllerName}");
            return;
        }
        
        require_once $controllerFile;
        
        // 컨트롤러 인스턴스 생성 및 메소드 호출
        $controller = new $controllerName();
        
        if (!method_exists($controller, $methodName)) {
            $this->sendNotFound("Method not found: {$methodName} in {$controllerName}");
            return;
        }
        
        try {
            $controller->$methodName();
        } catch (ValidationException $e) {
            Response::error(
                $e->getMessage(),
                $e->getCode(),
                ['error_code' => $e->getErrorCode()]
            );
        } catch (Exception $e) {
            Response::error(
                $e->getMessage(),
                $e->getCode() ?: 500,
                ['error_code' => 'INTERNAL_SERVER_ERROR']
            );
        }
        
        // 미들웨어 after 메소드 실행
        $this->securityMiddleware->after();
        $this->authMiddleware->after();
    }
    
    /**
     * 컨트롤러 파일 경로 가져오기
     * 
     * @param string $controllerName
     * @return string
     */
    private function getControllerFile($controllerName) {
        return __DIR__ . "/../controllers/{$controllerName}.php";
    }
    
    /**
     * 404 Not Found 응답 전송
     * 
     * @param string $message
     */
    private function sendNotFound($message) {
        Response::error(
            $message,
            404,
            ['error_code' => 'NOT_FOUND']
        );
    }
} 