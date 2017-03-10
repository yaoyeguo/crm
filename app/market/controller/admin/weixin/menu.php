<?php

class market_ctl_admin_weixin_menu extends desktop_controller {

    public function getCustomMenus()
    {
        $menus = array();
        $menus[1] = array('id'=>1,'order'=>'主菜单1','parent'=>0);
        $menus[2] = array('id'=>2,'order'=>'子菜单1','parent'=>1);
        $menus[3] = array('id'=>3,'order'=>'子菜单2','parent'=>1);
        $menus[4] = array('id'=>4,'order'=>'子菜单3','parent'=>1);
        $menus[5] = array('id'=>5,'order'=>'子菜单4','parent'=>1);
        $menus[6] = array('id'=>6,'order'=>'子菜单5','parent'=>1);

        $menus[7] = array('id'=>7,'order'=>'主菜单2','parent'=>0);
        $menus[8] = array('id'=>8,'order'=>'子菜单1','parent'=>7);
        $menus[9] = array('id'=>9,'order'=>'子菜单2','parent'=>7);
        $menus[10] = array('id'=>10,'order'=>'子菜单3','parent'=>7);
        $menus[11] = array('id'=>11,'order'=>'子菜单4','parent'=>7);
        $menus[12] = array('id'=>12,'order'=>'子菜单5','parent'=>7);

        $menus[13] = array('id'=>13,'order'=>'主菜单3','parent'=>0);
        $menus[14] = array('id'=>14,'order'=>'子菜单1','parent'=>13);
        $menus[15] = array('id'=>15,'order'=>'子菜单2','parent'=>13);
        $menus[16] = array('id'=>16,'order'=>'子菜单3','parent'=>13);
        $menus[17] = array('id'=>17,'order'=>'子菜单4','parent'=>13);
        $menus[18] = array('id'=>18,'order'=>'子菜单5','parent'=>13);

        foreach($menus as $id=>$menu){
            if($menu['parent']!=0){
                $menus[$menu['parent']]['sub_menus'][] = $id;
            }
            $menus[$id]['type'] = 1;
            //$menus[$id]['url'] = 'http://';
        }

        return $menus;
    }

    public function customMenu(){
        //$a = "是要";
        //var_dump($a[6]);exit;
        $menus = $this->getCustomMenus();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        if(!empty($wxuser)){
            $custommenu = $wxuser['custommenu'];
            foreach($menus as $k=>$menu){
                if(isset($custommenu[$k])){
                    $menu = array_merge($menu,$custommenu[$k]);
                    $menus[$k]= $menu;
                }
            }
        }
        if(empty($menus)){
            $this->pagedata['re_bool'] = false;
        }else{
            $this->pagedata['re_bool'] = true;
        }

        $clickType = array ('click' => '发送信息','view' => '跳转到网页');
        $this->pagedata['clickType'] = $clickType;
        $this->pagedata['menus'] = $menus;
        $this->pagedata['link'] = kernel::router()->app->base_url(1);
        $this->page('admin/weixin/custommenu.html');
    }

