<?php

/**
 * OME公共函数库
 * @copyright Copyright (c) 2010, shopex. inc
 * @author dongdong
 *
 */
class ecorder_func
{
    /**
     * 地区字符串格式验证
     * 正则匹配地区是否为本系统的标准地区格式，标准格式原样返回，非标准格式试图转换标准格式返回，否则
     * @access static
     * @param string $area 待验证地区字符串
     * @return string 转换后的本系统标准格式地区
     */
    public function region_validate(&$area){
        $is_correct_area = $this->is_correct_region($area);
        if (!$is_correct_area){
            //非标准格式进行转换
            $this->local_region($area);
        }
    }

    /**
     * ECOS本地标准地区格式判断
     * @access public
     * @param string $area 地区字符串，如：malind:上海/徐汇区:22
     * @return boolean
     */
    public function is_correct_region($area){
        $pattrn = "/^([a-zA-Z]+)\:(\S+)\:(\d+)$/";
        if (preg_match($pattrn, $area)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 本系统标准地区格式转换
     * 正则匹配地区是否为本系统的标准地区格式，转换成功返回标准地区格式，转换失败原地区字符串返回
     * @access static
     * @param string $area 待转换地区字符串
     * @return string  转换后的本系统标准格式地区
     */
    public function local_region(&$area){

        $tmp_area = explode("/",$area);
        //地区初始值临时存储
        $ini_first_name = trim($tmp_area[0]);
        $ini_second_name = trim($tmp_area[1]);
        $ini_third_name = trim($tmp_area[2]);

        //$tmp_area2 = preg_replace("/省|市|县|区/","",$tmp_area);
        //$first_name = trim($tmp_area2[0]);
        $first_name = $ini_first_name;
        //自治区兼容
        $tmp_first_name = $this->area_format($first_name);
        if ($tmp_first_name) $first_name = $tmp_first_name;
        //$second_name = trim($tmp_area2[1]);
        //$third_name = trim($tmp_area2[2]);
        $second_name = $ini_second_name;
        $third_name = $ini_third_name;
         
        $regionObj = &app::get('ectools')->model('regions');

        $region_first = $region_second = $region_third = "";
        if ($first_name){
            //省------region_id
            $region_first = $regionObj->dump(array('local_name|head'=>$first_name,'region_grade'=>'1'), 'package,region_id,local_name');
            $first_name = $region_first['local_name'];
            if (!$first_name){
                $region_first = array(
			        'local_name' =>$ini_first_name,
                    'package' =>'mainland',
                    'region_grade' =>'1',
                );
                $regionObj->save($region_first);
                $first_name = $region_first['local_name'];
                $region_path = ",".$region_first['region_id'].",";
                //更新region_path字段
                $regionObj->update(array('region_path'=>$region_path), array('region_id'=>$region_first['region_id']));
            }
        }
        if ($second_name){
            //市------region_id
            $second_filter = array('local_name|head'=>$second_name,'region_grade'=>'2','p_region_id'=>$region_first['region_id']);
            $region_second = $regionObj->dump($second_filter, 'package,region_id,p_region_id,local_name');
            $second_name = $region_second['local_name'];
            if (!$second_name){
                $region_second = array(
                    'local_name' =>$ini_second_name,
                    'p_region_id' =>$region_first['region_id'],
                    'package' =>'mainland',
                    'region_grade' =>'2',
                );
                $regionObj->save($region_second);
                $second_name = $region_second['local_name'];
                $region_path = ",".$region_first['region_id'].",".$region_second['region_id'].",";
                //更新region_path字段
                $regionObj->update(array('region_path'=>$region_path), array('region_id'=>$region_second['region_id']));
            }
        }
        if ($third_name){
            //县------region_id
            if (!$region_second['region_id']){
                //先根据第三级查出所有第二级
                $filter = array('local_name|head'=>$third_name);
                $regions = $regionObj->getList('p_region_id', $filter, 0, -1);
                if ($regions){
                    foreach ($regions as $k=>$v){
                        $region_second_tmp = $regionObj->dump(array('region_id'=>$v['p_region_id'],'region_grade'=>'2'), 'region_path,package,region_id,p_region_id,local_name');
                        $tmp = explode(",",$region_second_tmp['region_path']);
                        if (in_array($region_first['region_id'],$tmp)){
                            $region_second = $region_second_tmp;
                            $second_name = $region_second['local_name'];
                            break;
                        }
                    }
                }
            }
            $third_filter = array('local_name|head'=>$third_name,'region_grade'=>'3','p_region_id'=>$region_second['region_id']);

            $region_third = $regionObj->dump($third_filter, 'package,region_id,p_region_id,local_name');
            $third_name = $region_third['local_name'];
            if (!$third_name){
                $region_third = array(
                    'local_name' =>$ini_third_name,
                    'p_region_id' =>$region_second['region_id'],
                    'package' =>'mainland',
                    'region_grade' =>'3',
                );
                $regionObj->save($region_third);
                $third_name = $region_third['local_name'];
                $region_path = ",".$region_first['region_id'].",".$region_second['region_id'].",".$region_third['region_id'].",";
                //更新region_path字段
                $regionObj->update(array('region_path'=>$region_path), array('region_id'=>$region_third['region_id']));
            }
        }
        $return = false;
        if ($region_third['region_id']){
            $region_id = $region_third['region_id'];
            $package = $region_third['package'];
        }elseif ($region_second['region_id']){
            $region_id = $region_second['region_id'];
            $package = $region_second['package'];
        }
        $region_area = array_filter(array($first_name,$second_name,$third_name));
        $region_area = implode("/", $region_area);
        $area = $package.":".$region_area.":".$region_id;
        $return = true;
         
         
        //去除多余分隔符“/”
        if ($return==false){
            $area = implode("/", array_filter($tmp_area));
        }
         
    }

    /**
     * 前端店铺三级地区本地临时转换
     * @param $area
     */
    public function area_format($area){
        $area_format = array(
            '内蒙古自治区' => '内蒙古',
            '广西壮族自治区' => '广西',
            '西藏自治区' => '西藏',
            '宁夏回族自治区' => '宁夏',
            '新疆维吾尔自治区' => '新疆',
            '香港特别行政区' => '香港',
            '澳门特别行政区' => '澳门',
            '江苏省' => '江苏',
            '山东省'=>'山东',
            '广东省'=>'广东',
            '吉林省'=>'吉林',
            '湖南省'=>'湖南',
            '河北省'=>'河北',
            '浙江省'=>'浙江',
            '四川省'=>'四川',
            '山西省'=>'山西',
            '福建省'=>'福建',
            '黑龙江省'=>'黑龙江',
            '甘肃省'=>'甘肃',
            '陕西省'=>'陕西',
            '安徽省'=>'安徽',
            '海南省'=>'海南',
            '云南省'=>'云南',
            '河南省'=>'河南',
            '辽宁省'=>'辽宁',
            '湖北省'=>'湖北',
            '江西省'=>'江西',    
            '贵州省'=>'贵州',   
            '青海省'=>'青海', 
        	'台湾省'=>'台湾',
            '上海市'=>'上海',
            '北京市'=>'北京',
            '天津市'=>'天津',
            '重庆市'=>'重庆',
        );
        if ($area_format[$area]){
            return $area_format[$area];
        }else{
            return false;
        }
    }

    /**
     * 拆分标准格式为：省市县
     * @param string $area
     * @return array 下标从0开始，依次代表：省、市、县
     */
    public function split_area(&$area){
        preg_match("/:(.*):/", $area,$tmp_area);
        if($tmp_area[1]){
            $tmp_area = explode('/', $tmp_area[1]);
            $area = $tmp_area;
        }
    }

    /**
     * 数组转换字符串
     * 支持多维数组
     * @access public
     * @param array $data
     * @return string
     */
    static function array2string($data){
        if (!is_array($data)) return null;
        ksort($data, SORT_REGULAR);
        $string = '';
        if ($data)
        foreach ((array)$data as $k=>$v){
            $string .= $k . (is_array($v) ? self::array2string($v) : $v);
        }
        return $string;
    }

    /**
     * 日期型转换时间戳
     * @access public
     * @param $string $date_time 日期字符串或时间戳
     * @return 时间戳
     */
    public function date2time($date_time){
        if ( ! is_numeric($date_time)){
            return strtotime($date_time);
        }else{
            return $date_time;
        }
    }

    /**
     * 备注与留言格式化输出
     * @param string $memo 备注与留言内容：序列化数组
     * serail(array(0=>array('op_name'=>'2','op_time'=>'12342'),1=>array);
     * @return array 标准可直接读取的数组
     */
    public function format_memo($memo){
        $mark = array();
        $mark = unserialize($memo);
        if (!is_array($mark)) return null;
        foreach ($mark as $k=>$v){
            if (!strstr($v['op_time'], "-")){
                $v['op_time'] = date('Y-m-d H:i:s',$v['op_time']);
                $mark[$k]['op_time'] = $v['op_time'];
            }
        }
        return $mark;
    }

    /**
     * 计算两个时间的差值转换到日期
     *
     * @param $time1
     * @param $time2
     * @return array
     */
    public function toTimeDiff($time1,$time2){
        $arr_time_diff = array('d'=>0,'h'=>0,'m'=>0,'i'=>0);
        $time_diff = $time1 - $time2;
        $k = 86400;
        $arr_time_diff['d'] = intval($time_diff / $k);
        $time_diff = $time_diff % $k;
        $k = $k/24;
        $arr_time_diff['h'] = intval($time_diff/$k);
        $time_diff = $time_diff % $k;
        $k = $k/60;
        $arr_time_diff['m'] = intval($time_diff/$k);
        $arr_time_diff['i'] = intval($time_diff%$k);

        return $arr_time_diff;
    }

    static function createSign($paramArr,$secret) {
        $sign = '';
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key !='' && $val !=''&&$key!='sign') {
                $sign .= $key.$val;
            }
        }
        $sign = strtoupper(md5($sign.$secret));
        return $sign;
    }

