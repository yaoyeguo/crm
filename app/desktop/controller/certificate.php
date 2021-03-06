<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_certificate extends desktop_controller{

    function index(){

        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        if(empty($this->Certi) ||empty($this->Token)){
            $this->pagedata['license']=false;
        }else{
            $this->pagedata['license']=true;
        }
        $this->pagedata['certi_id']=$this->Certi;
        $this->pagedata['debug']=false;

        $this->page('certificate.html');
    }

    function upLicense(){
    	if ( $_FILES ){
    		if ( $_FILES['license']['name'] ){
	    		$fileName = explode( '.', $_FILES['license']['name'] );
	    		if ( 'CER' != $fileName['1'] ){
	    		    echo "<script>parent.MessageBox.error('".app::get('desktop')->_('"证书格式不对"')."');</script>";
	    		    return;
	    		}
	    		else {
			        $content = file_get_contents($_FILES['license']['tmp_name']);
			        list($certificate_id,$token) = explode('|||',$content);
			        $result = base_certificate::set_certificate(array('certificate_id'=>$certificate_id,'token'=>$token));
			        if(!$result){
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.error('".app::get('desktop')->_('"证书重置失败,请先上传文件"')."');</script>";
			        }else{
			            header("Content-type:text/html; charset=utf-8");
			            echo "<script>parent.MessageBox.success('".app::get('desktop')->_('"证书上传成功"')."');</script>";
			        }
	    		}
    		}
    		else {
    		    echo "<script>parent.MessageBox.error('".app::get('desktop')->_('"请选择要上传的文件"')."');</script>";
    		}
    	}
    }
    function download(){
        header("Content-type:application/octet-stream;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Cache-control: private");
        $this->fileName = 'CERTIFICATE.CER';
        header("Content-Disposition:filename=".$this->fileName);

        $this->Certi = base_certificate::get('certificate_id');
        $this->Token = base_certificate::get('token');
        echo $this->Certi;
        echo '|||';
        echo $this->Token;
    }
    function delete(){
        $this->begin();
        base_certificate::del_certificate();
        //base_certificate::register();
        $this->end();
    }

}

