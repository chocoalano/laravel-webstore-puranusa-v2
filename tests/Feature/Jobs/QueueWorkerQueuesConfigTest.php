<?php

it('uses database queue worker defaults that include whatsapp queue', function (): void {
    $configuredQueue = (string) config('queue.connections.database.queue');
    $queues = array_values(array_filter(array_map(
        static fn (string $queue): string => trim($queue),
        explode(',', $configuredQueue),
    )));

    expect($queues)->toContain('default')
        ->and($queues)->toContain('whatsapp');
});
