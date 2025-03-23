<?php
require_once 'common/Controller.php';

class HelloController extends Controller {
    public function default() {
        $this->hello();
    }
    
    public function hello() {
        echo json_encode([
            'message' => 'Hello from hello method!',
            'path' => '/v1/hello/hello'
        ], JSON_UNESCAPED_UNICODE);
    }
} 