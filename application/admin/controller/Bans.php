<?php
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
class Bans extends Backend
{

    /**
     * Bans模型对象
     * @var \app\admin\model\Bans
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Bans;
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        // $params = $this->preExcludeFields($params);
        //分隔字符串以换行分隔
        $arr = explode("\n", $params['name']);
        $level = $params['level']?$params['level']:2;
        $ci = 0;
        $重复 = [];
        foreach ($arr as $key => $v) {
            unset($ban);
            $ban['name'] = trim($v);
            if(strlen($ban['name']) >50){
                $this->error('单行违禁词不能超过50个字符');
            }   
            try {
            
                $res = Db::name('bans')->where($ban)->find();
                if (!$res) {
                    $ban['createtime'] = time();
                    $ban['level'] = $level;
                    $insert = Db::name('bans')->insert($ban);


                    if ($insert) {
                        $ci++;
                    }
                } else {
                    $重复[] = $v;
                }
            } catch (\Throwable $th) {
                $this->error($th->getMessage() . '违禁词:' . $ban['name']);
            }
        }
        if ($ci > 0) {
            $this->success('成功添加' . $ci . '个违禁词', null, ['count' => $ci, 'repeat' => $重复]);
        } else {
            $this->error('未添加新词，重复的词有：' . implode(',', $重复), null, ['repeat' => $重复]);
        }



        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }
}
