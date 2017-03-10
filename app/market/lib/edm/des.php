<?php
class market_edm_des{

    var $edm_key = 'shopex_smsSec';
    var $sms_key = 'taoxh_33cf66ea1cb9894ac03cbc0e1d9d52c2';
    var $iv=0; //偏移量
	//13818243332
	
	function get_key( $key, $iv=0 ) {  
        //key长度8例如:1234abcd
        $key = $this->make_key($key);
        $this->key = $key;
        if( $iv == 0 ) {
            $this->iv = $key; //默认以$key 作为 iv  
        } else {  
            $this->iv = $iv; //mcrypt_create_iv ( mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM );  
        }
    }

    function make_key($tmpKey = '') {
        return substr(md5($tmpKey),5,8);
    }

    function str_extend($str = '') {
        return $str.'_'.time();
    }

    function encrypt($str) {  
	
		$this->get_key($this->edm_key);
	
        //加密，返回大写十六进制字符串
        $size = mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC );  
        $str = $this->pkcs5Pad ( $this->str_extend($str), $size );
		//echo('key--'.$this->key);
        return strtoupper( bin2hex( mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv ) ) );
    }

    function decrypt($str) {  
	
		$this->get_key($this->sms_key);
        //解密  
        $strBin = $this->hex2bin( strtolower( $str ) );		
        $str = mcrypt_cbc( MCRYPT_DES, $this->key, $strBin, MCRYPT_DECRYPT, $this->iv );
        $str = $this->pkcs5Unpad( $str );
        $str = explode('_',$str);
        array_pop($str);
        $str = implode("_",$str);
        return $str;
    }

    function hex2bin($hexData) {  
        $binData = "";  
        for($i = 0; $i  < strlen ( $hexData ); $i += 2) {  
            $binData .= chr ( hexdec ( substr ( $hexData, $i, 2 ) ) );  
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );  
        if ($pad > strlen ( $text )) return false;

        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)   return false;  

        return substr ( $text, 0, - 1 * $pad );
    }

	/*
    function make_key($tmpKey = '') {
        Return substr(md5($tmpKey),5,8);
    }

    function str_extend($str = '') {
        Return $str.'_'.time();
    }

    function encrypt($str) {
        $key = $this->make_key($this->$key);
        if( $this->$iv == 0 ) {  
            $iv = $key; //默认以$key 作为 iv  
        } else {  
            $iv = $iv; //mcrypt_create_iv ( mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM );  
        }
        //加密，返回大写十六进制字符串
        $size = mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC );
        $str = $this->pkcs5Pad ( $this->str_extend($str), $size );
        return strtoupper( bin2hex( mcrypt_cbc(MCRYPT_DES, $key, $str, MCRYPT_ENCRYPT, $iv ) ) );
    }
	
	function decrypt($str) {  
        //解密  
        $strBin = $this->hex2bin( strtolower( $str ) );  
        $str = mcrypt_cbc( MCRYPT_DES, $this->key, $strBin, MCRYPT_DECRYPT, $this->iv );
        $str = $this->pkcs5Unpad( $str );
        $str = explode('_',$str);
        array_pop($str);
        $str = implode("_",$str);
        return $str;
    }

    function decrypt($str) {  
        //解密 
        $key = $this->make_key($this->$key);
        if( $this->$iv == 0 ) {  
            $iv = 0;    //默认以$key 作为 iv  
        } else {  
            $iv = $iv; //mcrypt_create_iv ( mcrypt_get_block_size (MCRYPT_DES, MCRYPT_MODE_CBC), MCRYPT_DEV_RANDOM );  
        }
        $strBin = $this->hex2bin( strtolower( $str ) );  
        $str = mcrypt_cbc( MCRYPT_DES, $key, $strBin, MCRYPT_DECRYPT, $iv );
        $str = $this->pkcs5Unpad( $str );
        $str = explode('_',$str);
        array_pop($str);
        $str = implode("_",$str);
        return $str;
    }

    function hex2bin($hexData) {  
        $binData = "";  
        for($i = 0; $i  < strlen ( $hexData ); $i += 2) {  
            $binData .= chr ( hexdec ( substr ( $hexData, $i, 2 ) ) );  
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );  
        if ($pad > strlen ( $text )) return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)   return false;  
        return substr ( $text, 0, - 1 * $pad );
    }
	*/
}
