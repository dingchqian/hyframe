<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id 
 * @property string $mobile 
 * @property string $username 
 * @property string $nickname 
 * @property string $headimgurl 
 * @property int $birthday 
 * @property string $country 
 * @property string $province 
 * @property string $city 
 * @property int $sex 
 * @property string $remark 
 * @property int $create_time 
 * @property int $update_time 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
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
    protected $casts = ['id' => 'integer', 'birthday' => 'integer', 'sex' => 'integer', 'create_time' => 'integer', 'update_time' => 'integer'];
}