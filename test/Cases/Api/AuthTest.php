<?php
/**
 * Created by PhpStorm.
 * Script Name: AuthTest.php
 * Create: 9:33 下午
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace HyperfTest\Cases\Api;

use HyperfTest\HttpTestCase;

class AuthTest extends HttpTestCase
{
    public function testRegister() {
        $res = $this->doJson('/api/auth/registerPost', [
            'mobile' => '13212345679',
            'password' => '123456',
            'username' => '大侠'
        ], false);

        var_dump($res);
        $this->assertContains($res['code'], $this->codeArr);
    }

    public function testLogin() {
        $res = $this->doJson('/api/auth/loginPost', [
            'mobile' => '13212345679',
            'password' => '123456',
        ], false);

        var_dump($res);
        if($res['code'] == 1){
            $this->getToken($res['data']['token']);
        }
        $this->assertContains($res['code'], $this->codeArr);
    }
}