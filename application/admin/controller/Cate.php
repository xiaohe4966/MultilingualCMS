<?php
/*
 * @Author: XiaoHe
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;
use think\Cache;
use think\Db;
use think\Log;
use think\Config;

/**
 * 栏目管理
 *
 * @icon fa fa-circle-o
 */
class Cate extends Backend
{

    /**
     * Cate模型对象
     * @var \app\admin\model\Cate
     */
    protected $model = null;

    protected $noNeedRight = ['validate_diyname', 'ajax_table_list'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Cate;

        $更新栏目文章数量 = false;
        //缓存60秒内只执行一次
        $cache_key = 'cateList_items';
        if (!Cache::get($cache_key)) {
            $更新栏目文章数量 = true;
        } else {
            Cache::set($cache_key, time(), 60);
        }

        $where = null;
        if (false === $this->request->isAjax()) {
        } else {
            $post = $this->request->post();
            if (isset($post['custom'])) {
                $where = $post['custom'];
            }
        }


        // 必须将结果集转换为数组
        $cateList = Db::name("cate")->field('byname,images,model_id,seotitle,keywords,description', true)
            ->order('weigh DESC,id ASC')
            ->whereNull('deletetime')
            ->where($where)
            ->select();
        foreach ($cateList as $k => &$v) {
            $v['name'] = __($v['name']);

            if ($更新栏目文章数量 && $v['type'] == 'list') {
                //更新文章数量
                try {
                    $count = Db::table($v['table_name'])->where('cate_id', $v["id"])->whereNull('deletetime')->count();
                    Db::name('cate')->where('id', $v["id"])->update(['items' => $count]);
                } catch (\Exception $e) {
                    Log::error('更新栏目文章数量失败：' . $v['table_name'] . $e->getMessage());
                }
            }
        }
        unset($v);
        Tree::instance()->init($cateList, 'parent_id')->icon = ['&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'];
        $this->cateList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');

        $catedata = [0 => __('None')];
        foreach ($this->cateList as $k => &$v) {
            $catedata[$v['id']] = $v['name'];
            $v['ChildrenIds'] = implode(',', Tree::instance()->init($cateList, 'parent_id')->getChildrenIds($v['id'], true));
            unset($v['spacer']);
        }

        unset($v);
        $this->view->assign('catedata', $catedata);


        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index($keyValue = null, $q_word = null)
    {
        // //设置过滤方法
        // $this->request->filter(['strip_tags', 'trim']);
        // if (false === $this->request->isAjax()) {
        //     return $this->view->fetch();
        // }
        // //如果发送的来源是 Selectpage，则转发到 Selectpage
        // if ($this->request->request('keyField')) {
        //     return $this->selectpage();
        // }
        // [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        // $list = $this->model
        //     ->where($where)
        //     ->order($sort, $order)
        //     ->paginate($limit);
        // $result = ['total' => $list->total(), 'rows' => $list->items()];
        // return json($result);

        if ($this->request->isAjax()) {
            $list = $this->cateList;
            if ($keyValue) {
                foreach ($list as $key => $cate) {
                    if ($keyValue && $cate['id'] == $keyValue) {
                        $list = [];
                        $list[] = $cate;
                        break;
                    }
                }
            }

            $total = count($list);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }



    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add($parent_id = null)
    {
        if (false === $this->request->isPost()) {
            if ($parent_id) {
                $row = $this->model->get($parent_id);
                $this->view->assign('row', $row);
            }

            $this->view->assign('parent_id', $parent_id);
            // //获取数据库的表名和注释
            $this->view->assign("tableList", $this->renderTable());
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        $this->verifyCateParams($params);
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
            //更新左侧菜单栏栏目
            $this->update_cate_to_auth_rule();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            $this->view->assign("tableList", $this->renderTable());
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if (isset($params['parent_id']) && $ids == $params['parent_id'])
            $this->error('上级ID和自己栏目不能一样');
        $this->verifyCateParams($params);
        //判断类型是否更改
        if (isset($params['type']) && $row['type'] != $params['type']) {
            // 如果原来类型为list 查询下面是否有数据列表
            if ($row['type'] == 'list') {
                $old_count = Db::table($row['table_name'])->where('cate_id', $ids)->count();
                if ($old_count > 0) {
                    $this->error('《' . $row->name . '》栏目下面有数据列表' . $old_count . '条,请永久删除或移动到其他栏目下');
                }
            }
        }

        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
            //更新左侧菜单栏栏目
            $this->update_cate_to_auth_rule();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 表名是否存在
     * @return bool
     */
    protected function verifyCateParams($params)
    {
        if (isset($params['table_name'])) {
            $table_name = $params['table_name'];
            if (empty($params['outlink'])) {
                switch ($params['type']) {

                    default:
                        if (empty($table_name)) {
                            $this->error('数据库表名不能为空');
                        }
                        break;
                }
            }
            if ($table_name) {
                //查询数据库表名是否存在
                $sql = "select table_name from information_schema.tables where table_name='" . $table_name . "'";
                $result = Db::query($sql);
                if (empty($result)) {
                    $this->error('数据库表名不存在');
                }
            }
        }
    }


    /**
     * 删除
     *
     * @param $ids
     * @return void
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 真实删除
     *
     * @param $ids
     * @return void
     */
    public function destroy($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post('ids');
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $item) {
                $count += $item->delete(true);
            }
            Db::commit();
        } catch (PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }



    /**
     * 数据库的表名即备注
     */
    protected function renderTable($search = null)
    {
        $tableList = [];
        $dbname = Config::get('database.database');
        $list = \think\Db::query("SELECT `TABLE_NAME`,`TABLE_COMMENT` FROM `information_schema`.`TABLES` where `TABLE_SCHEMA` = '{$dbname}';");
        foreach ($list as $key => $row) {

            if ($search) {
                if (stripos($row['TABLE_NAME'], $search) !== false) { // 使用 stripos 进行不区分大小写的搜索
                    $tableList[$row['TABLE_NAME']] = $row['TABLE_COMMENT'];
                }
            } else {
                $tableList[$row['TABLE_NAME']] = $row['TABLE_COMMENT'];
            }
        }
        return $tableList;
    }

    public function ajax_table_list($keyValue = null, $q_word = [])
    {
        $tableList = $this->renderTable(empty($q_word) ? null : $q_word[0]);

        foreach ($tableList as $name => $ps) {
            if ($keyValue && $name != $keyValue) {
                continue;
            }
            $list[] = ['name' => $name, 'ps' => $name . "👉" . $ps];
        }
        return $result = ['total' => count($list), 'list' => $list];
    }



    /**
     * 获取模板列表
     * @internal
     */
    public function get_template_list()
    {
        $files = [];
        $keyValue = $this->request->request("keyValue");

        if (!$keyValue) {
            $type = $this->request->request("type");
            $name = $this->request->request("name");

            if ($name) {
                //$files[] = ['name' => $name . '.html'];
            }
            //设置过滤方法
            $this->request->filter(['strip_tags']);

            // $config = get_addon_config('cms');
            // $themeDir = ADDON_PATH . 'cms' . DS . 'view' . DS . $config['theme'] . DS;

            $themeDir = APP_PATH . 'cms' . DS . 'view' . DS . 'index' . DS;
            // halt($themeDir);
            $dh = opendir($themeDir);
            while (false !== ($filename = readdir($dh))) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                // if ($type) {
                //     $rule = $type == 'channel' ? '(channel|list)' : $type;
                //     if (!preg_match("/^{$rule}(.*)/i", $filename)) {
                //         continue;
                //     }
                // }
                $files[] = ['name' => str_replace(strrchr($filename, "."), "", $filename)];
            }
            sort($files);
        } else {
            $files[] = ['name' => $keyValue];
        }
        return $result = ['total' => count($files), 'list' => $files];
    }

    /**
     * 验证路由是否存在
     */
    public function validate_diyname($id = null)
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($id) {
                $where['id'] = ['<>', $id];
            }
            $where['diyname'] = $params['diyname'];
            $res = Db::name('cate')->where($where)->find();
            if ($res) {
                $this->error('已存在该路由');
            } else {
                $this->success('ok');
            }
        }
    }


