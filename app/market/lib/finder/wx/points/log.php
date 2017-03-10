<?php
class market_finder_wx_points_log
{
    var $addon_cols = "points,point_mode";

    var $column_points = "积分";
    var $column_points_width = 80;
    var $column_points_order = 80;
    function column_points($row)
    {
        $points = $row[$this->col_prefix.'points'];
        $point_mode = $row[$this->col_prefix.'point_mode'];
        if($point_mode == '-'){
            $color = '#FF3300';
        }
        return "<font color='$color'>".$point_mode.$points."</font>";
    }

}