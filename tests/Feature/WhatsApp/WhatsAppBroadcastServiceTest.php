<?php

use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppBroadcastRecipient;
use App\Repositories\WhatsApp\Contracts\WhatsAppBroadcastRepositoryInterface;
use App\Services\QontactService;
use App\Services\WhatsApp\WhatsAppBroadcastService;
use Illuminate\Support\Collection;
use Mockery as M;

it('sends broadcast recipient using configured header image url fallback', function (): void {
    config()->set('services.qontak.broadcast_header_image_url', '');
    config()->set('services.qontak.wd_approved_header_image_url', 'https://cdn.example.test/logo.png');

    $broadcast = new WhatsAppBroadcast;
    $broadcast->forceFill([
        'id' => 101,
        'template_id' => 'tmpl-001',
        'message' => 'Promo test',
    ]);

    $recipient = new WhatsAppBroadcastRecipient;
    $recipient->forceFill([
        'id' => 202,
        'broadcast_id' => 101,
        'customer_name' => 'Tester Broadcast',
        'normalized_phone' => '6281234567890',
    ]);

    $repository = M::mock(WhatsAppBroadcastRepositoryInterface::class);
    $repository->shouldReceive('findById')
        ->once()
        ->with(101)
        ->andReturn($broadcast);
    $repository->shouldReceive('markProcessing')
        ->once()
        ->with($broadcast);
    $repository->shouldReceive('countRecipients')
        ->once()
        ->with(101)
        ->andReturn(1);
    $repository->shouldReceive('getPendingRecipients')
        ->once()
        ->with(101)
        ->andReturn(new Collection([$recipient]));
    $repository->shouldReceive('markRecipientProcessing')
        ->once()
        ->with(202);
    $repository->shouldReceive('markRecipientSent')
        ->once()
        ->with(202, 'Sent via Qontak (HTTP 201)');
    $repository->shouldReceive('summarizeRecipientStats')
        ->once()
        ->with(101)
        ->andReturn([
            'total' => 1,
            'success' => 1,
            'failed' => 0,
            'pending' => 0,
        ]);
    $repository->shouldReceive('markCompleted')
        ->once()
        ->with($broadcast, 1, 1, 0, null);

    $qontactService = M::mock(QontactService::class);
    $qontactService->shouldReceive('sendWhatsAppWithResultFromParams')
        ->once()
        ->with(
            'Tester Broadcast',
            '6281234567890',
            'tmpl-001',
            ['Tester Broadcast', 'Promo test'],
            'id',
            'https://cdn.example.test/logo.png',
        )
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [],
        ]);

    $service = new WhatsAppBroadcastService($repository, $qontactService);

    $service->process(101);

    expect(true)->toBeTrue();
});

it('falls back to png header when configured header extension is unsupported', function (): void {
    config()->set('services.qontak.broadcast_header_image_url', '');
    config()->set('services.qontak.wd_approved_header_image_url', 'https://puranusa.id/assets/logo-puranusa.webp');

    $broadcast = new WhatsAppBroadcast;
    $broadcast->forceFill([
        'id' => 201,
        'template_id' => 'tmpl-002',
        'message' => 'Promo test 2',
    ]);

    $recipient = new WhatsAppBroadcastRecipient;
    $recipient->forceFill([
        'id' => 302,
        'broadcast_id' => 201,
        'customer_name' => 'Tester Fallback',
        'normalized_phone' => '6281234567891',
    ]);

    $repository = M::mock(WhatsAppBroadcastRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with(201)->andReturn($broadcast);
    $repository->shouldReceive('markProcessing')->once()->with($broadcast);
    $repository->shouldReceive('countRecipients')->once()->with(201)->andReturn(1);
    $repository->shouldReceive('getPendingRecipients')->once()->with(201)->andReturn(new Collection([$recipient]));
    $repository->shouldReceive('markRecipientProcessing')->once()->with(302);
    $repository->shouldReceive('markRecipientSent')->once()->with(302, 'Sent via Qontak (HTTP 201)');
    $repository->shouldReceive('summarizeRecipientStats')->once()->with(201)->andReturn([
        'total' => 1,
        'success' => 1,
        'failed' => 0,
        'pending' => 0,
    ]);
    $repository->shouldReceive('markCompleted')->once()->with($broadcast, 1, 1, 0, null);

    $qontactService = M::mock(QontactService::class);
    $qontactService->shouldReceive('sendWhatsAppWithResultFromParams')
        ->once()
        ->with(
            'Tester Fallback',
            '6281234567891',
            'tmpl-002',
            ['Tester Fallback', 'Promo test 2'],
            'id',
            'https://puranusa.id/logo.png',
        )
        ->andReturn([
            'success' => true,
            'status' => 201,
            'error' => null,
            'body' => [],
        ]);

    $service = new WhatsAppBroadcastService($repository, $qontactService);
    $service->process(201);

    expect(true)->toBeTrue();
});
