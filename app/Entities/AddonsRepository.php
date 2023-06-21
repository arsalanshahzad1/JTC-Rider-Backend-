<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class AddonsRepository.
 *
 * @package namespace App\Entities;
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsRepository newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsRepository newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsRepository query()
 * @mixin \Eloquent
 */
class AddonsRepository extends Model implements Transformable
{
    use TransformableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

}
