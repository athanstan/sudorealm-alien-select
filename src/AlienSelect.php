<?php

namespace Sudorealm\AlienSelect;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Str;

class AlienSelect extends Component
{
    const OPTION_LIMIT = 5;

    public string $modelClass;
    public string $relation;
    public string $parentModel;
    public ?int $parentModelId;
    public string $searchTerm = '';
    public array $selectedOptions = [];
    public array $selectedOptionIds = [];
    public string $attribute;
    public bool $shouldCache;
    public bool $byUser;
    public ?string $selectedOptionCacheKey;

    public function mount(
        string $modelClass,
        string $parentModel,
        ?int $parentModelId,
        string $attribute = 'name',
        bool $shouldCache = false,
        bool $byUser = false,
        ?string $relation = null,
    ) {
        $this->modelClass = $this->getModelWithNamespace($modelClass);
        $this->parentModel = $this->getModelWithNamespace($parentModel);
        $this->relation = $this->getRelationshipName($relation, $modelClass);
        $this->attribute = $attribute;
        $this->parentModelId = $parentModelId;
        $this->shouldCache = $shouldCache;
        $this->byUser = $byUser;

        $this->selectedOptionCacheKey = ($shouldCache && $parentModelId) ?
            "alien-" . $this->parentModel
            . "-" . $this->parentModelId . $this->relation
            . "-selected-options"
            : null;

        if (
            class_exists($this->parentModel)
            && method_exists($this->parentModel, $this->relation)
            && $this->parentModelId
        ) {
            $parentModelInstance = $this->fetchParentModel($shouldCache);

            if ($parentModelInstance) {
                $this->selectedOptions = $this->fetchSelectedOptions($shouldCache);
            }
        }
    }

    public function updatedSearchTerm($value): void
    {
        $this->emit('optionsUpdated', $this->options->count());
    }

    public function selectOption($id, $value)
    {
        if (!array_key_exists($id, $this->selectedOptions)) {
            $this->selectedOptions[$id] = $value;
            $this->updateSelectedOptions();
        }
    }

    public function deselectOption($id)
    {
        unset($this->selectedOptions[$id]);
        $this->updateSelectedOptions();
    }

    public function getOptionsProperty()
    {
        if (class_exists($this->modelClass)) {
            $modelInstance = new $this->modelClass;
            return $modelInstance::query()
                ->select('id', $this->attribute)
                ->when(
                    strlen($this->searchTerm) > 2,
                    fn ($q) => $q->where($this->attribute, 'like', '%' . $this->searchTerm . '%')
                )
                ->when(
                    $this->byUser,
                    fn ($q) => $q->where('user_id', auth()->id())
                )
                ->orderBy('id', 'desc')
                ->limit(self::OPTION_LIMIT)
                ->get();
        }
    }

    public function render()
    {
        return view('alien-select::components.alien-select', [
            'options' => $this->options,
        ]);
    }

    private function getModelWithNamespace($modelClass): string
    {
        return 'App\\Models\\' . ucfirst($modelClass);
    }

    private function getRelationshipName(?string $relation, string $modelClass): string
    {
        return $relation ?? Str::plural($modelClass);
    }

    private function fetchParentModel(bool $shouldCache)
    {
        if ($shouldCache) {
            $cacheKey = "alien-" . $this->parentModel . "-" . $this->parentModelId . "-model";

            return Cache::remember($cacheKey, 60 * 60 * 24, function () {
                return $this->parentModel::find($this->parentModelId);
            });
        }

        return $this->parentModel::find($this->parentModelId);
    }

    private function fetchSelectedOptions($shouldCache): array
    {
        if ($shouldCache) {
            $cacheKey = $this->selectedOptionCacheKey;
            return Cache::remember($cacheKey, 60 * 60 * 24, function () {
                return $this->parentModel::find($this->parentModelId)
                    ->{$this->relation}()
                    ->pluck($this->attribute, $this->relation . '.id')
                    ->toArray();
            });
        }

        return $this->parentModel::find($this->parentModelId)
            ->{$this->relation}()
            ->pluck($this->attribute, $this->relation . '.id')
            ->toArray();
    }

    private function updateSelectedOptions(): void
    {
        $this->selectedOptionIds = array_keys($this->selectedOptions);
        $this->emitUp(
            'selectionUpdated',
            $this->selectedOptionIds,
            $this->parentModel,
            $this->parentModelId ?? null,
            $this->relation,
            $this->selectedOptionCacheKey ?? null
        );
    }
}
