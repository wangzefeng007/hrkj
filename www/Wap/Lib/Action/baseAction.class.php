<?php
class baseAction extends Action
{
	//公共参数
	public $common = array();
	
	//model
	public $model = array();	
	
	//usid
	public $usid;
	public $usinfo = array();
	
	public function _initialize()
	{
		$this->setUsid();
		$this->setUserinfo();
		// dump($this->usid);
		// dump($this->usinfo);
	}
	
	public function setCommon()
	{
		$this->vaild_params('is_empty',I('machine_code'),'请传入机器码');
		$this->common['macine_code'] = I('machine_code');
		$this->common['machine_info'] = I('machine_info');
		$this->common['version'] = I('version');
		$this->common['os'] = I('os');
		$this->common['sign'] = I('sign');
		$this->common['timestamp'] = I('timestamp');
	}
	
	public function setUsid()
	{
		$this->usid = intval(I('usid'));
	}
	
	public function setUserinfo()
	{
		if (!$this->usid) return false;
		$this->usinfo = $this->loadModel('userSaler')->getInfoByid($this->usid);		
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
	
	/*
	*	接口响应输出
	*	@param			int		$result_code	响应代码
	*	@param			string	$result_msg		接口响应信息
	*	@param			array	$data					接口数据
	*/
	public function response($result_code,$result_msg,$data = array())
	{
		$response = array();
		$response['result']['code'] = $result_code;
		$response['result']['msg'] = $result_msg;
		$response['data'] = $data;
		return json_encode($response);
	}
	
	/*
	*	api输出
	*	@param			array		$data		要显示的接口数据或根据数据判断接口显示的结构体
	*	@param			bool		$show	是否显示数据结构体，如果false只显示result部分，不显示data部分
	*/
	public function apiOut($data = array(),$show = true)
	{
		if (!$data)
		{
			return $this->response(INTERNAL_ERROR,'服务器内部错误或数据为空');
		}
		else
		{
			if ($show)
			{
				return $this->response(REQUEST_SUCCESS,'请求成功',$data);
			}
			else
			{
				return $this->response(REQUEST_SUCCESS,'请求成功');
			}
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
	
    //失败函数
	public function is_error($message=''){
		if(empty($message)){
			die(json_encode(array('status'=>404)));
		}
		die(json_encode(array('status'=>404, 'message'=>$message)));
	}
	
	//成功函数
	public function is_right($message='', $arr="", $total=''){
		if(empty($message)){
			die(json_encode(array('status'=>10000)));
		}
		if(empty($arr)){
			die(json_encode(array('status'=>10000, 'message'=>$message)));
		}
		if(empty($total)){
			die(json_encode(array('status'=>10000, 'message'=>$message, 'item'=>$arr)));
		}
		die(json_encode(array('status'=>10000, 'message'=>$message, 'item'=>$arr, 'total'=>$total)));
	}
}
