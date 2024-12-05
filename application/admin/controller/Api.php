<?php
/*
 * @Author: he4966
 */
/*
 * @Author: he4966
 */
/*
 * @Author: XiaoHe
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 违禁词
 *
 * @icon fa fa-circle-o
 */
class Api extends Backend
{

    // /**
    //  * Bans模型对象
    //  * @var \app\admin\model\Bans
    //  */
    // protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        // $this->model = new \app\admin\model\Bans;
    }


    /**
     * 同步栏目翻译其他语言
     */
    public function fanyi_cate($ids=null){
       $LangCom = new \app\common\library\LangCom();
       $size = $LangCom->fanyiCate($ids);

       $this->success('同步完成,共翻译'.$size.'条数据');
    }

    /**
     * 翻译所有栏目
     */
    public function fanyi_all_cate(){
       $LangCom = new \app\common\library\LangCom();
       $LangCom->updateCate();

       $this->success('同步完成,共插入'.$LangCom::$insertNum.'条栏目');
    }

}
