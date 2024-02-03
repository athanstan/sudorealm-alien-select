<?php

namespace Sudorealm\AlienSelect\Helpers;

use Illuminate\Support\Facades\Cache;

trait AlienSelect
{
    public $syncData = [];

    public function getListeners(): array
    {
        return array_merge($this->listeners, [
            'selectionUpdated' => 'addToSyncData',
        ]);
    }

    public function addToSyncData(
        array $selectedIds,
        string $parentModel,
        ?int $parentModelId,
        string $relation,
        ?string $cacheKey
    ): void {
        $this->syncData[$relation] = [
            'parentModel' => $parentModel,
            'parentModelId' => $parentModelId,
            'ids' => $selectedIds,
            'cacheKey' => $cacheKey,
        ];
    }

    public function alienSelectSync(int $parentModelId): void
    {
        foreach ($this->syncData as $relation => $data) {
            $parentModel = $data['parentModel'];
            $ids = $data['ids'];

            if (class_exists($parentModel) && method_exists($parentModel, $relation)) {
                $modelInstance = new $parentModel;
                $modelInstance->query()
                    ->select('id')
                    ->find($parentModelId)
                    ->$relation()
                    ->sync($ids);
            }

            if ($data['cacheKey']) {
                Cache::forget($data['cacheKey']);
            }
        }
    }
}
