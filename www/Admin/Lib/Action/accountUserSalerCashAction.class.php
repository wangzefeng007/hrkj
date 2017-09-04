<?php
class accountUserSalerCashAction extends baseAction
{
	//收款结算
	public function normal()
	{
		$xls_type = I('xls_type');
		$_params = $this->get_params(array('add_starttime', 'add_endtime', 'dispos_starttime', 'dispos_endtime','ptid','status','ctid','keytype','keyword','export','id','split_type','risk','sn'));

		if(empty($_params['add_starttime'])){
		    $add_time = date('Y-m-d');
		    $end_time = date('Y-m-d');
		}
		if((!empty($_params['dispos_starttime'])||!empty($_params['dispos_endtime']))){
		    $add_time = $_params['add_starttime'];
		    $end_time =$_params['add_endtime'];
		    unset($_params['add_endtime']);
		    unset($_params['add_starttime']);		    
		}
		$_params['add_starttime'] = $add_time;
		$_params['add_endtime'] = $end_time;
		$where = array('type'=>CASH_NORMAL);
		if ($_params['add_starttime']) $where['addtime'][] = array('egt',strtotime($_params['add_starttime']));
		if ($_params['add_endtime']) $where['addtime'][] = array('lt',strtotime($_params['add_endtime'])+24*3600);
		if ($_params['dispos_starttime']) $where['dispostime'][] = array('egt',strtotime($_params['dispos_starttime']));
		if ($_params['dispos_endtime']) $where['dispostime'][] = array('lt',strtotime($_params['dispos_endtime'])+24*3600);
		if ($_params['id']) $where['id'] = array('in',$_params['id']);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['ctid']) $where['ctid'] = $_params['ctid'];
		if ($_params['sn']) $where['sn'] = array('like', "%" .trim($_params['sn']). "%");
		if ($_params['status'] !='') 
		{
			$where['status'] = intval($_params['status']);
			$_params['status'] = intval($_params['status']);
		}
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where['user_saler.'.$_params['keytype']] = array('like',"%".trim($_params['keyword'])."%");
		}
		// dump($_params);
		$fields = array();
		$fields['account_user_saler_cash'] = '*';
		$fields['user_saler'] = 'name,card_no,mobile,bank_no,bank,bank_address';
		$fields['pay_type'] = 'name as pt_name';
		$join[] = array('user_saler','usid','id');
		$join[] = array('pay_type','ptid','id');
		if ($_params['export'])
		{
			//表格下载
			if ($xls_type == 'xls')
			{
				$this->xls($_params,$fields,$join,$where,'收款结算');
			}
			// elseif ($xls_type == 'eb_xls')
			// {
				// $this->xls_eb($_params,$fields,$join,$where,'易宝-收款结算');
			// }
			elseif ($xls_type == 'cmbc_txt')
			{
				$_params['export'] = 'txt';
				$this->cmbc_txt($_params,$fields,$join,$where,'民生-收款结算');
			}
			exit;
		}
		else
		{
			$data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc',true);
			// $all_data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
		}

		// $ids = reset_array_key($all_data['list'],'id','id');
		$data['money'] = $this->loadModel('accountUserSalerCash')->where($where)->sum('money');
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1),'sort desc');
		$this->assign('pay_type',$pay_type['list']);
		$cash_type = $this->loadModel('cashType')->getList('id,name');
		$this->assign('cash_type',$cash_type['list']);
        $this->assign('cash_status', C('CASH_STATUS'));
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	
	//分润结算（分润及其他）
	public function split()
	{
		$xls_type = I('xls_type');
		$_params = $this->get_params(array('add_starttime', 'add_endtime', 'dispos_starttime', 'dispos_endtime','ptid','status','ctid','keytype','keyword','export','id','split_type','risk','sn'));
		$add_time = $_params['add_starttime'];
		$end_time = $_params['add_endtime'];
	    if(empty($_params['add_starttime'])){
		    $add_time = date('Y-m-d');
		    $end_time = date('Y-m-d');
		}
		if((!empty($_params['dispos_starttime'])||!empty($_params['dispos_endtime']))){
		    unset($_params['add_endtime']);
		    unset($_params['add_starttime']);
		    $add_time = $_params['add_starttime'];
		    $end_time =$_params['add_endtime'];
		    
		}
		$_params['add_starttime'] = $add_time;
		$_params['add_endtime'] = $end_time;
		$where = array('type'=>array('in',array(CASH_SPLIT,CASH_COMMISSION)));
		if ($_params['add_starttime']) $where['addtime'][] = array('egt',strtotime($_params['add_starttime']));
		if ($_params['add_endtime']) $where['addtime'][] = array('lt',strtotime($_params['add_endtime'])+24*3600);
		if ($_params['dispos_starttime']) $where['dispostime'][] = array('egt',strtotime($_params['dispos_starttime']));
		if ($_params['dispos_endtime']) $where['dispostime'][] = array('lt',strtotime($_params['dispos_endtime'])+24*3600);
		if ($_params['ptid']) $where['ptid'] = $_params['ptid'];
		if ($_params['ctid']) $where['ctid'] = $_params['ctid'];
		if ($_params['sn']) $where['sn'] = array('like', "%" . trim($_params['sn']) . "%");
		if ($_params['status'] !='') 
		{
			$where['status'] = intval($_params['status']);
			$_params['status'] = intval($_params['status']);
		}
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where['user_saler.'.$_params['keytype']] = array('like',"%".trim($_params['keyword'])."%");
		}
		$fields = array();
		$fields['account_user_saler_cash'] = '*';
		$fields['user_saler'] = 'name,mobile,bank_no,bank,bank_address';
		$fields['pay_type'] = 'name as pt_name';
		$join[] = array('user_saler','usid','id');
		$join[] = array('pay_type','ptid','id');
		if ($_params['export'])
		{
			//表格下载
			if ($xls_type == 'xls')
			{
				$this->xls($_params,$fields,$join,$where,'分润结算');
			}
			// elseif ($xls_type == 'eb_xls')
			// {
				// $this->xls_eb($_params,$fields,$join,$where,'易宝-分润结算');
			// }
			elseif ($xls_type == 'cmbc_txt')
			{
				$_params['export'] = 'txt';
				$this->cmbc_txt($_params,$fields,$join,$where,'民生-分润结算');
			}
			exit;
		}
		else
		{
			$data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc',true);
			// $all_data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
		}
		// $ids = reset_array_key($all_data['list'],'id','id');
		$data['money'] = $this->loadModel('accountUserSalerCash')->where($where)->sum('money');
		$pay_type = $this->loadModel('payType')->getList('id,name',array('status'=>1));
		$this->assign('pay_type',$pay_type['list']);
		$cash_type = $this->loadModel('cashType')->getList('id,name');
		$this->assign('cash_type',$cash_type['list']);
        $this->assign('cash_status', C('CASH_STATUS'));
		$this->assign('data',$data);		
		$this->assign('_params',$_params);
		$this->display();
	}
	
	//批量修改未结算状态->已结算
	function pay_cash()
	{
		$_params = $this->get_params(array('pay_cash_id'));
		$this->vaild_params('is_empty',$_params['pay_cash_id'],'错误!请选择需要处理的项目!');
		if ($_params['pay_cash_id'])
		{
			$where = array();
			$where['status'] = 0;
			$where['id'] = array('in',$_params['pay_cash_id']);
		}
		//更新状态
		$rs = $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),$where);
		$rs?$this->response(REQUEST_SUCCESS,'操作成功'):$this->ajaxOut();
	}
	
	//普通表格下载
	function xls($_params,$fields,$join,$where,$file)
	{
		import("@.Tool.export");
		if (method_exists('export',$_params['export']))
		{
			// $_title = array('批次号','订单号','帐户名称','银行账号','开户行','省','市','金额','打款原因','开户银行全称','手机号');
			$_title = array('结算单号','商户姓名','身份证号码','手机号','银行名称','银行账号','支付方式','结算方式','结算金额','结算费','费率','实得金额','申请时间','结算时间');
			
			if ($_params['id'])
			{
				$where['id'] = array('in',implode(',',$_params['id']));
			}
			$data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
			$_data = array();
			$update_ids = array();
			foreach($data['list'] as $key=>$value)
			{
				$is_sheng = strpos($value['bank_address'],"省");
				$is_shi = strpos($value['bank_address'],"市");

				$update_ids[] = $value['id'];
				$_data[$key][] = $value['sn'];
				$_data[$key][] = $value['name'];
				$_data[$key][] = $value['card_no'];
				$_data[$key][] = $value['mobile'];
				$_data[$key][] = $value['bank'];
				$_data[$key][] = (string) $value['bank_no'];
				$_data[$key][] = $value['pt_name'];
				$_data[$key][] = $value['ct_name'];
				$_data[$key][] =  (string) $value['money'];
				$_data[$key][] =  (string) $value['fee_static'];
				$_data[$key][] =  (string) $value['fee_rate'];
				$_data[$key][] =  (string) round($value['money']-$value['money']*$value['fee_rate']-$value['fee_static'],2);
				$_data[$key][] =  vtime("Y-m-d H:i:s",$value['addtime']);
				$_data[$key][] =  vtime("Y-m-d H:i:s",$value['dispostime']);
			}
			export::$_params['export']($_title,$_data,$file);
			//更新状态
			// $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),array('status'=>0,'id'=>array('in',$update_ids)));
		}
	}
	
	//易宝表格下载
	function xls_eb($_params,$fields,$join,$where,$file)
	{
		import("@.Tool.export");
		if (method_exists('export',$_params['export']))
		{
			$_title = array('批次号','订单号','帐户名称','银行账号','开户行','省','市','金额','打款原因','开户银行全称','手机号');
			
			if ($_params['id'])
			{
				$where['id'] = array('in',implode(',',$_params['id']));
			}
			$data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
			$_data = array();
			$update_ids = array();
			foreach($data['list'] as $key=>$value)
			{
				$is_sheng = strpos($value['bank_address'],"省");
				$is_shi = strpos($value['bank_address'],"市");

				$update_ids[] = $value['id'];
				$_data[$key]['id'] = $value['id'];
				$_data[$key]['sn'] = $value['sn'];
				$_data[$key]['name'] = $value['name'];
				$_data[$key]['bank_no'] = (string) $value['bank_no'];
				$_data[$key]['bank'] = $value['bank'];
				$_data[$key]['add_sheng']  = $is_sheng?substr($value['bank_address'],0,$is_sheng)."省":'';
				$_data[$key]['add_shi']  = $is_shi?str_replace($_data[$key]['add_sheng'],'',substr($value['bank_address'],0,$is_shi))."市":'';
				$_data[$key]['money'] = (string) round($value['money']-$value['money']*$value['fee_rate']-$value['fee_static'],2);
				$_data[$key]['memo'] = $value['memo'];
				$_data[$key]['bank_address']  = $value['bank_address'];
				$_data[$key]['mobile'] = $value['mobile'];

			}
			export::$_params['export']($_title,$_data,$file);
			// 更新状态
			// $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),array('status'=>0,'id'=>array('in',$update_ids)));
		}
	}
	//民生TXT表格下载
	function cmbc_txt($_params,$fields,$join,$where,$file)
	{
		if (!$_params['id'])
		{
			echo '请选择要结算的项目';
			exit;
		}
		import("@.Tool.export");
		if (method_exists('export',$_params['export']))
		{
			$_title = array('第三方流水号','帐号','户名','支付行号','开户行名称','金额','摘要','备注');
			
			if ($_params['id'])
			{
				$where['id'] = array('in',implode(',',$_params['id']));
			}
			$data = $this->loadModel('accountUserSalerCash')->getJoinList($fields,$join,$where,'id desc');
			$_data = array();
			$update_ids = array();
			$total_money = 0;
			foreach($data['list'] as $key=>$value)
			{
				$update_ids[] = $value['id'];

				$_data[$key]['sn'] = $value['sn'];
				$_data[$key]['bank_no'] = (string) $value['bank_no'];
				$_data[$key]['name'] = $value['name'];
				$_data[$key]['bank_id'] = '[请填入支付行号]';
				$_data[$key]['bank'] = $value['bank'];
				$_data[$key]['money'] = (string) round($value['money']-$value['money']*$value['fee_rate']-$value['fee_static'],2) * 100;
				$_data[$key]['disp'] = '';
				$_data[$key]['memo'] = $value['memo'];
				
				$total_money += $_data[$key]['money'];
			}
			$txt = "PO|".count($_data)."|{$total_money}\r\n";
			if ($_data)
			{
				foreach($_data as $key => $val)
				{
					$txt .= implode('|',$val);
					$txt .= "\r\n";
				}
			}
			$txt .= '########';
			// 更新状态
			// $this->loadModel('accountUserSalerCash')->update(array('status'=>1,'dispostime'=>time()),array('status'=>0,'id'=>array('in',$update_ids)));
			// 导出文件
			export::$_params['export']($txt,$file);
		}
	}
	
	public function detail() {
		$id = intval(I('id'));
		$fields = array();
		$fields['account_user_saler_cash'] = '*';
		$fields['user_saler'] = 'name,mobile,bank_no,bank';
		$join[] = array('user_saler', 'usid', 'id');
		$where = array('id' => $id);
		$cash = $this->loadModel('accountUserSalerCash')->getJoinInfo($fields, $join, $where);
		$this->assign('cash', $cash);

		$fields = array();
		$fields['account_user_saler_income'] = "*";
		$fields['user_saler'] = 'name as us_name,lfid';
		$fields['level_fee'] = 'name as lf_name';
		$join = array();
		$join[] = array('user_saler', 'usid', 'id');
		$join[] = array('level_fee', 'lfid', 'id');
		$where = array('uscid' => $id);
		$split = $this->loadModel('accountUserSalerIncome')->getJoinList($fields, $join, $where);
		$this->assign('split', $split['list']);

		$this->display();
	}

}
