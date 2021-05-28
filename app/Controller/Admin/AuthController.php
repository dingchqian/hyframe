<?php
/**
 * Created by PhpStorm.
 * Script Name: AuthController.php
 * Create: 2020/12/25 9:05
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */
namespace App\Controller\Admin;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * @AutoController()
 */
class AuthController extends BaseController
{
    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    private $validationFactory;

    /**
     * 登录
     * Author: Jason<dcq@kuryun.cn>
     */
    public function login() {
        if($this->request->isMethod('post')) {
            $validator = $this->validationFactory->make(
                $this->request->all(),
                [
                    'username' => 'required|between:3,20',
                    'password' => 'required|between:6,20'
                ]
            );

            if ($validator->fails()){
                return $this->response->error($validator->errors()->first());
            }

            $params = $this->request->all();
            $user = $this->adminDao->getOneByMap(['username' => $params['username']]);
            if ($user && $user['status'] == 1) {
                if(password_verify($params['password'], $user['password'])){
                    $this->adminDao->updateOne([
                        'id' => $user['id'],
                        'ip' => get_client_ip(),
                        'last_time' => time()
                    ]);
                    $this->session->set('aid', $user->id);
                    return $this->response->success(['url' => '/admin/index/index'], '登录成功');
                }else{
                    return $this->response->error('账号或密码错误');
                }
            }else{
                return $this->response->error('用户不存在或已被禁用');
            }
        }

        return $this->show();
    }

    /**
     * 退出
     * Author: Jason<dcq@kuryun.cn>
     */
    public function logout()
    {
        $this->session->clear();
        return $this->response->redirect($this->url('admin/auth/login'));
    }
}