    static function createCallBackUrl($callback_url,$params=array()){
        $callback_url = $callback_url.'?';
        foreach($params as $k=>$v) {
            $callback_url.= $k ."=".$v."&";
        }
        $callback_url = substr($callback_url,0,strlen($callback_url)-1);
        return $callback_url;
    }
    
    //过滤数组元素的空格
    public function trim_array($arr)
    {
        foreach($arr as $k=>$v){
            if(is_string($v)){
                $arr[$k] = trim($v);
            }elseif(is_array($v)){
                $arr[$k] = $this->trim_array($v);
            }
        }
        return $arr;
    }
    
    //删除数组里的空元素
    public function clear_value($arr)
    {
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $arr[$k] = $this->clear_value($v);
            }else{
                //检测邮政编码格式
                if($k==='zip' && !preg_match("/^(\d){5,6}$/", $v)){
                    unset($arr[$k]);
                }
                
                if( ! $v) unset($arr[$k]);
            }
        }
        return $arr;
    }

    static function get_total_count_cache($db_table)
    {
        base_kvstore::instance('system')->fetch('total_count:'.$db_table, $total_count);
        if($total_count){
            return $total_count;
        }else{
            return false;
        }
    }
    
    static function set_total_count_cache($db_table, $total_count)
    {
        $ttl = 86400*3;
        if(defined('COUNT_CACHE_TTL')) $ttl = COUNT_CACHE_TTL;

        base_kvstore::instance('system')->store('total_count:'.$db_table, $total_count, $ttl);
    }
    
    static function add_total_count_cache($db_table, $count=0)
    {
        $ttl = 86400*3;
        if(defined('COUNT_CACHE_TTL')) $ttl = COUNT_CACHE_TTL;
        
        base_kvstore::instance('system')->fetch('total_count:'.$db_table, $total_count);
        if($total_count){
            $total_count += $count;
            base_kvstore::instance('system')->store('total_count:'.$db_table, $total_count, $ttl);
        }
    }
}