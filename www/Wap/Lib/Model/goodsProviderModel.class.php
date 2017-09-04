<?php
class goodsProviderModel extends ebModel
{
	/*
	*	获取供应上的商品信息
	*/
	public function getList($where,$order = '')
	{
		$_where = array();
		foreach($where as $key=>$value)
		{
			$_where[C('DB_PREFIX')."goods_provider.".$key] = $value;
		}
		$fields = C('DB_PREFIX')."goods_provider.*,".C('DB_PREFIX')."goods_category.name AS category ";
		$page = "1,40";
		$order = $order?$order:C('DB_PREFIX')."goods_provider.id DESC";
		$join = array(C('DB_PREFIX')."goods_category ON ". C('DB_PREFIX')."goods_provider.gcid=".C('DB_PREFIX')."goods_category.id");
		return $this->lists('goods_provider',$_where,$fields,$page,$order,$join);
	}
	
	/*
	*	获取商品详细信息
	*/
	public function getInfo($gpid,$fields = "*")
	{
		return M('goods_provider')->field($fields)->where(array('id'=>$gpid))->find();
	}
	
	
	/*
	*	获取商品佣金设置情况
	*/
	public function getCommission($gpid)
	{
		$fields = "gc.rate,".$this->db_prefix."level_sale.name,".$this->db_prefix."level_sale.id";
		$where = array();
		$where['gc.gpid'] = $gpid;
		$where[$this->db_prefix.'level_sale.status'] = 1;
		$join = $this->db_prefix."goods_commission AS gc ON ".$this->db_prefix.'level_sale.id = gc.lsid';
		return M('level_sale')->field($fields)->where($where)->join($join)->select();
	}
		
	/*
	*	添加商品信息
	*/
	public function add($data)
	{
		return M('goods_provider')->add($data);
	}
	
	/*
	*	更新商品信息
	*	
	*/
	public function update($id,$data)
	{
		$rs = M('goods_provider')->where(array('id'=>$id))->save($data);
		return ($rs === false)?false:true;
	}

	/*
	*	删除商品
	*/
	public function delete($gid)
	{
		return M('goods_provider')->where(array('id'=>$gid))->delete();
	}
	
	
	
	/*
	*	获取商品所有分类信息
	*/
	public function get_categories($pid = 0)
	{
		static $categories = array();
		$category= M('goods_category')->where(array('pid'=>$pid))->select();
		if ($category)
		{
			foreach($category as $value)
			{
				$categories[] = $value;
				$this->get_categories($value['id']);
			}
		}
		return $categories;
	}	
}
