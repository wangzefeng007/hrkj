<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/17
 * Time: 17:35
 */
class bannerAction  extends baseAction
{
    function geturl(){
        $where ='';
        $rs = $this->loadModel('banner')->getList('*',$where,"sort asc");
        if ($rs) {
            $data = $rs;
        } else {
            $data['is_url'] = 0;
        }
        return $this->apiOut($data);
    }
}