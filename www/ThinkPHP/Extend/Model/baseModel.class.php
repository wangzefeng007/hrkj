<?php
/*
*	自定义模型类，所有模型均从此类继承，便于今后扩展
*/
class baseModel extends Model
{

	
	/*
	*	获取列表，仅获取单表数据
	*/
	public function getList($field = "*",$where = array(),$order = 'id desc',$showpage = false)
	{
		
		$data = array();
		if ($showpage)
		{
			$p = (intval(I('p'))>0) ? intval(I('p')) : 1;
			$psize = intval(I('psize'))>0 ? intval(I('psize')):C('psize');
			$page = $p.",".$psize;
			$data['list'] = $this->where($where)->field($field)->order($order)->page($page)->select();
			$data['count'] = $this->where($where)->field($field)->count();
			$data['psize'] = $psize;	
		}
		else
		{
			$data['list'] = $this->where($where)->field($field)->order($order)->select();
		}
		return $data;
	
	}
	
	/*
	*	连表查询，tp连表查询需要添加表前缀，此方法无需添加表前缀
	*	@params		array			$field			需要查询的字段,格式array('表名'=>'字段名',……)
	*	@params		array			$join			连表信息，
    *   格式 array(连表名，原表连接字段，连表连接字段,连接类型，默认left join)
	*	@params		array			$where		查询条件
	*	@params		array			$order		排序
	*	@params		bool			$showpage	是否显示分页信息
	*/
	public function getJoinList($field,$join = array(),$where=array(),$order = '',$showpage = false)
	{
		$db_prefix = C('DB_PREFIX');
		$_filed = '';
		foreach($field as $table=>$f)
		{	
			$_field .= $db_prefix.$table.".".str_replace(",",",".$db_prefix.$table.".",$f).",";
		}
		$_field = substr($_field,0,-1);
		if (!is_array($join[0])) $join = array($join);
		$_join = array();
		foreach($join as $value)
		{
			$join_sql = isset($value[3])?$value[3]:'';
			$join_sql .= " ".$db_prefix.$value[0]." ON ";			
			if (false !== strpos($value[1],"."))
			{				
				$join_sql .= $db_prefix.$value[1];
			}
			else
			{
				$join_sql .= $this->trueTableName.".".$value[1];
			}
			$join_sql .= "=";
			if (false !== strpos($value[2],"."))
			{				
				$join_sql .= $db_prefix.$value[2];
			}
			else
			{
				$join_sql .= $db_prefix.$value[0].".".$value[2];
			}
			$_join[] = $join_sql;
		}

		$_where = array();
		foreach($where as $key=>$value)
		{
			if (false !== strpos($key,"."))
			{
				$_key = $db_prefix.$key;
				$_where[$_key] = $value;
			}
			else
			{
				$_key = $this->trueTableName.".".$key;
				$_where[$_key] = $value;
			}
		}
		
		$data = array();
		if ($showpage)
		{
			$p = (intval(I('p'))>0) ? intval(I('p')) : 1;
			$psize = intval(I('psize'))>0 ? intval(I('psize')):C('psize');
			$page = $p.",".$psize;
			$data['list'] = $this->where($_where)->join($_join)->field($_field)->order($order)->page($page)->select();
			$data['count'] = $this->where($_where)->join($_join)->field($_field)->count();
			$data['psize'] = $psize;			
		}
		else
		{
			$data['list'] = $this->where($_where)->join($_join)->field($_field)->order($order)->select();
		}
		return $data;		
	}
	
	public function getJoinInfo($field,$join = array(),$where=array(),$order = '')
	{
		$rs = $this->getJoinList($field,$join,$where,$order);
		return $rs['list'][0];
	}
	
	public function getInfoByid($id,$field = "*",$filter = array(),$order = '')
	{
		$where = array();
		if ($id) $where['id'] = $id;
		$where = array_merge($where,$filter);
		return $this->getInfo($field,$where,$order);
	}
	
	public function getInfo($field = '*',$where = array(),$order = '')
	{
		return $this->field($field)->where($where)->order($order)->find();
	}

	
	public function update($data,$where = array())
	{
		$rs = $this->where($where)->save($data);
		return ($rs === false)?false:true;
	}
	
	public function del($where)
	{
		return $this->where($where)->delete();	
	}
	
	/*
	*	如果数据存在，则更新数据，不存在则插入
	*/
	public function replace($data,$where)
	{
		$rs = false;
		if ($this->where($where)->find())
		{
			$rs = $this->update($data,$where);
		}
		else
		{
			$rs = $this->add($data);
		}
		return $rs;
	}
	

}
