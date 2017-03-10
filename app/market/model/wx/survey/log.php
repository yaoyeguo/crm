<?php
class market_mdl_wx_survey_log extends dbeav_model {

    var $txtSurvey = '';

    function getSurveyByKey($searchkey){
        $curTime = time();

        return $this->db->selectrow('select * from sdb_market_wx_survey where start_date < '.$curTime.' and end_date > '.$curTime.' and keywords="'.$searchkey.'"');
    }

    function createSurvey($wx_id,$survey){
        $item_ids = json_decode($survey['item_ids'],true);
        $txtSurvey = '';
        $survey_log_id = 0;
        if($item_ids){

            //创建问答快照
            $firstSurveyItem = array();
            $survey_items = array();
            $rows = $this->db->select('select item_id,title,item_type,options,option_tags,remark from sdb_market_wx_survey_items where item_id in ('.implode(',', $item_ids).')');
            $items = array();
            foreach($rows as $row){
                $items[$row['item_id']] = $row;
            }
            foreach($item_ids as $k=>$item_id){
                $item = $items[$item_id];
                $item['options'] = json_decode($item['options'],true);
                $item['option_tags'] = json_decode($item['option_tags'],true);
                $survey_items[] =  $item;
                if($item_ids[0] == $item['item_id']){
                    $firstSurveyItem = $item;
                }
            }
            $surveyLog = array(
            'survey_id'=>$survey['survey_id'],
            'survey_items'=>json_encode($survey_items),
            'wx_id'=>$wx_id,
            'end_words'=>$survey['end_words'],
            'created'=>time(),
            'start_date'=>$survey['start_date'],
            'end_date'=>$survey['end_date']
            );
            $this->save($surveyLog);

            $survey_log_id = $surveyLog['survey_log_id'];

            $txtSurvey = $this->getSurveyItem(1,$firstSurveyItem);
        }

        $this->txtSurvey = $txtSurvey;

        return $survey_log_id;
    }

    function getSurveyItem($index,$item){
        $txtSurvey = '';
        if($item){
            $txtSurvey = sprintf("%s:%s\n",$index,$item['title']);
            if($item['item_type'] == 1){//选择题
                foreach($item['option_tags'] as $k=>$tag){
                    if(!empty($tag)){
                        $txtSurvey .= sprintf("%s:%s\n",$tag,$item['options'][$k]);
                    }
                }
            }else{//文字题
                 
            }
        }

        return $txtSurvey;
    }

    function closeSurveyBySys($survey_log_id){

        $this->db->exec('update sdb_market_wx_survey_log set status="sysclose" where survey_log_id='.$survey_log_id);
    }

    function finishSurvey($survey_log_id){

        $this->db->exec('update sdb_market_wx_survey_log set status="finish" where survey_log_id='.$survey_log_id);
    }

    function closeSurveyByUser($survey_log_id){

        $this->db->exec('update sdb_market_wx_survey_log set status="userclose" where survey_log_id='.$survey_log_id);
    }

    function getStartSurvey(){
        return $this->txtSurvey;
    }

    function checkSurvey($wx_id){
        $surveyLog = $this->db->selectrow('select * from sdb_market_wx_survey_log where wx_id="'.$wx_id.'" and status="survey"');
        $survey_log_id = 0;
        if($surveyLog){
            $curTime = time();
            if($curTime > $surveyLog['start_date'] && $curTime < $surveyLog['end_date']){
                $survey_log_id = $surveyLog['survey_log_id'];
            }else{
                $this->closeSurveyBySys($surveyLog['survey_log_id']);
            }
        }

        return $survey_log_id;
    }

    function getEndWords($surveyLog){
        if(!empty($surveyLog['end_words'])){
            return $surveyLog['end_words'];
        }else{
            return '问答已结束';
        }
    }

    function continueSurvey($survey_log_id,$searchkey){
        $surveyLog = $this->db->selectrow('select * from sdb_market_wx_survey_log where survey_log_id='.$survey_log_id);

        if(strtolower($searchkey) == 'n'){//退出问答
            $this->closeSurveyByUser($survey_log_id);
            return $this->getEndWords($surveyLog);
        }

        $survey_items = json_decode($surveyLog['survey_items'],true);
        $survey_result = json_decode($surveyLog['result'],true);
        //var_dump($survey_result);exit;
        if($survey_result){
            $result_ids = array_keys($survey_result);
        }else{
            $result_ids = array();
        }

        $nextSurveyItem = array();
        $curSurveyItem = array();
        $survey_index = 0;

        //找出当前和下一道题
        foreach($survey_items as $k=>$item){
            if(!in_array($item['item_id'], $result_ids)){
                $curSurveyItem = $item;
                if(isset($survey_items[$k+1])){
                    $nextSurveyItem = $survey_items[$k+1];
                    $survey_index = $k+1;
                }
                break;
            }
        }
        //var_dump($curSurveyItem);exit;

        //保存用户回复的答案
        $objSurveyResult = app::get('market')->model('wx_survey_result');
        $data = array('survey_id'=>$surveyLog['survey_id'],'item_id'=>$curSurveyItem['item_id'],'survey_log_id'=>$survey_log_id,'wx_id'=>$surveyLog['wx_id'],'created'=>time());
        if($curSurveyItem['item_type'] == 1){//选择题
            $result = '';
            foreach($curSurveyItem['option_tags'] as $k=>$optionTag){
                if(!empty($optionTag) && $optionTag == $searchkey){
                    $result = $curSurveyItem['options'][$k];
                    break;
                }
            }
            if(empty($result)){
                return '请回复正确问答选项,退出问答,请回复[n]';
            }
            $data['result'] = $result;
            $data['result_no'] = $searchkey;
        }else{//文字题
            $data['result'] = $searchkey;
        }
        $objSurveyResult->save($data);

        //更新用户问答快照表回复字段
        $survey_result[$curSurveyItem['item_id']] = $searchkey;
        $survey_log_data = array('survey_log_id'=>$survey_log_id,'result'=>json_encode($survey_result));
        $this->save($survey_log_data);

        //响应用户请求
        $responseTxt = '';
        if($nextSurveyItem){
            $responseTxt = $this->getSurveyItem($survey_index, $nextSurveyItem);
        }else{
            $this->finishSurvey($survey_log_id);
            $responseTxt = $this->getEndWords($surveyLog);
        }

        return $responseTxt;
    }
}
