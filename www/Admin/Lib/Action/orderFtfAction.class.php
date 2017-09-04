<?php
class orderFtfAction extends baseAction
{

	
	public function lists()
	{
		$_params = $this->get_params(array('starttime','endtime','ptid','keytype','keyword','status','sn','export'));
        $_params['status'] = I('status','1');
		$where = array('order_type'=>array('in',array(ORDER_SHOP,ORDER_FTF)));
	    if(empty($_params['starttime'])){
	        $_params['starttime'] = date('Y-m-d');
	    }
	    if(empty($_params['endtime'])){
	        $_params['endtime'] = date('Y-m-d');
	    }
		if ($_params['status']!='') $where['status'] = $_params['status'];
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['sn']) $where['sn'] = trim($_params['sn']);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".trim($_params['keyword'])."%");
		}
		// $fields = array();
		// $fields['account_user_saler_income'] = 'id,sn,pt_name,money,addtime,type,order_type';
		// $fields['user_saler'] = 'name,mobile';
		// $join = array('user_saler','usid','id');
		// $data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc',true);
		
		if ($_params['export'])
		{
			//表格下载
			import("@.Tool.export");
			if (method_exists('export',$_params['export']))
			{
				$_title = array('订单号','姓名','账号','支付方式','收款金额','收款时间','状态','备注');
				// $_field = array('name','mobile','bank','bank_no','pt_name','ct_name','money','fee_static','fee_rate');
				
				$data = $this->loadModel('orderFtf')->getList('',$where,'id desc');
				$_data = array();
				foreach($data['list'] as $key=>$value)
				{
					$_data[$key]['sn'] = $value['sn'];
					$_data[$key]['us_name'] = $value['us_name'];
					$_data[$key]['us_mobile'] = $value['us_mobile'];
					$_data[$key]['pt_name'] = $value['pt_name'];
					$_data[$key]['money'] = $value['money'];
					$_data[$key]['paytime'] = date("Y-m-d H:i:s",$value['paytime']);
					$_data[$key]['status'] = status_desc("PAY_STATUS",$value['status']);
					$_data[$key]['desc'] = $value['desc'];
				}
				export::$_params['export']($_title,$_data,'收款列表');
				exit;				
			}
		}
		else
		{
			$data = $this->loadModel('orderFtf')->getList('',$where,'id desc',true);
			// $all_data = $this->loadModel('orderFtf')->getList('',$where,'id desc');
		}
		
		// $ids = reset_array_key($all_data['list'],'id','id');
		$data['money'] = $this->loadModel('orderFtf')->where($where)->sum('money');
		
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1),'sort desc');
		$this->assign('pay_type',$pay_type['list']);
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	

}
