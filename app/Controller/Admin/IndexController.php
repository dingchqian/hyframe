<?php
/**
 * Created by PhpStorm.
 * Script Name: IndexController.php
 * Create: 11:12 上午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Admin;

use App\Service\Dao\AdminGroupDao;
use App\Service\Dao\AdminRuleDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Utils\Context;
use App\Middleware\AdminMiddleware;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class IndexController extends BaseController
{
    /**
     * @Inject()
     * @var AdminGroupDao
     */
    private $adminGroupDao;

    /**
     * @Inject()
     * @var AdminRuleDao
     */
    private $adminRuleDao;

    /**
     * 首页
     * Author: Jason<dcq@kuryun.cn>
     */
    public function index() {
        return $this->show(['admin' => Context::get('adminInfo')]);
    }

    /**
     * 欢迎页
     * Author: Jason<dcq@kuryun.cn>
     */
    public function welcome() {
        return $this->show(['admin' => Context::get('adminInfo')]);
    }

    /**
     * 获取初始化数据
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getSystemInit(){
        $homeInfo = [
            'title' => '首页',
            'href'  => 'welcome',
        ];
        $logoInfo = [
            'title' => config('app_title'),
            'href' => '/admin/index/index',
            'image' => 'http://via.placeholder.com/10x10'
        ];
        $menuInfo = $this->getMenuList();
        $systemInit = [
            'homeInfo' => $homeInfo,
            'logoInfo' => $logoInfo,
            'menuInfo' => $menuInfo,
        ];

        return $this->response->json($systemInit);
    }

    /**
     * 获取菜单列表
     * @return array
     * @author: fudaoji<fdj@kuryun.cn>
     */
    private function getMenuList() {
        $adminInfo = Context::get('adminInfo');
        $where = ['status' => 1, 'type' => 1];
        if($adminInfo['group_id'] != 1) {
            $adminGroup = $this->adminGroupDao->getOne($adminInfo['group_id']);
            $where['id'] = ['in', explode(',', $adminGroup['rules'])];
        }

        $menuList = $this->adminRuleDao->getAll($where, ['sort', 'desc'], ['id', 'pid', 'title', 'icon', 'href', 'target']);
        $menuList = $this->buildMenuChild(0, $menuList);

        return $menuList;
    }

    /**
     * 递归获取子菜单
     * @param $pid
     * @param $menuList
     * @return array
     * @author: fudaoji<fdj@kuryun.cn>
     */
    private function buildMenuChild($pid, $menuList){
        $treeList = [];
        foreach ($menuList as $v) {
            if ($pid == $v['pid']) {
                $node = $v;
                $child = $this->buildMenuChild($v['id'], $menuList);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                // todo 后续此处加上用户的权限判断
                $treeList[] = $node;
            }
        }
        return $treeList;
    }
}