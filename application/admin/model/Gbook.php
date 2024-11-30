<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Gbook extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'gbook';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'reply_type_text'
    ];
    

    
    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getReplyTypeList()
    {
        return ['all' => __('Reply_type all'), 'email' => __('Reply_type email'), 'tel' => __('Reply_type tel')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReplyTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['reply_type']) ? $data['reply_type'] : '');
        $list = $this->getReplyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
