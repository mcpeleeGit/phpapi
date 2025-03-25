<?php

class Response {
    /**
     * 성공 응답 전송
     * 
     * @param mixed $data 응답 데이터
     * @param string $message 성공 메시지
     * @param int $statusCode HTTP 상태 코드
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        self::send([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * 에러 응답 전송
     * 
     * @param string $message 에러 메시지
     * @param int $statusCode HTTP 상태 코드
     * @param mixed $errors 추가 에러 정보
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = null) {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        self::send($response, $statusCode);
    }
    
    /**
     * JSON 응답 전송
     * 
     * @param array $data 응답 데이터
     * @param int $statusCode HTTP 상태 코드
     */
    private static function send($data, $statusCode) {
        header('HTTP/1.1 ' . $statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
} 