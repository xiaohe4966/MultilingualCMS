<?php
/*
 * @Author: he4966
 */
/*
 * @Author: XiaoHe
 */
/*
 * @Author: XiaoHe
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;
use think\Db;
use fast\Tree;

class Cate extends Model
{

    use SoftDelete;



    // 表名
    protected $name = 'cate';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            if($row->weigh<1)
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            // $this->updateLang($row[$pk]);
            (new \app\common\library\LangCom())->updateCate($row[$pk]);
        });

        //插入前回调
        // self::beforeInsert(function ($row) {
        //     if (!isset($row->id))
        //         $row->id = self::max('id') + 1;
        // });

        // 删除前回调
        self::beforeDelete(function ($row) {
            if (isset($row->isnav_switch)) {
                $row->isnav_switch = 0;
            }
            if (isset($row->status)) {
                //状态:1=显示,2=隐藏
                $row->status = 2;
            }
            // 删除该栏目下面的所有文章
            if ($row->type == 'list')
                $res = Db::table($row->table_name)->where('cate_id', $row['id'])->useSoftDelete('deletetime', time())->whereNull('deletetime')->delete();
            \app\common\library\LangCom::delCate($row['id']);
        });
    }


    public function updateLang($id=null){
        //查询所有的主语言
        $cateList = Db::table('cate')->where('lang','=',config('fastadmin.default_lang'))->whereNull('deletetime')->select();
        Tree::instance()->init($cateList, 'parent_id')->icon = ['&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;'];
    }


    public function getTypeList()
    {
        return ['list' => __('Type list'), 'page' => __('Type page'), 'link' => __('Type link')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
}
