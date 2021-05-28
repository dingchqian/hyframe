<?php

namespace App\Service\Dao;


class DistrictDao extends BaseDao
{
    protected $isCache = true;

    /**
     * 根据ID返回地区名称
     * @param int $id
     * @return string
     * Author: Doogie<461960962@qq.com>
     */
    public function getTitle($id=0){
        $data = $this->getOne($id);
        if($data){
            return $data['title'];
        }
        return '';
    }
}