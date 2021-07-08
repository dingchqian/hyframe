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

class QueueTest extends HttpTestCase
{
    public function testQueueIndex() {
        $params = [];
        $res = $this->doJson('/api/queue/index', $params, true);

        var_dump($res);
        $this->assertContains($res['code'], $this->codeArr);
    }

    public function testQueueAmqp() {
        $params = [];
        $res = $this->doJson('/api/queue/amqpMsg', $params, true);

        var_dump($res);
        $this->assertContains($res['code'], $this->codeArr);
    }
}