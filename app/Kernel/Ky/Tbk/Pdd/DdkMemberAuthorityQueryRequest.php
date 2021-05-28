<?php
/**
 * Created by PhpStorm.
 * Script Name: DdkMemberAuthorityQueryRequest.php
 * Create: 2021/4/27 12:08
 * Description:
 * Author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Kernel\Ky\Tbk\Pdd;


class DdkMemberAuthorityQueryRequest
{
    public $type = 'pdd.ddk.member.authority.query';

    protected $pid;

    protected $custom_parameters;

    /**
     * @param mixed $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }


    /**
     * @param mixed $custom_parameters
     */
    public function setCustomParameters($custom_parameters)
    {
        $this->custom_parameters = is_array($custom_parameters) ? json_encode($custom_parameters) : $custom_parameters;
    }

    public function getParams()
    {
        $params = [
            'type' => $this->type,
            'pid' => $this->pid,
            'custom_parameters' => $this->custom_parameters,
        ];
        return array_filter($params);
    }
}