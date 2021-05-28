<?php
/**
 * Created by PhpStorm.
 * Script Name: Setting.php
 * Create: 2020/12/31 17:26
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Controller\Admin;


use App\Service\Dao\SettingDao;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AdminMiddleware;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class SettingController extends BaseController
{
    /**
     * @Inject()
     * @var SettingDao
     */
    protected $dao;

    /**
     * 结算设置
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function withdraw(){
        $tabs = [
            'withdraw_b' => [
                'title' => '商家',
                'href' => '/admin/setting/withdraw?name=withdraw_b'
            ],
            'withdraw_c' => [
                'title' => '用户',
                'href' => '/admin/setting/withdraw?name=withdraw_c'
            ]
        ];
        $current_name = $this->request->input('name', 'withdraw_b');
        $setting = $this->dao->getOneByMap(['name' => $current_name]);
        if($this->request->isMethod('post')){
            $post_data = $this->request->post();
            if(empty($setting)){
                $res = $this->dao->addOne([
                    'name' => $current_name,
                    'title' => $tabs[$current_name]['title'],
                    'value' => json_encode($post_data)
                ]);
            }else{
                $res = $this->dao->updateOne([
                    'id' => $setting['id'],
                    'value' => json_encode($post_data)
                ]);
            }
            if($res){
                return $this->response->success([], '保存成功');
            }else{
                return $this->response->error('保存失败，请刷新重试');
            }
        }

        if(empty($setting)){
            $data = [];
        }else{
            $data = json_decode($setting['value'], true);
        }
        $builder = new FormBuilder();
        switch ($current_name){
            case 'withdraw_b':
                $builder->addFormItem('min', 'text', '最低提现', '单位元', [], 'required')
                    ->addFormItem('mall', 'legend', '自营商城', '自营商城')
                    ->addFormItem('rate_mall', 'text', '手续费比例', '填写小数，例如0.01', [], 'required min=0')
                    ->addFormItem('o2o', 'legend', '生活服务', '生活服务')
                    ->addFormItem('rate_o2o', 'text', '手续费比例', '填写小数，例如0.01', [], 'required min=0')
                ;
                break;
            case 'withdraw_c':
                $builder->addFormItem('min', 'text', '最低提现', '单位元',[], 'required')
                    ->addFormItem('rate', 'text', '手续费比例', '填写小数，例如0.01', [], 'required min=0')
                ;
                break;

        }
        $builder->setFormData($data);
        return $builder->show(['tab_nav' => ['tab_list' => $tabs, 'current_tab' => $current_name]]);
    }

    /**
     * 设置首页
     * Author: Jason<dcq@kuryun.cn>
     */
    public function index() {
        $current_name = $this->request->input('name', 'site');
        $setting = $this->dao->getOneByMap(['name' => $current_name]);
        if($this->request->isMethod('post')){
            $post_data = $this->request->post();
            if(empty($setting)){
                $res = $this->dao->addOne([
                    'name' => $current_name,
                    'title' => $this->dao->tabList()[$current_name]['title'],
                    'value' => json_encode($post_data)
                ]);
            }else{
                $res = $this->dao->updateOne([
                    'id' => $setting['id'],
                    'value' => json_encode($post_data)
                ]);
            }
            if($res){
                return $this->response->success([], '保存成功');
            }else{
                return $this->response->error('保存失败，请刷新重试');
            }
        }

        if(empty($setting)){
            $data = [];
        }else{
            $data = json_decode($setting['value'], true);
        }
        $builder = new FormBuilder();
        switch ($current_name){
            case 'tbk':
                $builder->addFormItem('jd', 'legend', '京东联盟', '京东联盟')
                    ->addFormItem('jd_appid', 'text', 'appID', 'appID', [], 'required maxlength=150')
                    ->addFormItem('jd_key', 'text', 'appkey', 'appkey', [], 'required maxlength=150')
                    ->addFormItem('jd_secret', 'text', 'secretkey', 'secretkey', [], 'required maxlength=150')
                    ->addFormItem('tb', 'legend', '淘宝联盟', '淘宝联盟')
                    ->addFormItem('tb_key', 'text', 'appkey', 'appkey', [], 'required maxlength=150')
                    ->addFormItem('tb_secret', 'text', 'secretkey', 'secretkey', [], 'required maxlength=150')
                    ->addFormItem('tb_appid', 'text', '推广位ID', '推广位ID', [], 'required maxlength=150')
                    ->addFormItem('pdd', 'legend', '拼多多联盟', '拼多多联盟')
                    ->addFormItem('pdd_key', 'text', 'client_id', 'client_id', [], 'required maxlength=150')
                    ->addFormItem('pdd_secret', 'text', 'client_secret', 'client_secret', [], 'required maxlength=150')
                    ->addFormItem('pdd_appid', 'text', '推广位ID', '推广位ID', [], 'required maxlength=150');
                break;
            case 'device':
                $builder->addFormItem('hik', 'legend', '海康威视', '海康威视')
                    ->addFormItem('hik_ak', 'text', 'clientId', 'client_id', [], 'required maxlength=150')
                    ->addFormItem('hik_sk', 'text', 'clientSecret', 'client_secret', [], 'required maxlength=150');
                break;
            case 'common':
                $builder/*->addFormItem('print_title', 'legend', '打印机', '打印机')
                    ->addFormItem('print_ip', 'text', '打印机ip', '打印机ip', [], ' maxlength=60')
                    ->addFormItem('print_port', 'text', '打印机端口', '打印机端口', [], ' maxlength=20')*/
                    ->addFormItem('express_title', 'legend', '快递接口', '快递接口')
                    ->addFormItem('kuaidi_key', 'text', '接口key', '接口key', [], 'maxlength=64')
                    ->addFormItem('map_title', 'legend', '地图', '地图')
                    ->addFormItem('map_qq_key', 'text', '腾讯地图', '开发者key', [], ' maxlength=64');
                break;
            case 'payment':
                $builder->addFormItem('wx', 'legend', '微信支付', '微信支付')
                    ->addFormItem('wx_appid', 'text', '支付AppId', 'AppId', [], 'required maxlength=150')
                    ->addFormItem('wx_secret', 'text', '支付Secret', 'Secret', [], 'required maxlength=150')
                    ->addFormItem('wx_merchant_id', 'text', '商户ID', '商户ID', [], 'required maxlength=100')
                    ->addFormItem('wx_key', 'text', '支付秘钥', '支付秘钥', [], 'required maxlength=32 minlength=32')
                    ->addFormItem('ali', 'legend', '支付宝', '支付宝')
                    ->addFormItem('ali_pid', 'text', '商户ID', '商户ID', [], 'required maxlength=100')
                    ->addFormItem('ali_appid', 'text', 'AppId', 'AppId', [], 'required maxlength=150')
                    ->addFormItem('ali_appkey', 'text', 'Appkey', 'AppKey', [], 'required maxlength=150')
                    ->addFormItem('ali_app_private_key', 'textarea', '应用私钥', 'privateKey', [], 'required')
                    ->addFormItem('ali_app_public_key', 'textarea', '应用公钥', 'appPublicKey', [], '')
                    ->addFormItem('ali_public_key', 'textarea', '支付宝公钥', '支付宝公钥', [], '');
                break;
            case 'site':
                empty($data) && $data['close'] = 0;
                $builder->addFormItem('company_title', 'text', '公司名称', '公司名称')
                    ->addFormItem('domain', 'text', '域名', 'http或https开头')
                    ->addFormItem('kefu_tel', 'text', '客服电话', '客服联系电话')
                    ->addFormItem('third_login', 'radio', '开启三方登录', '开启三方登录', [0 => '否', 1 => '是'])
                    ->addFormItem('privacy_policy', 'ueditor', '隐私政策', '隐私政策')
                    ->addFormItem('service_policy', 'ueditor', '服务协议', '服务协议');
                break;
            case 'upload':
                empty($data) && $data = [
                    'driver' => 'qiniu',
                    'file_size' => 53000000,
                    'image_size' => 5000000,
                    'image_ext' => 'jpg,gif,png,jpeg',
                    'file_ext' => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,mp3,mp4,xls,xlsx,pdf',
                ];
                $data['driver'] = 'qiniu';
                $builder->addFormItem('driver_title', 'legend', '上传驱动', '上传驱动')
                    ->addFormItem('driver', 'hidden', '上传驱动', '上传驱动')
                    ->addFormItem('qiniu_ak', 'text', '七牛accessKey', '七牛accessKey')
                    ->addFormItem('qiniu_sk', 'text', '七牛secretKey', '七牛secretKey')
                    ->addFormItem('qiniu_bucket', 'text', '七牛bucket', '七牛bucket')
                    ->addFormItem('qiniu_domain', 'url', '七牛domain', '七牛domain')
                    ->addFormItem('image_title', 'legend', '图片设置', '图片设置')
                    ->addFormItem('image_size', 'number', '图片大小限制', '单位B', [], 'required min=1 max=1000000000')
                    ->addFormItem('image_ext', 'text', '图片格式支持', '多个用逗号隔开', [], 'required')
                    ->addFormItem('file_title', 'legend', '文件设置', '文件设置')
                    ->addFormItem('file_size', 'number', '文件大小限制', '单位B', [], 'required min=1 max=1000000000')
                    ->addFormItem('file_ext', 'text', '文件格式支持', '多个用逗号隔开', [], 'required')
                    ->addFormItem('voice_title', 'legend', '音频设置', '音频设置')
                    ->addFormItem('voice_size', 'number', '音频大小限制', '单位B', [], 'required min=1 max=1000000000')
                    ->addFormItem('voice_ext', 'text', '音频格式支持', '多个用逗号隔开', [], 'required')
                    ->addFormItem('video_title', 'legend', '视频设置', '视频设置')
                    ->addFormItem('video_size', 'number', '视频大小限制', '单位B', [], 'required min=1 max=1000000000')
                    ->addFormItem('video_ext', 'text', '视频格式支持', '多个用逗号隔开', [], 'required')
                ;
                break;
            case 'sms':
                $builder
                    ->addFormItem('sms_account', 'text', 'sms账号', 'sms账号', [], 'required maxlength=150')
                    ->addFormItem('sms_pwd', 'text', 'sms密码', 'sms密码', [], 'required maxlength=150')
                    ->addFormItem('sms_type', 'text', 'sms类型', 'sms类型', [], 'required maxlength=150');
                break;
        }
        $builder->setFormData($data);
        return $builder->show(['tab_nav' => ['tab_list' => $this->dao->tabList(), 'current_tab' => $current_name]]);
    }
}