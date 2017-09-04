<?php
class userBankModel extends baseModel
{
	function __construct()
	{
		parent::__construct();
	}
	
	/*
    * 银行列表
    */
    public function getList($usid) {
        $where = array(
            'usid' => $usid,
            'a.status' => 1
        );
        $rs = M('userBank')->alias('a')
            ->join(C('DB_PREFIX') . "setting_bank b ON a.bank_id=b.id")
            ->where($where)
            ->order("addtime DESC")
            ->field("a.id,a.bank_id,b.name bank_name,b.img bank_img,a.bank_no,a.name,a.idcard,a.status,a.addtime")
            ->select(); 
        return $rs;
    }
	
}