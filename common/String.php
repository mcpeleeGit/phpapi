<?php

class StringUtil {
    /**
     * 현재 도메인이 localhost인지 확인
     * 
     * @return bool
     */
    public static function is_localhost() {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        return $domain === 'localhost' || strpos($domain, 'localhost:') === 0;
    }
    
    /**
     * 문자열의 첫 글자를 대문자로 변환
     * 
     * @param string $string
     * @return string
     */
    public static function ucfirst($string) {
        return ucfirst($string);
    }
    
    /**
     * 문자열의 첫 글자를 소문자로 변환
     * 
     * @param string $string
     * @return string
     */
    public static function lcfirst($string) {
        return lcfirst($string);
    }
    
    /**
     * 문자열의 모든 단어의 첫 글자를 대문자로 변환
     * 
     * @param string $string
     * @return string
     */
    public static function ucwords($string) {
        return ucwords($string);
    }
    
    /**
     * 문자열의 모든 문자를 대문자로 변환
     * 
     * @param string $string
     * @return string
     */
    public static function upper($string) {
        return strtoupper($string);
    }
    
    /**
     * 문자열의 모든 문자를 소문자로 변환
     * 
     * @param string $string
     * @return string
     */
    public static function lower($string) {
        return strtolower($string);
    }
    
    /**
     * 문자열의 시작 부분에서 지정된 문자열을 제거
     * 
     * @param string $string
     * @param string $prefix
     * @return string
     */
    public static function remove_prefix($string, $prefix) {
        if (strpos($string, $prefix) === 0) {
            return substr($string, strlen($prefix));
        }
        return $string;
    }
    
    /**
     * 문자열의 끝 부분에서 지정된 문자열을 제거
     * 
     * @param string $string
     * @param string $suffix
     * @return string
     */
    public static function remove_suffix($string, $suffix) {
        if (substr($string, -strlen($suffix)) === $suffix) {
            return substr($string, 0, -strlen($suffix));
        }
        return $string;
    }
    
    /**
     * 문자열이 지정된 문자열로 시작하는지 확인
     * 
     * @param string $string
     * @param string $prefix
     * @return bool
     */
    public static function starts_with($string, $prefix) {
        return strpos($string, $prefix) === 0;
    }
    
    /**
     * 문자열이 지정된 문자열로 끝나는지 확인
     * 
     * @param string $string
     * @param string $suffix
     * @return bool
     */
    public static function ends_with($string, $suffix) {
        return substr($string, -strlen($suffix)) === $suffix;
    }
    
    /**
     * 문자열에서 지정된 문자열을 다른 문자열로 대체
     * 
     * @param string $string
     * @param string $search
     * @param string $replace
     * @return string
     */
    public static function replace($string, $search, $replace) {
        return str_replace($search, $replace, $string);
    }
    
    /**
     * 문자열의 길이를 반환
     * 
     * @param string $string
     * @return int
     */
    public static function length($string) {
        return strlen($string);
    }
    
    /**
     * 문자열이 비어있는지 확인
     * 
     * @param string $string
     * @return bool
     */
    public static function is_empty($string) {
        return empty($string);
    }
    
    /**
     * 문자열이 null인지 확인
     * 
     * @param string|null $string
     * @return bool
     */
    public static function is_null($string) {
        return $string === null;
    }
} 