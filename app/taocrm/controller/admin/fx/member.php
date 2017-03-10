<?php
class taocrm_ctl_admin_fx_member extends desktop_controller{
    var $workground = 'taocrm.member';

   
    public function getOrderInfo($shop_id,$order_id,$page){
        $pagelimit = 20;
        $page = $page ? $page : 1;
        $orderItems = app::get('ecorder')->model('fx_order_items')->getPager(array('order_id'=>$order_id),'name,price,nums,amount,evaluation,bn,`delete`',$pagelimit * ($page - 1), $pagelimit);

        $trade_rates = array('good'=>'好评','bad'=>'差评','neutral'=>'中评','unkown'=>'-');
        foreach($orderItems['data'] as $k=>$v){
            $orderItems['data'][$k]['evaluation'] = $trade_rates[$v['evaluation']];
            $orderItems['data'][$k]['pmt_amount'] = ($v['price'] * $v['nums']) -  $v['amount'];
        }

        $count = $orderItems ['count'];
        $total_page = ceil ( $count / $pagelimit );
        $pager = $this->ui ()->pager ( array ('current' => $page, 'total' => $total_page, 'link' => 'index.php?app=taocrm&ctl=admin_fx_member&act=getOrderInfo&p[0]='.$shop_id.'&p[1]='.$order_id.'&p[2]=%d' ) );
        $this->pagedata['pager'] = $pager;

        $this->pagedata['orderItems'] = $orderItems['data'];
        $this->display('admin/member/order_info.html');
    }

    
}

