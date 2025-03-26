<?php

return [
    // 허용된 IP 주소 목록
    'allowed_ips' => [
        '127.0.0.1',    // localhost
        '::1',          // localhost IPv6
        'unknown'
        // TODO: 실제 운영 환경의 IP 주소 추가
    ],
    
    // 허용된 User-Agent 목록
    'allowed_user_agents' => [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',
        'curl/8.7.1',
        'facebookexternalhit/1.1; kakaotalk-scrap/1.0; +https://devtalk.kakao.com/t/scrap/33984',
        // TODO: 실제 클라이언트의 User-Agent 추가
    ],
    
    // 보안 미들웨어 예외 경로
    'exclude_paths' => [
        '/healthcheck/hello'
    ],
    
    // 로그 파일 경로
    'log_file' => __DIR__ . '/../logs/security.log'
]; 