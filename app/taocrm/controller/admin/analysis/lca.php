<?php
class taocrm_ctl_admin_analysis_lca extends desktop_controller
{
    static $middleware_conn = null;
    var $workground = 'taocrm.analysts';

    public function __construct($app)
    {
        parent::__construct($app);


        if (self::$middleware_conn == null)
            self::$middleware_conn = kernel::single('taocrm_middleware_connect');

        $timeBtn = array(
            'today' => date("Y-m-d"),
            'yesterday' => date("Y-m-d", time()-86400),
            'this_month_from' => date("Y-m-" . 01),
            'this_month_to' => date("Y-m-d"),
            'this_week_from' => date("Y-m-d", time()-(date('w')?date('w')-1:6)*86400),
            'this_week_to' => date("Y-m-d"),
            'sevenday_from' => date("Y-m-d", time()-6*86400),
            'sevenday_to' => date("Y-m-d"),
        );
        $this->pagedata['timeBtn'] = $timeBtn;

        //初始化统计时间段
        $kv = base_kvstore::instance('analysis');
        if($_POST['date_from'] && $_POST['date_to']){
            $kv->store('analysis_date_from',$_POST['date_from']);
            $kv->store('analysis_date_to',$_POST['date_to']);
        }
        if($_POST['shop_id']) $kv->store('analysis_shop_id',$_POST['shop_id']);
        $kv->fetch('analysis_shop_id',$this->shop_id);
        $kv->fetch('analysis_date_from',$this->date_from);
        $kv->fetch('analysis_date_to',$this->date_to);
        if(!$this->date_from) $this->date_from = date('Y-m-d',(time()-86400*7));
        if(!$this->date_to) $this->date_to = date('Y-m-d',(time()-86400*1));
    }

    public function index()
    {
        $rs = app::get('ecorder')->model('shop')->get_shops('no_fx');
        foreach($rs as $v){
            if(!$args['shop_id'])
                $args['shop_id'] = $v['shop_id'];
            $shops[$v['shop_id']] = $v['name'];
        }
        $this->pagedata['shops']= $shops;
        $this->pagedata['form_action'] = 'index.php?app=taocrm&ctl=admin_analysis_lca&act=index';
        $frist_shop = $rs[0];

        $count_by = $_POST['count_by'] ? $_POST['count_by'] : 45;
        
        $this->pagedata['beginTime'] = strtotime($this->date_from);
        $this->pagedata['endTime'] = strtotime($this->date_to);
        $this->pagedata['combineRange'] = $count_by;

        $this->pagedata['date_from'] = $this->date_from;
        $this->pagedata['date_to']	= $this->date_to;
        $this->pagedata['shop_id']	= $this->shop_id ? $this->shop_id : $frist_shop['shop_id'];
        $this->pagedata['count_by']	= $count_by;

        $params = array(
            'shop_id' => $this->pagedata['shop_id'],
            'range' => $count_by,
            'date_from' => strtotime($this->date_from),
            'date_to' => strtotime($this->date_to),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanLca($params);
        $all_data = $all_res[800026]['data'];

        $this->pagedata['coordinate'] = $all_data['coordinate'];
        $this->pagedata['lifecycle'] = $all_data['lifecycle'];

        $this->page('admin/analysis/lca/index.html');
    }

    public function member_list()
    {
        $params = array(
            'shopId' => !empty($_GET['shop_id']) ? trim($_GET['shop_id']) : 0,
            'beginDays' => !empty($_GET['beginDays']) ? intval($_GET['beginDays']) : 0,
            'endDays' => !empty($_GET['endDays']) ? intval($_GET['endDays']) : 0,
            'targets' => !empty($_GET['targets']) ? intval($_GET['targets']) : 2,
        );

        if(!$params['shopId'] || !$params['endDays'])
        {
            exit("参数有误");
        }
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanMemberList($params);
    }

    public function order_status()
    {
        $params = array(
                'shop_id' => $_GET['shop_id'],//'e5191c274efb3e2a851bc659fb4989ad',
                'range' => $_GET['count_by'],
                'date_from' => strtotime($_GET['date_from']),
                'date_to' => strtotime($_GET['date_to']),
        );
        $all_res = kernel::single('taocrm_middleware_connect')->createCallplanLca($params);
        $all_data = $all_res[800026]['data'];

        $coordinate = $all_data['coordinate'];

        $chartLabel = array('y1'=>'占比','y2'=>'人数');
        foreach($coordinate as $k => $v){
            $dataset[] = array(
                    'x' => $k.'天',
                    'y1' => $v['persent'],
                    'y2' => $v['members'],
            );
        }

        foreach($dataset as $k=>$v)
        {
            $dataset[$k] = $v;
        }

        $chartData = json_encode($dataset);


        $this->pagedata['chartLabel'] = $chartLabel;
        $this->pagedata['chartData'] = $chartData;

        $this->display("admin/analysis/lca/showtable.html");
    }
}
