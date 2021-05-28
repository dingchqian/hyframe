<?php
/**
 * Created by PhpStorm.
 * Script Name: AdminruleController.php
 * Create: 2020/12/31 18:07
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */
namespace App\Controller\Admin;


use App\Service\Dao\AdminRuleDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AdminMiddleware;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class AdminruleController extends BaseController
{
    /**
     * @Inject()
     * @var AdminRuleDao
     */
    protected $dao;

    /**
     * 列表
     * Author: Jason<dcq@kuryun.cn>
     */
    public function index(){
        if($this->request->isMethod('post')){
            $list = $this->dao->getAll([], ['sort', 'desc']);
            return $this->response->success(['total' => count($list), 'list' => $list]);
        }
        return $this->show();
    }

    /**
     * 添加
     * Author: Jason<dcq@kuryun.cn>
     */
    public function add() {
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('新增权限菜单')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('pid', 'select', '上级菜单', '上级菜单', select_list_as_tree($this->dao, ['status' => 1, 'type' => 1], '==顶级菜单==', 'id', ['sort', 'desc']))
            ->addFormItem('type', 'select', '类型', '选择类型', $this->dao->types(), 'required')
            ->addFormItem('title', 'text', '标题', '标题', [], 'required maxlength="32"')
            ->addFormItem('name', 'text', '标识', '权限控制使用', [], ' maxlength="32"')
            ->addFormItem('href', 'text', '链接', '链接', [])
            ->addFormItem('icon', 'icon', '图标', 'font-awesome图标')
            ->addFormItem('sort', 'number', '排序', '按数字从大到小排列', [], 'required');

        return $builder->show();
    }

    /**
     * 编辑
     * Author: Jason<dcq@kuryun.cn>
     */
    public function edit() {
        $id = $this->request->input('id');
        $data = $this->dao->getOne($id);
        if(! $data){
            return $this->response->error('id非法');
        }
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('编辑菜单权限')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('id', 'hidden', 'id', 'id')
            ->addFormItem('pid', 'select', '上级菜单', '上级菜单', select_list_as_tree($this->dao, ['status' => 1, 'type' => 1], '==顶级菜单==', 'id', ['sort', 'desc']))
            ->addFormItem('type', 'select', '类型', '选择类型', $this->dao->types(), 'required')
            ->addFormItem('title', 'text', '标题', '标题', [], 'required maxlength="32"')
            ->addFormItem('name', 'text', '标识', '权限控制使用', [], ' maxlength="32"')
            ->addFormItem('href', 'text', '链接', '链接', [])
            ->addFormItem('icon', 'icon', '图标', 'font-awesome图标')
            ->addFormItem('sort', 'number', '排序', '按数字从大到小排列', [], 'required')
            ->addFormItem('status', 'radio', '状态', '状态', [1 => '显示', 0 => '隐藏'], 'required')
            ->setFormData($data);

        return $builder->show();
    }
}