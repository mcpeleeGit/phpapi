<?php

// Load utility classes
require_once __DIR__ . '/../common/String.php';

// Error reporting settings
// E_ALL: 모든 에러와 경고를 표시
// E_ERROR: 치명적인 실행 시 에러
// E_WARNING: 실행 시 경고
// E_PARSE: 컴파일 시 파싱 에러
// E_NOTICE: 실행 시 알림
// E_CORE_ERROR: PHP 코어에서 발생한 치명적인 에러
// E_CORE_WARNING: PHP 코어에서 발생한 경고
// E_COMPILE_ERROR: 컴파일 시 발생한 치명적인 에러
// E_COMPILE_WARNING: 컴파일 시 발생한 경고
// E_USER_ERROR: 사용자가 생성한 에러 메시지
// E_USER_WARNING: 사용자가 생성한 경고 메시지
// E_USER_NOTICE: 사용자가 생성한 알림 메시지
// E_STRICT: PHP가 코드의 최적화 및 상호운용성 제안
// E_RECOVERABLE_ERROR: 치명적이지 않은 에러
// E_DEPRECATED: 향후 버전에서 제거될 기능 사용 시 경고
// E_USER_DEPRECATED: 사용자가 생성한 deprecated 경고

// Set error reporting based on domain
if (StringUtil::is_localhost()) {
    // Development environment
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Production environment
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
    
    // Create logs directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
}

// Set default timezone
date_default_timezone_set('Asia/Seoul');

// Response headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 