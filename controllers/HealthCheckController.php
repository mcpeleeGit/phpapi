<?php

class HealthCheckController {
    /**
     * 헬스 체크 엔드포인트
     */
    public function getHello() {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Hello from HealthCheck API!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
} 