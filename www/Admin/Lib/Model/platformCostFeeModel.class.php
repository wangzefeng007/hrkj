<?php
class platformCostFeeModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	/**
	*	提现
	*/
	function getFee($ptid = '')
	{
		$where = array();
		if ($ptid)
		{
			$where['ptid'] = $ptid;
		}
		$rs = D('platformCostFee')->getList('*',$where);
		$cost_fee = array();
		if ($rs['list'])
		{
			foreach ($rs['list'] as $val)
			{
				foreach ($val as $f_key => $f_val)
				{
					$cost_fee[$val['ptid']][$val['ctid']][$f_key] = $f_val;
				}
				// $cost_fee[$val['ptid']][$val['ctid']]['id'] = $val['id'];
				// $cost_fee[$val['ptid']][$val['ctid']]['fee_rate'] = $val['fee_rate'];
				// $cost_fee[$val['ptid']][$val['ctid']]['fee_static'] = $val['fee_static'];
			}
		}
		return $cost_fee;
	}
}