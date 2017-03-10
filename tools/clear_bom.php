<?php

$exts = array('.x','.html','.dwt','.lbi','.htm','.php','.js','.css','.xml','.txt',);

$start = false;
if ($_POST && !empty($_POST['path']))
{
 $start = true;
 $PATH = $_POST['path'];
 $PATH = addslashes($PATH);
 $EXTS = $_POST['exts'];
}

function ReadDirs($path, $ext)
{
 global $BOM;
 $dir = opendir($path);
 echo '<ul>';
 while ( ($file = readdir($dir)))
 {
    if ($file == '.' || $file == '..') continue;
  
    $f = $path . '/' . $file;
  
    if (is_dir($f))
    {
  echo '<li class="folder"><span class="symbol">1</span>' . $file;
  echo '<ul>' . ReadDirs($f, $ext) . '</ul></li>';
    }
    else
    {
  $flag = false;
  if ( is_array($ext))
  {
   if (! in_array(getExt($file), $ext))
   {
    continue;
   }
   else
   {
    $flag = true;
   }
  }
  else
  {
   $flag = true;
  }
  if ($flag)
  {
   $cssClass = 'file';
   $isBom='';
   if (checkBOM($f))
   {
    $cssClass = 'bom';
    $isBom=$BOM[] = str_replace('//','/',str_replace('\\','/',$f));
   }
  
   echo '<li class="'.$cssClass.'"><span class="symbol">2</span>' . $file . '<del>'.$isBom.'</del>' . "</li>";
  }
    }
 }
 echo '</ul>';
}

function getExt($filename)
{
    $ext = strrchr($filename,'.');
    // 根本没有扩展名
    if ( empty($ext) )
    {
        return null;
    }
    return $ext;
}

function checkBOM($filename)
{
 $contents = file_get_contents($filename);
 $char[1] = substr($contents, 0, 1); // 1
 $char[2] = substr($contents, 1, 1); // 2
 $char[3] = substr($contents, 2, 1); // 3
 // EF BB BF
 if ( ord($char[1]) == 239 && ord($char[2]) == 187 && ord($char[3]) == 191 )
 {
  
  $rest=substr($contents, 3);
  //自动清除bom
  if($_REQUEST['auto_clear']=='Y') rewrite ($filename, $rest);
  //echo ("<font color=green>--Bom 已经清除完毕。</font>");
  
    return true;
 }
 return false;
}


function rewrite ($filename, $data) {
 $filenum=fopen($filename,"w");
 flock($filenum,LOCK_EX);
 fwrite($filenum,$data);
 fclose($filenum);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BOM检测工具</title>
<style type="text/css">
body, td, th {
 font-size: 12px;
}
body {
 margin-left: 15%;
 margin-top: 2px;
 margin-right: 15%;
 margin-bottom: 2px;
 font-family:Tahoma, Geneva, sans-serif;
}
form {
 margin: 0px;
 padding: 0px;
}
ul {
 margin: 0px 0px 0px auto;
 padding: 0px;
}
.symbol {
 font-family: Wingdings;
 font-size: 20px;
 padding-right: 10px;
}
.path {
 color:#0033CC;
}
li {
 color: #333333;
 list-style: none;
}
.bom {
 color:#F30;
 line-height:20px;
}
.folder {
 color: #0000ff;
}
.file {
 color: #333333;
}
</style>
</head>
<body>
<br/>
<br/>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#8DAFDA">
  <tr>
    <td height="70" align="center" valign="middle" bgcolor="#CBDBEE"><form id="form1" name="form1" method="post" action="">
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td height="30" align="left" valign="middle">文件夹：</td>
            <td align="left" valign="middle">
            <select name="path" >
                <?php
    $path = trim($_POST['path']);
                $dir = opendir('.');
    while ( ($f = readdir($dir)) ){
     if ($f == '..' || is_file('./' . $f) ) continue; ?>
                    <option value="<?php echo($f);?>" <?php if(isset($path) && $path == $f) echo 'selected';?> >
                    <?php echo($f);?>
                    </option>
                <?php
    }?>
              </select>
              自动清理：
              <input type="checkbox" value="Y" name="auto_clear" />
             
              </td>
            <td align="left" valign="middle"><input type="submit" name="button" id="button" value=" 提交 " /></td>
          </tr>
        </table>
        <?php foreach($exts as $ext){ ?>
        <label>
          <input type="checkbox" name="exts[]" value="<?php echo($ext);?>" <?php if(isset($_POST['exts']) && is_array($_POST['exts']) && in_array($ext, $_POST['exts'])) echo 'checked'; ?> />
          <?php echo($ext);?>
        </label>
        <?php }?>
      </form></td>
  </tr>
</table>
<div id="result"><br/>
  <br/>
  <?php if($start){?>
  <?php echo '搜索路径: <span class="path">' . str_replace('\\\\','\\',$PATH) . '</span> , 实际路径: <span class="path">' . realpath($PATH) . '</span>';?>
  <br/>
  <div style="height:200px; overflow:scroll;">
  <?php echo '文件列表: '; ReadDirs( $PATH, $EXTS);?></div>
  <br/>
  <br/>
  <?php if ($BOM) { ?>
  发现BOM文件列表：<br/>
  <ul>
    <?php foreach( $BOM as $k=>$f){?>
    <li class="bom">
      <?php echo($k)?>.
      <?php echo($f)?>
    </li>
    <?php }?>
  </ul>
  <?php }?>
  <?php }?>
</div>
</body>
</html>