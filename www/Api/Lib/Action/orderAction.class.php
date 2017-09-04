<?php
class orderAction extends baseAction
{
	function lists()
	{
		$data = $this->get_params(array('type','status'));
		// $where = array();
		// if ($data['type']) $where['type'] = $data['type'];
		// if ($data['status']) $where['status'] = $data['status'];
		if ($data['type'] == 1)
		{
			
		}
		
	}
}