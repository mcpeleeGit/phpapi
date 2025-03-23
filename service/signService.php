<?php
class SignupService {
    public function signup($data) {
        // TODO: 실제 회원가입 로직 구현
        return [
            'success' => true,
            'message' => '회원가입이 완료되었습니다.',
            'data' => $data
        ];
    }
} 