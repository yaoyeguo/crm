<?php
class market_ctl_site_weixin_ucenter extends base_controller{
    
    function __construct($app)
    {
        parent::__construct($app);
        
        $this->pagedata['page_url'] = kernel::base_url(1).'/';
        
        $this->lottery_id = intval($_GET['lottery_id']);
        $this->wx_id = $_GET['wx_id'] ? $_GET['wx_id'] : $_POST['wx_id'];
        $this->pagedata['wx_id'] = $this->wx_id;
        
        base_kvstore::instance('market')->fetch('ucenter', $ucenter);
        $ucenter = json_decode($ucenter, true);
        if($ucenter['logo']){
            $ucenter['logo'] = base_storager::image_path($ucenter['logo'],'s' );
        }else{
            $ucenter['logo'] = $this->pagedata['page_url'].'app/market/statics/cart.png';
        }
        if(!$ucenter['shop_name']) $ucenter['shop_name']='我的店铺';
        $this->shop_name = $ucenter['shop_name'];
        $this->pagedata['ucenter'] = $ucenter;
        
        if( !isset($_POST['passcode']) && $this->lottery_id==0 ){
            $this->chk_mobile();
        }else{
            $this->rs_member = array('all_points'=>0);
            $this->pagedata['rs_member'] = $this->rs_member;
        }        
    }
    
    function chk_mobile()
    { 
        $o_wx_member = $this->app->model('wx_member');
        $this->rs_wx_member = $o_wx_member->dump(array('FromUserName'=>$this->wx_id));
        
        if(!$this->rs_wx_member['mobile']){
            $this->mobile_validate();
        }
        
        $this->mobile = $this->rs_wx_member['mobile'];
        //$sql = "select sum(a.points) as points from sdb_taocrm_member_analysis as a left join sdb_taocrm_members as b on a.member_id=b.member_id where b.mobile='".$this->mobile."' ";
        $sql = 'select sum(points) as points from sdb_taocrm_member_points where member_id='.$this->rs_wx_member['member_id'].' and (invalid_time >= '.time().' or ISNULL(invalid_time))';
        //var_dump($sql);
        //echo strtotime('2015-07-30 00:00:00');
        $this->rs_member = $o_wx_member->db->selectRow($sql);
        if($this->rs_member){
            $this->rs_member['all_points'] = ceil($this->rs_member['points']+$this->rs_wx_member['points']);
        }else{
            $this->rs_member['all_points'] = ceil($this->rs_wx_member['points']);
        }

        $this->pagedata['rs_member'] = $this->rs_member;
        $this->pagedata['rs_wx_member'] = $this->rs_wx_member;
    }
    
    //积分支付，优先消费积分，其次微信积分
    public function point_paid($wx_id, $mobile, $paid_points=0)
    {
        if($paid_points <= 0){
            return false;
        }
        
        $o_wx_member = $this->app->model('wx_member');
        $oPointsLog = app::get('taocrm')->model('all_points_log');
        $mdl_member_points = app::get('taocrm')->model('member_points');
        
        $sql = "select a.id,a.points as points,a.shop_id,a.member_id from sdb_taocrm_member_analysis as a left join sdb_taocrm_members as b on a.member_id=b.member_id where b.mobile='".$mobile."' and a.points>0 ";
        $rs = $o_wx_member->db->select($sql);
        foreach((array)$rs as $v){
            $id = $v['id'];
            if($paid_points > $v['points']){
                $sql = "update sdb_taocrm_member_analysis set points=0 where id={$id} ";
                $points = $v['points'];
                $paid_points -= $v['points'];
            }else{ 
                $sql = "update sdb_taocrm_member_analysis set points=points-{$paid_points} where id={$id} ";
                $points = $paid_points;
                $paid_points = 0;
            }
            
            $o_wx_member->db->exec($sql);
            
            //写入积分日志
            $arr_log = array();
            $arr_log['shop_id'] = $v['shop_id'];
            $arr_log['member_id'] = $v['member_id'];
            $arr_log['op_time'] = time();
            $arr_log['op_user'] = $mobile;
            $arr_log['point_desc'] = $this->point_desc;
            $arr_log['points'] = -1 * $points;
            //$arr_log['points_type'] = 'wechat';
            $oPointsLog->save($arr_log);
            
            //全渠道积分
            $logs = array(
                'member_id' => $v['member_id'],
                'points_type' => 'wechat',
                'points' => -1 * $points,
                'shop_id' => $v['shop_id'],
            );
            $mdl_member_points->save_points($logs);
            
            if($paid_points <= 0){
                break;
            }
        }
        
        if($paid_points > 0){
            $msg = '';
            $o_wx_member->updatePoint($this->rs_wx_member['wx_member_id'],2,-1 * $paid_points,$this->point_desc,$msg);
        }
    }
    
