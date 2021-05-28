<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $group_id 
 * @property int $shop_id 
 * @property string $username 
 * @property string $password 
 * @property string $email 
 * @property string $mobile 
 * @property string $realname 
 * @property string $ip 
 * @property int $status 
 * @property int $last_time 
 * @property int $create_time 
 * @property int $update_time 
 */
class Admin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin';
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
    protected $casts = ['id' => 'integer', 'group_id' => 'integer', 'shop_id' => 'integer', 'status' => 'integer', 'last_time' => 'integer', 'create_time' => 'integer', 'update_time' => 'integer'];
}