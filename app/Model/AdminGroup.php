<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $name 
 * @property string $title 
 * @property int $status 
 * @property string $rules 
 * @property int $sort 
 * @property int $pid 
 * @property int $create_time 
 * @property string $remark 
 * @property int $update_time 
 */
class AdminGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_group';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'pid' => 'integer', 'create_time' => 'integer', 'update_time' => 'integer'];
}