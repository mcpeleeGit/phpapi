<?php

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/common/Router.php';

// Initialize and handle routing
$router = new Router();
$router->handle();