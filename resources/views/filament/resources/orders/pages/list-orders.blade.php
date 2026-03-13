<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::callout
            icon="heroicon-o-information-circle"
            color="info"
        >
            <x-slot name="heading">
                Pusat Monitoring Pesanan Pelanggan
            </x-slot>

            <x-slot name="description">
                Halaman ini menampilkan seluruh pesanan pelanggan yang sudah masuk ke sistem dari proses checkout untuk menjaga konsistensi data item, promo, pembayaran, pengiriman, dan histori transaksi.
            </x-slot>

            <x-slot name="footer">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    <li>Gunakan tab status untuk memantau progres pesanan dari <strong>pending</strong> sampai <strong>delivered</strong> atau <strong>cancelled</strong>.</li>
                    <li>Manfaatkan filter, grouping, dan pencarian tabel untuk audit transaksi atau investigasi pesanan tertentu.</li>
                    <li>Buka detail order untuk memverifikasi pelanggan, item, pembayaran, alamat, dan pengiriman sebelum melakukan perubahan data.</li>
                </ul>
            </x-slot>
        </x-filament::callout>

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
    </div>
</x-filament-panels::page>
