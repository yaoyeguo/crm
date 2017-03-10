<?php
class ecgoods_finder_shop_goods{

    var $addon_cols = 'no_use,pic_url,outer_id,sale_money,info_url';
    var $column_edit_width;
    
    function __construct()
    {
        if($_GET['ctl'] == 'admin_shop_goods_manage'){
            $this->column_edit_width = 110;
        }else{
            $this->column_edit_width = 60;
        }
    }

    var $column_edit = "操作";
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row)
    {
        $return = '<a target="dialog::{width:800,height:405,title:\'编辑\'}" href="index.php?app=ecgoods&ctl=admin_shop_goods_manage&act=edit&id='.$row['goods_id'].'">编辑</a>';
        if($_GET['ctl'] == 'admin_shop_goods_manage'){
            $return .= ' | <a target="dialog::{width:700,height:300,title:\'商品设置\'}" href="index.php?app=ecgoods&ctl=admin_shop_goods_manage&act=set_goods&id='.$row['goods_id'].'">商品设置</a>';
        }
        return $return;
    }

  	var $column_pic = "图片";
  	var $column_pic_width = 60;
  	var $column_pic_order = 5;
    function column_pic($row)
    {
    	$goodsrul = $row[$this->col_prefix.'pic_url'];
        if($goodsrul){
            return '<a class="img-tip pointer " onmouseover="bindFinderColTip(event);" target="_blank" href='.'"'.$goodsrul.'"'.'title=""></a>';
        }else{
            return '-';
        }
    }

    var $column_see = "查看";
    var $column_see_width =60;
    var $column_see_order = COLUMN_IN_HEAD;
    function column_see($row)
    {
        if(defined('ECSTORE_URL')){
            return '<a target="_blank" href="'.ECSTORE_URL.'gallery.html?scontent=n,'.$row['bn'].'">查看</a>';
        }
        
    	$info_url = $row[$this->col_prefix.'info_url'];
        if($info_url)
        {
            return '<a target="_blank" href="'.$info_url.'">查看</a>';
        }else
        {
        $outer_idurl=$row[$this->col_prefix.'outer_id'];
        if($outer_idurl) {
            return '<a target="_blank" href="http://item.taobao.com/item.htm?id='.$outer_idurl.'">查看</a>';
        }else{
            return '-';
        }
    }
    }

    var $column_avgprice = "平均价格";
    var $column_avgprice_order = 30;
    var $column_avgprice_width = 80;
    function column_avgprice($row)
    {
 		$outer_id = $row[$this->col_prefix.'sale_money'];
    	$avg_price=$row['sale_money'] / $row['total_num'];
    	$avg_price = round($avg_price,2);
		return  '￥'.$avg_price;
    }
}
