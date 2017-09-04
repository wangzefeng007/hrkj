<?php
class mainAction extends baseAction
{
	/*
	*	管理菜单
	*/
	public $menu = array();
	public $menuHtml = '';

	function index()
	{

		$mid = I('mid',1);
		$this->setMenuHtml($mid);
		$menus = array_values($this->menu);
		$start_link = U($menus[1][0]['module']."/".$menus[1][0]['action']);
		$top_menu = $this->topMenu();
		$this->assign('top_menu',$top_menu);
		$this->assign('mid',$mid);
		$this->assign('start_link',$start_link);
		$this->assign('menuHtml',$this->menuHtml);	
		$this->display();
	}
	
	function menu()
	{
		$this->setMenu();
		$this->setMenuHtml();
		$this->assign('menuHtml',$this->menuHtml);
		$this->display();
	}
	
	function top()
	{
		$this->display();
	}
	
	//顶部菜单列表
	private function topMenu()
	{
		$menu = M('module')->where(array('pid'=>'0','is_menu'=>1,'rank_id'=>array('egt',$_SESSION['admin']['rank_id'])))->select();
		return $menu;
	}
	
	//菜单列表
	private function setMenu($pid = '0')
	{

		$menu = M('module')->where(array('pid'=>$pid,'is_menu'=>1,'rank_id'=>array('egt',$_SESSION['admin']['rank_id'])))->order('sort')->select();
		if ($menu)
		{
			foreach($menu as $value)
			{
				$this->menu[$pid][] = $value;
				$this->setMenu($value['id']);
			}
		}
	}
	
	//菜单HTML
	private function setMenuHtml($mid)
	{
		$this->setMenu($mid);
		foreach($this->menu[$mid] as $key=>$value)
		{
			$this->menuHtml .= '<ul>';
			$this->menuHtml .= '<li class="title"><a href="javascript:void(0)"><img src="__RES__/images/'.$value['icon'].'" />'.$value['name'].'</a></li>';
			foreach($this->menu[$value['id']] as $v)
			{
				$this->menuHtml .= '<li><a target="frame_main" href="/admin.php/'.$v['module'].'/'.$v['action'].'/'.str_replace(array('&','='),'/',$v['params']).'">'.$v['name'].'</a></li>';
			}
			$this->menuHtml .= '</ul>';		
		}
	}
}

