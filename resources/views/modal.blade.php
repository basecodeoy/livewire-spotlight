<div>
    <div
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        @close.stop="closeModal()"
        @keydown.escape.window="closeModal()"
        @keydown.window.prevent.cmd.k="toggleOpen()"
        @keydown.window.prevent.cmd.slash="toggleOpen()"
        @keydown.window.prevent.ctrl.k="toggleOpen()"
        @keydown.window.prevent.ctrl.slash="toggleOpen()"
        @toggle-spotlight.window="toggleOpen()"
        x-cloak
        x-data="() => {
            return {
                commands: @js($commands),
                selectedCommandIndex: -1,
                isOpen: false,
                init() {
                    this.$watch('isOpen', value => {
                        if (value === false) {
                            setTimeout(() => {
                                this.selectedCommandIndex = -1;
                            }, 300);
                        }
                    });
                },
                closeModal() {
                    this.$wire.set('searchQuery', '');

                    if (this.$wire.get('commandId')) {
                        this.$wire.set('commandId', '');

                        return;
                    }

                    this.isOpen = false;
                },
                forceCloseModal() {
                    this.isOpen = false;

                    setTimeout(() => {
                        this.$wire.set('searchQuery', '');
                        this.$wire.set('commandId', '');
                    }, 300);
                },
                toggleOpen() {
                    this.isOpen = !this.isOpen;

                    if (!this.isOpen) {
                        return;
                    }

                    setTimeout(() => {
                        this.$refs.input.focus();
                    }, 100);
                },
                goToPrevious() {
                    this.selectedCommandIndex = Math.max(0, this.selectedCommandIndex - 1);

                    this.$nextTick(() => {
                        this.toggleStateClasses();
                    })
                },
                goToNext() {
                    if (this.selectedCommandIndex + 1 > this.commands.length) {
                        this.selectedCommandIndex = 0;
                    } else {
                        this.selectedCommandIndex = Math.min(this.commands.length - 1, this.selectedCommandIndex + 1);
                    }

                    this.$nextTick(() => {
                        this.toggleStateClasses();
                    })
                },
                executeCommand(id) {
                    this.$wire.executeCommand(this.commands.find((command) => {
                        return command.id === (id ? id : this.commands[this.selectedCommandIndex].id);
                    }).id);
                },
                toggleStateClasses() {
                    for (const child of this.$refs.results.children) {
                        child.classList.remove('bg-gray-100');
                    }

                    this.$refs.results.children[this.selectedCommandIndex].classList.add('bg-gray-100');

                    this.$refs.results.children[this.selectedCommandIndex].scrollIntoView({
                        block: 'nearest',
                    });
                }
            };
        }"
        x-init="init()"
        x-show="isOpen"
    >
        <div
            class="fixed inset-0 bg-gray-500 bg-opacity-25 transition-opacity"
            @click="toggleOpen()"
            x-show="isOpen"
            x-transition:enter-end="opacity-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter="ease-out duration-300"
            x-transition:leave-end="opacity-0"
            x-transition:leave-start="opacity-100"
            x-transition:leave="ease-in duration-200"
        ></div>

        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
            <div class="flex min-h-full items-center justify-center">
                <div
                    class="mx-auto w-full max-w-2xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all"
                    @click.away="forceCloseModal()"
                    @close.stop="forceCloseModal()"
                    x-show="isOpen"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                >
                    <div class="relative">
                        <x-heroicons:outline-magnifying-glass
                            class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                        />

                        <input
                            class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                            type="text"
                            role="combobox"
                            aria-controls="options"
                            aria-expanded="false"
                            placeholder="Search..."
                            wire:model="searchQuery"
                            @keydown.tab.prevent=""
                            @keydown.prevent.stop.enter="executeCommand()"
                            @keydown.prevent.arrow-up="goToPrevious()"
                            @keydown.prevent.arrow-down="goToNext()"
                            x-ref="input"
                        >
                    </div>

                    @if ($command)
                        {!! $command !!}
                    @elseif (count($commands) > 0)
                        <ul
                            class="max-h-96 scroll-py-3 overflow-y-auto p-3"
                            role="listbox"
                            x-ref="results"
                        >
                            @foreach ($commands as $command)
                                <li
                                    class="group flex cursor-default select-none rounded-xl p-3 hover:bg-gray-100"
                                    role="option"
                                    tabindex="-1"
                                    wire:key="{{ $command['id'] }}"
                                    wire:click="executeCommand('{{ $command['id'] }}')"
                                >
                                    <div
                                        class="bg-{{ $command['iconColor'] }}-500 flex h-10 w-10 flex-none items-center justify-center rounded-lg">
                                        <x-dynamic-component
                                            class="h-6 w-6 text-white"
                                            :component="$command['icon']"
                                        />
                                    </div>

                                    <div class="ml-4 flex-auto">
                                        <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                            {{ $command['name'] }}
                                        </p>

                                        <p class="text-sm text-gray-500 group-hover:text-gray-700">
                                            {{ $command['description'] }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-6 py-14 text-center text-sm sm:px-14">
                            <svg
                                class="mx-auto h-6 w-6 text-gray-400"
                                aria-hidden="true"
                                fill="none"
                                stroke-width="1.5"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <p class="mt-4 font-semibold text-gray-900">No results found</p>
                            <p class="mt-2 text-gray-500">No components found for this search term. Please try again.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