    /**
     * 更新栏目到菜单
     */
    protected function update_cate_to_auth_rule()
    {
        if (Config::get('site.update_cate_to_auth_rule')) {
            //查询出所有的分类
            $cateList = Db::name('cate')->where('status', '1')->select();
            Tree::instance()->init($cateList, 'parent_id')->icon = ['', '', ''];
            $this->cateList2 = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
            Db::startTrans();
            try {
                //code...

                foreach ($this->cateList2  as $key => $c) {
                    if ($c['type'] == 'link' && Config::get('site.cms_link_not_menu')) {
                        continue; //跳过链接
                    }
                    // echo $v['name'].'<br>';
                    unset($rule);
                    $rule['name'] = 'cate' . $c['id'];
                    $auth_rule = Db::name('auth_rule')->where($rule)->find(); //后面判断是否更新还是新插入


                    if ($c['parent_id'] == 0) {
                        $rule['pid'] = Config::get('site.default_auth_rule_pid') ?? 0;
                    } else {
                        // 上级id
                        $pid = Db::name('auth_rule')->where('name', 'cate' . $c['parent_id'])->value('id');
                        if (!$pid) {
                            var_dump($rule);
                            $this->error('没有上级id');
                        }

                        $rule['pid'] = $pid;
                    }
                    $rule['type'] = 'file';
                    $rule['title'] = $c['name'];
                    //字符串去前缀fa_             // news/index?cate_id=4&ref=addtabs
                    $rule['url'] = str_replace('fa_', '', $c['table_name']) . '/index?cate_id=' . $c['id'] . '&ref=addtabs';
                    ///类型:page=单页,link=链接,list=列表
                    switch ($c['type']) {
                        case 'page':
                            $rule['icon'] = 'fa fa-pagelines';
                            $rule['url'] = str_replace('fa_', '', $c['table_name']) . '/edit?cate_id=' . $c['id'] . '&ref=addtabs';
                            break;
                        case 'link':
                            $rule['icon'] = 'fa fa-link';

                            break;
                        case 'list':
                            $rule['icon'] = 'fa fa-list-ol';
                            break;
                        default:
                            $rule['icon'] = 'fa fa-dot-circle-o';

                            break;
                    }


                    $rule['remark'] = '自动更新';
                    $rule['ismenu'] = 1; //是否为菜单
                    $rule['menutype'] = 'addtabs'; //菜单类型
                    $rule['extend'] = ''; //扩展属性
                    $pinyin = new \Overtrue\Pinyin\Pinyin('Overtrue\Pinyin\MemoryFileDictLoader'); //拼音首字母
                    $rule['createtime'] = time();
                    $rule['py'] = $pinyin->abbr($c['name']);
                    $rule['pinyin'] = $pinyin->permalink($c['name']);
                    $rule['weigh'] = $c['weigh'];
                    $rule['status'] = 'normal';


                    if ($auth_rule) {
                        //更新
                        $rule['updatetime'] = time();
                        $res = Db::name('auth_rule')->where('id', $auth_rule['id'])->update($rule);
                    } else {
                        //插入
                        $res = Db::name('auth_rule')->insert($rule);
                    }
                }

                Db::commit();
                \think\Cache::rm('__menu__');
            } catch (\Throwable $th) {
                Db::rollback();
                $this->error($th->getMessage());
                //throw $th;
            }
        }
    }
}
