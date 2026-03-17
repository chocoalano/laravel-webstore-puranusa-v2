<?php

namespace App\Filament\Widgets\WhatsAppBroadcasts;

use App\Models\WhatsAppBroadcast;
use App\Services\QontactService;
use Illuminate\View\View;
use Livewire\Component;

class WhatsAppOutboundLogWidget extends Component
{
    public ?string $selectedBroadcastId = null;

    /** @var list<array<string, mixed>> */
    public array $logs = [];

    public ?string $errorMessage = null;

    public bool $isLoading = false;

    public function mount(): void
    {
        $latest = WhatsAppBroadcast::query()->latest()->first();

        if ($latest) {
            $this->selectedBroadcastId = (string) $latest->id;
            $this->loadLog();
        }
    }

    public function updatedSelectedBroadcastId(): void
    {
        $this->logs = [];
        $this->errorMessage = null;

        if ($this->selectedBroadcastId) {
            $this->loadLog();
        }
    }

    public function loadLog(): void
    {
        if (! $this->selectedBroadcastId) {
            return;
        }

        $this->isLoading = true;
        $this->logs = [];
        $this->errorMessage = null;

        $broadcast = WhatsAppBroadcast::query()->find($this->selectedBroadcastId);

        if (! $broadcast || ! $broadcast->template_id) {
            $this->errorMessage = 'Broadcast tidak ditemukan atau tidak memiliki Template ID Qontak.';
            $this->isLoading = false;

            return;
        }

        $result = app(QontactService::class)->getWhatsAppBroadcastLog((string) $broadcast->template_id);
        $this->logs = $result['data'];
        $this->errorMessage = $result['error'];
        $this->isLoading = false;
    }

    /** @return array<string, string> */
    public function getBroadcastsProperty(): array
    {
        return WhatsAppBroadcast::query()
            ->latest()
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (WhatsAppBroadcast $b): array => [
                (string) $b->id => "[#{$b->id}] {$b->title} — {$b->status}",
            ])
            ->all();
    }

    public function render(): View
    {
        return view('filament.widgets.whats-app-outbound-log-widget');
    }
}
