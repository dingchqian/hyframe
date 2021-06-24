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
    public function testGetUser() {
        $res = $this->doJson('/api/user/getUserPost', [], true);

        var_dump($res);
        $this->assertContains($res['code'], $this->codeArr);
    }
}