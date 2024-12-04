<?php
/*
 * @Author: XiaoHe
 */
/*
 * @Author: XiaoHe
 */

namespace app\admin\controller;

use app\common\controller\BackendLangs;
use think\Db;
/**
 * 新闻文章
 *
 * @icon fa fa-circle-o
 */
class News extends BackendLangs
{

    /**
     * News模型对象
     * @var \app\admin\model\News
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\News;
        $this->view->assign("hotdataList", $this->model->getHotdataList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['cate'])
                ->where('news.lang', $this->webLang)
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {

                $row->getRelation('cate')->visible(['name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 复制
     *
     * @param $ids
     * @return string
     * @throws \think\Exception
     */
    public function copy($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        unset($row->id);
        try {
            $row->lang = $this->webLang;
            $result = $this->model->allowField(true)->save($row->toArray());
        } catch (ValidateException | PDOException | Exception $e) {
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 批量移动栏目
     *
     * @param $ids
     * @return string
     * @throws \think\Exception
     */
    public function movecate($ids = null, $cate_id = null)
    {
        if (false === $this->request->isPost()) {
            $this->view->assign('ids', $ids);
            return $this->view->fetch();
        }

        $pk = $this->model->getPk();
        $list = $this->model->where($pk, 'in', $ids)->where('lang', $this->webLang)->select();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $item->cate_id = $cate_id;
                $count += $item->save();
            }
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success('成功移动' . $count . '条');
        }
        $this->error('未更新任何数据');
    }



    
}
