<?php
/**
 * Created by PhpStorm.
 * Script Name: AdminController.php
 * Create: 2020/12/25 9:05
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */
namespace App\Controller\Admin;

use App\Service\Dao\AdminDao;
use App\Service\Dao\AdminGroupDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AdminMiddleware;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class AdminController extends BaseController
{
    /**
     * @Inject()
     * @var AdminDao
     */
    protected $dao;

    /**
     * @Inject()
     * @var AdminGroupDao
     */
    protected $adminGroupDao;
    
    /**
     * 管理员列表
     * Author: Jason<dcq@kuryun.cn>
     */
    public function index() {
        if($this->request->isMethod('post')){
            $post_data = $this->request->post();
            $where = [];
            !empty($post_data['search_key']) && $where['admin.username'] = ['like', '%'.$post_data['search_key'].'%'];
            if(!empty($post_data['group_id'])) {
                $where['group_id'] = $post_data['group_id'];
            }
            $params = [
                'join' => [
                    ['admin_group as ag', 'admin.group_id=ag.id', 'left']
                ],
                'where' => $where,
                'refresh' => true
            ];
            $total = $this->dao->totalJoin($params);
            if($total){
                $params_list = [
                    'field' => ['admin.*', 'ag.title as group_title'],
                    'limit' => [$post_data['page'], $post_data['limit']],
                    'order' => ['admin.id' => 'desc']
                ];
                $params = array_merge($params, $params_list);
                $list = $this->dao->getListJoin($params);
            }else{
                $list = [];
            }

            return $this->response->success(['total' => $total, 'list' => $list]);
        }

        $group_list = $this->adminGroupDao->getField(['title', 'id'], ['status' => 1]);
        $builder = new ListBuilder();
        $builder->setSearch([
            ['type' => 'text', 'name' => 'search_key', 'title' => '搜索词','placeholder' => '账号、手机号、姓名'],
            ['type' => 'select', 'name' => 'group_id', 'title' => '角色', 'options' => [0 => '全部角色'] + $group_list]
        ])
            ->addTopButton('addnew')
            ->addTableColumn(['id' => 'ID', 'field' => 'id'])
            ->addTableColumn(['title' => '账号', 'field' => 'username'])
            ->addTableColumn(['title' => '邮箱', 'field' => 'email'])
            ->addTableColumn(['title' => '手机号', 'field' => 'mobile'])
            ->addTableColumn(['title' => '姓名', 'field' => 'realname'])
            ->addTableColumn(['title' => '角色', 'field' => 'group_title'])
            ->addTableColumn(['title' => '状态', 'field' => 'status', 'type' => 'enum', 'options' => [0 => '禁用', 1 => '启用']])
            ->addTableColumn(['title' => '操作', 'width' => 220, 'type' => 'toolbar'])
            ->addRightButton('edit')
            ->addRightButton('self', ['title' => '修改密码','class' => 'layui-btn layui-btn-warm layui-btn-xs','href' => $this->url('setPassword') . '?id=__data_id__'])
            ->addRightButton('delete');
        return $builder->show();
    }

    /**
     * 添加
     * Author: Jason<dcq@kuryun.cn>
     */
    public function add(){
        $groups = $this->adminGroupDao->getField(['title', 'id'], ['status' => 1]);
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('新增')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('group_id', 'select', '角色', '角色', $groups, 'required')
            ->addFormItem('username', 'text', '账号', '4-20位', [], 'required minlength="4" maxlength="20"')
            ->addFormItem('password', 'password', '密码', '6-20位', [], 'required')
            ->addFormItem('email', 'text', '邮箱', '邮箱')
            ->addFormItem('mobile', 'text', '手机', '手机')
            ->addFormItem('realname', 'text', '姓名', '姓名');

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
            return $this->response->error('id参数错误');
        }
        $groups = $this->adminGroupDao->getField(['title', 'id'], ['status' => 1]);
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('编辑')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('id', 'hidden', 'id', 'id')
            ->addFormItem('group_id', 'select', '角色', '角色', $groups, 'required')
            ->addFormItem('username', 'text', '账号', '4-20位', [], 'required minlength="4" maxlength="20"')
            ->addFormItem('email', 'text', '邮箱', '邮箱')
            ->addFormItem('mobile', 'text', '手机', '手机')
            ->addFormItem('realname', 'text', '姓名', '姓名')
            ->setFormData($data);

        return $builder->show();
    }

    /**
     * 编辑密码
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setPassword(){
        $id = $this->request->input('id');
        $data = $this->dao->getOne($id);
        if(! $data){
            return $this->response->error('id参数错误');
        }
        unset($data['password']);
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('编辑')  //设置页面标题
            ->setPostUrl($this->url('savePost')) //设置表单提交地址
            ->addFormItem('id', 'hidden', 'id', 'id')
            ->addFormItem('password', 'password', '新密码', '6-20位', [], 'required')
            ->setFormData($data);

        return $builder->show();
    }

    /**
     * 保存数据
     * @param string $url
     * @param array $data
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function savePost($url='', $data=[]){
        $post_data = $this->request->post();
        if(!empty($post_data['password'])){
            $post_data['password'] = ky_generate_password($post_data['password']);
        }

        return parent::savePost($this->url('index'), $post_data);
    }

    /**
     * 修改个人密码
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setPersonPw(){
        if($this->request->isMethod('post')){
            $post_data = ['password' => $this->request->input('password')];
            if(!empty($post_data['password'])){
                $post_data['password'] = ky_generate_password($post_data['password']);
            }
            $post_data['id'] = $this->session->get('aid');

            $res = $this->dao->updateOne($post_data);
            if($res){
                $this->dao->getOneByMap(['username' => $res['username']], 1);
                $this->session->set('aid', $res->id);
                return $this->response->success('密码修改成功', $this->url('auth/login'));
            }else{
                return $this->response->error('系统出错');
            }
        }
        //使用FormBuilder快速建立表单页面。
        $builder = new FormBuilder();
        $builder->setMetaTitle('修改个人密码')  //设置页面标题
            ->setPostUrl($this->url('setPersonPw')) //设置表单提交地址
            ->addFormItem('password', 'password', '新密码', '6-20位', [], 'required minlength="6" maxlength="20"');

        return $builder->show();
    }
}