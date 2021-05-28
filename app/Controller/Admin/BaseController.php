<?php
/**
 * Created by PhpStorm.
 * Script Name: BaseController.php
 * Create: 10:41 下午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Admin;


use App\Controller\AbstractController;
use App\Model\Admin;
use App\Service\Dao\AdminDao;
use Hyperf\Contract\SessionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Hyperf\View\RenderInterface;

class BaseController extends AbstractController
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;
    protected $assign = [];
    protected $dao;
    protected $pk = 'id';

    /**
     * @Inject()
     * @var RenderInterface
     */
    protected $render;

    /**
     * @Inject()
     * @var SessionInterface
     */
    protected $session;

    /**
     * @Inject
     * @var AdminDao
     */
    public $adminDao;

    /**
     * 统一视图
     * @param array $assign
     * @param string $view_path
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function show($assign = [], $view_path = '') {
        if(!$view_path) {
            $view_path = strtolower($this->request->getRequestUri());
        }
        //[$this->module, $this->controller, $this->action] = explode('/', substr($this->request->getRequestUri(), 1));
        $assign['module'] = Context::get('module');
        $assign['controller'] = Context::get('controller');
        $assign['action'] = Context::get('action');
        $assign['app_name'] = config('app_name');
        $assign['app_title'] = config('app_title');
        $assign['system_config'] = config('system');
        $assign['admin'] = Context::get('adminInfo');
        $this->assign = array_merge($this->assign, $assign);

        return $this->render->render($view_path, $this->assign);
    }

    /**
     * 构造url
     * @param $action
     * @param $params
     * @return string
     * Author: Jason<dcq@kuryun.cn>
     */
    public function url($action, $params = []) {
        $path = explode('/', trim($action, '/'));
        switch (count($path)){
            case 1:
                $module = Context::get('module');
                $controller = Context::get('controller');
                $action = $path[0];
                break;
            case 2:
                $module = Context::get('module');
                $controller = $path[0];
                $action = $path[1];
                break;
            case 3:
                $module = $path[0];
                $controller = $path[1];
                $action = $path[2];
                break;
        }
        $url = '/' . $module . '/' . $controller . '/' . $action;
        if(!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * 设置一条或者多条数据的状态
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function setStatus() {
        $ids = $this->request->input('ids');
        $status = $this->request->input('status');

        if (empty($ids)) {
            return $this->response->error('请选择要操作的数据');
        }

        $ids = (array) $ids;
        if($status == 'delete'){
            if($this->dao->delBatch($ids)){
                return $this->response->success([], '删除成功');
            }else{
                return $this->response->error('删除失败');
            }
        }else{
            $arr = [];
            $msg = [
                'success' => '操作成功！',
                'error'   => '操作失败！',
            ];
            switch ($status) {
                case 'forbid' :  // 禁用条目
                    $data['status'] = 0;
                    break;
                case 'resume' :  // 启用条目
                    $data['status'] = 1;
                    break;
                case 'hide' :  // 隐藏条目
                    $data['status'] = 2;
                    break;
                case 'show' :  // 显示条目
                    $data['status'] = 1;
                    break;
                case 'recycle' :  // 移动至回收站
                    $data['status'] = 1;
                    break;
                case 'restore' :  // 从回收站还原
                    $data['status'] = 1;
                    break;
                default:
                    return $this->response->error('参数错误');
                    break;
            }
            foreach($ids as $id){
                $data[$this->pk] = $id;
                $arr[] = $data;
            }
            if($this->dao->updateBatch($arr)){
                return $this->response->success([], $msg['success']);
            }else{
                return $this->response->error($msg['error']);
            }
        }
    }
    /**
     * 保存数据
     * @param string $jump_to
     * @param array $data
     * @return mixed
     * Author Doogie<461960962@qq.com>
     */
    public function savePost($jump_to = '', $data=[]){
        $post_data = $data ? $data :  $this->request->post();
        if(empty($post_data[$this->pk])){
            $res = $this->dao->addOne($post_data);
        }else {
            $res = $this->dao->updateOne($post_data);
        }
        unset($post_data, $data);
        if($res){
            return $this->response->success($res, '数据保存成功', $jump_to);
        }else{
            return $this->response->error('数据保存出错', [], $jump_to);
        }
    }

    protected function getAdmins($where = []){
        return $this->adminDao->getField(['realname', 'id'], $where);
    }

    protected function getAdminInfo(): Admin
    {
        return Context::get('adminInfo');
    }
}