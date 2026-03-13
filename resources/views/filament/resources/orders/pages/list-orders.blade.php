<x-filament-panels::page>
    <div
        x-data="{
            isLoading: false,
            pendingTabCommits: 0,
            cleanupCommitHook: null,
            init() {
                const componentId = this.$root.closest('[wire\\:id]')?.getAttribute('wire:id')

                this.cleanupCommitHook = window.Livewire?.hook('commit', ({ component, commit, succeed, fail }) => {
                    if (! componentId || component?.id !== componentId) {
                        return
                    }

                    if (! Object.prototype.hasOwnProperty.call(commit?.updates ?? {}, 'activeTab')) {
                        return
                    }

                    this.pendingTabCommits += 1
                    this.isLoading = true

                    const stopLoading = () => {
                        this.pendingTabCommits = Math.max(0, this.pendingTabCommits - 1)
                        this.isLoading = this.pendingTabCommits > 0
                    }

                    succeed(() => {
                        requestAnimationFrame(() => {
                            queueMicrotask(stopLoading)
                        })
                    })

                    fail(stopLoading)
                })
            },
            destroy() {
                this.cleanupCommitHook?.()
            },
        }"
        class="relative"
    >
        <div
            x-cloak
            x-show="isLoading"
            x-transition.opacity.duration.150ms
            class="pointer-events-auto absolute inset-x-0 bottom-0 top-16 z-20 rounded-xl bg-white/70 backdrop-blur-sm dark:bg-gray-950/70"
        >
            <div
                role="status"
                aria-live="polite"
                class="absolute left-1/2 top-1/2 inline-flex -translate-x-1/2 -translate-y-1/2 items-center gap-3 rounded-xl bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-100 dark:ring-white/10"
            >
                <x-filament::loading-indicator class="h-5 w-5 text-primary-600" />
                <span>Memuat data pesanan...</span>
            </div>
        </div>

        <div
            x-bind:class="isLoading ? 'pointer-events-none opacity-60' : ''"
            class="transition-opacity duration-150"
        >
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page>
