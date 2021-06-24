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

class UserTest extends HttpTestCase
{
    public function testSetUser() {
        $params = [
            'province' => '福建省',
            'city' => '厦门市',
            'area' => '湖里区',
            'sex' => '1',
            'headimgurl' => 'https://zyx.images.huihuiba.net/FjIw_L1Pmha-5gZqGVdZUhRNr4yg'
        ];
        $res = $this->doJson('/api/user/setUserPost', $params, true);

        var_dump($res);
        $this->assertContains($res['code'], $this->codeArr);
    }
}