<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Addons
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Addons newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addons newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addons query()
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addons whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Addons extends BaseModel
{
    use HasFactory ,HasJsonResourcefulData;

    /**
     * @var string
     */
    protected $table = 'addons';


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
            'self' => route('addons.show', $this->id),
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
            'addon_items' => $this->addonsItems,
        ];

        return $fields;
    }

    /**
     * @return BelongsToMany
     */
    public function addonsItems(): BelongsToMany
    {
        return $this->belongsToMany(AddonItem::class,'addons_and_addon_items' ,'addons_id', 'addon_items_id');
    }

}
