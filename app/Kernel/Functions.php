<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * Script Name: Functions.php
 * Create: 8:40 下午
 * Description: 自定义助手函数
 * Author: Jason<dcq@kuryun.cn>
 */

use App\Kernel\Tree\Tree;
use GuzzleHttp\HandlerStack;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

if(!function_exists('short_url')){
    /**
     * 短连接生成
     * @param string $url
     * @return false|string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    function short_url($url = ''){
        $api_url = 'https://tinyurl.com/create.php';
        $client = new \GuzzleHttp\Client([
            'handler' => HandlerStack::create(new CoroutineHandler()),
            'timeout' => 10
        ]);
        $response = $client->post($api_url, [
            'form_params' => ['url' => $url]
        ]);
        $content = $response->getBody()->getContents();
        $pattern = '/<b>(.+)<\/b><div id="success">.*<\/div>/U';
        preg_match($pattern, $content,$dir);
        return  (is_array($dir) && count($dir) >=2) ? $dir[1] : '' ;
    }
}

if(!function_exists('get_unique_id')){
    /**
     * 产生长度为16的唯一id
     *
     * 组成: 时间戳(10位) + 用户id(用户id被0填充到左边，使长度为6) =
     */
    function get_unique_id($id, $length = 6, $string = 0)
    {
        $id = str_pad((string) $id, $length, (string)$string, STR_PAD_LEFT);
        $timestamp = time();
        $uniqid = $timestamp . $id;
        return intval($uniqid);
    }
}
/**
 * 抓取远程图片
 * @param string $url
 * @param int $type
 * @param int $timeout
 * @return array
 * Author: Doogie<fdj@kuryun.cn>
 */
if(!function_exists('curl_img')) {
    function curl_img($url = '', $type = 0, $timeout = 30)
    {
        $msg = ['code' => 0, 'msg' => '未知错误！', 'size' => 0];
        $imgs = ['image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'text/html' => 'html',
            'text/plain' => 'txt',
            'image/pjpeg' => 'jpg',
            'image/x-png' => 'png',
            'image/x-icon' => 'ico',
            'image/bmp' => 'bmp'
        ];
        if (!stristr($url, 'http')) {
            $msg['msg'] = 'url地址不正确!';
            return $msg;
        }
        $dir = pathinfo($url);
        $host = $dir['dirname'];
        $refer = $host . '/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, $refer); //伪造来源地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//返回变量内容还是直接输出字符串,0输出,1返回内容
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);//在启用CURLOPT_RETURNTRANSFER的时候，返回原生的（Raw）输出
        curl_setopt($ch, CURLOPT_HEADER, 0); //是否输出HEADER头信息 0否1是
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //超时时间
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $httpCode = intval($info['http_code']);
        $httpContentType = $info['content_type'];
        $httpSizeDownload = intval($info['size_download']);

        if ($httpCode != '200') {
            $msg['msg'] = 'url返回内容不正确！';
            return $msg;
        }
        if ($type > 0 && !isset($imgs[$httpContentType])) {
            $msg['msg'] = 'url资源类型未知！';
            return $msg;
        }
        if ($httpSizeDownload < 1) {
            $msg['msg'] = '内容大小不正确！';
            return $msg;
        }
        $msg['size'] = $httpSizeDownload;
        $msg['code'] = 1;
        $msg['msg'] = '资源获取成功';
        if ($type == 0 or $httpContentType == 'text/html') $msg['data'] = $data;
        $base_64 = base64_encode($data);
        switch ($type) {
            case 1:
                $msg['data'] = $base_64;
                break;
            case 2:
                $msg['data'] = "data:{$httpContentType};base64,{$base_64}";
                break;
            case 3:
                $msg['data'] = "<img src='data:{$httpContentType};base64,{$base_64}' />";
                break;
            case 4:
                $msg['data'] = base64_encode($base_64);
                break;
            default:
                $msg['msg'] = '未知返回需求！';
        }
        unset($info, $data, $base_64);
        return $msg;
    }
}

