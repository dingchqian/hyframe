<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2020/12/31 17:31
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service\Dao;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;

class SettingDao extends BaseDao
{
    /**
     *
     * 全局设置
     * @param int $refresh
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function settings($refresh = 0){
        $list = $this->getAll([], [], [], $refresh);
        $data = [];
        foreach ($list as $v){
            $data[$v['name']] = json_decode($v['value'], true);
        }
        ApplicationContext::getContainer()->get(ConfigInterface::class)->set('system', $data);
        return $data;
    }

    /**
     * 设置栏目
     * Author: Jason<dcq@kuryun.cn>
     */
    public function tabList() {
        return [
            'site' => [
                'title' => '站点信息',
                'href' => '/admin/setting/index?name=site'
            ],
            'tbk' => [
                'title' => '电商联盟',
                'href' => '/admin/setting/index?name=tbk'
            ],
            'upload' => [
                'title' => '附件设置',
                'href' => '/admin/setting/index?name=upload'
            ],
            'payment' => [
                'title' => '支付设置',
                'href' => '/admin/setting/index?name=payment'
            ],
            'sms' => [
                'title' => '短信设置',
                'href' => '/admin/setting/index?name=sms'
            ],
            'device' => [
                'title' => '设备厂商',
                'href' => '/admin/setting/index?name=device'
            ],
            'common' => [
                'title' => '其他设置',
                'href' => '/admin/setting/index?name=common'
            ]
        ];
    }
}