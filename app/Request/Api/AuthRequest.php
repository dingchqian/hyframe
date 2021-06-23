<?php

declare(strict_types=1);

namespace App\Request\Api;

use App\Request\BaseRequest;

class AuthRequest extends BaseRequest
{
    /**
     * 设置规则
     * @param $fields array
     * Author: Jason<dcq@kuryun.cn>
     */
    public function setRules($fields = []) {
        $this->rules = [
            'mobile' => $this->ruleMobile,
            'password' => 'required|between:1,6',
            'username' => 'required|string|between:1,20',
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
            'mobile.required' => '请填写 :attribute',
            'mobile.regex' => '请填写 :attribute',
            'password.required'  => '请填写 :attribute',
            'username.required'  => '请填写 :attribute',
        ];
    }

    /**
     * 获取验证错误的自定义属性
     */
    public function attributes(): array
    {
        return [
            'mobile' => '手机号',
            'password' => '密码',
            'username' => '用户名',
        ];
    }

    /**
     * 注册场景
     * Author: Jason<dcq@kuryun.cn>
     */
    public function sceneRegister(){
        $fields = ['mobile', 'password', 'username', 'headimgurl'];
        $this->setRules($fields);
    }
}
