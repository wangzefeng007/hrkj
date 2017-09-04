<?php
/**
 * @desc 变更储蓄卡
 * Class changeDebitCardAction
 */

class changeDebitCardAction extends baseAction
{

    /*
    *	默认入口
    */
    public function index()
    {
        $_params = $this->get_params(array('name'));
        $_params = $this->get_params(array('starttime','endtime','startaddtime','endaddtime','lfid','mobile','name','realname','status','status_business','export','id','bank_no'));
        $where = array();
        if ($_params['mobile']){
            $user = $this->loadModel('userSaler')->getInfo('id',array('mobile'=>array('like',"%{$_params['mobile']}%")));
            $where['usid'] = $user['id'];
        }
        if ($_params['bank_no']!=''){
            $where['bank_no'] = $_params['bank_no'];
        }
        //审核时间
        if ($_params['starttime']) $where['checktime'][] = array('egt',strtotime($_params['starttime']));
        if ($_params['endtime']) $where['checktime'][] = array('lt',strtotime($_params['endtime'])+24*3600);
        //申请时间
        if ($_params['startaddtime']) $where['addtime'][] = array('egt',strtotime($_params['startaddtime']));
        if ($_params['endaddtime']) $where['addtime'][] = array('lt',strtotime($_params['endaddtime'])+24*3600);

        if ($_params['status']=='0')
        {
            $_params['status'] = 0;
        }elseif($_params['status']=='') {
            $_params['status'] = 1;
            $where['status'] = $_params['status'];
        }else{
            $where['status'] = $_params['status'];
            $_params['status'] = intval($_params['status']);
        }

        $data = $this->loadModel('changeDebitCard')->getList('*',$where,'id desc',true);
        foreach ($data['list'] as $key=>$value){
            $user = $this->loadModel('userSaler')->getInfoByid($value['usid']);
            $data['list'][$key]['name'] = $user['name'];
            $data['list'][$key]['mobile'] = $user['mobile'];
        }
        $this->assign('data',$data);
        $this->assign('_params',$_params);
        $this->display();
    }
    /**
     *@desc 审核储蓄卡变更
     */
    public function edit(){
        $id = intval(I('id'));
        $rs = $this->loadModel('changeDebitCard')->getInfoByid($id);
        $where =array(
            'usid'=>$rs['usid'],
            'status'=>2,
        );
        $rs['count'] = $this->loadModel('changeDebitCard')->where($where)->field('id')->count();
        $user = $this->loadModel('userSaler')->getInfoByid($rs['usid']);
        $rs['card_no'] = $user['card_no'];
        $rs['new_bank_no'] = $rs['bank_no'];
        $this->assign('rs',$rs);
        $this->display('view');
    }
    /**
     *@desc 审核储蓄卡
     */
    public function save()
    {
        Vendor('Jpush.jpush');//调用 极光 接口
        $pushObj = new jpush();

        $id = intval(I('id'));
        $_params = $this->get_params(array('new_bank_no','card_image','usid','status','audit_memo'));
        $data['bank_no'] = $_params['new_bank_no'];
        $result = $this->loadModel('changeDebitCard')->getInfoByid($id);
        $usid = $_params['usid'];
        $date['checkname'] = $_SESSION['admin']['realname'];//审核人姓名
        $date['checktime'] = time();//审核时间
        $date['status'] = $_params['status'];
        $date['audit_memo'] = $_params['audit_memo'];
        if ($_params['status']==3){
            $this->vaild_params('is_empty',$_params['audit_memo'],'请填写备注信息');
        }
        if ($_params['status']==2){
            $user = $this->loadModel('userSaler')->getInfoByid($usid);
            $data['bank_no'] = $result['bank_no'];
            $data['bank'] = $result['bank'];
            $user['profile'] = unserialize($user['profile']);
            foreach ($user['profile'] as $key=>$value){
                if ($key=='card_hand'){
                    $user['profile']['card_hand']=$result['card_image'];
                }
            }
            $data['profile'] = serialize($user['profile']);
            $rs =$this->loadModel('userSaler')->update($data,array('id'=>$usid));
            if ($rs!=true){
                eblog("变更储蓄卡后台管理 - {$data}",'储蓄卡信息','bank_'.date("Ymd"));
                return false;
            }
            $rs = $this->loadModel('changeDebitCard')->update($date,array('id'=>$id));
            $title = '变更储蓄卡通知';
            $content ='恭喜您，您变更的储蓄卡已审核通过，请放心使用！客服电话400-6998890';
        }else{
            $title ='变更储蓄卡通知';
            $content  ='很遗憾，您变更的储蓄卡审核不通过，原因是'. $date['audit_memo'];
            $rs = $this->loadModel('changeDebitCard')->update($date,array('usid'=>$usid));
        }
        //极光推送
        $mobile['alias'][0] = $result['mobile'];
        $result = $pushObj->push($mobile,$content,$title,$m_type='',$m_txt='',$m_time='86400',json_encode(array('type'=>4,'content'=>30,'title'=>$title)));
        $this->ajaxOut($rs,'index');
    }
}