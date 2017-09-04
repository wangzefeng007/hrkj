<?php
class qrcodeAction extends baseAction
{
	/*
     *  刷卡分享,展示二维码
     */
    public function qrcode() { 
        $key = I('key');  
        $key = base64_decode($key);
        $price = I('price');
        $type = I('type');
        $this->assign('key', $key);
        $this->assign('price', $price);
        $this->assign('type', $type);
        $this->display();
    }
}
