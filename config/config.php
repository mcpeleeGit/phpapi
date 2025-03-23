<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Asia/Seoul');

// API Configuration
define('API_VERSION', 'v1');
define('DEFAULT_CONTROLLER', 'hello');
define('DEFAULT_METHOD', 'default'); 