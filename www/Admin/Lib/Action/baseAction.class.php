<?php
class baseAction extends Action
{
	public $model;	
	public $url;	//当前url
	
	public function _initialize()
	{
		$this->setUrl();
		$this->isLogin();
	}
	
	//判断是否登录
	public function isLogin(){
		$action = strtolower(MODULE_NAME."/".ACTION_NAME);
		$module = strtolower(MODULE_NAME."/");
		$nocheck = array('base/login','base/logout','base/code');
		
		if (!session('admin') && !in_array($action,$nocheck) && !in_array($module,$nocheck))
		{
			header("Location:".U('base/login'));
			exit;
		}
	}
	
	//登录界面
	public function login()
	{
		$username = I('username');
		$password = I('password');
		$code = I('code');
		if (I('login'))
		{
			if ($code != session('verify')) {
				$this->ajaxReturn(array('href'=>U('base/login')),'验证码错误！',PARAMS_ERROR);
				exit;
			}
			$where = array();
			$where['username'] = $username;
			$where['password'] = md5($password);
			$where['status'] = 1;
			$user = M('admin')->where($where)->find();
			if (!$user)
			{
				$this->ajaxReturn(array('href'=>U('base/login')),'密码错误!',PARAMS_ERROR);
				exit;
			}
			else
			{
				session('admin',$user);
				$this->ajaxReturn(array('href'=>U('main/index')),'登录成功！',REQUEST_SUCCESS);
				exit;
			}
		}
		$this->display('common/login');
	}
	
	public function logout()
	{
		session(null);
		header("Location:".U('base/login'));
		exit;
	}
	
	/*
	*	检查用户是否登录
	*/
	private function checkLogin()
	{
		$action = strtolower(MODULE_NAME."/".ACTION_NAME);
		$nocheck = array('base/login','base/logout');
		
		if ((!$this->upid || !$this->upinfo) && !in_array($action,$nocheck))
		{
			header("Location:".U('base/login'));
			exit;
		}
	}

	/*
	*	接口响应输出
	*	@param			int		$result_code	响应代码
	*	@param			string	$result_msg		接口响应信息
	*	@param			array	$data					接口数据
	*/
	public function response($result_code,$result_msg,$data = array(),$link='')
	{
		$response = array();
		$response['result']['code'] = $result_code;
		$response['result']['msg'] = $result_msg;
		$response['link'] = $link;
		$response['data'] = $data;
		echo json_encode($response);
		exit;
	}
	
	/*
	*	api输出
	*	@param			array		$data		要显示的接口数据或根据数据判断接口显示的结构体
	*	@param			bool		$show	是否显示数据结构体，如果false只显示result部分，不显示data部分
	*/
	public function ajaxOut($data = array(),$link = '',$show = true)
	{
		if (!$data)
		{
			$this->response(INTERNAL_ERROR,'服务器内部错误或数据为空');
		}
		else
		{
			if ($show)
			{
				$this->response(REQUEST_SUCCESS,'请求成功',$data,U($link));
			}
			else
			{
				$this->response(REQUEST_SUCCESS,'请求成功',array(),U($link));
			}
		}
	}	
	
	/*
	*	参数验证
	*	@param		callback		$call			调用函数名
	*	@param		array			$params	调用函数的参数名
	*	@param		string			$msg			提示信息
	*	@param		bool			$rule			验证规则
	*/
	public function vaild_params($call,$params,$msg='',$rule = true)
	{
		if (!is_callable($call))
		{
			throw new Exception($call." can not callable!");
		}
		$params = !is_array($params)?array($params):$params;
		if (call_user_func_array($call,$params) == $rule)
		{
			return true;
		}
		else
		{
			$this->response(PARAMS_ERROR,$msg);
		}
		
	}
	
	public function loadModel($model)
	{
		$this->model[$model] = D($model);
		return $this->model[$model];
	}
	
	/*
	*	获取指定的请求参数
	*	@param			array			$field		需要获取的参数的字段名
	*	@param			string			$method		获取类型，可取值_request,_get,_post
	*/
	public function get_params($field,$method = '_request')
	{
		if (!in_array($method,array('_request','_get','_post')))
		{
			throw new Exception('get_params invaild params!');
		}
		$request = $this->$method();
		$data = array();
		foreach($field as $k)
		{
			$data[$k] = isset($request[$k])?$request[$k]:'';
		}
		return $data;
	}

	//验证码
	public function code(){
		import('@.Tool.Image');
		Image::verify();
	}
	
	private function setUrl()
	{
		$server = $this->_server();		
		$this->url = "http://".rtrim($server['HTTP_HOST'].$server['REQUEST_URI'],'&');
		$this->assign('_url',$this->url);
	}
}
