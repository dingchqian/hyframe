<?php
/**
 * Created by PhpStorm.
 * Script Name: Uploader.php
 * Create: 2021/1/19 10:15
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Controller\Admin;
use App\Service\UploadService;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\AdminMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;

/**
 * @AutoController()
 * @Middleware(AdminMiddleware::class)
 */
class UploaderController extends BaseController
{
    /**
     * @Inject()
     * @var UploadService
     */
    protected $uploadService;


    public function index(){
        $data = $this->uploadService->config();
        var_dump(config('system.upload.driver'));
    }

    /**
     * 图片上传
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function picturePost()
    {
        return self::upload($this->uploadService->config());
    }

    /**
     * 文件上传
     * @return mixed
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function filePost()
    {
        return self::upload($this->uploadService->config('file'));
    }

    /**
     * 上传
     * @param array $config
     * @return \Psr\Http\Message\ResponseInterface
     * Author: fudaoji<fdj@kuryun.cn>
     */
    private function upload($config = [])
    {
        /**
         * @var \Hyperf\HttpMessage\Upload\UploadedFile
         */
        $file = $this->request->file('file');
        $return = $this->uploadService->upload($file, $config, [
            'dir' => $this->getFileDir() .date('Ymd').'/'
        ]);
        return $this->response->success($return);
    }

    private function getFileDir(){
        return config('app_name').'/'.Context::get('adminInfo')->id . '/' ;
    }

    /**
     * ueditor的服务端接口
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function editorPost(){
        $action = $this->request->input('action');
        $ue_config = $this->uploadService->ueConfig();
        switch ($action) {
            case 'config':
                $return = $ue_config;
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                /**
                 * @var \Hyperf\HttpMessage\Upload\UploadedFile
                 */
                $file = $this->request->file('upfile');
                $return = $this->uploadService->ueUpload($file, $action, ['dir' => $this->getFileDir().date('Ymd').'/']);
                break;

            /* 列出图片 */
            case 'listimage':
                /* 列出文件 */
            case 'listfile':
                $return = $this->uploadService->ueList($action, [
                    'dir' => $this->getFileDir(),
                    'start' => intval($this->request->input('start')),
                    'size' => intval($this->request->input('size')),
                ]);
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $return['state'] = '请求地址出错';
                break;

            default:
                $return['state'] = '请求地址出错';
                break;
        }
        return $this->response->json($return);
    }
}