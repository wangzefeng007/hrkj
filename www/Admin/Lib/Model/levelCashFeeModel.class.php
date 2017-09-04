<?php
class levelCashFeeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	*	获取提现费率信息
	*/
	function getCashfee($lfid,$ptid,$ctid)
	{
		$fields = "fee_rate,fee_static,min,max";
		$where = array('lfid'=>$lfid,'ptid'=>$ptid,'ctid'=>$ctid);
		return $this->getInfo($fields,$where);
	}
	
	
	/*
	*	建立提现通道与提现方式的对应关系
	*/
	function level_cash_fee($lfid,$setting)
	{

		$error = 0;		
		$pcids = array();
		$rs = true;
		
		foreach($setting as $value)
		{
			if ($rs = $this->getInfo('id',array('lfid'=>$lfid,'ptid'=>$value['ptid'],'ctid'=>$value['ctid'])))
			{
				$id[] = $rs['id'];
				$rs = $this->update($value,array('id'=>$rs['id']));
				if ($rs === false) 
				{
					return false;
				}
			
			}
			
			else
			{
				$data = $value;
				$data['lfid'] = $lfid;
				$rs = $this->add($data);
				if (!$rs)
				{
					return false;
				}
				$id[] = $rs;
			}

		}
		
		
		//删除之前已设置的记录
		$rs = $this->del(array('lfid'=>$lfid,'id'=>array(' not in ',$id)));
		return ($rs === false)?false:true;
	}
}