<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AddonItem
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AddonItem extends BaseModel
{
    use HasFactory ,HasJsonResourcefulData;

    /**
     * @var string
     */
    protected $table = 'addon_items';


    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * @var array
     */
    public static $rules = [
        'name' => 'required',
        'slug' => 'required',
        'description' => 'required',
    ];

    /**
     * @return array
     */
    public function prepareLinks(): array
    {
        return [
            'self' => route('addon-items.show', $this->id),
        ];
    }

    /**
     * @return array
     */
    public function prepareAttributes(): array
    {
        $fields = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];

        return $fields;
    }
}
