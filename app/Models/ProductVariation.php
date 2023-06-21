<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 */
class ProductVariation extends BaseModel
{
    use HasFactory,HasJsonResourcefulData,SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'product_variations';


    /**
     * @var array
     */
    protected $fillable = [
        'product_id',
        'addons_id',
        'addon_items_id',
        'price',
    ];

    /**
     * @var array
     */
    public static $rules = [
        'product_id' => 'required',
        'addons_id' => 'required',
        'addon_items_id' => 'required',
        'price' => 'required',
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
            'product_id' => $this->product_id,
            'product_name' => $this->getProductName(),
            'addons_id' => $this->addons_id,
            'addon_name' => $this->getAddonName(),
            'addon_items_id' => $this->addon_items_id,
            'addon_items_name' => $this->getAddonItemName(),
            'price' => $this->price,
        ];

        return $fields;
    }

    /**
     * @return array|string
     */
    public function getProductName()
    {
        $item = Product::select('name')->whereId($this->product_id)->first();
        if ($item) {
            return $item->toArray();
        }
        return '';
    }
    public function getAddonName()
    {
        $item = Addons::select('name')->whereId($this->addons_id)->first();
        if ($item) {
            return $item->toArray();
        }
        return '';
    }
    public function getAddonItemName()
    {
        $item = AddonItem::select('name')->whereId($this->addon_items_id)->first();
        if ($item) {
            return $item->toArray();
        }
        return '';
    }

    public function addons(): BelongsTo
    {
        return $this->belongsTo(Addons::class, 'addons_id', 'id');
    }
    public function addonItems(): BelongsTo
    {
        return $this->belongsTo(AddonItem::class, 'addon_items_id', 'id');
    }

}
