<?php

class Env {
    private static $instance = null;
    private $env = [];
    
    private function __construct() {
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->env[trim($key)] = trim($value);
                }
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key, $default = null) {
        return $this->env[$key] ?? $default;
    }
    
    public function getDbConfig() {
        return [
            'host' => $this->get('DB_HOST', 'localhost'),
            'port' => $this->get('DB_PORT', '3306'),
            'name' => $this->get('DB_NAME', 'giun'),
            'user' => $this->get('DB_USER', 'root'),
            'password' => $this->get('DB_PASSWORD', '')
        ];
    }
} 