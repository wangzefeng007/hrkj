<?php
class msgJiGuangPushAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{
		$data = $this->loadModel('msgJiGuangPush')->getList('*',$where,'id desc',true);
		$this->assign('data',$data);
		$this->display();
	}
	
	public function show($a)
	{
		echo '<pre style="color:red">';
		echo print_R($a);
		echo '</pre>';
	}
	public function add()
	{
		if (I('submit')) $this->save();
		$levels = $this->loadModel('levelFee')->getList('name,id');
		$this->assign('levels',$levels['list']);
		$this->display('view');
	}
	
	public function edit()
	{
		if (I('submit')) $this->save();
		$id = intval(I('id'));
		$rs = $this->loadModel('msgJiGuangPush')->getInfoByid($id);
		$levels = $this->loadModel('levelFee')->getList('name');
		$this->assign('levels',$levels['list']);
		$this->assign('rs',$rs);		
		$this->display('view_edit');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('title','type','audience','content'));
		$this->vaild_params('is_empty',$_params['title'],'请填写标题！');
		$this->vaild_params('is_empty',$_params['content'],'请填写内容！');
		
		if(!$id) $_params['addtime'] = time();
		$data = array();
		$data = $_params;
		$rs = !$id?$this->loadModel('msgJiGuangPush')->add($data):$this->loadModel('msgJiGuangPush')->update($data,array('id'=>$id));
		$this->ajaxOut($rs,'index');		
	}
	
	public function jpush()
	{
		$id = intval(I('id'));
		$rs = $this->loadModel('msgJiGuangPush')->getInfoByid($id);
		//组装需要的参数
		if($rs['type']==0){
			$receive = 'all';//全部
		}else if($rs['type']==1){
			$receive = array('alias'=>array($rs['audience']));//别名
		}else{
			$receive = array('tag'=>array($rs['audience']));//标签
		}
		$title = $rs['title'];
		//调用推送,并处理
		Vendor('Jpush.jpush');//调用 极光 接口
		$pushObj = new jpush();
		var_dump($receive,$rs['content'],$title,$m_type='',$m_txt='',$m_time='86400',json_encode(array('type'=>4,'content'=>$id,'title'=>$title)));
		$result = $pushObj->push($receive,$rs['content'],$title,$m_type='',$m_txt='',$m_time='86400',json_encode(array('type'=>4,'content'=>$id,'title'=>$title)));
     var_dump($result);  exit;
		$success = 1;
		if($result){
			$res_arr = json_decode($result, true);
			if(isset($res_arr['error'])){  
				$data = array('jpushreturn'=>$result);
				$success = 0;
			}else{
				//处理成功的推送......
				$success = 1;
				$data = array('status'=>1,'jpushreturn'=>$result);
			}
			$this->loadModel('msgJiGuangPush')->update($data,array('id'=>$id));
		}else{
			$data = array('status'=>1,'jpushreturn'=>'无论有没有推送，curl没有返回值');//暂时特殊处理
			$this->loadModel('msgJiGuangPush')->update($data,array('id'=>$id));
		}
		$this->ajaxOut($success,'index');
	}
	
	/*
	*	级别删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('msgJiGuangPush')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}
}
