<?php
class market_func {

    /**
     *php实现下载远程图片到本地
     *@param $url       string      远程文件地址
     *@param $filename  string      保存后的文件名（为空时则为随机生成的文件名，否则为原文件名）
     *@param $fileType  array       允许的文件类型
     *@param $dirName   string      文件保存的路径（路径其余部分根据时间系统自动生成）
     *@param $type      int         远程获取文件的方式
     *@return           json        返回文件名、文件的保存路径
     * 
     * 例子：{'fileName':13668030896.jpg, 'saveDir':/www/test/img/2013/04/24/}
     */
    static function getImage($url, $filename='', $dirName = 'data/images')
    {
        if($url == ''){return false;}
        //获取文件原文件名
        $defaultFileName = basename($url);
        //获取文件类型
        $suffix = substr(strrchr($url,'.'), 1);
//        if(!in_array($suffix, $fileType)){
//            return false;
//        }
        //设置保存后的文件名
        $filename = $filename == '' ? time().rand(0,9).'.'.$suffix : $defaultFileName;

        //获取远程文件资源
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file = curl_exec($ch);
        curl_close($ch);
        //设置文件保存路径
        $dirName = $dirName.'/wx_upload/';
        if(!file_exists($dirName)){
            mkdir($dirName, 0777, true);
        }
        //保存文件
        $res = fopen($dirName.$filename,'a');
        fwrite($res,$file);
        fclose($res);
        return array('filename' => $filename,'filedir' => $dirName);
    }

    /**
     * 取得文件扩展
     *
     * @param $filename 文件名
     * @return 扩展名
     */
    static function fileext($filename)
    {
        return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
    }
    
    /**
     * 生成缩略图
     * @author liuqi
     * @param string     源图绝对完整地址{带文件名及后缀名}
     * @param string     目标图绝对完整地址{带文件名及后缀名}
     * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
     * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
     * @param int        是否裁切{宽,高必须非0}
     * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
     * @return boolean
     */
    static function img2thumb($src_img, $dst_img, $width = 150, $height = 150, $cut = 0, $proportion = 0)
    {
        if(!is_file($src_img)){
            return false;
        }
        $ot = self::fileext($dst_img);
        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        if( ! $type) $type = 'jpeg';
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0)){
            $proportion = 1;
        }
        
        if($width> $src_w){
            $dst_w = $width = $src_w;
        }
        
        if($height> $src_h){
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion){
            return false;
        }
        
        if(!$proportion){
            if($cut == 0){
                if($dst_w && $dst_h){
                    if($dst_w/$src_w> $dst_h/$src_h){
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }else{
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h)
                {
                    if($dst_w && !$dst_h)  //有宽无高
                    {
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h)  //有高无宽
                    {
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else
            {
                if(!$dst_h)  //裁剪时无高
                {
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w)  //裁剪时无宽
                {
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);
        return true;
    }

}