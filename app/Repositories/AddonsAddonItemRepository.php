<?php

namespace App\Repositories;

use App\Models\AddonsAndAddonItem;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CurrencyRepository
 */
class AddonsAddonItemRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'addons_id',
        'addon_items_id'
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
        return AddonsAndAddonItem::class;
    }

    public function storeAddonsAddonItem($input): AddonsAndAddonItem
    {
        try {
            DB::beginTransaction();

            $addonItemIds = json_decode($input['addon_items_id'], true);
            $allAddonItems = [];
            foreach ($addonItemIds as $addonItem) {
                $checkAddonItemAlready = AddonsAndAddonItem::whereAddonsId($input['addons_id'])->whereAddonItemsId($addonItem)->first();
                if (isset($checkAddonItemAlready)){
                    continue;
                }
                $allAddonItems = AddonsAndAddonItem::create([
                    'addons_id' => $input['addons_id'],
                    'addon_items_id' => $addonItem,
                ]);
            }

            DB::commit();
            return $allAddonItems;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }


    public function updateAddonsAddonItem($input): AddonsAndAddonItem
    {
        try {
            DB::beginTransaction();

            $checkAddonItemAlready = AddonsAndAddonItem::whereAddonsId($input['addons_id'])->delete();

            $addonItemIds = json_decode($input['addon_items_id'], true);
            $allAddonItems = [];
            foreach ($addonItemIds as $addonItem) {
                $allAddonItems = AddonsAndAddonItem::create([
                    'addons_id' => $input['addons_id'],
                    'addon_items_id' => $addonItem,
                ]);
            }

            DB::commit();
            return $allAddonItems;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

}
