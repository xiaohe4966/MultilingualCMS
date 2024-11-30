<?php
/*
 * @Author: XiaoHe
 */
/*
 * @Author: XiaoHe
 */

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class News extends Model
{

    use SoftDelete;



    // 表名
    protected $name = 'news';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'publishtime_text',
        'hotdata_text'
    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            if($row->weigh<1)
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });

        // 删除前回调
        self::beforeDelete(function ($row) {
            if (isset($row->switch)) {
                $row->switch = 0;
            }
        });
    }



    public function getHotdataList()
    {
        return ['1' => __('Hotdata 1'), '2' => __('Hotdata 2'), '3' => __('Hotdata 3'), '4' => __('Hotdata 4')];
    }

    public function getHotdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['hotdata']) ? $data['hotdata'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getHotdataList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public function getPublishtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['publishtime']) ? $data['publishtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setHotdataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


    public function cate()
    {
        return $this->belongsTo('Cate', 'cate_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
