<?php
class levelSaleModel extends ebModel
{
	/*
	*	获取销售级别列表
	*/
	function getList($where = array(),$fields = "*")
	{
		return M('level_sale')->field($fields)->where($where)->select();
	}
	
}