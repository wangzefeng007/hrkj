<?php
/**
 * @desc 消息设置
 * Class settingMsgAction
 */
class settingMsgAction extends baseAction
{
	/*
	*	默认入口
	*/
	public function index()
	{

		$_params = $this->get_params(array('name'));
		$where = array(
			'status' => 1,
		);
		if ($_params['name']) $where['name'] = array('like',"%{$_params['name']}%");
		$data = $this->loadModel('settingMsg')->getList('*',$where,'id asc',true);
		if ($data['list'])
		{
			foreach ($data['list'] as $k => $v)
			{
				if ($v['params']) {
					$params = unserialize($v['params']);
					foreach ($params as $k_p => $v_p)
					{
						$status[$v['id']][$k_p] = $v_p['status'];
					}
				}
			}
		}
		$this->assign('data',$data);
		$this->assign('status',$status);
		$this->assign('_params',$_params);		
		$this->display();
	}
	
	
	public function add()
	{
		$this->display('view');
	}
	
	public function edit()
	{
		$id = intval(I('id'));
		$rs = $this->loadModel('settingMsg')->getInfoByid($id);
		$this->assign('rs',$rs);		
		$this->display('view');
	}
	
	public function save()
	{
		$id = intval(I('id'));
		$_params = $this->get_params(array('name','type'));
		$this->vaild_params('is_empty',$_params['name'],'消息类型名称 不能为空！');
		$this->vaild_params('is_empty',$_params['type'],'类型编号 不能为空！');
		
		if (!$id)
		{
			$rs = $this->loadModel('settingMsg')->getInfo('*',array('type'=>$_params['type']));
			$this->vaild_params('is_empty',$rs,'类型编号 已经存在!',false);
		}
		$data = array();
		$data = $_params;
		$data['addtime'] = time();
		$rs = !$id?$this->loadModel('settingMsg')->add($data):$this->loadModel('settingMsg')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}

	/*
	*	删除
	*/
	public function delete()
	{
		$ids = is_array(I('id'))?I('id'):array(I('id'));
		$rs = $this->loadModel('settingMsg')->del(array('id'=>array('IN',$ids)));
		$this->ajaxOut($rs,'index');
	}

	//消息模板编辑
	public function board_edit()
	{
		$id = intval(I('id'));
		$send_type = I('send_type');
		$rs = $this->loadModel('settingMsg')->getInfoByid($id);
		$params = unserialize($rs['params']);
		$data = $params[$send_type];
		$this->assign('rs',$rs);		
		$this->assign('data',$data);		
		$this->assign('send_type',$send_type);		
		$this->display($send_type);
	}
	
	//消息模板保存
	public function board_save()
	{
		$id = intval(I('id'));
		$send_type = I('send_type');
		$_params = I('data');
		
		if(!$_params['content']) $_params['status'] = 0;
		$rs = $this->loadModel('settingMsg')->getInfoByid($id);
		$params = unserialize($rs['params']);
		$params[$send_type] = $_params;
		
		$data['params'] = serialize($params);
		$rs = $this->loadModel('settingMsg')->update($data,array('id'=>$id));

		$this->ajaxOut($rs,'index');		
	}
	
}
