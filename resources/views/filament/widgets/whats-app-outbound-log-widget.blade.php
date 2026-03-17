<div class="space-y-4 p-4">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-60">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Pilih Broadcast
            </label>
            <select
                wire:model.live="selectedBroadcastId"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500"
            >
                <option value="">-- Pilih Broadcast --</option>
                @foreach ($this->broadcasts as $id => $label)
                    <option value="{{ $id }}" @selected($selectedBroadcastId === (string) $id)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <button
            wire:click="loadLog"
            wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
        >
            <x-heroicon-o-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" />
            Refresh Log
        </button>
    </div>

    @if ($errorMessage)
        <div class="rounded-lg border border-danger-200 bg-danger-50 dark:bg-danger-950/30 dark:border-danger-900/40 px-4 py-3 text-sm text-danger-700 dark:text-danger-300">
            {{ $errorMessage }}
        </div>
    @elseif ($isLoading)
        <div class="text-sm text-gray-500 dark:text-gray-400">Memuat log...</div>
    @elseif (empty($logs))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Tidak ada data log untuk broadcast ini.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penerima</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor WA</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dikirim</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach ($logs as $log)
                        @php
                            $status = strtolower((string) ($log['status'] ?? $log['message_status'] ?? '-'));
                            $isSuccess = in_array($status, ['sent', 'delivered', 'read', 'success']);
                            $isFailed = in_array($status, ['failed', 'undelivered', 'error']);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $log['to_name'] ?? $log['recipient_name'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">
                                {{ $log['to_number'] ?? $log['phone_number'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300' => $isSuccess,
                                    'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300' => $isFailed,
                                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => ! $isSuccess && ! $isFailed,
                                ])>
                                    {{ strtoupper($status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $log['created_at'] ?? $log['sent_at'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                {{ $log['error'] ?? $log['error_message'] ?? $log['description'] ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-2 text-xs text-gray-500 dark:text-gray-400">
                Total: {{ count($logs) }} pesan
            </div>
        </div>
    @endif
</div>
