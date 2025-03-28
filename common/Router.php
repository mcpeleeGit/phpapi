<?php

class Router {
    private $authMiddleware;
    private $securityMiddleware;
    private $logger;
    
    public function __construct() {
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        require_once __DIR__ . '/../middleware/SecurityMiddleware.php';
        require_once __DIR__ . '/Response.php';
        require_once __DIR__ . '/Request.php';
        require_once __DIR__ . '/ValidationException.php';
        require_once __DIR__ . '/Logger.php';
        
        $this->authMiddleware = new AuthMiddleware();
        //$this->securityMiddleware = new SecurityMiddleware();
        $this->logger = Logger::getInstance();
    }
    
    /**
     * 라우팅 처리
     */
    public function handle() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = rtrim($path, '/');
            
            $this->logger->info("Request received", [
                'method' => $method,
                'path' => $path,
                'request_body' => file_get_contents('php://input')
            ]);
            
            // 보안 미들웨어 실행
            //if (!$this->securityMiddleware->before()) {
            //    return;
            //}
            
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
            
            $controller->$methodName();
            
        } catch (ValidationException $e) {
            $this->logger->error("Validation Error", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'error_code' => $e->getErrorCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'path' => $_SERVER['REQUEST_URI'],
                    'body' => file_get_contents('php://input'),
                    'headers' => getallheaders()
                ]
            ]);
            
            Response::error(
                $e->getMessage(),
                $e->getCode(),
                ['error_code' => $e->getErrorCode()]
            );
        } catch (PDOException $e) {
            $this->logger->error("Database Error", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0],
                'sql_message' => $e->errorInfo[2],
                'trace' => $e->getTraceAsString()
            ]);
            
            Response::error(
                '데이터베이스 처리 중 오류가 발생했습니다.',
                500,
                ['error_code' => 'DATABASE_ERROR']
            );
        } catch (Exception $e) {
            $this->logger->error("Server Error", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Response::error(
                '서버 처리 중 오류가 발생했습니다.',
                $e->getCode() ?: 500,
                ['error_code' => 'INTERNAL_SERVER_ERROR']
            );
        } finally {
            // 미들웨어 after 메소드 실행
            //$this->securityMiddleware->after();
            $this->authMiddleware->after();
        }
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
        $this->logger->error("Not Found", [
            'message' => $message,
            'request_data' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'path' => $_SERVER['REQUEST_URI'],
                'body' => file_get_contents('php://input'),
                'headers' => getallheaders()
            ]
        ]);
        
        Response::error(
            $message,
            404,
            ['error_code' => 'NOT_FOUND']
        );
    }
} 