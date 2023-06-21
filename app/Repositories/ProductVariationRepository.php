<?php

namespace App\Repositories;

use App\Http\Resources\ProductVariationCollection;
use App\Http\Resources\ProductVariationResource;
use App\Models\ProductVariation;

/**
 * Class CurrencyRepository
 */
class ProductVariationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'product_id',
        'addons_id',
        'addon_items_id',
        'price',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ProductVariation::class;
    }

    /**
     *
     * @param int $productId
     * @return ProductVariationCollection
     */
    public function findProductVariation($productId)
    {
        return ProductVariation::where('product_id',$productId)->get();
    }
}
