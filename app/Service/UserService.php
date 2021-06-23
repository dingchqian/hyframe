<?php
/**
 * Created by PhpStorm.
 * Script Name: Demo.php
 * Create: 2021/6/23 16:28
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service;


use App\Model\User;
use App\Service\Dao\UserDao;
use Hyperf\DbConnection\Db;
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
     * @return object
     * Author: Jason<dcq@kuryun.cn>
     */
    public function register(array $params) {
        $insert_data = $params;
        $insert_data['password'] = md5($params['password']);
        $result = $this->userDao->addOne($insert_data);

        return UserAuth::instance()->init($result);
    }

    /**
     * 登录
     * @param array $params
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function login(array $params)
    {
        //启动事务
        Db::beginTransaction();
        try{
            /**
             * @var User
             */
            $result = $this->userDao->getOneByMap(['mobile' => $params['mobile']]);
            if(! $result){
                $result = $this->userDao->addOne([
                    'mobile' => $params['mobile'],
                    'last_time' => time(),
                    'nickname' => 'yll' . time()
                ]);
            }else{
                if($result->status < 1){
                    return  '账号已被禁用！';
                }
                $result = $this->userDao->updateOne(['id' => $result['id'], 'last_time' => time()]);
            }
            Db::commit();
        } catch(\Throwable $ex){
            $this->logger->error('登录失败，错误信息' . json_encode($ex->getMessage(), JSON_UNESCAPED_UNICODE));
            Db::rollBack();
            $result = '登录失败！';
        }
        return $result;
    }
}