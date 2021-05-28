<?php
/**
 * Created by PhpStorm.
 * Script Name: AdminRules.php
 * Create: 3:06 下午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service\Dao;


class AdminRuleDao extends BaseDao
{
    /**
     * 类型
     * @param null $type
     * @return array
     */
    public function types($type=null){
        $list = [
            1 => '菜单',
            2 => '权限'
        ];
        return isset($list[$type]) ? $list[$type] : $list;
    }
}