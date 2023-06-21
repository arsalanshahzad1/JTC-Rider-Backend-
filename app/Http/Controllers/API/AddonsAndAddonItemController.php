<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\AddonsAddonItems\CreateAddonsAddonItemRequest;
use App\Http\Requests\AddonsAddonItems\UpdateAddonsAddonItemRequest;
use App\Http\Resources\AddonsAddonItemResource;
use App\Repositories\AddonsAddonItemRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddonsAndAddonItemController extends AppBaseController
{

    private $addonsAddonItemRepository;

    /**
     * @param AddonsAddonItemRepository $addonsAddonItemRepository
     */
    public function __construct(AddonsAddonItemRepository $addonsAddonItemRepository)
    {
        $this->addonsAddonItemRepository = $addonsAddonItemRepository;
    }

    /**
     * @param CreateAddonsAddonItemRequest $request
     * @return AddonsAddonItemResource
     */
    public function store(CreateAddonsAddonItemRequest $request)
    {
        $input = $request->all();

        $item = $this->addonsAddonItemRepository->storeAddonsAddonItem($input);

        return new AddonsAddonItemResource($item);

    }

    public function update(UpdateAddonsAddonItemRequest $request)
    {
        $input = $request->all();

        $item = $this->addonsAddonItemRepository->updateAddonsAddonItem($input);

        return new AddonsAddonItemResource($item);

    }
}