    public function gifts()
    {
        $m_gifts = $this->app->model("wx_points_buy");
        $curr_time = time();
        $sql = "select * from sdb_market_wx_points_buy where goods_all_stock>0 and goods_stock>0 and buy_status in ('create','start') and start_time<={$curr_time} and end_time>={$curr_time} ";
        $rs_gifts = $m_gifts->db->select($sql);
        foreach($rs_gifts as $k=>$v){
            if($v['goods_img']){
                $rs_gifts[$k]['preview'] = base_storager::image_path($v['goods_img'],'s' );
            }
        }//var_dump($rs_gifts);
        
        $sql = "update sdb_market_wx_points_buy set buy_status='start' where buy_status in ('create') ";
        $m_gifts->db->select($sql);
        
        $this->pagedata['rs'] = $rs;
        $this->pagedata['gifts'] = $rs_gifts;
        $this->display('site/weixin/ucenter/gifts.html');
    }

    public function points()
    {
        $this->display('site/weixin/ucenter/points.html');
    }
    
    public function lottery()
    {
        //获取活动信息
        $filter = array();
        if($this->lottery_id>0){
            $filter = array('lottery_id'=>$this->lottery_id);
            $rs_lottery = $this->app->model('wx_integral_lottery')->dump($filter);
        }else{
            $time = time();
            $sql = "select * from sdb_market_wx_integral_lottery where start_time<=$time and end_time>=$time and lottery_status in('create','start') ";
            $rs_lottery = $this->app->model('wx_integral_lottery')->db->selectRow($sql);
            
            //将活动更新为执行中
            if($rs_lottery['lottery_status'] == 'create'){
                $filter = array(
                    'lottery_id'=>$rs_lottery['lottery_id'],
                );
                $this->app->model('wx_integral_lottery')->update(array(
                    'lottery_status'=>'start'
                ) ,$filter);
            }
        }
        
        //var_dump($rs_lottery);
        if(!$rs_lottery){
            $this->display('site/weixin/ucenter/no_lottery.html');
            exit;
        }else{
            $rs_lottery['minus_score'] = intval($rs_lottery['minus_score']);
        }
        
        //活动奖项
        $filter = array('lottery_id'=>$rs_lottery['lottery_id']);
        $rs_lottery_info = $this->app->model('wx_integral_lotteryinfo')->getList('*',$filter);
        //var_dump($rs_lottery_info);
        
        $awards = array();
        foreach($rs_lottery_info as $v){
            $awards[] = array(
                'id'=>$v['info_id'],
                'name'=>$v['awards_name'],
                'content'=>$v['awards_info'],
                'num'=>$v['awards_stock'],
                'probability'=>$v['win_rate'],
            );
        }
        $awards = json_encode($awards);
        //var_dump($awards);
    
        $this->pagedata['awards'] = $awards;
        $this->pagedata['rs_lottery'] = $rs_lottery;
        $this->pagedata['rs_lottery_info'] = $rs_lottery_info;
        $this->display('site/weixin/ucenter/lottery.html');
    }
    
