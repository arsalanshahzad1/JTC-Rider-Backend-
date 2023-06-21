<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\AddonItems\CreateAddonItemRequest;
use App\Http\Requests\AddonItems\UpdateAddonItemRequest;
use App\Http\Resources\AddonItemCollection;
use App\Http\Resources\AddonItemResource;
use App\Repositories\AddonItemRepository;
use Illuminate\Http\Request;

class AddonItemAPIController extends AppBaseController
{
    /**
     * @var AddonItemRepository
     */
    private $addonItemRepository;

    /**
     * @param AddonItemRepository $addonItemRepository
     */
    public function __construct(AddonItemRepository $addonItemRepository)
    {
        $this->addonItemRepository = $addonItemRepository;
    }

    /**
     * @param Request $request
     * @return AddonItemCollection
     */
    public function index(Request $request)
    {
        $perPage = getPageSize($request);
        $item = $this->addonItemRepository->paginate($perPage);
        AddonItemResource::usingWithCollection();

        return new AddonItemCollection($item);
    }

    /**
     * @param CreateAddonItemRequest $request
     * @return AddonItemResource
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreateAddonItemRequest $request)
    {
        $input = $request->all();
        $item = $this->addonItemRepository->create($input);

        return new AddonItemResource($item);
    }

    /**
     * @param $id
     * @return AddonItemResource
     */
    public function show($id)
    {
        $item = $this->addonItemRepository->find($id);

        return new AddonItemResource($item);
    }

    /**
     * @param UpdateAddonItemRequest $request
     * @param $id
     * @return AddonItemResource
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(UpdateAddonItemRequest $request, $id)
    {
        $input = $request->all();
        $item = $this->addonItemRepository->update($input, $id);

        return new AddonItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
