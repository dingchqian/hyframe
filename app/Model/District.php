<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property string $title 
 * @property int $pid 
 */
class District extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'district';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer'];
}