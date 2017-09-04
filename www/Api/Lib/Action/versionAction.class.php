<?php
class versionAction extends baseAction
{
	/**
	 * 客户端升级检查接口
	 */
	public function upgrade()
	{
		$version_code = I('version_code');
		$os = I('os');

		$this->vaild_params('is_empty',$version_code,'版本号不能为空!');
		$this->vaild_params('is_empty',$os,'os类型不能为空!');

		$where = array(
			'version_code' => array('gt',$version_code),
			'os' => $os,
		);
		$rs = $this->loadModel('version')->getInfo('*',$where,"version_code desc");
		if ($rs) {
			$data = $rs;
		}
		else {
			$data['is_update'] = 0;
		}
		return $this->apiOut($data);
	}
    /**
     * 最新客户端升级检查接口
     */
    public function newupgrade(){
        foreach (getallheaders() as $key => $value) {
            if ($key=='version_name'){
                $version_name = $value;
            }elseif ($key=='os'){
                $os = $value;
            }
        }
        $this->vaild_params('is_empty',$version_name,'版本名称不能为空!');
        $this->vaild_params('is_empty',$os,'os类型不能为空!');
        $where = array(
            'os' => $os,
        );
        $rs = $this->loadModel('version')->getInfo('*',$where,"version_code desc");
            if ($rs['version_name']!=$version_name){
                if ($rs['status']==1){//判断版本状态为审核通过，才可以提醒用户更新版本
                    $data = $rs;
                }else{
                    $data['is_update'] = 0;
                }
            } else {
                $data['is_update'] = 0;
            }
        return $this->apiOut($data);
    }
    /**
     *@desc	 判断app是否通过审核
     */
    public function appstatus(){
        foreach (getallheaders() as $key => $value) {
            if ($key=='Version-Name'){
                $version_name = $value;
            }elseif ($key=='Os'){
                $os = $value;
            }elseif ($key=='Channel-Id'){
                $channel_id = $value;
            }
        }
//        $version_name = I('version_name');
//        $os = I('os');
        $this->vaild_params('is_empty',$version_name,'版本名称不能为空!');
        $this->vaild_params('is_empty',$os,'os类型不能为空!');
        if ($os==1){
//           $this->vaild_params('is_empty',$channel_id,'渠道id不能为空!');
            $where = array(
                'version_name' => array('like',"%{$version_name}%"),
                'os' => $os,
                'channel_id' => array('like',"%{$channel_id}%"),
            );
        }else{
            $where = array(
                'version_name' => array('like',"%{$version_name}%"),
                'os' => $os,
            );
        }

        $rs = $this->loadModel('version')->getInfo('*',$where,"version_code desc");
        if ($rs){
            if ($rs['status']==1){
                if (strstr($rs['channel_id'], $channel_id)){
                    $data['status'] = 0;
                }else{
                    $data['status'] = strval($rs['status']);
                }
            }else{
                $data['status'] = strval($rs['status']);
            }
        }else{
            $data['status'] = strval($rs['status']);
        }
        return $this->apiOut($data);
    }
}
