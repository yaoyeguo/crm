<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class ectools_finder_refunds{
    
    var $detail_info = '退款单明细';
    
    public function __construct($app){
        $this->app=$app;
        }
        
    public function detail_info($refund_id){
        
        $refund= $this->app->model('refunds');
        $sdf_refund = $refund->dump($refund_id, '*', array('orders' => '*'));
        if($sdf_refund){
            $render = $this->app->render();
            
            $render->pagedata['refunds'] = $sdf_refund;
            if (isset($render->pagedata['refunds']['member_id']) && $render->pagedata['refunds']['member_id'])
            {
                $obj_pam = app::get('pam')->model('account');
                $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['refunds']['member_id'], 'account_type' => 'member'), 'login_name');
                $render->pagedata['refunds']['member_id'] = $arr_pam['login_name'];
            }
            if (isset($render->pagedata['refunds']['op_id']) && $render->pagedata['refunds']['op_id'])
            {
                $obj_pam = app::get('pam')->model('account');
                $arr_pam = $obj_pam->dump(array('account_id' => $render->pagedata['refunds']['op_id']), 'login_name');
                $render->pagedata['refunds']['op_id'] = $arr_pam['login_name'];
            }
            if (isset($render->pagedata['refunds']['orders']) && $render->pagedata['refunds']['orders'])
			{
				foreach ($render->pagedata['refunds']['orders'] as $key=>$arr_order_bills)
				{
					$render->pagedata['refunds']['order_id'] = $key;
				}
			}
			
            return $render->fetch('refund/refund.html',$this->app->app_id);
            /*$ui= new base_component_ui($this);
            $html .= $ui->form_start();
            foreach($sdf_refund as $k=>$val){
                $v['value'] = $val;
                $v['name'] = $k;
                $v['type'] = 'label';
                $v['title'] = $refund->schema['columns'][$k]['label'];
                $html .= $ui->form_input($v);
            }
            
            $html .= $ui->form_end(0);
            return $html;*/
        }else{
            return app::get('ectools')->_('无内容');
        }
    }
    
    public $column_rel_id = '退款对象';
    public function column_rel_id($row)
    {
        $obj_refund = $this->app->model('refunds');
        
        $arr_refund = $obj_refund->dump($row['refund_id'], '*', array('orders' => '*'));
        if ($arr_refund)
		{
			if ($arr_refund['orders'])
				$order_bill = array_shift($arr_refund['orders']);
			else
				$order_bill = array('rel_id'=>0);
		}
        
        return $order_bill['rel_id'];
    }
}
