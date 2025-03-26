<?php

class Request {
    private $data;
    private $files;
    
    public function __construct() {
        // JSON 요청 데이터 파싱
        $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        // 파일 업로드 데이터 처리
        $this->files = $_FILES;
    }
    
    /**
     * 요청 파라미터 존재 여부 확인
     * 
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return isset($this->data[$key]);
    }
    
    /**
     * 요청 데이터 가져오기
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * 모든 요청 데이터 가져오기
     * 
     * @return array
     */
    public function all() {
        return $this->data;
    }
    
    /**
     * 파일 데이터 가져오기
     * 
     * @param string $key
     * @return array|null
     */
    public function file($key) {
        return $this->files[$key] ?? null;
    }
    
    /**
     * 필수 필드 검증
     * 
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function validateRequired($fields) {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || empty($this->data[$field])) {
                throw new Exception("필수 필드가 누락되었습니다: {$field}");
            }
        }
        return true;
    }
    
    /**
     * 이메일 형식 검증
     * 
     * @param string $field
     * @return bool
     * @throws Exception
     */
    public function validateEmail($field) {
        if (!isset($this->data[$field]) || !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("올바른 이메일 형식이 아닙니다: {$field}");
        }
        return true;
    }
    
    /**
     * 문자열 길이 검증
     * 
     * @param string $field
     * @param int $min
     * @param int $max
     * @return bool
     * @throws Exception
     */
    public function validateLength($field, $min, $max) {
        if (!isset($this->data[$field])) {
            throw new Exception("필드가 존재하지 않습니다: {$field}");
        }
        
        $length = mb_strlen($this->data[$field], 'UTF-8');
        if ($length < $min || $length > $max) {
            throw new Exception("{$field}는 {$min}자 이상 {$max}자 이하여야 합니다.");
        }
        return true;
    }
    
    /**
     * 숫자 범위 검증
     * 
     * @param string $field
     * @param int $min
     * @param int $max
     * @return bool
     * @throws Exception
     */
    public function validateNumber($field, $min, $max) {
        if (!isset($this->data[$field]) || !is_numeric($this->data[$field])) {
            throw new Exception("올바른 숫자가 아닙니다: {$field}");
        }
        
        $value = (int)$this->data[$field];
        if ($value < $min || $value > $max) {
            throw new Exception("{$field}는 {$min} 이상 {$max} 이하여야 합니다.");
        }
        return true;
    }
    
    /**
     * XSS 방지를 위한 데이터 필터링
     * 
     * @param array $fields
     * @return array
     */
    public function sanitize($fields) {
        $sanitized = [];
        foreach ($fields as $field) {
            if (isset($this->data[$field])) {
                $sanitized[$field] = htmlspecialchars($this->data[$field], ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }
    
    /**
     * 요청 파라미터 값 설정
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }
} 