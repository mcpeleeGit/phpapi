<?php

class ValidationException extends Exception {
    private $errorCode;
    
    public function __construct(string $message, int $code, string $errorCode) {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
    }
    
    public function getErrorCode(): string {
        return $this->errorCode;
    }
} 