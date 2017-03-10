<?php 
class ecgoods_finder_group{

    var $addon_cols = 'cat_path,group_name';

    var $column_edit = "操作";
    var $column_edit_width = 180;
    var $column_edit_order = COLUMN_IN_HEAD;
    function column_edit($row){
        //$oBrand = &app::get('ecgoods')->model('brand');
        //$rs_brand = $oBrand->dump(array('brand_id'=>$row['brand_id']));

        $return = '<a target="dialog::{width:400,height:170,title:\'编辑分组\'}" href="index.php?app=ecgoods&ctl=admin_group&act=edit&group_id='.$row['group_id'].'">修改</a>';
        
        $return .= '　<a target="dialog::{width:400,height:170,title:\'编辑分组\'}" href="index.php?app=ecgoods&ctl=admin_group&act=edit&parent_id='.$row['group_id'].'">增加分组</a>';
        
        $return .= '　<a target="dialog::{width:780,height:410,title:\'设置商品\'}" href="index.php?app=ecgoods&ctl=admin_group&act=set_goods&group_id='.$row['group_id'].'">设置商品</a>';

        return $return;
    }
    
    var $column_title = "分组名称";
    var $column_title_width = 240;
    var $column_title_order = 5;
    function column_title($row){
        //$oBrand = &app::get('ecgoods')->model('brand');
        //$rs_brand = $oBrand->dump(array('brand_id'=>$row['brand_id']));
        $cat_path = $row[$this->col_prefix.'cat_path'];
        $group_name = $row[$this->col_prefix.'group_name'];
        $cat_path_arr = explode(',',$cat_path);
        $cat_prefix = '';
        if(sizeof($cat_path_arr)>2){
            for($i=3;$i<sizeof($cat_path_arr);$i++){
                $cat_prefix .= '　　';
            }
            $cat_prefix .= '　┗ ';
        }

        $return = $cat_prefix.''.$group_name.'';

        return $return;
    }
    
}