<?php
/**
 * Created by PhpStorm.
 * Script Name: UploadService.php
 * Create: 2021/1/19 10:52
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Service;


use App\Service\Dao\SettingDao;
use HyperfX\Utils\Service;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

class UploadService extends Service
{
    private $error;
    const CUSTOM_ERROR_SIZE = 9;
    const CUSTOM_ERROR_EXT = 10;
    const CUSTOM_ERROR_MINE = 11;

    /**
     * @Inject()
     * @var SettingDao
     */
    protected $settingDao;

    private $setting = [
        'driver' => 'local'
    ];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $upload_setting = $this->settingDao->getOneByMap(['name' => 'upload']);
        $this->setting = json_decode($upload_setting->value, true);
    }

    public function getSetting(){
        return $this->setting;
    }

    /**
     *
     * @param string $media_type
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function config($media_type = 'image')
    {
        $media_type = strtolower($media_type);
        $config = [
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => max(0, intval($this->setting['image_size'])), //上传的文件大小限制 (0-不做限制)
            'exts'     => $this->setting['image_ext'] ?: 'jpg,gif,png,jpeg,bmp', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => ['date', 'Y-m-d'], //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './public/uploads', //保存根路径
            'savePath' => '/image/', //保存路径
            'saveName' => ['uniqid', ''], //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        ];
        switch ($media_type){
            case 'file':
                $config['savePath'] = '/file/';
                $config['maxSize'] = $this->setting['file_size'];
                $config['exts'] = $this->setting['file_ext'] ?:'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,mp3,mp4,xls,xlsx,pdf';
                break;
            case 'voice':
                $config['savePath'] = '/voice/';
                $config['maxSize'] = $this->setting['voice_size'];
                $config['exts'] = $this->setting['voice_ext'] ?:'mp3,wma,wav,amr';
                break;
            case 'video':
                $config['savePath'] = '/video/';
                $config['maxSize'] = $this->setting['video_size'];
                $config['exts'] = $this->setting['video_ext'] ?:'mp4';
                break;
        }

        return $config;
    }

    /**
     * ueditor的配置
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function ueConfig(){
        $path_pre = '';
        return [
            /* 上传图片配置项 */
            "imageActionName" => "uploadimage", /* 执行上传图片的action名称 */
            "imageFieldName"=> "upfile", /* 提交的图片表单名称 */
            "imageMaxSize"=> $this->setting['image_size'], /* 上传大小限制，单位B */
            "imageAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
            "imageCompressEnable"=> true, /* 是否压缩图片,默认是true */
            "imageCompressBorder"=> 1600, /* 图片压缩最长边限制 */
            "imageInsertAlign"=> "none", /* 插入的图片浮动方式 */
            "imageUrlPrefix"=> "", /* 图片访问路径前缀 */
            "imagePathFormat"=> $path_pre."/uploads/image/{yyyy}-{mm}-{dd}/{time}{rand:6}",
            /*"/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}",*/
            /* 上传保存路径,可以自定义保存路径和文件名格式 */
            /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
            /* {rand:6} 会替换成随机数,后面的数字是随机数的位数 */
            /* {time} 会替换成时间戳 */
            /* {yyyy} 会替换成四位年份 */
            /* {yy} 会替换成两位年份 */
            /* {mm} 会替换成两位月份 */
            /* {dd} 会替换成两位日期 */
            /* {hh} 会替换成两位小时 */
            /* {ii} 会替换成两位分钟 */
            /* {ss} 会替换成两位秒 */
            /* 非法字符 \ : * ? " < > | */
            /* 具请体看线上文档: fex.baidu.com/ueditor/#use-format_upload_filename */

            /* 涂鸦图片上传配置项 */
            "scrawlActionName"=> "uploadscrawl", /* 执行上传涂鸦的action名称 */
            "scrawlFieldName"=> "upfile", /* 提交的图片表单名称 */
            "scrawlPathFormat"=>$path_pre."/uploads/image/{yyyy}-{mm}-{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "scrawlMaxSize"=> $this->setting['image_size'], /* 上传大小限制，单位B */
            "scrawlUrlPrefix"=> "", /* 图片访问路径前缀 */
            "scrawlInsertAlign"=> "none",

            /* 截图工具上传 */
            "snapscreenActionName"=>"uploadimage", /* 执行上传截图的action名称 */
            "snapscreenPathFormat"=> $path_pre."/uploads/image/{yyyy}-{mm}-{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "snapscreenUrlPrefix"=> "", /* 图片访问路径前缀 */
            "snapscreenInsertAlign"=> "none", /* 插入的图片浮动方式 */

            /* 抓取远程图片配置 */
            "catcherLocalDomain"=>["127.0.0.1", "localhost", "img.baidu.com"],
            "catcherActionName"=> "catchimage", /* 执行抓取远程图片的action名称 */
            "catcherFieldName"=> "source", /* 提交的图片列表表单名称 */
            "catcherPathFormat"=> $path_pre."/uploads/image/{yyyy}-{mm}-{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "catcherUrlPrefix"=> "", /* 图片访问路径前缀 */
            "catcherMaxSize"=> 2048000, /* 上传大小限制，单位B */
            "catcherAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

            /* 上传视频配置 */
            "videoActionName"=> "uploadvideo", /* 执行上传视频的action名称 */
            "videoFieldName"=> "upfile", /* 提交的视频表单名称 */
            "videoPathFormat"=>$path_pre."/uploads/video/{yyyy}-{mm}-{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "videoUrlPrefix"=> "", /* 视频访问路径前缀 */
            "videoMaxSize"=> 102400000, /* 上传大小限制，单位B，默认100MB */
            "videoAllowFiles"=> [".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */

            /* 上传文件配置 */
            "fileActionName"=> "uploadfile", /* controller里,执行上传视频的action名称 */
            "fileFieldName"=> "upfile", /* 提交的文件表单名称 */
            "filePathFormat"=> $path_pre."/uploads/file/{yyyy}-{mm}-{dd}/{time}{rand:6}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
            "fileUrlPrefix"=> "", /* 文件访问路径前缀 */
            "fileMaxSize"=> $this->setting['file_size'], /* 上传大小限制，单位B，默认50MB */
            "fileAllowFiles"=> [
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
            ], /* 上传文件格式显示 */

            /* 列出指定目录下的图片 */
            "imageManagerActionName"=> "listimage", /* 执行图片管理的action名称 */
            "imageManagerListPath"=> $path_pre."/uploads/image/", /* 指定要列出图片的目录 */
            "imageManagerListSize"=> 20, /* 每次列出文件数量 */
            "imageManagerUrlPrefix"=> "", /* 图片访问路径前缀 */
            "imageManagerInsertAlign"=> "none", /* 插入的图片浮动方式 */
            "imageManagerAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

            /* 列出指定目录下的文件 */
            "fileManagerActionName"=> "listfile", /* 执行文件管理的action名称 */
            "fileManagerListPath"=>$path_pre."/uploads/file/", /* 指定要列出文件的目录 */
            "fileManagerUrlPrefix"=> "", /* 文件访问路径前缀 */
            "fileManagerListSize"=>20, /* 每次列出文件数量 */
            "fileManagerAllowFiles"=> [
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
            ] /* 列出的文件类型 */

        ];
    }

    /**
     * ueeditor编辑器上传
     * @param string $action
     * @param array $extra
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function ueUpload(\Hyperf\HttpMessage\Upload\UploadedFile $file, $action = '', $extra = []){
        $config = $this->ueConfig();
        switch($action){
            case $config['imageActionName']:
                $upload_config = $this->config();
                break;
            case $config['uploadvideo']:
                $upload_config = $this->config('video');
                break;
            default:
                $upload_config = $this->config('file');
                break;
        }

        $res = $this->upload($file, $upload_config, $extra);
        if(is_array($res)){
            $file = $res[0];
            $return = [
                "state" => "SUCCESS",
                'url' => $file['url'],
                'title' => $file['name'],
                'original' => $file['original_name'],
                'type' => $file['ext'],
                'size' => $file['size']
            ];
        }else{
            $return['state'] = $res;
        }
        return $return;
    }

    /**
     * 编辑器列出图片和文件
     * @param string $action
     * @param array $extra
     * @return array
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function ueList($action='', $extra = []){
        $factory = $this->container->get(\Hyperf\Filesystem\FilesystemFactory::class);
        $driver = $this->setting['driver'];
        /**
         * @var \League\Flysystem\Filesystem $manager
         */
        $manager = $factory->get($driver);
        $config = self::ueConfig();
        /* 判断类型 */
        switch ($action) {
            /* 列出文件 */
            case 'listfile':
                $listSize = $config['fileManagerListSize'];
                $path = $config['fileManagerListPath'];
                break;
            default:/* 默认列出图片 */
                $listSize = $config['imageManagerListSize'];
                $path = $config['imageManagerListPath'];
                break;
        }
        /* 获取参数 */
        $size = isset($extra['size']) ? $extra['size'] : $listSize;
        $start = isset($extra['start']) ? $extra['start'] : 0;
        $page = intval(($extra['start'] / $size))+1;
        $total = 0;
        $files = [];
        switch($driver){
            //不同的上传驱动对应不同的列表
            case 'qiniu':
            default:
                if($action === 'listimage'){
                    $all = $manager->getAdapter()->listContents($extra['dir'], true);
                    $total = count($all);
                    $files = array_slice($all, $start, $size);
                }
                if($files){
                    foreach($files as &$v){
                        $v['mtime'] = $v['timestamp'];
                        $v['url'] = $manager->getAdapter()->getUrl($v['path']);
                    }
                }
                break;
        }
        unset($size, $page, $driver, $config, $listSize, $path, $extra);

        /* 返回数据 */
        return [
            "state" => "SUCCESS",
            "list" => $files,
            "start" => $start,
            "total" => $total
        ];
    }

    public function upload(\Hyperf\HttpMessage\Upload\UploadedFile $file, array $config, array $extra)
    {
        if(($err = $this->check($file, $config)) !== true){
            return $this->getError($err);
        }

        $factory = $this->container->get(\Hyperf\Filesystem\FilesystemFactory::class);
        $driver = $this->setting['driver'];
        /**
         * @var \League\Flysystem\Filesystem $manager
         */
        $manager = $factory->get($driver);
        $path = empty($extra['dir']) ? md5(uniqid() . $file->getClientFilename())
            : (date('His') .'-'.$extra['dir'] . $file->getClientFilename());
        $res = $manager->write($path, file_get_contents($file->getRealPath()));
        if($res){
            switch ($driver){
                case 'qiniu':
                    $url = $manager->getAdapter()->getUrl($path);
                    break;
                default:
                    $url = '/uploads/' . $path;
                    break;
            }
            $info[] = [
                'uid' => $extra['uid'],
                'path' => $path,
                'url' => $url,
                'size' => $file->getSize(),
                'ext' => $file->getExtension(),
                'location' => ucfirst(strtolower($driver)),
                'name' => $path,
                'original_name' => $file->getClientFilename()
            ];
            return $info;
        }
        return $this->getError();
    }

    /**
     * 除了hyperf的插件验证，自己的业务逻辑也有验证
     * @param \Hyperf\HttpMessage\Upload\UploadedFile $file
     * @param $config
     * @return bool
     * Author: fudaoji<fdj@kuryun.cn>
     */
    private function check($file, $config) {
        /* 文件上传失败，捕获错误代码 */
        if (! $file->isValid()) {
            return $file->getError();
        }

        /* 检查文件大小 */
        if (!$this->checkSize($file->getSize(), $config)) {
            return self::CUSTOM_ERROR_SIZE;
        }

        /* 检查文件Mime类型 */
        if (!$this->checkMime($file->getMimeType(), $config)) {
            return self::CUSTOM_ERROR_MINE;
        }

        /* 检查文件后缀 */
        if (!$this->checkExt($file->getExtension(), $config)) {
            return self::CUSTOM_ERROR_EXT;
        }

        return true;
    }

    /**
     * 检查文件大小是否合法
     * @param $size
     * @param $config
     * @return bool
     * Author  Doogie<461960962@qq.com>
     */
    private function checkSize($size, $config) {
        return (0 == $config['maxSize']) || !($size > $config['maxSize']);
    }

    /**
     * 检查上传的文件MIME类型是否合法
     * @param $mime
     * @param array $config
     * @return bool
     * Author  Doogie<461960962@qq.com>
     */
    private function checkMime($mime, array $config) {
        return empty($config['mimes']) ? true : in_array(strtolower($mime), explode(',', $config['mimes']));
    }

    /**
     * 检查上传的文件后缀是否合法
     * @param $ext
     * @param array $config
     * @return bool
     * Author  Doogie<461960962@qq.com>
     */
    private function checkExt($ext, array $config) {
        return empty($config['exts']) ? true : in_array(strtolower($ext), explode(',', $config['exts']));
    }

    /**
     * 获取错误代码信息
     * @param $error_no
     * @return string
     * Author: fudaoji<fdj@kuryun.cn>
     */
    public function getError($error_no): string
    {
        switch ($error_no) {
            case UPLOAD_ERR_INI_SIZE:
                $this->error = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值！';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值！';
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->error = '文件只有部分被上传！';
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error = '没有文件被上传！';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error = '找不到临时文件夹！';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->error = '文件写入失败！';
                break;
            case UPLOAD_ERR_EXTENSION:
                $this->error = '上传的文件被PHP扩展程序中断！';
                break;
            case self::CUSTOM_ERROR_SIZE:
                $this->error = '上传的文件大小不符！';
                break;
            case self::CUSTOM_ERROR_MINE:
                $this->error = '上传文件MIME类型不允许!';
                break;
            case self::CUSTOM_ERROR_EXT:
                $this->error = '上传文件类型不允许!';
                break;
            default:
                $this->error = '未知上传错误！';
        }
        return  $this->error;
    }
}