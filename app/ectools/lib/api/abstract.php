<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class ectools_api_abstract {


    /**
     * 客服数据
     *
     * @var array
     */

    /**
     * HTTP访问对像
     *
     * @var Object httpclient
     */
    private static $httpObejct = null;


    /**
     * XML 转为数组
     * @param String $xml
     * @return Array
     */
    public function xml2array($xml) {

        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();

            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = $this->xml2array($matches[2][$i]);

                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }

            return $arr;
        } else {
            return $xml;
        }
    }

}
