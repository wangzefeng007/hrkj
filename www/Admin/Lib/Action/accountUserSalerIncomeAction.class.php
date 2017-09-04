<?php
class accountUserSalerIncomeAction extends baseAction
{

	
	public function lists()
	{
		$_params = $this->get_params(array('starttime','endtime','ptid','keytype','keyword','order_sn','export'));
		$where = array('order_type'=>array('in',array(ORDER_SHOP,ORDER_FTF)));
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['order_sn']) $where['order_sn'] = $_params['order_sn'];
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		$fields = array();
		$fields['account_user_saler_income'] = 'id,order_sn,pt_name,money,addtime,type,order_type';
		$fields['user_saler'] = 'name,mobile';
		$join = array('user_saler','usid','id');
		// $data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc',true);
		
		if ($_params['export'])
		{
			//表格下载
			import("@.Tool.export");
			if (method_exists('export',$_params['export']))
			{
				$_title = array('订单号','姓名','账号','支付方式','收款金额','收款时间','类型');
				$_field = array('name','mobile','bank','bank_no','pt_name','ct_name','money','fee_static','fee_rate');
				
				$data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc');
				$_data = array();
				foreach($data['list'] as $key=>$value)
				{
					$_data[$key]['order_sn'] = $value['order_sn'];
					$_data[$key]['name'] = $value['name'];
					$_data[$key]['mobile'] = $value['mobile'];
					$_data[$key]['pt_name'] = $value['pt_name'];
					$_data[$key]['money'] = $value['money'];
					$_data[$key]['addtime'] = date("Y-m-d H:i:s",$value['addtime']);
					$_data[$key]['type'] = status_desc("INCOME_TYPE",$value['type']);
				}
				export::$_params['export']($_title,$_data,'收款列表.xls');
				exit;				
			}
		}
		else
		{
			$data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc',true);
			$all_data = $this->loadModel('accountUserSalerIncome')->getJoinList($fields,$join,$where,'id desc');
		}
		
		$ids = reset_array_key($all_data['list'],'id','id');
		$data['money'] = $this->loadModel('accountUserSalerIncome')->where(array('id'=>array('in',$ids)))->sum('money');
		
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1),'sort desc');
		$this->assign('pay_type',$pay_type['list']);
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	

}
