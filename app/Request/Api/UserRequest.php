<?php

declare(strict_types=1);

namespace App\Request\Api;

use App\Request\BaseRequest;

class UserRequest extends BaseRequest
{
    /**
     * 设置规则
     * @param $fields array
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setRules($fields = []) {
        $this->rules = [
            'province' => 'string|between:1,50',
            'city' => 'string|between:1,50',
            'area' => 'string|between:1,50',
            'sex' => 'integer|in:0,1,2',
            'headimgurl' => 'string'
        ];
        if(count($fields)){
            foreach ($this->rules as $k => $v){
                if(!in_array($k, $fields)){
                    unset($this->rules[$k]);
                }
            }
        }
    }

    /**
     * 获取已定义验证规则的错误消息
     */
    public function messages(): array
    {
        return [

        ];
    }

    /**
     * 获取验证错误的自定义属性
     */
    public function attributes(): array
    {
        return [

        ];
    }

    /**
     * 用户信息编辑
     * Author: Jason<dcq@kuryun.cn>
     */
    public function sceneSetUser() {
        $fields = ['province', 'city', 'area', 'headimgurl', 'sex'];
        $this->setRules($fields);
    }
}
