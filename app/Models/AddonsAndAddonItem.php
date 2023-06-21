<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AddonsAndAddonItem
 *
 * @property int $id
 * @property int|null $addons_id
 * @property int|null $addon_items_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem whereAddonItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem whereAddonsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AddonsAndAddonItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AddonsAndAddonItem extends BaseModel
{
    use HasFactory ,HasJsonResourcefulData;

    protected $table = 'addons_and_addon_items';

    public const JSON_API_TYPE = 'addons_and_addon_items';

    protected $fillable = [
        'addons_id',
        'addon_items_id'
    ];

    public static $rules = [
        'addons_id' => 'required',
        'addon_items_id' => 'required'
    ];


    /**
     * @return array
     */
    public function prepareLinks(): array
    {
        return [

        ];
    }

    /**
     * @return array
     */
    public function prepareAttributes(): array
    {
        $fields = [
            'addons_id' => $this->addons_id,
            'addon_items_id' => $this->addon_items_id,
        ];

        return $fields;
    }

//    /**
//     * @return BelongsTo
//     */
//    public function addons(): BelongsTo
//    {
//        return $this->belongsTo(Addons::class, 'addons_id', 'id');
//    }

//    /**
//     * @return BelongsTo
//     */
//    public function addonItems(): BelongsTo
//    {
//        return $this->belongsTo(AddonItem::class, 'addon_items_id', 'id');
//    }

}
