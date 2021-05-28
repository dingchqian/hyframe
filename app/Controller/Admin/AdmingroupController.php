<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/1/4 9:37
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Admin;

use App\Kernel\Tree\Tree;
use App\Service\Dao\AdminGroupDao;
use App\Service\Dao\AdminRuleDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AdminMiddleware;
use Hyperf\Utils\Context;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class AdmingroupController extends BaseController
{
    /**
     * @Inject()
     * @var AdminGroupDao
     */
    protected $dao;

    /**
     * @Inject()
     * @var AdminRuleDao
     */
    protected $ruleDao;

    /**
     * 分组列表
     * Author: Jason<dcq@kuryun.cn>
     */
    public function index(){
        if($this->request->isMethod('post')){
            $post_data = $this->request->post();
            $where = [];
            !empty($post_data['search_key']) && $where['title'] = ['like', '%'.$post_data['search_key'].'%'];
            $total = $this->dao->total($where, true);
            if($total){
                $list = $this->dao->getList(
                    [$post_data['page'], $post_data['limit']], $where,
                    ['sort', 'asc'], [], 1
                );
            }else{
                $list = [];
            }
            return $this->response->success(['total' => $total, 'list' => $list]);
        }

        $adminInfo = Context::get('adminInfo');
        $builder = new ListBuilder();
        $builder->setSearch([
            ['type' => 'text', 'name' => 'search_key', 'title' => '搜索词','placeholder' => '部门名称']
        ]);
        //超管拥有新增权限
        if($adminInfo['group_id'] == 1) {
            $builder->addTopButton('addnew');
        }
        $builder->addTableColumn(['title' => '权限名称', 'field' => 'title'])
            ->addTableColumn(['title' => '备注信息', 'field' => 'remark'])
            ->addTableColumn(['title' => '状态', 'field' => 'status', 'type' => 'enum', 'options' => [1 => '启用', 0 => '禁用']])
            ->addTableColumn(['title' => '操作', 'width' => 120, 'type' => 'toolbar'])
            ->addRightButton('edit')
            ->addRightButton('self', ['title' => '授权','class' => 'layui-btn layui-btn-success layui-btn-xs','lay-event' => 'auth', 'href' => $this->url('auth') . '?group_id=__data_id__']);
        //超管拥有删除权限
        if($adminInfo['group_id'] == 1) {
            $builder->addRightButton('delete');
        }

        return $builder->show();
    }

    /**
     * 添加
     * Author: Jason<dcq@kuryun.cn>
     */
    public function add(){
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('新增')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('title', 'text', '角色名称', '角色名称', [], 'required')
            ->addFormItem('remark', 'textarea', '备注', '备注');

        return $builder->show();
    }

    /**
     * 编辑
     * Author: Jason<dcq@kuryun.cn>
     */
    public function edit(){
        $id = $this->request->input('id');
        $data = $this->dao->getOne($id);
        if(! $data){
            return $this->response->error('参数错误');
        }
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('编辑')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('id', 'hidden', 'id', 'id')
            ->addFormItem('title', 'text', '角色名称', '角色名称', [], 'required')
            ->addFormItem('remark', 'textarea', '备注', '备注')
            ->setFormData($data);

        return $builder->show();
    }

    /**
     * 授权
     * Author: Jason<dcq@kuryun.cn>
     */
    public function auth() {
        if($this->request->isMethod('post')) {
            $post_data = $this->request->post();
            $update_data = [
                'id' => $post_data['id'],
                'rules' => $post_data['rules']
            ];
            $result = $this->dao->updateOne($update_data);
            if($result) {
                return $this->response->success(['result' => $result, 'url' => '/admin/admingroup/index'], '授权成功');
            }else {
                return $this->response->error('授权失败');
            }
        }
        $group_id = $this->request->input('group_id');
        $data = $this->dao->getOne($group_id);
        if(! $data) {
            return $this->response->error('id非法');
        }

        return $this->show(['data' => $data]);
    }

    /**
     * 节点树
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getRulesTree() {
        if($this->request->isMethod('post')){
            $post_data = $this->request->post();
            $rules = $this->ruleDao->getAll(['status' => 1], ['sort', 'asc'], ['id', 'pid', 'title', 'href']);
            $group = $this->dao->getOne($post_data['group_id']);
            $group_rules = explode(',', $group['rules']);

            //插入layui展开参数
            foreach ($rules as &$item) {
                $item['spread'] = true;
                if($item['href']) {
                    $item['title'] = $item['title'] . '【' . $item['href'] . '】';
                }
                //设置数据源中勾选的叶子节点checked属性为true
                $total = $this->ruleDao->total(['pid' => $item['id'], 'status' => 1]);
                if(in_array($item['id'], $group_rules) && !$total) {
                    $item['checked'] = true;
                }else {
                    $item['checked'] = false;
                }
            }
            $Tree = new Tree();
            $rules_tree = $Tree->listToTree($rules->toArray(), $pk='id', $pid='pid', $child='children');

            return $this->response->success(['rules_tree' => $rules_tree]);
        }
    }
}