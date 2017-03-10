<?php
class stock extends PHPUnit_Framework_TestCase {
    private function gen_sign($params) {
        // 私钥
        $token = '私钥';
        
        if (! $token) {
            return false;
        }
        return strtoupper ( md5 ( strtoupper ( md5 ( $this->assemble ( $params ) ) ) . $token ) );
    }
    private function assemble($params) {
        if (! is_array ( $params ))
            return null;
        ksort ( $params, SORT_STRING );
        $sign = '';
        foreach ( $params as $key => $val ) {
            if (is_null ( $val ))
                continue;
            if (is_bool ( $val ))
                $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array ( $val ) ? $this->assemble ( $val ) : $val);
        }
        return $sign;
    }
    Public function testGetAll() {
        $url = 'http://@domain@ /index.php/openapi/rpc/service/';
        
        $params = array (
                'product_bn' => '',
                'brand_name' => '',
                'type_name' => '',
                'type' => 'json',
                'flag' => 'superdata',
                'charset' => 'utf-8',
                'page_no' => 1,
                'page_size' => 100,
                'ver' => 1,
                'method' => 'stock.getAll' 
        );
        
        $sign = $this->gen_sign ( $params );
        $params ['sign'] = $this->gen_sign ( $params );
        
        foreach ( $params as $k => $v ) {
            $arg [] = urlencode ( $k );
            $arg [] = urlencode ( $v );
        }
        
        $http = kernel::single ( 'base_httpclient' );
        $response = $http->set_timeout ( $time_out )->post ( $url, $_POST, $headers );
    }
    public function testStock() {
        $this->testGetAll ();
    }
}