/**
 * 获取Container
 * @param null|mixed $id
 * @return \Psr\Container\ContainerInterface
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('di')) {
    function di($id = null) {
        $container = ApplicationContext::getContainer();
        if($id) {
            return $container->get($id);
        }

        return $container;
    }
}

/**
 * redis客户端实例
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('redis')) {
    function redis() {
        return di()->get(\Redis::class);
    }
}

/**
 * 缓存实例简单的缓存
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('cache')) {
    function cache() {
        return di()->get(\Psr\SimpleCache\CacheInterface::class);
    }
}

/**
 * 获取客户端ip
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('get_client_ip')) {
    function get_client_ip() {
        try {
            /**
             * @var ServerRequestInterface $request
             */
            $request = Context::get(ServerRequestInterface::class);
            $ip_addr = $request->getHeaderLine('x-forwarded-for');
            if(verify_ip($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('remote-host');
            if(verify_ip($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getHeaderLine('x-real-ip');
            if(verify_ip($ip_addr)) {
                return $ip_addr;
            }
            $ip_addr = $request->getServerParams()['remote_addr'] ?? '0.0.0.0';
            if(verify_ip($ip_addr)) {
                return $ip_addr;
            }
        } catch (Throwable $e) {
            return '0.0.0.0';
        }
        return '0.0.0.0';
    }
}

/**
 * IP验证
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('verify_ip')) {
    function verify_ip($realip) {
        return filter_var($realip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
}

/**
 * 过滤Emoji标签
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('filter_emoji')) {
    function filter_emoji($str) {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        $cleaned = strip_tags($str);
        return htmlspecialchars(($cleaned));
    }
}

/**
 * 产生数字与字母混合随机字符串
 * @param int $len 数值长度,默认6位
 * @return string
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('get_rand_str')) {
    function get_rand_str($len = 6) {
        $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
            'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
            'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2',
            '3', '4', '5', '6', '7', '8', '9',
        ];
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = '';
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }

        return $output;
    }
}

/**
 * 智能字符串模糊化
 * @param string $str 被模糊的字符串
 * @param int $len 模糊的长度
 * @return string
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('auto_hid_substr')) {
    function auto_hid_substr($str, $len = 3) {
        if(empty($str)) {
            return null;
        }
        $str = (string)$str;

        $sub_str = mb_substr($str, 0, 1, 'utf-8');
        for($i = 0; $i < $len; $i++) {
            $sub_str .= '*';
        }
        if(mb_strlen($str, 'utf-8') <= 2) {
            $str = $sub_str;
        }
        $sub_str .= mb_substr($str, -1, 1, 'utf-8');

        return $sub_str;
    }
}

/**
 * 推送任务到异步队列
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('queue_push')) {
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'admin'): bool {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

/**
 * AMQP生产者消息
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('amqp_produce')) {
    function amqp_produce(ProducerMessageInterface $message): bool {
        return di()->get(Producer::class)->produce($message, true);
    }
}

if(!function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

/**
 * 生成唯一订单号
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('build_order_no')) {
    function build_order_no($prefix = ''){
        return $prefix . time().substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}

/**
 * hash加密密码
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('ky_generate_password')) {
    function ky_generate_password($password) {
        $options['cost']  = 10;
        return password_hash($password, PASSWORD_DEFAULT, $options);
    }
}

/**
 * 获取所有数据并转换成一维数组
 * @param $dao
 * @param array $where
 * @param null $extra
 * @param string $key
 * @param array $order
 * @return array
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('select_list_as_tree')) {
    function select_list_as_tree($dao, $where = [], $extra = null, $key = 'id', $order=['sort', 'asc']) {
        //获取列表
        $con['status'] = 1;
        if ($where) {
            $con = array_merge($con, $where);
        }

        $list = $dao->getAll($con, $order);

        $result = [];
        if ($extra) {
            $result[0] = $extra;
        }
        if($list){
            //转换成树状列表(非严格模式)
            $list = di()->get(Tree::class)->toFormatTree($list->toArray(), 'title', 'id', 'pid', 0, false);
            //转换成一维数组
            foreach ($list as $val) {
                $result[$val[$key]] = $val['title_show'];
            }
        }

        return $result;
    }
}

/**
 * 字符串长度验证
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('stringValid')) {
    function stringValid($string, $min, $max) {
        $string = trim(strip_tags($string));
        $stringLen = mb_strlen($string, 'utf8');
        if ($stringLen < $min || $stringLen > $max) {
            return false;
        }
        return $string;
    }
}

/**
 * 手机号校验
 * Author: Jason<dcq@kuryun.cn>
 */
if(!function_exists('checkTel')) {
    function checkTel($tel, $type = 'mobile') {
        $isMob="/^1[3-5,6,7,8,9]{1}[0-9]{9}$/"; // 手机号码
        $isTel="/^([0-9]{3,4}-?)?[0-9]{7,8}$/"; // 固定电话
        switch($type){
            case 'mobile':
                if(!preg_match($isMob,$tel)) $tel = false;
                break;
            case 'telephone':
                if(!preg_match($isTel,$tel)) $tel = false;
                break;
            default:
                if(!preg_match($isMob,$tel) && !preg_match($isTel,$tel)) {
                    $tel = false;
                }
                break;
        }
        return $tel;
    }
}