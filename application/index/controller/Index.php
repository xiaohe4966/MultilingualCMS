<?php
/*
 * @Author: XiaoHe
 */

namespace app\index\controller;

use app\common\controller\Frontend;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        //重定向
        $this->redirect('cms/index/index');
        // return $this->view->fetch();
    }

}
