<?php
/**
 * Created by PhpStorm.
 * Script Name: ExcelService.php
 * Create: 下午10:52
 * Description:
 * Author: Jason<dcq@kuryun.cn>
 */

namespace App\Service;

use HyperfX\Utils\Service;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService extends Service
{
    /**
     * 读取excel中的数据
     * @param string $filename
     * @throws \Exception
     * @return array
     * Author: Jason<dcq@kuryun.cn>
     */
    public function readExcel($filename) {
        $inputFileType = IOFactory::identify($filename);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $data = $spreadsheet->getActiveSheet()->ToArray();
        unset($spreadsheet);
        return $data;
    }
}