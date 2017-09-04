<?php
class regAction extends baseAction
{
   public function index(){
		$usid = $this->usid;
		$invite_mobile = I('invite_mobile');
		$this->assign('invite_mobile',$invite_mobile);
		$this->display(); // 输出模板
   }
   public function reg(){
		$usid = $this->usid;
		$invite_mobile = I('invite_mobile');
		$sms_verify = randcode(4);
		session('sms_verify',$sms_verify);
		$this->assign('sms_verify',$sms_verify);
		$this->assign('invite_mobile',$invite_mobile);
		$this->display(); // 输出模板
   }
	public function download(){
		$this->display();
	}
}
