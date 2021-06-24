<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/23 16:28
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service;


use App\Constants\ErrorCode;
use App\Exception\ApiException;
use App\Service\Dao\UserDao;
use Hyperf\Di\Annotation\Inject;
use HyperfX\Utils\Service;

class UserService extends Service
{
    /**
     * @Inject()
     * @var UserDao
     */
    private $userDao;

    /**
     * 注册
     * @param $params array
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function register(array $params) {
        if($this->userDao->getOneByMap(['mobile' => $params['mobile']])) {
            throw new ApiException(ErrorCode::BAD_PARAM, '该手机号已注册');
        }
        $insert_data = $params;
        $insert_data['password'] = md5($params['password']);
        $result = $this->userDao->addOne($insert_data);
        if($result) {
            unset($result['password']);
            return UserAuth::instance()->init($result);
        }
        throw new ApiException(ErrorCode::BAD_PARAM, '注册失败');
    }

    /**
     * 登录
     * @param $params array
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function login(array $params) {
        $result = $this->userDao->getOneByMap(['mobile' => $params['mobile']]);
        if(!$result) {
            throw new ApiException(ErrorCode::BAD_PARAM, '账号未注册');
        }
        if($result['password'] != md5($params['password'])) {
            throw new ApiException(ErrorCode::BAD_PARAM, '账号或密码错误');
        }
        if($result) {
            unset($result['password']);
            return UserAuth::instance()->init($result);
        }
        throw new ApiException(ErrorCode::BAD_PARAM, '登录失败');
    }

    /**
     * 完善用户信息
     * @param $params array
     * @return mixed
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setUser(array $params) {
        $update_data = $params;
        $update_data['id'] = get_user_id();
        $result = $this->userDao->updateOne($update_data);

        if($result) {
            unset($result['password']);
            return UserAuth::instance()->init($result);
        }
        throw new ApiException(ErrorCode::BAD_PARAM, '保存失败');
    }
}