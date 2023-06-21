<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\ProductVariationCollection;
use App\Http\Resources\ProductVariationResource;
use App\Repositories\ProductVariationRepository;
use Illuminate\Http\Request;

class ProductVariationController extends AppBaseController
{
    private $productVariationRepository;

    /**
     * @param ProductVariationRepository $productVariationRepository
     */
    public function __construct(ProductVariationRepository $productVariationRepository)
    {
        $this->productVariationRepository = $productVariationRepository;
    }

    public function productVariationByProduct($productId)
    {
        $item = $this->productVariationRepository->findProductVariation($productId);

        ProductVariationResource::usingWithCollection();

        return new ProductVariationCollection($item);
    }


}