    public function point_logs()
    {
        $conf_week_name = array('星期天','星期一','星期二','星期三','星期四','星期五','星期六');
        $point_logs = $this->app->model('wx_point_log')->getList('*', array('FromUserName'=>$this->wx_id),0,20,'log_id desc');
        foreach($point_logs as $k=>$v){
            $point_logs[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']).' '.$conf_week_name[date('w', $v['create_time'])];
            $point_logs[$k]['color'] = ($v['point_mode']=='+') ? 'red':'green';
        }
        
        $logs = $point_logs;
        $this->pagedata['logs'] = $logs;
        $this->display('site/weixin/ucenter/point_logs.html');
    }
    
    public function gift_view()
    {   
        $gift_id = intval($_GET['gift_id']);
        $m_gifts = $this->app->model("wx_points_buy");
        $rs_gifts = $m_gifts->dump($gift_id);
        $rs_gifts['preview'] = base_storager::image_path($rs_gifts['goods_img'],'m' );
        
        if($rs_gifts['buy_status']!='create' && $rs_gifts['buy_status']!='start'){
            echo('非常抱歉，本次活动已结束，请关注我们的更多活动~');
            exit;
        }
        
        $curr_time = time();
        if($rs_gifts['start_time']>$curr_time or $rs_gifts['end_time']<$curr_time){
            echo('非常抱歉，本次活动已结束，请关注我们的更多活动~');
            exit;
        }

        $this->pagedata['gifts'] = $rs_gifts;
        $this->display('site/weixin/ucenter/gift_view.html');
    }
    
    public function mobile_validate()
    {
        if(isset($_POST['act']) && $_POST['act']=='send_passcode'){
            $mobile = trim($_POST['mobile']);
            $this->send_passcode($mobile);
            exit;
        }
    
        $this->display('site/weixin/ucenter/mobile_validate.html');
        exit;
    }
    
    public function save_mobile()
    {
        $this->check_passcode();
        die('succ');
    }
    
    public function send_passcode()
    {
        $mobile = trim($_POST['mobile']);
        $err_msg = '';
        
            $kvstore = base_kvstore::instance('taocrm');
            $kvstore->fetch('wx_bind_passcode'.$mobile, $kv_passcode);
            if($kv_passcode) {
                $kv_passcode = json_decode($kv_passcode, 1);
            }
             
            if(isset($kv_passcode['create_time']) && $mobile==$kv_passcode['mobile'] && (time() - $kv_passcode['create_time'] < 600)){
                die('10分钟内只能申请一次验证码，请稍后再试。');
                }
            
                $passcode = rand(1000,9999);
        $content = "尊敬的用户，您的验证码为(4位数字):{$passcode}。请在一小时内用此验证码进行验证。";
        
        //发送验证码
        $market_service_sms = kernel::single('market_service_sms');
        $market_service_sms->send_passcode($mobile, $content, $err_msg);
                
        if($err_msg == ''){
                    $kv_passcode = array(
                        'mobile' => $mobile,
                        'passcode' => $passcode,
                'create_time' => time(),
                    );
                    $kvstore->store('wx_bind_passcode'.$mobile, json_encode($kv_passcode));
            die("验证码已经发送到 $mobile ，请注意查收。");
                }else{
            die($err_msg);
        }
    }
    
    public function check_passcode()
    {
        $mobile = trim($_POST['mobile']);
        $passcode = trim($_POST['passcode']);
         
        $kvstore = base_kvstore::instance('taocrm');
        $kvstore->fetch('wx_bind_passcode'.$mobile, $kv_passcode);
        $result = array('res'=>'succ');
         
        if($kv_passcode) {
            $kv_passcode = json_decode($kv_passcode, 1);
            if($kv_passcode['mobile'] != $mobile){
                $result = array('res'=>'fail','msg'=>'手机号码不匹配:'.$kv_passcode['mobile']);
            }
            if($kv_passcode['passcode'] != $passcode){
                $result = array('res'=>'fail','msg'=>'验证码错误，请重新输入。');
            }
            if(isset($kv_passcode['create_time']) && (time() - $kv_passcode['create_time'] > 3600)){
                $result = array('res'=>'fail','msg'=>'验证码过期，请重新申请。');
            }

            //会员识别
            $members = app::get('taocrm')->model("members");
            $members_data = $members->dump(array('mobile'=>$mobile));
            if($members_data){
                $data['member_id'] = $members_data['member_id'];
            }else{
                //如果此手机号在全局会员表中不存在记录，用此手机号新创建一条记录
                $membersObj = kernel::single("taocrm_members");
                $msg = '';
                $sdf = array('mobile'=>$mobile);
                $memberId = $membersObj->add($sdf,$msg);
                $data['member_id'] = $memberId['member_id'];
            }

            $o_wx_member = $this->app->model('wx_member');
            $o_wx_member->update(
                array('mobile'=>$mobile,'member_id'=>$data['member_id']),
                array('FromUserName'=>$this->wx_id)
            );
            //把微信会员中积分更新到会员积分中，微信会员表积分清零
            $wxMemberObj = app::get('market')->model("wx_member");
            $wxMemberData = $wxMemberObj->dump(array('member_id'=>$data['member_id']));
            $id = kernel::single('taocrm_member_point')->update('',$data['member_id'],2,$wxMemberData['points'],'手机绑定微信积分',$msg,null,'wechat');
            $id = $wxMemberObj->updatePoint($wxMemberData['wx_member_id'],2,-$wxMemberData['points'],'手机绑定积分清零',$msg);
            $result = array('res'=>'succ','msg'=>'succ');
        }else{
            $result = array('res'=>'fail','msg'=>'请先点击获取验证码。');
        }

        echo $result['msg'];
        exit;
    }
    
    public function order_confirm()
    {
        $gift_id = isset($_GET['gift_id']) ? intval($_GET['gift_id']) : intval($_POST['gift_id']);
        $m_gifts = $this->app->model("wx_points_buy");
        $rs_gifts = $m_gifts->dump($gift_id);

        if($rs_gifts['buy_status']!='create' && $rs_gifts['buy_status']!='start'){
            echo('<font style="font-size:50px;">非常抱歉，本次活动已结束，请关注我们的更多活动~</font>');
            exit;
        }
        
        $curr_time = time();
        if($rs_gifts['start_time']>$curr_time or $rs_gifts['end_time']<$curr_time){
            echo('<font style="font-size:50px;">非常抱歉，本次活动已结束，请关注我们的更多活动~</font>');
            exit;
        }
        //限制次数
        //$do_times = $this->app->model('wx_points_buylog')->count(array('buy_id'=>$rs_gifts['buy_id'],'FromUserName'=>$this->wx_id));
        $wxMember = $this->app->model('wx_member')->dump(array('FromUserName'=>$this->wx_id));
        $do_times = app::get('ecorder')->model('exchange_orders')->count(array('buy_id'=>$rs_gifts['buy_id'],'member_id'=>$wxMember['member_id']));
        //echo $do_times;
        if(!empty($rs_gifts) && $rs_gifts['limit_times'] != 'Unlimited'){
            if($rs_gifts['limit_times'] <= $do_times){
                echo('<font style="font-size:50px;">非常抱歉，每人仅限'.$rs_gifts['limit_times'].'次，请关注我们的更多活动~</font>');
                exit;
            }
        }
        //积分是否够用
        if($this->rs_member['all_points'] < $rs_gifts['minus_score']){
            echo('<font style="font-size:50px;">非常抱歉，您的积分不够，请关注我们的更多活动~</font>');
            exit;
        }
        
        if($_POST){            
            //扣除积分
            $paid_points = $rs_gifts['minus_score'];
            $this->point_desc = '积分兑换 - '.$rs_gifts['goods_name'].'x1';
            //$this->point_paid($this->wx_id, $this->rs_wx_member['mobile'], $paid_points);
            $msg = '';
            $id = kernel::single('taocrm_member_point')->update('',$wxMember['member_id'],2,-$paid_points,$this->point_desc,$msg,null);

            //写入积分日志
            /*
            $point_log = array(
                'wx_member_id' => $this->rs_wx_member['wx_member_id'],
                'ToUserName' => $this->rs_wx_member['ToUserName'],
                'FromUserName' => $this->rs_wx_member['FromUserName'],
                'point_mode' => '-',
                'op_before_point' => $this->rs_wx_member['points'],
                'op_after_point' => $this->rs_wx_member['points'] - $paid_points,
                'points' => $paid_points,
                'create_time' => time(),
                'point_desc' => '积分兑换 - '.$rs_gifts['goods_name'].'x1',
            );
            $this->app->model('wx_point_log')->insert($point_log);
            */
            
            //扣减库存
            $sql = 'update sdb_market_wx_points_buy set goods_stock=goods_stock-1,join_num=join_num+1 where buy_id='.$gift_id.' ';
            $m_gifts->db->exec($sql);
        
            //创建订单
            if($_POST['receiver_addr'] == 'new_addr'){
                $addr = implode(',', $_POST['new_addr']);
            }else{
                $addr = trim($_POST['receiver_addr']);
            }
           /* $order = array(
                'FromUserName' => $this->wx_id,
                'buy_id' => $gift_id,
                'receiver' => trim($_POST['receiver']),
                'mobile' => trim($_POST['mobile']),
                'addr' => $addr,
                'minus_score' => $paid_points,
                'goods_code' => $rs_gifts['goods_code'],
                'goods_name' => $rs_gifts['goods_name'],
                'buy_num' => 1,
                'update_time' => time(),
                'create_time' => time(),
            );
            $this->app->model("wx_points_buylog")->insert($order);*/
            $members = app::get('taocrm')->model('members')->dump(array('member_id'=>$wxMember['member_id']));
            $order = array(
                'order_bn' => $this->get_order_bn(),
                'uname' => $members['account']['uname'],
                'member_id' => $wxMember['member_id'],
                'buy_id' => $gift_id,
                'receiver' => trim($_POST['receiver']),
                'mobile' => trim($_POST['mobile']),
                'state' => trim($_POST['new_addr']['state']),
                'city' => trim($_POST['new_addr']['city']),
                'area' => trim($_POST['new_addr']['district']),
                'addr' => $addr,
                'minus_score' => $paid_points,
                'goods_bn' => $rs_gifts['goods_code'],
                'goods_name' => $rs_gifts['goods_name'],
                'num' => 1,
                'source' => 'weixin',
                'modified_time' => time(),
                'create_time' => time(),
            );
            app::get('ecorder')->model("exchange_orders")->insert($order);
            
            //返回结果
            echo('兑换成功！我们会尽快和您联系，请耐心等待。');
            exit;
        }

        //处理商品缩略图
        $rs_gifts['preview'] = base_storager::image_path($rs_gifts['goods_img'],'m' );

        //根据手机号查询用户的历史收货地址
        $m_orders = app::get('ecorder')->model('orders');
        $rs_orders = $m_orders->getList('ship_name,ship_area,ship_addr,ship_mobile', array('ship_mobile'=>$this->rs_wx_member['mobile']));
        foreach((array)$rs_orders as $k=>$v){
            //mainland:黑龙江/七台河市/桃山区:1262
            $rs_orders[$k]['addr'] = trim(preg_replace("/(mainland:)|(:\d{1,8})|\//",' ',$v['ship_area'].' '.$v['ship_addr']));
        }
        
        $this->pagedata['gifts'] = $rs_gifts;
        $this->pagedata['rs_orders'] = $rs_orders;
        $this->display('site/weixin/ucenter/order_confirm.html');
    }
    //生成订单编号
    public function get_order_bn(){
        $cur_time = date('YmdHis',time());
        $order_bn = 'wx'.$cur_time.rand(0,9);
        return $order_bn;
    }

    public function BigWheel()
    {
        $lottery_id = intval($_POST['lottery_id']);
        $filter = array('lottery_id'=>$lottery_id);
        $m_wx_integral_lottery = $this->app->model('wx_integral_lottery');
        $m_wx_integral_lotteryinfo = $this->app->model('wx_integral_lotteryinfo');
        $rs_lottery = $m_wx_integral_lottery->dump($filter);
        $rs_lottery_info = $m_wx_integral_lotteryinfo->getList('*',$filter);
        $max_rate = 1;
        $total_rate = 0;
        foreach($rs_lottery_info as $v){            
            //如果库存不足，不参与抽奖，安全库存设置为 0
            if($v['awards_stock'] <= ($v['send_num'])){
                continue;
            }
            
            $prize_arr[$v['info_id']] = array(
                'id' => $v['info_id'],
                'prize_id' => $v['info_id'],
                'prize' => $v['awards_name'],
                'prize_detail' => $v['awards_info'],
                'v' => $v['win_rate'],
                'status' => true,
                'left' => 999,
            );
            $max_rate = max($max_rate, $v['win_rate']);
        }
        
        foreach($prize_arr as $k=>$v){
            $prize_arr[$k]['v'] = ceil($max_rate/$v['v']);
            $total_rate += $prize_arr[$k]['v'];
        }
        
        $prize_arr[0] = array(
            'id' => 0,
            'prize_id' => 0,
            'prize' => '谢谢参与',
            'v' => ($max_rate - $total_rate),
            'status' => false,
            'left' => 999,
        );
        
        foreach ($prize_arr as $key => $val) { 
            $arr[$val['id']] = $val['v']; 
        }
         
        $info_id = $this->get_rand($arr); //根据概率获取奖项id 
        $res = $prize_arr[$info_id];
        
        //扣除积分
        $paid_points = $rs_lottery['minus_score'];
        $this->point_desc = '积分抽奖 - '.$res['prize'].' '.$res['prize_detail'];
        //$this->point_paid($this->wx_id, $this->rs_wx_member['mobile'], $paid_points);
        $msg = '';
        $id = kernel::single('taocrm_member_point')->update('',$this->rs_wx_member['member_id'],2,-$paid_points,$this->point_desc,$msg,null);
        
        //写入积分日志
        $point_log = array(
            'wx_member_id' => $this->rs_wx_member['wx_member_id'],
            'ToUserName' => $this->rs_wx_member['ToUserName'],
            'FromUserName' => $this->rs_wx_member['FromUserName'],
            'point_mode' => '-',
            'op_before_point' => $this->rs_wx_member['points'],
            'op_after_point' => $this->rs_wx_member['points'] - $paid_points,
            'points' => $paid_points,
            'create_time' => time(),
            'point_desc' => '积分抽奖 - '.$res['prize'].' '.$res['prize_detail'],
        );
        //$this->app->model('wx_point_log')->insert($point_log);
        
        //扣减库存
        if($info_id>0){
            $sql = 'update sdb_market_wx_integral_lotteryinfo set send_num=send_num+1 where info_id='.$info_id.' ';
            $m_wx_integral_lottery->db->exec($sql);
        }
        
        //更新参与人数
        $sql = 'update sdb_market_wx_integral_lottery set participants=participants+1 where lottery_id='.$lottery_id.' ';
        $m_wx_integral_lottery->db->exec($sql);
    
        //创建订单
        $order = array(
            'FromUserName' => $this->wx_id,
            'lottery_id' => $lottery_id,
            'people_name' => '',
            'people_adr' => '',
            'lottery_name' => $rs_lottery['lottery_name'],
            'phone' => $this->rs_wx_member['mobile'],
            'minus_score' => $paid_points,
            'lottery_info_id' => $info_id,
            'lottery_info_name' => $res['prize'].'：'.$res['prize_detail'],
            'create_time' => time(),
            'update_time' => time(),
        );
        $this->app->model("wx_integral_lotterylog")->insert($order);
        $res['log_id'] = $order['log_id'];
      
        echo (json_encode($res));
    }
    
    public function lottery_res()
    {
        $log_id = intval($_GET['log_id']);
        
        if($_POST){
        
            $log_id = intval($_POST['log_id']);
        
            //更新收货地址
            if($_POST['receiver_addr'] == 'new_addr'){
                $addr = implode(',', $_POST['new_addr']);
            }else{
                $addr = trim($_POST['receiver_addr']);
            }
            $logs = array(
                'people_name' => trim($_POST['receiver']),
                'phone' => trim($_POST['mobile']),
                'people_adr' => $addr,
                'update_time' => time(),
            );
            $this->app->model("wx_integral_lotterylog")->update($logs, array('log_id'=>$log_id));
            
            //返回结果
            echo('保存成功！我们会尽快和您联系，请耐心等待。');
            exit;
        }

        //根据手机号查询用户的历史收货地址
        $m_orders = app::get('ecorder')->model('orders');
        $rs_orders = $m_orders->getList('ship_name,ship_area,ship_addr,ship_mobile', array('ship_mobile'=>$this->rs_wx_member['mobile']));
        foreach((array)$rs_orders as $k=>$v){
            //mainland:黑龙江/七台河市/桃山区:1262
            $rs_orders[$k]['addr'] = trim(preg_replace("/(mainland:)|(:\d{1,8})|\//",' ',$v['ship_area'].' '.$v['ship_addr']));
        }
        
        $this->pagedata['rs_orders'] = $rs_orders;
        $this->pagedata['log_id'] = $log_id;
        $this->display('site/weixin/ucenter/lottery_res.html');
    }
    
    public function get_rand($proArr)
    { 
        $result = ''; 
        //概率数组的总概率精度 
        $proSum = array_sum($proArr); 
        //概率数组循环 
        foreach ($proArr as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum); 
            if ($randNum <= $proCur) { 
                $result = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            }
        } 
        unset ($proArr); 
        return $result; 
    }
}
