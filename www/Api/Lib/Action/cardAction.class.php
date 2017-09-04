<?php
class cardAction extends baseAction
{
	protected static $realnameAuth;
	function _initialize() {
		parent::_initialize();
		//实名认证   1：开启
		$this->realNameAuth=1;
	}
	 /*
     * 卡BIN
     */
    public function cardbin(){
		$_params = $this->get_params(array('cardno'));
		//$_params = $this->get_params(array('bankname', 'keyword'));
        foreach ($_params as $value) {
            $this->vaild_params('is_empty', $value, '资料填写不完整');
        }
        eblog("卡BIN 查询 卡号",$_params['cardno'],'cardbin_'.date("Ymd"));
        $cardbin = substr($_params['cardno'],0,6) ;
        $condition["card_bin"] = array("like", $cardbin."%");		
    	$bankno = M('card_bin')->where($condition)->field(array('bank_code'=>'bank','card_type'=>'type'))->find();  
    	//dump($bankno);
    	eblog("卡BIN 查询 银行代号",$bankno,'cardbin_'.date("Ymd"));
    	$bankcode = M('bankmore')->where(array('id' => $bankno[bank]))->find();//dump($region);	
    	eblog("卡BIN 查询 银行资料",$bankcode,'cardbin_'.date("Ymd"));
		if($bankno['type'] == '01')
		{
				
			$backData[bank] =  $bankno[bank];
			$backData[type] =  $bankno[type];
			$backData[name] =  $bankcode[name];
		
		}
		else 
		{
			$backData[bank] =  $bankno[bank];
			$backData[type] =  $bankno[type];
			$backData[name] =  $bankcode[name];
		
		}
		//$list = array('list' => $bankno) ;//echo gettype($list);
		//echo json_encode($this->object_array($data));
        $this->apiOut($backData);
    } 
}
