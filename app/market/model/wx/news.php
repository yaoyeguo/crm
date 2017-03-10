<?php
class market_mdl_wx_news extends dbeav_model {

    function saveNews($post)
    {
        if($post['type'] == 1)//单条图文编辑
        {
            if($post['news']['link_type'] == 'url')
            {
                unset($post['news']['link_type_article']);
            }else{
                unset($post['news']['link_type_url']);
            }
            $news_info = json_encode($post['news']);
            $data = array('type'=>$post['type'],'modified'=>date('Y-m-d H:i:s'),'news_info'=>$news_info,'title'=>$post['news']['title'],'picurl'=>$post['news']['picurl'],'digest'=>$post['news']['digest']);
        }else//多条图文编辑
        {
            $news_info = array();
            foreach($post['news']['title'] as $k=>$row)
            {
                if(!empty($row))
                {
                    $news_info_tmp = array('title'=>$row,'picurl'=>$post['news']['picurl'][$k],'digest'=>$post['news']['digest'][$k],'link_type'=>$post['news']['link_type'][$k]);
                    if($post['news']['link_type'][$k] == 'url')
                    {
                        $news_info_tmp['link_type_url'] = $post['news']['link_type_url'][$k];
                    }else
                    {
                        $news_info_tmp['link_type_article'] = $post['news']['link_type_article'][$k];
                    }
                    $news_info[] = $news_info_tmp;
                }
            }
            $news_info = json_encode($news_info);
            $data = array('type'=>$post['type'],'modified'=>date('Y-m-d H:i:s'),'news_info'=>$news_info,'title'=>$post['news']['title'][0],'picurl'=>$post['news']['picurl'][0],'digest'=>$post['news']['digest'][0]);
        }

        if(empty($post['wx_news_id']))
        {
            $data['created'] = date('Y-m-d H:i:s');
        }else
        {
            $data['wx_news_id'] = $post['wx_news_id'];
        }

        return $this->save($data);
    }


    function modifier_picurl($row){
        if ($row){
            $img_src = base_storager::image_path($row,'s' );
            if(!$img_src)return '';
            return '<img class="img-tip pointer " onmouseover="bindFinderColTip(event);" src="'.$img_src.'"  title="" />';
        }else{
            return '-';
        }
    }
}
