<?php
require_once 'config/config.php';
require_once 'common/Controller.php';
require_once 'controller/helloController.php';
require_once 'controller/signupController.php';

// Get the request URI and remove query strings
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove trailing slash if exists
$request_uri = rtrim($request_uri, '/');

// Split the URI into segments
$segments = explode('/', trim($request_uri, '/'));

// Check if this is a v1 API request
if (count($segments) >= 1 && $segments[0] === 'v1') {
    // Remove 'v1' from segments
    array_shift($segments);
    
    // Get controller name (default to 'hello' if not specified)
    $controller_name = !empty($segments[0]) ? $segments[0] : 'hello';
    array_shift($segments);
    
    // Get method name (default to 'default' if not specified)
    $method_name = !empty($segments[0]) ? $segments[0] : 'default';
    
    // Construct controller class name
    $controller_class = ucfirst($controller_name) . 'Controller';
    
    // Check if controller file exists
    $controller_file = "controller/{$controller_name}Controller.php";
    if (file_exists($controller_file)) {
        // Create controller instance
        $controller = new $controller_class();
        
        // Check if method exists
        if (method_exists($controller, $method_name)) {
            // Call the method
            $controller->$method_name();
            exit;
        }
    }
    
    // If we get here, either the controller or method doesn't exist
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'Not Found']);
    exit;
}

// If not a v1 API request, return 404
header('HTTP/1.1 404 Not Found');
echo json_encode(['error' => 'Not Found']); 