    public function saveCustoMmenu()
    {
        $this->begin();
        $menus = $this->getCustomMenus();
        $custommenu = $_POST['custommenu'];
        $userCustomMenu = array();
        foreach($custommenu as $id=>$menu){
            $menu['name'] = trim($menu['name']);
            if(!empty($menu['name'])){
                if(isset($menu['name'][16])){
                    $this->end(false,$menu['name'].'不能超过16个字符');
                }

                if($menu['type'] == 'click'){
                    if(empty($menu['key'])){
                        $this->end(false,$menu['name'].'的消息关键词不能为空');
                    }else{
                        unset($menu['url']);
                    }
                }

                if($menu['type'] == 'view'){
                    if(empty($menu['url'])){
                        $this->end(false,$menu['name'].'的跳转网页地址不能为空');
                    }else{
                        unset($menu['key']);
                    }
                }

                $userCustomMenu[$id] = $menu;
            }
        }

        if(empty($userCustomMenu)){
            $this->end(false,'请填写完成菜单');
        }

        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        $wxuser['custommenu'] = $userCustomMenu;
        base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));

        //发布菜单到微信接口
        $this->publishCustomMenu();

        $this->end(true,'操作成功','index.php?app=market&ctl=admin_weixin&act=customMenu');
    }

    private function publishCustomMenu()
    {
        $menus = $this->getCustomMenus();
        base_kvstore::instance('market')->fetch('wxuser', $wxuser);
        $wxuser = json_decode($wxuser,true);
        
        //获取自定义的微信菜单
        if(!empty($wxuser)){
            $custommenu = $wxuser['custommenu'];
            $data = array('button'=>array());
            foreach($custommenu as $id=>$menu){
                if($menus[$id]['parent'] == 0){
                    $tmpMenu = array('name'=>$menu['name'],'type'=>$menu['type'],'key'=>$menu['key'],'url'=>$menu['url']);
                    $tmpSubMenu = array();
                    foreach($menus[$id]['sub_menus'] as $sub_menu_id){
                        if(isset($custommenu[$sub_menu_id])){
                            $sub_menu = $custommenu[$sub_menu_id];
                            $tmpSubMenu[] = array('name'=>$sub_menu['name'],'type'=>$sub_menu['type'],'key'=>$sub_menu['key'],'url'=>$sub_menu['url']);
                        }
                    }

                    if(!empty($tmpSubMenu)){
                        $tmpMenu['sub_button'] = $tmpSubMenu;
                    }

                    $data['button'][] = $tmpMenu;
                }
            }

            $str = '[';
            foreach( $data['button'] as $menu){
                if(isset($menu['sub_button'])){
                    $str.= '{"name":"'.$menu['name'].'",
           "sub_button":[';
                    foreach( $menu['sub_button'] as $submenu){
                        if($submenu['type'] == 'click'){
                            $str.= '{
                          "type":"'.$submenu['type'].'",
                          "name":"'.$submenu['name'].'",
                          "key":"'.$submenu['key'].'"
                            },';
                        }else if($submenu['type'] == 'view'){
                            $str .= '{
                          "type":"'.$submenu['type'].'",
                          "name":"'.$submenu['name'].'",
                          "url":"'.$submenu['url'].'"
                            },';
                        }
                    }
                    $str = substr($str, 0,strlen($str)-1);
                    $str.= ']},';
                }else{
                    if($menu['type'] == 'click'){
                        $str.= '{
                          "type":"'.$menu['type'].'",
                          "name":"'.$menu['name'].'",
                          "key":"'.$menu['key'].'"
                      },';
                    }else if($menu['type'] == 'view'){
                        $str .= '{
                          "type":"'.$menu['type'].'",
                          "name":"'.$menu['name'].'",
                          "url":"'.$menu['url'].'"
                      },';
                    }
                }
            }

            $str = substr($str, 0,strlen($str)-1);
            $str .= ']';
        }else{
            $this->end(false,'菜单读取失败，可能是缓存出错');
        }        

        //1.矩阵的微信接口
        $wechat_shops = app::get('ecorder')->model('shop')->get_shops('wechat');
        if($wechat_shops){
            $wechat_shops = array_values($wechat_shops);
            $data['button'] = str_replace("\r\n",'',$str);
            $data['node_id'] = $wechat_shops[0]['node_id'];
            $resp = kernel::single('ecorder_rpc_request_weixin_menu')->create($data);
            $result = json_decode($resp, true);
            if($result && $result['rsp']!='fail'){
                $this->end(true,'发布成功');
            }else{
                $err_msg = $result['err_msg'] ? $result['err_msg'] : $resp;
                $this->end(false,'发布失败:'.$err_msg);
            }
        }
        
        //2.老的微信接口
        $str = '{"button":'.$str.'}';
        $http = new base_httpclient;

        //获取token凭证
        if(!isset($wxuser['appid']) || !isset($wxuser['secret'])){
            $this->end(false,'请到微信互动界面->绑定微信:设置appid或者secret参数');
        }

        $request_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxuser['appid'].'&secret='.$wxuser['secret'];
        $result = $http->post($request_token_url,array());
        $result = json_decode($result,true);
        if($result && isset($result['access_token'])){
            $wxuser['access_token'] = $result['access_token'];
            $wxuser['expires_in'] = $result['expires_in'];
            $wxuser['get_access_token_time'] = time();
            base_kvstore::instance('market')->store('wxuser', json_encode($wxuser));
        }else{
            $this->end(false,'获取token凭证失败:'.$result['errmsg'].',错误码:'.$result['errcode']);
        }

        $request_wx_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$wxuser['access_token'];
        $result = $http->post($request_wx_url, $str);
        $result = json_decode($result,true);
        if($result && $result['errcode'] == 0){
            $this->end(true,'发布成功');
        }else{
            $this->end(false,'发布失败:'.$result['errmsg'].',错误码:'.$result['errcode']);
        }
    }
    
}
