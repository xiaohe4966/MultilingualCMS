<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Pro extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'pro';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'fanyi_time_text',
        'hotdata_text',
        'publishtime_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            if (!$row['weigh']) {
                $pk = $row->getPk();
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    
    public function getHotdataList()
    {
        return ['1' => __('Hotdata 1'), '2' => __('Hotdata 2'), '3' => __('Hotdata 3'), '4' => __('Hotdata 4')];
    }


    public function getFanyiTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['fanyi_time']) ? $data['fanyi_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
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

    protected function setFanyiTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setHotdataAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setPublishtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function cate()
    {
        return $this->belongsTo('Cate', 'cate_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
