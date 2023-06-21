<?php

namespace App\Repositories;

use App\Models\AddonItem;

/**
 * Class CurrencyRepository
 */
class AddonItemRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'slug',
        'description',
        'created_at',
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
        return AddonItem::class;
    }
}
