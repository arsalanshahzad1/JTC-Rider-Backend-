<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Addons\CreateAddonRequest;
use App\Http\Requests\Addons\UpdateAddonRequest;
use App\Http\Resources\AddonsCollection;
use App\Http\Resources\AddonsResource;
use App\Repositories\AddonsRepository;
use Illuminate\Http\Request;

class AddonsAPIController extends AppBaseController
{

    /**
     * @var AddonsRepository
     */
    private $addonsRepository;

    /**
     * @param AddonsRepository $addonsRepository
     */
    public function __construct(AddonsRepository $addonsRepository)
    {
        $this->addonsRepository = $addonsRepository;
    }

    /**
     * @param Request $request
     * @return AddonsCollection
     */
    public function index(Request $request)
    {
        $perPage = getPageSize($request);
        $item = $this->addonsRepository->paginate($perPage);
        AddonsResource::usingWithCollection();

        return new AddonsCollection($item);
    }

    public function addonRelatedItems($id)
    {
        $item = $this->addonsRepository->find($id);

        return new AddonsResource($item);
    }

    /**
     * @param CreateAddonRequest $request
     * @return AddonsResource
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreateAddonRequest $request)
    {
        $input = $request->all();
        $item = $this->addonsRepository->create($input);

        return new AddonsResource($item);
    }

    /**
     * @param $id
     * @return AddonsResource
     */
    public function show($id)
    {
        $item = $this->addonsRepository->find($id);

        return new AddonsResource($item);
    }

    /**
     * @param UpdateAddonRequest $request
     * @param $id
     * @return AddonsResource
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(UpdateAddonRequest $request, $id)
    {
        $input = $request->all();
        $item = $this->addonsRepository->update($input, $id);

        return new AddonsResource($item);
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
