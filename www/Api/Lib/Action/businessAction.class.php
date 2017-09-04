<?php
class businessAction extends baseAction
{
	protected static $realnameAuth;
	function _initialize() {
		parent::_initialize();
		//实名认证   1：开启
		$this->realNameAuth=1;
	}
	/*
	*	注册接口
	*/
	public function register()
	{ 
		$data = array();		
		$name = I('name');
		$address = I('address');
		$picture = I('picture');		
		$usid = $this->usid;		
		
		$this->vaild_params('is_empty',$name,'请输入商户名称');
		$this->vaild_params('is_empty',$address,'请输入商户地址');
		$this->vaild_params('is_empty',$picture,'请上传图片');		
		
		//$this->loadModel('userSaler');
		//$this->loadModel('levelFee');
		
		$data['business_name'] = $name;
		$data['business_address'] = $address;
		import("@.Tool.file");
		$picture = file::tmp_to_final($picture,'image','user');
		$data['businesspic'] = $picture;

		$data['status_business'] = 2;
		$rs = $this->loadModel('userSaler')->update($data,array('id'=>$this->usid));
		
		if ($rs)
		{			
			
			$user = $this->loadModel('userSaler')->getInfo("*",array('id'=>$usid));
			$response= array(			
				'name' => $user['business_name'],
				'address' => $user['business_address'],
				'picture' => $user['businesspic'],
				//'address' => $user['status'],
				'status' => $user['status_business'],
				'qrcode' => APP_SITE.$user['status'],			
			);
			$this->apiOut($response);
		}
		else 
		{
			$response= array(							
			);
			$this->response(4001,'提交失败！');	
		}
		
		
	} 	
	/*
	* 商户信息
	*/
	public function info()
	{
		$usid = $this->usid;	
		
		$user = $this->loadModel('userSaler')->getInfo("*",array('id'=>$usid));
		
		if (!$user )
		{
			$this->response(DATA_EMPTY,'帐号不存在!');
		}
		if ($user['status'] == -1)
		{
			$this->response(DATA_EMPTY,'您的帐号暂未通过审核或被冻结，请联系管理员');
		}		
		$response= array(			
			'name' => $user['business_name'],
			'address' => $user['business_address'],
			'picture' => $user['businesspic'],
			//'address' => $user['status'],
			'status' => $user['status_business'],
			'qrcode' => 'http://wallet.huirongpay.com/api.php?m=qrpay&a=index&usid='.$this->usid,
		);
		$this->apiOut($response);		
	}
	
	public function test()
	{
		import("@.Tool.file");
		$test = '65df48a94fef8e2e50043acb42159b4e';
		$testpic = file::tmp_to_final($test,'image','user');
		echo $testpic;
	}
	/**
	*	上传/修改用户头像
	*/
	public function headpic()
	{
		$_params = $this->get_params(array('headpic'));
		$this->vaild_params('is_empty',$_params['headpic'],'请选择要上传的图片');
		$data = array();
		import("@.Tool.file");
		$data['headpic'] = file::tmp_to_final($_params['headpic'],'image','user');
		$rs = $this->loadModel('userSaler')->update($data,array('id'=>$this->usid));
		$rs = $rs?$data:false;
		$this->apiOut($data);
	}
	
	
}
