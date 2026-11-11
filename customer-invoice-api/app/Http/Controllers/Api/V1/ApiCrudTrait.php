<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\ApiFilter;
use App\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

trait ApiCrudTrait
{
    abstract protected function getModelClass(): string;
    abstract protected function getFilterClass(): string;
    abstract protected function getResourceClass(): string;
    abstract protected function getCollectionClass(): string;
    abstract protected function getStoreRequestClass(): string;
    abstract protected function getUpdateRequestClass(): string;

    public function index(Request $request)
    {
        /** @var ApiFilter $filter */
        $filter = new $this->getFilterClass();
        $queryItems = $filter->transform($request);

        $modelClass = $this->getModelClass();
        $query = $modelClass::query()->where($queryItems);

        $query = $this->applyIncludes($request, $query);

        $paginator = $query->paginate()->appends($request->query());

        $collectionClass = $this->getCollectionClass();
        return new $collectionClass($paginator);
    }

    public function store(Request $request)
    {
        $requestClass = $this->getStoreRequestClass();
        $validated = $request->validate((new $requestClass())->rules());

        $modelClass = $this->getModelClass();
        $model = $modelClass::create($validated);

        $resourceClass = $this->getResourceClass();
        return new $resourceClass($model);
    }

    public function show(Request $request, $model)
    {
        $modelClass = $this->getModelClass();
        $query = $modelClass::query()->whereKey($model->getKey());

        $query = $this->applyIncludes($request, $query);

        $model = $query->firstOrFail();

        $resourceClass = $this->getResourceClass();
        return new $resourceClass($model);
    }

    public function update(Request $request, $model)
    {
        $requestClass = $this->getUpdateRequestClass();
        $validated = $request->validate((new $requestClass())->rules());

        $model->update($validated);

        $resourceClass = $this->getResourceClass();
        return new $resourceClass($model->fresh());
    }

    public function destroy($model)
    {
        $model->delete();

        return response()->noContent();
    }

    protected function applyIncludes(Request $request, $query)
    {
        return $query;
    }
}