<?php
/**
 * @desc 商户管理
 * Class userSalerAction
 */
class userSalerAction extends baseAction
{
	
	public function index()
	{

		$_params = $this->get_params(array('starttime','endtime','startaddtime','endaddtime','lfid','mobile','name','realname','status','status_business','export','id'));
		$where = array();

		if ($_params['starttime']) $where['checktime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['checktime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
        if ($_params['startaddtime']) $where['addtime'][] = array('egt',strtotime($_params['startaddtime']));
        if ($_params['endaddtime']) $where['addtime'][] = array('lt',strtotime($_params['endaddtime'])+24*3600);
		if ($_params['lfid']) $where['lfid'] = $_params['lfid'];//var_dump($where);exit;
        if (trim($_params['mobile'])){
            $where['mobile'] = array('like',"%".$_params['mobile']."%");
        }
        if (trim($_params['name'])){
            $where['name'] = array('like',"%".$_params['name']."%");
        }
        if (trim($_params['realname'])){
            $where['realname'] = array('like',"%".$_params['realname']."%");
        }
		if ($_params['status']!='')
		{
		    if ($_params['status']==10){
                $_params['status'] = 10;
            }else{
                $where['status'] = $_params['status'];
                $_params['status'] = intval($_params['status']);
            }
		}
		if ($_params['status']==''){
            $where['status'] = 0;
            $_params['status'] = 0;
        }
		if ($_params['status_business']!='') 
		{
			$where['status_business'] = $_params['status_business'];
			$_params['status_business'] = intval($_params['status_business']);
		}
		$fields = array();
		// $fields['user_saler'] = 'id,name,mobile,lfid,addtime,lastlogintime,status';
		$fields['user_saler'] = '*';
		$fields['level_fee'] = 'name AS lf_name';
		$fields['user_saler_account_total'] = 'normal_usable,split_usable';
		$join[] = array('level_fee','lfid','id');
		$join[] = array('user_saler_account_total','id','usid');
		// $data = $this->loadModel('userSaler')->getJoinList($fields,$join,$where," id DESC ",true);

		if ($_params['export'])
		{
			//表格下载
			import("@.Tool.export");
			if (method_exists('export',$_params['export']))
			{
				$_title = array('姓名','账号','费率级别','身份证','储蓄卡号','所属支行','账户余额','分润余额','注册时间','最后登录','状态','上级','审核人','审核时间');
				if ($_params['id']) $where['id'] = array('in',$_params['id']);
				$data = $this->loadModel('userSaler')->getJoinList($fields,$join,$where," id DESC ");
				$_data = array();
				foreach($data['list'] as $key=>$value)
				{
					$_data[$key][] = $value['name'];
					$_data[$key][] = $value['mobile'];
					$_data[$key][] = $value['lf_name'];
                    $_data[$key][] = $value['card_no'];
                    $_data[$key][] = $value['bank_no'];
                    $_data[$key][] = $value['bank_address'];
					$_data[$key][] = $value['normal_usable'];
					$_data[$key][] = $value['split_usable'];
					$_data[$key][] = vtime("Y-m-d H:i:s",$value['addtime']);
					$_data[$key][] = vtime("Y-m-d H:i:s",$value['logintime']);
					$_data[$key][] = status_desc('USER_SALER_STATUS',$value['status']);
                    $_data[$key][] = $value['pid'];
                    $_data[$key][] = $value['realname'];
                    $_data[$key][] = vtime("Y-m-d H:i:s",$value['checktime']);
				}
				export::$_params['export']($_title,$_data,'商户列表');
				exit;				
			}
		}
		else
		{
			$sort = I('sort');
			if ($sort)
			{
				$arr = explode(',',$sort);
				$sort = array( $arr[0] => $arr[1] );
			}
			$order = $sort?$sort:'id desc';
			$data = $this->loadModel('userSaler')->getJoinList($fields,$join,$where,$order,true);
		}
		
		$total = $this->loadModel('userSalerAccountTotal')
							->field('SUM(normal_usable) AS total_normal_usable,SUM(split_usable) AS total_split_usable')
							->where($where)
							->join("INNER JOIN ".C('DB_PREFIX')."user_saler ON usid=".C('DB_PREFIX')."user_saler.id")
							->find();
		$data = array_merge($data,$total);
		$this->assign('data',$data);

		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('sort',$sort);
		$this->assign('levels',$levels['list']);
		$this->assign('_params',$_params);
		$this->display();
	}
	
	//二次认证
	public function verify()
	{
		$_params = $this->get_params(array('starttime','endtime','lfid','keytype','keyword','status','export','id'));
		$where = array(
			'verify_num' => array('egt',2),
		);
		
		if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		if ($_params['lfid']) $where['lfid'] = $_params['lfid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		if ($_params['status']!='') 
		{
			$where['status'] = $_params['status'];
			$_params['status'] = intval($_params['status']);
		}	
		$fields = array();
		// $fields['user_saler'] = 'id,name,mobile,lfid,addtime,lastlogintime,status';
		$fields['user_saler'] = '*';
		$fields['level_fee'] = 'name AS lf_name';
		// $fields['user_saler_account_total'] = 'normal_usable,split_usable';
		$join[] = array('level_fee','lfid','id');
		// $join[] = array('user_saler_account_total','id','usid');
		
		$sort = I('sort');
		if ($sort)
		{
			$arr = explode(',',$sort);
			$sort = array( $arr[0] => $arr[1] );
		}
		$order = $sort?$sort:'id desc';
		$data = $this->loadModel('userSaler')->getJoinList($fields,$join,$where,$order,true);
		
		// $total = $this->loadModel('userSalerAccountTotal')
							// ->field('SUM(normal_usable) AS total_normal_usable,SUM(split_usable) AS total_split_usable')
							// ->where($where)
							// ->join("INNER JOIN ".C('DB_PREFIX')."user_saler ON usid=".C('DB_PREFIX')."user_saler.id")
							// ->find();
		// $data = array_merge($data,$total);
		$this->assign('data',$data);
		
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('sort',$sort);
		$this->assign('levels',$levels['list']);
		$this->assign('_params',$_params);
		
		$this->display();
	}
	
	/*
	*	用户审核
	*/
	public function audit($ids='',$audit_value='',$output='1')
	{
		$ids = I('id',$ids);
		$this->vaild_params('is_empty',$ids,'未选中项目');
		$ids = is_array($ids)?$ids:array($ids);
		$audit_value = I('audit_value',$audit_value);
		$where['id'] = array('IN',$ids);

		//查找历史状态
		if ($audit_value === '1')
		{
			$where['status'] = '-1';
		}
//		elseif ($audit_value == '-1')
//		{
//			$where['status'] = '1';
//		}
		elseif ($audit_value == "+1")
		{
            $data['lfid']=1;
			$where['status'] = '0';
		}
		if ($audit_value == '+2')
		{
			$status_data = I('status_data');
			$this->vaild_params('is_empty',$status_data,'请选择拒绝项目');
			$arr = array(
				'info' => 1,
				'img' => 1,
				'video' => 1,
				// 'risk_pact' => 1,
			);
			foreach ($status_data as $key => $val)
			{
				$arr[$key] = 0;
			}
			$data['status_data'] = serialize($arr);
		}
		$data['status'] = intval($audit_value);
		$result = $this->loadModel('userSaler')->getList('*',$where);
		//eblog("短信审核+2",$result,'sms_'.date("Ymd"));
		if ($result['list'])
		{
			$rs = $this->loadModel('userSaler')->update($data,$where);  
			if ($audit_value === '+1')
			{  
				foreach ($result['list'] as $val)
				{
					//通过审核后--发送异步消息 通知用户
					$msg['type'] = 'salerPass';
					$msg['target'] = array (
						'mobile' => $val['mobile'],
					);
					$msg['params'] = array (
						'name' => $val['name'],
					);
					Vendor('MsgCenter.MsgCenter');
					MsgCenter::send($msg['target'],$msg['type'],$msg['params']);
					$smsmsg = $val['name']."恭喜您，汇融钱包注册成功，请持续分享。客服电话400-699-8890";
					sendsms($val['mobile'],$smsmsg);
					// if (TASK_MSG) send_task('MsgTask','sendMsg',$msg);
				}
				$this->ajaxOut(true,'userSaler/index');
			}
			elseif ($audit_value == '+2')
			{
				//eblog("短信审核+2",$result,'sms_'.date("Ymd"));
				foreach ($result['list'] as $val)
				{
					//审核未通过(资料未完善)--发送异步消息 通知用户
					$msg['type'] = 'salerNoInfo';
					$msg['target'] = array (
						'mobile' => $val['mobile'],
					);
					$msg['params'] = array (
						'name' => $val['name'],
						'reason' => $val['audit_memo']?$val['audit_memo']:'无法通过审核,请检查后重新提交',
					);
					Vendor('MsgCenter.MsgCenter');
					MsgCenter::send($msg['target'],$msg['type'],$msg['params']);
					$smsmsg = $val['name']."很抱歉，审核未通过，".$val['audit_memo']."，请完善资料重新提交，谢谢！客服电话400-699-8890";
					sendsms($val['mobile'],$smsmsg);
					//eblog("短信审核",$smsmsg,'sms_'.date("Ymd"));
					// if (TASK_MSG) send_task('MsgTask','sendMsg',$msg);
				}
			}
		}
		if ($output)
		{
			$this->ajaxOut(true,'userSaler/index');
		}
		else
		{
			return true;
		}
	}
	
	public function add()
	{
		if (I('submit')) $this->save();
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('levels',$levels['list']);
		$this->display();
	}
	
	public function edit()
	{
		if (I('submit')) $this->save();
		$id = intval(I('id'));
		$user = $this->loadModel('userSaler')->getInfoByid($id);
		$user['profile'] = unserialize($user['profile']);
		if ($user['pid']>0)
		{
			$parent = $this->loadModel('userSaler')->getInfoByid($user['pid'],'id,name,mobile');
			$user['parent'] = $parent;
		}
		// $user['profile']['video_exist'] = is_file(ROOT_PATH.$user['profile']);
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$this->assign('levels',$levels['list']);
		$this->assign('user',$user);
		$this->assign('verify',I('verify',0));
		$this->display();
	}
	/*
	*	保存用户信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
	public function save()
	{
		$images = I('images');
		$_params = $this->get_params(array('id','pid','lfid','mobile','password','re-password','bank','bank_type','name','card_no','bank_no','re-bank_no','bank_name','bank_address','audit_memo'));
		$this->vaild_params('is_empty',$_params['mobile'],'请填写手机号');

		if ($_params['id']<=0)
		{
			$this->vaild_params('is_empty',$_params['password'],'请填写密码');
			$this->vaild_params('is_empty',$_params['re-password'],'请填写确认密码');
			$this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$_params['mobile'],'该手机号已注册',false);
		}
		$this->vaild_params('is_empty',$_params['lfid'],'请填写商户等级');
		$this->vaild_params('is_empty',$_params['name'],'请填写姓名');
		$this->vaild_params('is_empty',$_params['card_no'],'请填写身份证号');
		$this->vaild_params('is_empty',$_params['bank_no'],'请填写银行卡号');
		$this->vaild_params('is_empty',$_params['re-bank_no'],'请确认银行卡号');
		$this->vaild_params('is_empty',$_params['bank'],'请填写所属银行');
		//$this->vaild_params('is_empty',$_params['bank_type'],'请填写联行号');
		$this->vaild_params('is_empty',$_params['bank_name'],'请填写开户行所在地');
		//$this->vaild_params('is_empty',$_params['bank_address'],'请填写支行信息');
		$this->vaild_params('eq',array($_params['password'],$_params['re-password']),'两次输入密码不一致');
		if ($_params['re-bank_no']!='none') $this->vaild_params('eq',array($_params['bank_no'],$_params['re-bank_no']),'银行卡号不一致');
		// $this->vaild_params('is_empty',$images['card_front'],'请上传 身份证正面与银行卡正面照');
		// $this->vaild_params('is_empty',$images['card_back'],'请上传 身份证及银行卡反面照');
		// $this->vaild_params('is_empty',$images['card_hand'],'请上传 本人持身份证正面照');
		if ($_params['pid']>0)
		{
			$parent = $this->loadModel('userSaler')->getInfoByid($_params['pid'],'id,lfid,depth_pid');
			$this->vaild_params('is_empty',$parent,'上级商户不存在');		
		}
		$data = $_params;
		unset($data['re-password']);
		unset($data['re-bank_no']);
		$id = intval($_params['id']);
		$data['depth_pid'] = empty($parent['depth_pid'])?$parent['id']:$parent['depth_pid']."-".$parent['id'];
		//更新下级可以比上级高的功能 ，作者：zf
        /*
		if ($parent)
		{
			$rs = $this->loadModel('userSaler')->compareLevel($data,$parent);
			$this->vaild_params('eq',array($rs,true),'上级商户的级别比您低，无法添加');
		}
        */
		if ($id>0)
		{
			$user = $this->loadModel('userSaler')->getInfoByid($id,'id,pid,depth_pid,lfid,status,status_data,profile,verify_ag');
			// if ($data['pid'] && $data['pid'] != $user['pid']) $this->vaild_params('is_empty',$user['pid'],'您已存在上级商户，无法进行编辑',false);
			$this->vaild_params('eq',array($data['pid'],$user['id']),'您不能选择自己作为自己的上级商户！',false);
			if ($user['pid'] == $data['pid'])
			{
				unset($data['pid']);
				unset($data['depth_pid']);
			}

			//检查是否存在等级高于要修改的等级的用户
			if ($user['lfid'] != $data['lfid'])
			{
				$fields = array('user_saler'=>'id,lfid','level_fee'=>'level');
				$join = array('level_fee','lfid','id');
				$where = array('pid'=>$user['id']);
				$higest_level_child = $this->loadModel('userSaler')->getJoinInfo($fields,$join,$where,'level desc');
				if ($higest_level_child)
				{
					$rs = $this->loadModel('userSaler')->compareLevel($higest_level_child,$data);
					$this->vaild_params('eq',array($rs,true),'下级商户等级高于当前所选等级，请先处理下级商户等级');
				}
			}

            //检查目标上级是否是自己的下级
			$user_pid = $user['depth_pid'] ? $user['depth_pid'].'-':'';
			$where = "(pid=".$user['id']." or depth_pid like '".$user_pid.$user['id']."-%') and id=".$data['pid'];
			$rs = $this->loadModel('userSaler')->getInfo('*',$where);
			if($rs)
			{
				$this->response(PARAMS_ERROR,'不能选择自己的下级作为自己的上级用户');
			}
            $status = I('status');//修改的状态
            $rs = $this->loadModel('userSaler')->getInfo('check_num',array('id'=>$user['id']));//获取审核次数
            if (intval($status)==1 && $user['lfid']==13){//判断待审核员工审核通过之后变成员工
                $data['lfid']=1;
            }
            if ($user['status']!=$status){
                $data['check_num'] = $rs['check_num']+1;//计算审核次数，改变状态一次为审核一次
                $data['realname'] = $_SESSION['admin']['realname'];//审核人姓名
                $data['checktime'] = time();//审核时间
            }else{
                $data['changename'] = $_SESSION['admin']['realname'];//修改人姓名
                $data['changetime'] = time();//修改时间
            }
            if (intval($status)==2 && $rs['check_num']>=4){
                $status = -1;
            }
			if (!$data['password']) unset($data['password']);
			if ($images) 
			{      
				$profile = unserialize($user['profile']);
				$status_data = unserialize($user['status_data']);
				if ( ($images['card_front'] && $images['card_front'] != $profile['card_front']) && ($images['card_back'] && $images['card_back'] != $profile['card_back']) )
				{
					$status_data['img'] = 1;
				}
				/* if ($images['risk_pact'])
				{
					$status_data['risk_pact'] = 1;
				} */
				if ($images['video'] && $images['video'] != $profile['video'])
				{
					$status_data['video'] = 1;
				}
				$data['status_data'] = serialize($status_data);
				
				$data['profile'] = $this->imageUp($images);

			}
			$rs = $this->loadModel('userSaler')->update($data,array('id'=>$id));

			if ($rs && $status != $user['status']) $rs = $this->audit($id,$status,0);
			if ($rs && $data['pid']>0 && $data['pid'] != $user['pid'])
			{
				//更新下级商户的上级列表层级'depth_pid'
				$user_pid = $user['depth_pid'] ? $user['depth_pid'].'-':'';
				$where = array();
				$where['pid'] = $user['id'];
				$where['depth_pid'] = array('like',$user_pid.$user['id'].'-%');	
				$where['_logic'] = 'or';
				$pid_len = $user_pid ? strlen($user_pid) + 1 : 1;
				$data = array('depth_pid'=>array("exp","CONCAT('".$data['depth_pid']."-',IFNULL(substring(depth_pid,".$pid_len."),0))"));
				$rs = $this->loadModel('userSaler')->update($data,$where);
			}
		}
		else
		{
			$status_data['info'] = 1;

			if ($images) 
			{
				$status_data['img'] = ($images['card_front'] && $images['card_back'])?1:0;
				// $status_data['risk_pact'] = ($images['risk_pact'])?1:0;
				$status_data['video'] = ($images['video'])?1:0;
				
				$data['profile'] = $this->imageUp($images);
			}

			foreach ($status_data as $val)
			{
				if (!$val)
				{
					$data['status'] = 2;
					break;
				}
				$data['status'] = 1;
			}
			$data['status_data'] = serialize($status_data);
			$data['addtime'] = time();
			$rs = $this->loadModel('userSaler')->add($data);
			$id = $rs;
		}
		if (I('verify'))
		{
			$this->ajaxOut($rs,'userSaler/verify');
		}
		else
		{
			$this->ajaxOut($rs,'userSaler/index');
		}
	}
	public function business()
	{
		if (I('submit')) $this->savebusiness();
		$id = intval(I('id'));
		$user = $this->loadModel('userSaler')->getInfoByid($id);
		$user['profile'] = unserialize($user['profile']);
		if ($user['pid']>0)
		{
			$parent = $this->loadModel('userSaler')->getInfoByid($user['pid'],'id,name,mobile');
			$user['parent'] = $parent;
		}
		// $user['profile']['video_exist'] = is_file(ROOT_PATH.$user['profile']);
		
		$this->assign('user',$user);
		$this->assign('verify',I('verify',0));
		$this->display();
	}
	/*
	*	保存用户信息
	*	@todo 1、图片上传部分 2、上级商户
	*/
	public function savebusiness()
	{		
		
		$_params = $this->get_params(array('id','status','address','name'));
		//$this->vaild_params('is_empty',$_params['mobile'],'请填写手机号');
		if ($_params['id']<=0)
		{
			$this->vaild_params('is_empty',$_params['password'],'请填写密码');
			$this->vaild_params('is_empty',$_params['re-password'],'请填写确认密码');
			$this->vaild_params(array($this->loadModel('userSaler'),'checkRegister'),$_params['mobile'],'该手机号已注册',false);
		}	
		
		
		//$data = $_params;
		unset($data['re-password']);
		unset($data['re-bank_no']);
		$id = intval($_params['id']);
		//$data['id'] =  intval($_params['id']);
		$data['status_business'] =  intval($_params['status']);
		$data['business_name'] =  $_params['name'];
		$data['business_address'] =  $_params['address'];
		//print_r($data);
		//$data['status_business'] = serialize($status_data);
		//$data['addtime'] = time();
		$rs = $this->loadModel('userSaler')->where('id='.$id)->save($data);
		$this->ajaxOut($rs,'userSaler/index');
		
		
	}
	public function delete()
	{
		$id = intval(I('id'));
		$this->vaild_params('compare',array($id,0),'请选择要删除的项！');
		$children = $this->loadModel('userSaler')->getList('id',array('pid'=>$id));
		$this->vaild_params('is_empty',$children['list'],'该商户存在下级商户，请先删除下级商户',false);
		$rs = $this->loadModel('userSaler')->del(array('id'=>$id));
		$this->ajaxOut($rs,'userSaler/index');
	}
	
	//处理临时上传的图片/视频
	public function imageUp($images)
	{
		import("@.Tool.file");
		if ($images)
		{
			if ($images['video']) $this->vaild_params('is_empty',strpos($images['video'],'.mp4'),'视频必须是mp4格式！');
			foreach($images as $k => $v)
			{
				if (strpos($v,'tmp'))
				{
					$type = ($k=='video')?'video':'image';
					$images[$k] = file::tmp_to_final(str_replace('/Upload/tmp/','',$v),$type,'user');
					//异步文件转码
					if ($k == 'video') send_task('fileTask','videoTrans',ROOT_PATH.$images[$k]);
				}
			}
		}
		return serialize($images);
	}

	/*
	*	查找用户
	*/
	public function search()
	{
		$pword = I('pword');
		$pword = trim($pword);
		$where = array();
        $pword = explode(' - ',$pword);
		if ($pword)
		{
			$where['mobile'] = $pword[1];
			$where['name'] = $pword[0];
		}
		$rs = $this->loadModel('userSaler')->getList('id,name,mobile',$where);
		$this->ajaxOut($rs['list']);
	}
	
	/*
	*	查看下级商户
	*/
	public function child()
	{
		$_params = $this->get_params(array('starttime','endtime','lfid','keytype','keyword','status'));
		$where = array();
		// if ($_params['starttime']) $where['addtime'][] = array('egt',strtotime($_params['starttime']));
		// if ($_params['endtime']) $where['addtime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
		// if ($_params['lfid']) $where['lfid'] = $_params['lfid'];
		if ($_params['keytype'] && $_params['keyword'])
		{
			$where[$_params['keytype']] = array('like',"%".$_params['keyword']."%");	
		}
		if ($_params['status']!='')
		{
			$where['status'] = $_params['status'];
			$_params['status'] = intval($_params['status']);
		}	

		$id = intval(I('id'));
		$user = $this->loadModel('userSaler')->getInfoByid($id);
		$levels = $this->loadModel('levelFee')->getList('id,name');
		$levels = reset_array_key($levels['list'],'id','name');
		//统计直接下级用户数
		$where['pid'] = $id;
		$rs = $this->loadModel('userSaler')->getList('*',$where,'',true);
		
		$this->assign('_params',$_params);
		$this->assign('levels',$levels);
		$this->assign('user',$user);
		$this->assign('data',$rs);
		$this->display();
	}
}
