<div x-data="alienDropdown">
    <div class="relative">
        <input type="text" @focus="showOptions=true" @click.outside="showOptions=false"
            @keydown.down.prevent="selectNext" wire:model.live.debounce.300ms="searchTerm"
            class="block w-full min-w-0 flex-grow rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            placeholder="Search {{ $relation }}...">

        <div class="absolute z-50 w-full p-2 mt-2 overflow-auto text-pink-200 bg-gray-800 border rounded-md shadow-sm max-h-40 group"
            x-cloak x-show="showOptions">
            @if (!empty($options))
                <ul class="flex flex-col" @keydown.down.prevent="selectNext" @keydown.up.prevent="selectPrevious">
                    @foreach ($options as $option)
                        <li>
                            <button type="button" x-ref="option{{ $loop->index }}"
                                class="w-full p-2 text-sm font-semibold text-left transition rounded-md cursor-pointer focus-within:outline-pink-300 focus:bg-pink-50/90 hover:bg-pink-50/90 hover:text-pink-900 focus:text-pink-900"
                                wire:key="option-{{ $option->id }}"
                                wire:click="selectOption({{ $option->id }}, '{{ $option->{$attribute} }}')">
                                {{ $option->{$attribute} }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-2 selected-options">
        @foreach ($selectedOptions as $id => $value)
            <span
                class="inline-flex items-center bg-gray-800 text-pink-200 text-sm font-semibold mr-2 px-2.5 py-0.5 rounded">
                {{ $value }}
                <button type="button" wire:click="deselectOption({{ $id }}, '{{ $value }}')"
                    class="ml-2 text-pink-300">×</button>
            </span>
        @endforeach
    </div>

    <script type="text/javascript">
        document.addEventListener('livewire:load', () => {
            Alpine.data('alienDropdown', () => ({
                showOptions: false,
                optionCount: 5,
                focusIndex: -1,

                init() {
                    Livewire.on('optionsUpdated', (optionsCount) => {
                        this.optionCount = optionsCount;
                        this.resetFocus();
                    })
                },

                resetFocus() {
                    this.focusIndex = -1;
                },

                selectNext() {
                    this.focusIndex = this.focusIndex < this.optionCount - 1 ?
                        this.focusIndex + 1 :
                        this.focusIndex - (this.optionCount - 1)
                    this.$nextTick(() => {
                        this.$refs[`option${this.focusIndex}`].focus()
                    })
                },

                selectPrevious() {
                    this.focusIndex = this.focusIndex > 0 ?
                        this.focusIndex - 1 :
                        this.focusIndex + (this.optionCount - 1)
                    this.$nextTick(() => {
                        this.$refs[`option${this.focusIndex}`].focus()
                    })
                },
            }))
        })
    </script>
</div>
