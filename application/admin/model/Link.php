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

class Link extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'link';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

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

}
