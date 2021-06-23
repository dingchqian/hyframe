<?php
/**
 * Created by PhpStorm.
 * Script Name: UserAuth.php
 * Create: 10:22 下午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Service\Dao\UserDao;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Traits\StaticInstance;

class UserAuth
{
    use StaticInstance;

    const X_TOKEN = 'token';
    protected $userId = 0;
    protected $token = '';
    protected $user = null;

    /**
     * 初始化
     * @param $user User
     * @return object
     * Author: Jason<dcq@kuryun.cn>
     */
    public function init(User $user, ?string $token = null) {
        $this->user = $user;
        $this->userId = $user->id;
        $this->token = $token ?? md5(uniqid());
        di()->get(Redis::class)->set($this->getRedisKey(), Json::encode(['id' => $user->id]));

        return $this;
    }

    /**
     * 重载
     * @param $token string
     * @return object
     * Author: Jason<dcq@kuryun.cn>
     */
    public function reload(string $token) {
        $this->token = $token;
        $string = di()->get(Redis::class)->get($this->getRedisKey());
        if($string && $data = Json::decode($string)) {
            $this->userId = intval($data['id'] ?? 0);
        }

        return $this;
    }

    /**
     * 重构
     * @return object
     * Author: Jason<dcq@kuryun.cn>
     */
    public function build() {
        if ($this->getUserId() === 0) {
            throw new BusinessException(ErrorCode::TOKEN_INVALID);
        }

        return $this;
    }

    /**
     * 获取用户id
     * @return int
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getUserId(): int {
        return $this->userId;
    }

    /**
     * 获取token
     * @return string
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getToken(): ?string {
        return $this->token;
    }

    /**
     * 获取用户信息
     * @return User
     * Author: Jason<dcq@kuryun.cn>
     */
    public function getUser(): User {
        if ($this->user) {
            return $this->user;
        }
        $userId = $this->build()->getUserId();

        return $this->user = di()->get(UserDao::class)->getOne($userId, true);
    }

    /**
     * 获取key
     * Author: Jason<dcq@kuryun.cn>
     */
    protected function getRedisKey(): string {
        if(empty($this->token)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, 'TOKEN 未正常初始化');
        }

        return 'auth:' . $this->token;
    }
}